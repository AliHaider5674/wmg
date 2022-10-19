<?php
namespace App\Models\Service\Event;

use App\Models\Order;
use App\Models\Service\Model\Serialize;
use App\Models\Service\Model\Shipment;
use App\Models\Service\Model\ShipmentLineChange;

/**
 * Validator that validate service event rules
 * with a given request model
 *
 * Class ServiceRuleValidator
 * @category WMG
 * @package  App\Models\Service\Event
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class MetaDataExtractor
{
    const META_SALES_CHANNEL = 'sales_channel';
    const META_ORDER_ID = 'order_id';

    /**
     * Get Model Meta data
     * TODO, improve the speed
     *       Need to use cache
     * @param \App\Models\Service\Model\Serialize $serviceModel
     * @return array
     */
    public function getMetaData(Serialize $serviceModel)
    {
        $metaData = [];
        if ($serviceModel instanceof Shipment || $serviceModel instanceof ShipmentLineChange) {
            $orderId = $serviceModel->getHiddenOrderId();
            $order = Order::where('id', '=', $orderId)->first();
            if ($order) {
                $metaData[static::META_SALES_CHANNEL] = $order->getAttribute('sales_channel');
                $metaData[static::META_ORDER_ID] = $order->getAttribute('order_id');
            }
        }
        return $metaData;
    }
}
