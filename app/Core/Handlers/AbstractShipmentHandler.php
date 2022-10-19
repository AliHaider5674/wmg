<?php

namespace App\Core\Handlers;

use App\Core\Handlers\IO\IOInterface;
use App\Core\Handlers\Traits\ItemRollback;
use App\Core\Repositories\ShipmentRepository;
use App\Core\Services\EventService;
use App\Exceptions\NoRecordException;
use App\Models\OrderItem;
use App\Models\Service\Model\Shipment;
use App\Models\Service\ModelBuilder\ShipmentBuilder;
use App\Models\Service\ModelBuilder\ShipmentParameter;
use Illuminate\Support\Facades\Log;

/**
 * Handle shipments
 *
 * Class ShipmentHandler
 * @category WMG
 * @package  App\Models\Warehouse\Handler
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 * @changed
 */
abstract class AbstractShipmentHandler extends AbstractHandler
{
    use ItemRollback;
    protected ShipmentRepository $shipmentRepository;
    protected ShipmentBuilder $shipmentBuilder;
    protected EventService $eventManager;
    abstract public function handle() : void;

    public function __construct(
        IOInterface $ioAdapter,
        EventService $eventManager,
        ShipmentRepository $shipmentRepository,
        ShipmentBuilder $shipmentBuilder,
        Log $logger
    ) {
        $this->shipmentRepository = $shipmentRepository;
        $this->shipmentBuilder = $shipmentBuilder;
        $this->eventManager = $eventManager;
        parent::__construct($ioAdapter, $logger);
    }

    /**
     * @param Shipment $shipmentModel
     * @return $this
     * @throws NoRecordException
     */
    public function process(Shipment $shipmentModel)
    {
        foreach ($shipmentModel->packages as $package) {
            /** @var \App\Models\Service\Model\Shipment\Package $package */
            /** @var \App\Models\Service\Model\Shipment\Package\AggregatedItem $item */

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
                    $this->recordProcessed($orderItem);
                }
            }
            if (!empty($shipmentData['carrier']) || !empty($shipmentData['tracking_number'])) {
                $shipment = $this->shipmentRepository->createShipment($shipmentData);
                $this->recordProcessed($shipment);
            }
        }
        return $this;
    }

    /**
     * Process shipment parameter
     * @param \App\Models\Service\ModelBuilder\ShipmentParameter $parameter
     * @return $this
     * @throws \App\Exceptions\NoRecordException
     */
    public function processShipmentParameter(ShipmentParameter $parameter)
    {
        $shipmentModels = $this->shipmentBuilder->build($parameter);
        foreach ($shipmentModels as $shipmentModel) {
            $this->process($shipmentModel);
            $this->eventManager->dispatchEvent(EventService::EVENT_ITEM_SHIPPED, $shipmentModel);
        }
        return $this;
    }
}
