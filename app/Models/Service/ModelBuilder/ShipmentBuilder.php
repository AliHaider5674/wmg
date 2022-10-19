<?php
namespace App\Models\Service\ModelBuilder;

use App\Exceptions\NoRecordException;
use App\Models\OrderItem;
use App\Models\Service\Model\Shipment;
use App\Models\Order;

/**
 * A builder that build
 * shipment requests for external services
 *
 * Class ShipmentBuilder
 * @category WMG
 * @package  App\Models\Service\ModelBuilder
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class ShipmentBuilder
{
    /**
     * @var string
     */
    protected String $orderIdFilterField = 'id';

    /**
     * @var string
     */
    protected String $orderItemIdFilterField = 'id';

    /**
     * Magento order increment_id
     */
    const FIELD_ORDER_ID = 'order_id';


    /**
     * setOrderIdField
     * Set field which to filter orders by
     */
    public function setOrderIdField($field)
    {
        $this->orderIdFilterField = $field;
    }


    /**
     * Build shipment model that send to service endpoint
     * Shipment are grouped by tracking number
     *
     * @param \App\Models\Service\ModelBuilder\ShipmentParameter $parameter
     * @return \App\Models\Service\Model\Shipment []
     * @throws NoRecordException
     */
    public function build(ShipmentParameter $parameter)
    {
        $shipments = [];
        $order = Order::where($this->orderIdFilterField, '=', $parameter->orderId)->firstOrFail();
        $itemCount = 0;
        foreach ($parameter->packages as $package) {
            $shipment = new Shipment();
            $shipment->setHiddenOrderId($order->id);
            $shipment->setHiddenOrderNumber($order->getAttribute('order_id'));
            $newPackageModel = $shipment->newPackage();
            $newPackageModel->fill($package->toArray(true), true);
            $newPackageModel->id = 1;
            $orderItems = OrderItem::whereIn($this->orderItemIdFilterField, $package->itemIds)->get();
            $items = [];
            foreach ($orderItems as $orderItem) {
                /** @var OrderItem $orderItem */
                $shippedQty = $package->shippedQtyMap[$orderItem->id];
                if ($shippedQty <= 0) {
                    continue;
                }
                $items[] = $orderItem->getAttribute('order_line_number');
                $itemCount++;
                $aggregateId = $orderItem->getAttribute('aggregated_line_id');
                $newAggregatedItem = $newPackageModel->newAggregatedItem($aggregateId);
                $newAggregatedItem->sku = $orderItem->sku;
                $markedShippedQty = $shippedQty;
                $package->shippedQtyMap[$orderItem->id] -= $markedShippedQty;
                $newAggregatedItem->quantity += $markedShippedQty;
                $newAggregatedItem->addOrderLines($orderItem->getAttribute('order_line_number'));
                $newAggregatedItem->addHiddenLineQtyMap($orderItem->getAttribute('order_line_id'), $markedShippedQty);
                /**@var OrderItem $orderItem */
                $newItemModel = $shipment->newItem();
                $newItemModel->fill($orderItem->getAttributes(), false);
                $newItemModel->setHiddenData('item', $orderItem);

                if (!isset($shipment->requestId)) {
                    $shipment->requestId = $orderItem->order->getAttribute('request_id');
                }
            }
            $newPackageModel->items = $items;
            $shipments[] = $shipment;
        }

        if ($itemCount<=0) {
            throw new NoRecordException('No Order item found.');
        }

        return $shipments;
    }
}
