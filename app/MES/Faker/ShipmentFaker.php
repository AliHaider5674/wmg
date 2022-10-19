<?php
namespace App\MES\Faker;

use App\Exceptions\NoRecordException;
use App\Models\Order;
use App\Models\OrderItem;
use App\MES\Handler\IO\FlatOrder;

/**
 * Generate a fake MES shipment file
 *
 * Class ShipmentFaker
 * @category WMG
 * @package  App\MES\Faker
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class ShipmentFaker extends Faker
{
    /**
     * Create fake shipments
     *
     * @param $orders
     * @param string $type
     *
     * @return array
     * @throws \App\Exceptions\NoRecordException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function fake($orders, $shippedPercent = 1, $override = [])
    {
        $data = [];
        $data[] = ['record_type' => 'DEAHDR'];
        $orderNumber = 0;
        $itemCount = 0;
        foreach ($orders as $order) {
            /**@var Order $order*/
            $orderNumber++;

            $orderHeader = [
                'record_type' => 'DEAHDR',
                'order_number' => $orderNumber,
                FlatOrder::ORDER_ID_FIELD => $order->id,
                FlatOrder::ORDER_NUMBER_FIELD => $order->getAttribute('order_id')
            ];
            if (isset($override['order'])) {
                $orderHeader = array_merge($orderHeader, $override['order']);
            }
            $data[] = $orderHeader;

            $orderLine = [
                'record_type' => 'DEAORF',
                'distribution_centre_code' => 'US',
                'trading_partner_name' => 'US_D2C',
                'order_number' => $orderNumber,
                FlatOrder::ORDER_ID_FIELD => $order->id,
                'orig_order_number' => $orderNumber,
                'child_order_number' => null,
                'carrier_number' => '1',
                'carrier_name' => 'usps'
            ];
            if (isset($override['item'])) {
                $orderLine = array_merge($orderLine, $override['item']);
            }
            $data[] = $orderLine;
            $orderLineNumber = 0;
            $currentOrderItemCount = 0;
            foreach ($order->orderItems as $orderItem) {
                /**@var OrderItem $orderItem */
                if (!$orderItem->is_shippable) {
                    continue;
                }

                if ($orderItem->getShouldShippedQty() <= 0) {
                    continue;
                }
                $currentOrderItemCount++;
                $orderLineNumber++;
                $itemCount++;
                $itemLine = [
                    'record_type' => 'DEADTL',
                    'distribution_centre_code' => 'US',
                    'trading_partner_name' => 'US_D2C',
                    'order_number' => $orderNumber,
                    'order_line_number' => $orderLineNumber,
                    'item_number' => $orderItem->sku,
                    FlatOrder::ORDER_ITEM_ID_FIELD => $orderItem->id,
                    'order_quantity' => intval($orderItem->quantity),
                    'allocated_quantity' => intval($orderItem->quantity),
                    'nve' => '92612927005044'. $this->dataFaker->randomNumber(8),
                    'catalogue_item_barcode' => $orderItem->sku,
                    'customer_order_line_ref' => $orderItem->id,
                    'end_customer_order_line_ref' => $orderItem->getAttribute('order_line_id'),
                    'customer_order_reference' => $order->id,
                    'customer_e_order_reference' => $order->getAttribute('order_id')
                ];

                $itemLine['expected_delivery_quantity'] = intval($orderItem->getShouldShippedQty() * $shippedPercent);
                if ($shippedPercent == 0) {
                    $itemLine['backorder_reason_code'] = '4';
                    $itemLine['backorder_quantity'] = intval($orderItem->getShouldShippedQty());
                    $itemLine['expected_delivery_quantity'] = 0;
                }
                $data[] = $itemLine;
            }

            //Step back if no item
            if ($currentOrderItemCount<=0) {
                unset($data[count($data) - 1]);
            }
        }

        if ($itemCount<=0) {
            throw new NoRecordException('No items are ready to ship.');
        }
        $data[] = ['record_type' => 'MSGTRL'];
        $file = $this->outputData($data);
        return [
            'file' => $file,
            'count' => $itemCount
        ];
    }
}
