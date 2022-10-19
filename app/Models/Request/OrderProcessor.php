<?php
namespace App\Models\Request;

use App\Core\Exceptions\Mutators\ValidationException;
use App\Exceptions\OrderReceiveException;
use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\OrderItem;
use App\Exceptions\RecordExistException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Psr\Log\InvalidArgumentException;
use App\Events\OrderReceiveFailed;

/**
 * Process order request
 *
 * Class OrderProcessor
 * @category WMG
 * @package  App\Models\Request
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class OrderProcessor implements ProcessorInterface
{
    private $validator;
    public function __construct(Validator $validator)
    {
        $this->validator = $validator;
    }
    /**
     * Save request data
     * @param $data
     * @return $this
     * @throws \App\Exceptions\RecordExistException
     * @SuppressWarnings(PHPMD)
     * @todo fix PHPMD warnings
     */
    public function save($data)
    {

        try {
            $this->validateData($data);
            $orders = Order::where('request_id', $data['request_id'])
                ->where('sales_channel', $data['sales_channel'])
                ->get();
            if ($orders->count() > 0) {
                throw new RecordExistException('Request id already exist');
            }
            $order = null;
            DB::transaction(function () use ($data, &$order) {

                $order = $this->getOrder($data);
                $order->save();

                //shipping address
                $shippingAddress = new OrderAddress();

                //inject customer_address_type value shipping, to identify address type
                if (isset($data['shipping_address'])) {
                    $data['shipping_address'][OrderAddress::CUSTOMER_ADDRESS_TYPE_FIELD]
                        = OrderAddress::CUSTOMER_ADDRESS_TYPE_SHIPPING;
                }

                $shippingAddress->fill($data['shipping_address']);
                $shippingAddress->setAttribute('parent_id', $order->getAttribute('id'));
                $shippingAddress->save();

                //billing address
                $billingAddress = new OrderAddress();

                //inject customer_address_type value shipping, to identify address type
                if (isset($data['billing_address'])) {
                    $data['billing_address'][OrderAddress::CUSTOMER_ADDRESS_TYPE_FIELD]
                        = OrderAddress::CUSTOMER_ADDRESS_TYPE_BILLING;
                }

                $billingAddress->fill($data['billing_address']);
                $billingAddress->setAttribute('parent_id', $order->getAttribute('id'));
                $billingAddress->save();

                $aggregatedMap = [];
                foreach ($data['aggregated_items'] as $aggregatedItem) {
                    if (isset($aggregatedItem['quantity_detail'])) {
                        foreach ($aggregatedItem['order_lines'] as $lineNumber) {
                            if (!isset($aggregatedItem['quantity_detail'][$lineNumber])) {
                                throw new OrderReceiveException(
                                    'Item line number '
                                    . $lineNumber
                                    . ' is not in quantity_detail for aggregated_line_id '
                                    . $aggregatedItem['aggregated_line_id']
                                    . ' in order '
                                    . $data['order_id'] .' from '
                                    . $data['sales_channel'],
                                    OrderReceiveException::QUANTITY_AGGREGATE_ERROR
                                );
                            }

                            $aggregatedMap[$lineNumber] = [
                                'id' => $aggregatedItem['aggregated_line_id'],
                                'quantity' => $aggregatedItem['quantity_detail'][$lineNumber]
                            ];
                        }

                        continue;
                    }

                    $lineCount = count($aggregatedItem['order_lines']);

                    //Extract quantity from aggregate
                    //Quick fix for MCD-1803
                    //@TO DO: Make this to support same sku in multiple line with different quantity
                    //This is unlikely happen today.
                    if ($lineCount > 1 && $lineCount != $aggregatedItem['quantity']) {
                        $message = 'Unable extract quantity from aggregate section for order '
                                    . $data['order_id'] .' from '
                                    . $data['sales_channel'];
                        throw new OrderReceiveException($message, OrderReceiveException::QUANTITY_AGGREGATE_ERROR);
                    } elseif ($lineCount > 1) {
                        $quantity = 1;
                    } else {
                        $quantity = $aggregatedItem['quantity'];
                    }

                    foreach ($aggregatedItem['order_lines'] as $lineNumber) {
                        $aggregatedMap[$lineNumber] = [
                            'id' => $aggregatedItem['aggregated_line_id'],
                            'quantity' => $quantity
                        ];
                    }
                }

                foreach ($data['items'] as $itemData) {
                    if (!isset($aggregatedMap[$itemData['order_line_number']])) {
                        throw new InvalidArgumentException(
                            'Item line number '
                            . $itemData['order_line_number']
                            . ' is not in aggregated items'
                        );
                    }
                    $item = new OrderItem();
                    $itemData['aggregated_line_id'] = $aggregatedMap[$itemData['order_line_number']]['id'];
                    $itemData['quantity'] = 0;
                    if ($aggregatedMap[$itemData['order_line_number']]['quantity'] >= 0) {
                        $itemData['quantity'] = $aggregatedMap[$itemData['order_line_number']]['quantity'];
                    }
                    $item->fill($this->parseOrderItemData($itemData));
                    //If line item source not specified
                    if (!isset($itemData['source_id']) || empty($itemData['source_id'])) {
                        $item->setAttribute('source_id', $data['source_id'] ?? '');
                    }
                    $item->setCustomAttributes(isset($itemData['custom_details']) ? $itemData['custom_details'] : null);
                    $item->setAttribute('parent_id', $order->getAttribute('id'));
                    $item->save();
                }
            }, 3);

            //DO NOT TAKE TOO LONG FOR SUBSCRIBERS
            //OTHERWISE, WILL RESULT DATA LOCK
            event('internal.order.received', $order);
        } catch (OrderReceiveException $e) {
            if ($e->getCode() == OrderReceiveException::QUANTITY_AGGREGATE_ERROR) {
                event(new OrderReceiveFailed($data, $e));
            }
            throw $e;
        }

        return $this;
    }

    /**
     * @param $data
     * @throws \App\Core\Exceptions\Mutators\ValidationException
     */
    private function validateData($data)
    {
        $validator = $this->validator::make(
            $data,
            [
                'sales_channel' => 'required',
                'request_id' => 'required',
                'order_id' => 'required'
            ]
        );

        if ($validator->fails()) {
            $messages = ['Validation failed.'];
            if ($validator->errors()) {
                $messages = $validator->errors()->all();
            }
            throw new ValidationException($messages);
        }
    }

    /**
     * Parse order item data
     *
     * @param array $data
     * @return mixed
     */
    protected function parseOrderItemData(Array $data)
    {
        foreach ($data['order_line_price'] as $key => $value) {
            if (!is_array($value)) {
                $data[$key] = $value;
            }
        }
        return $data;
    }

    protected function getOrder(Array $data)
    {
        $order = new Order();
        $order->fill($data);
        $order->setCustomAttributes(isset($data['custom_details']) ? $data['custom_details'] : null);
        if (isset($data['shipping_price'])) {
            $order->setAttribute('shipping_net_amount', Arr::get($data, 'shipping_price.net_amount', 0));
            $order->setAttribute('shipping_gross_amount', Arr::get($data, 'shipping_price.gross_amount', 0));
            $order->setAttribute('shipping_tax_amount', Arr::get($data, 'shipping_price.tax_amount', 0));
            $order->setAttribute('shipping_tax_rate', Arr::get($data, 'shipping_price.tax_rate', 0));
            $order->setAttribute('shipping_tax_detail', json_encode(Arr::get($data, 'shipping_price.taxes', 0)));
        }
        return $order;
    }
}
