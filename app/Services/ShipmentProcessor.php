<?php

namespace App\Services;

use App\Core\Repositories\ShipmentRepository;
use App\Core\Services\EventService;
use App\Exceptions\NoRecordException;
use App\Models\OrderItem;
use App\Models\Service\Model\Shipment;
use App\Models\Service\Model\Shipment\Package;
use App\Models\Service\Model\Shipment\Package\AggregatedItem;
use App\Models\Service\Model\ShipmentLineChange\Item as LineChangeItem;
use App\Models\Service\ModelBuilder\ShipmentBuilder;
use App\Models\Service\ModelBuilder\ShipmentLineChangeParameter;
use App\Models\Service\ModelBuilder\ShipmentParameter;
use App\Models\Service\ModelBuilder\ShipmentLineChangeBuilder;
use Exception;

/**
 * Class ShipmentProcessor
 * @package App\Services
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2022
 * @link     http://www.wmg.com
 */
class ShipmentProcessor
{
    protected ShipmentRepository $shipmentRepository;
    protected ShipmentBuilder $shipmentBuilder;
    protected EventService $eventManager;

    /**
     * @var ShipmentLineChangeBuilder
     */
    protected ShipmentLineChangeBuilder $shipmentLineChangeBuilder;


    public function __construct(
        EventService $eventManager,
        ShipmentRepository $shipmentRepository,
        ShipmentBuilder $shipmentBuilder,
        ShipmentLineChangeBuilder $shipmentLineChangeBuilder
    ) {
        $this->shipmentRepository = $shipmentRepository;
        $this->shipmentBuilder = $shipmentBuilder;
        $this->eventManager = $eventManager;

        $this->shipmentLineChangeBuilder = $shipmentLineChangeBuilder;
    }

    /**
     * Process shipment parameter
     * @param ShipmentParameter $parameter
     * @throws NoRecordException
     */
    public function processShipmentParameter(ShipmentParameter $parameter)
    {
        $shipmentModels = $this->shipmentBuilder->build($parameter);
        foreach ($shipmentModels as $shipmentModel) {
            $this->processShipment($shipmentModel);
            $this->eventManager->dispatchEvent(EventService::EVENT_ITEM_SHIPPED, $shipmentModel);
        }
    }

    /**
     * @param Shipment $shipmentModel
     * @return $this
     */
    public function processShipment(Shipment $shipmentModel)
    {
        foreach ($shipmentModel->packages as $package) {
            /** @var Package $package */
            /** @var AggregatedItem $item */

            $shipmentData = [
                'carrier' => $package->carrier,
                'tracking_number' => $package->trackingNumber,
                'order_id' => $shipmentModel->getHiddenOrderId(),
                'items' => []
            ];

            foreach ($package->aggregatedItems as $item) {
                foreach ($item->getHiddenLineQtyMap() as $itemMap) {
                    $shipmentData['items'][] = [
                        'order_item_id' => $itemMap['order_item_id'],
                        'quantity' => $itemMap['qty']
                    ];
                }
                $orderItems = OrderItem::where('parent_id', $shipmentModel->getHiddenOrderId())
                    ->whereIn('order_line_number', $item->orderLines)
                    ->whereRaw('quantity > quantity_shipped')
                    ->get();
                $shipped = $item->quantity;
                foreach ($orderItems as $orderItem) {
                    $orderItem->setAttribute('quantity_shipped', $shipped);

                    if ($shipped >= $orderItem->getShouldShippedQty()) {
                        $shipped -= $orderItem->getShouldShippedQty();
                        $orderItem->setAttribute('quantity_shipped', $orderItem->quantity);
                    }

                    $orderItem->save();
                }
            }
            if (!empty($shipmentData['carrier']) || !empty($shipmentData['tracking_number'])) {
                $this->shipmentRepository->createShipment($shipmentData);
            }
        }
        return $this;
    }

    /**
     * Process parameters
     * @param ShipmentLineChangeParameter $parameter
     * @return void
     * @throws Exception|NoRecordException
     */
    public function processAckParameter(ShipmentLineChangeParameter $parameter) : void
    {
        $models = $this->shipmentLineChangeBuilder->build($parameter);

        foreach ($models as $model) {
            foreach ($model->items as $item) {
                $this->processAckItem($item);
            }

            $this->eventManager->dispatchEvent(
                EventService::EVENT_ITEM_WAREHOUSE_ACK,
                $model
            );
        }
    }

    /**
     * Process item
     *
     * @param LineChangeItem $item
     *
     * @return $this
     * @throws Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function processAckItem(LineChangeItem $item)
    {
        /** @var OrderItem $orderItem */
        $orderItem = $item->getHiddenData('item');
        $ackQty = $orderItem->getAttribute('quantity_ack') ? : 0;
        $ackQty += (float) $item->quantity;
        $backorderQty = $orderItem->getAttribute('quantity_backordered') ? : 0;
        $backorderQty += (float) $item->backorderQuantity;
        $orderItem->setAttribute('quantity_ack', $ackQty);
        $orderItem->setAttribute('quantity_backordered', $backorderQty);
        $orderItem->save();
        return $this;
    }
}
