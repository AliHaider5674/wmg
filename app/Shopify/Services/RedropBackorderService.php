<?php

namespace App\Shopify\Services;

use App\Core\Services\EventService;
use App\Models\OrderItem;
use App\Models\Service\Model\ShipmentRequest;

/**
 * Class RedropBackorderService
 * @package App\Shopify\Services
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2021
 * @link     http://www.wmg.com
 */
class RedropBackorderService
{
    private OrderItem $orderItem;
    private EventService $eventManager;

    public function __construct(
        OrderItem $orderItem,
        EventService $eventManager
    ) {
        $this->orderItem = $orderItem;
        $this->eventManager = $eventManager;
    }

    /**
     * getFulfillmentOrdersToRedropBySku
     * @param array $skus
     */
    public function getFulfillmentOrdersToRedropBySku(array $skus)
    {
        $shipmentRequests = array();

        //query order items for any backorders that have stock updated
        if (!empty($skus)) {
            $items = $this->orderItem->query()
                ->select(['orders.request_id', 'orders.order_id','order_items.order_line_id'])
                ->join('orders', 'order_items.parent_id', '=', 'orders.id')
                ->where('quantity_backordered', '>=', 0)
                ->whereIn('order_items.sku', $skus)
                ->get();

            foreach ($items as $item) {
                if (!array_key_exists($item->request_id, $shipmentRequests)) {
                    $shipmentRequests[$item->request_id] = new ShipmentRequest($item->request_id);
                    $shipmentRequests[$item->request_id]->setHiddenOrderId($item->order_id);
                }

                $shipmentRequests[$item->request_id]->addItem($item->order_line_id);
            }
        }

        foreach ($shipmentRequests as $request) {
            $this->eventManager->dispatchEvent(
                EventService::EVENT_ITEM_SHIPMENT_REQUEST,
                $request
            );
        }
    }
}
