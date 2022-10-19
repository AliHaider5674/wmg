<?php

namespace App\Shopify\Handlers\FetchShipmentOrder;

use App\Models\Order;
use App\Shopify\Models\ShopifyFailedFulfillmentOrder;

/**
 * Class FailOrderProcessor
 * @package App\Shopify\Handlers\FetchShipmentOrder
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2022
 * @link     http://www.wmg.com
 */
class FailOrderProcessor extends Processor
{
    /**
     * @inheritDoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function rejectOrder($client, $fulfillmentOrder, $message)
    {
    }

    /**
     * @inheritDoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function acceptOrder($client, $fulfillmentOrder)
    {
    }


    /**
     * handlePostSave
     * @param $order
     */
    public function handlePostSave($order)
    {
        ShopifyFailedFulfillmentOrder::where('fulfillment_order_id', $order['request_id'])->delete();
    }
}
