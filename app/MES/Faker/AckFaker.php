<?php
namespace App\MES\Faker;

use App\Exceptions\NoRecordException;
use App\Models\Order;
use App\Models\OrderItem;

/**
 * Generate a fake MES ack file
 *
 * Class AckFaker
 * @category WMG
 * @package  App\MES\Faker
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class AckFaker extends Faker
{
    /**
     * Create shipment
     *
     * @param $orders
     * @param $type
     * @return array
     * @throws \App\Exceptions\NoRecordException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function fake($orders, string $reasonCode = null, $override = [])
    {
        $data = [];
        $orderNumber = 0;
        $itemCount = 0;
        foreach ($orders as $order) {
            /**@var Order $order*/
            $orderNumber++;
            $orderLine = [
                'record_type' => 'T2W  H',
                'customer_order_reference' => $order->id,
                'additional_customer_reference' => $order->id,
            ];
            if (isset($override['order'])) {
                $orderLine = array_merge($orderLine, $override['order']);
            }
            $data[] = $orderLine;
            $orderLineNumber = 0;
            $currentOrderItemCount = 0;
            foreach ($order->orderItems as $orderItem) {
                /**@var OrderItem $orderItem */
                if (!$orderItem->is_shippable) {
                    continue;
                }

                if ($orderItem->getShouldAckQty()<=0) {
                    continue;
                }

                $currentOrderItemCount++;
                $orderLineNumber++;
                $itemCount++;
                $itemLine = [
                    'record_type' => 'T2W  L',
                    'item_number' => $orderItem->sku,
                    'customer_order_reference' => $order->id,
                    'reference_line_number' => $orderItem->id
                ];


                $itemLine['available_quantity'] = $orderItem->getShouldAckQty();
                $itemLine['order_quantity'] = $orderItem->getShouldAckQty();
                $itemLine['backorder_reason_code'] = $reasonCode;
                switch ($reasonCode) {
                    case '3':
                    case '4':
                        $itemLine['available_quantity'] = 0;
                        $itemLine['backorder_quantity'] = $orderItem->getShouldAckQty();
                        break;
                }

                if (isset($override['item'])) {
                    $itemLine = array_merge($itemLine, $override['item']);
                }
                $data[] = $itemLine;
            }

            //Step back if no item
            if ($currentOrderItemCount<=0) {
                unset($data[count($data) - 1]);
            }
        }

        if ($itemCount<=0) {
            throw new NoRecordException('No items are needed to be ack.');
        }

        $data[] = ['record_type' => 'MSGTRL'];

        $file = $this->outputData($data);
        return [
            'file' => $file,
            'count' => $itemCount
        ];
    }
}
