<?php

namespace App\MES\Handler;

use App\Core\Handlers\AbstractShipmentHandler;
use App\Core\Repositories\ShipmentRepository;
use App\Core\Services\EventService;
use App\MES\Handler\IO\FlatShipment;
use App\Models\Service\ModelBuilder\Shipment\ItemParameter;
use App\Models\Service\ModelBuilder\Shipment\PackageParameter;
use App\Models\Service\ModelBuilder\ShipmentParameter;
use App\Models\Service\ModelBuilder\ShipmentBuilder;
use App\Models\OrderItem;
use Exception;
use Illuminate\Support\Facades\Log;

/**
 * Handle digital product shipments
 *
 * Class DigitalHandler
 * @category WMG
 * @package  App\Models\Warehouse\Handler
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 * @changed
 */
class DigitalHandler extends AbstractShipmentHandler
{
    protected $name = 'digital';

    public function __construct(
        FlatShipment $ioAdapter,
        EventService $eventManager,
        ShipmentRepository $shipmentRepository,
        ShipmentBuilder $shipmentBuilder,
        Log $logger
    ) {
        parent::__construct($ioAdapter, $eventManager, $shipmentRepository, $shipmentBuilder, $logger);
    }


    /**
     * Handle digital item shipment
     *
     * @return void
     * @throws Exception
     */
    public function handle(): void
    {
        $this->removeAllRecordedProcessed();

        $digitalOrderItems = OrderItem::whereRaw('quantity > quantity_shipped')
            ->whereRaw('quantity > quantity_shipped')
            ->whereIn('item_type', OrderItem::ALL_DIGITAL_TYPES)
            ->get();

        /**@var OrderItem $digitalOrderItem*/
        foreach ($digitalOrderItems as $digitalOrderItem) {
            $this->processItem($digitalOrderItem);
        }
    }

    /**
     * Process item
     *
     * @param OrderItem $item
     *
     * @return $this
     * @throws Exception
     */
    public function processItem(OrderItem $item): self
    {
        $order = $item->order;

        $parameter = new ShipmentParameter();
        $parameter->orderId = $order->getAttribute('order_id');
        $parameter->addPackage(new PackageParameter());
        $shipmentItem = new ItemParameter();
        $shipmentItem->sku = $item->sku;
        $shipmentItem->orderItemId = $item->getAttribute('id');
        $shipmentItem->quantity = $item->quantity;
        $parameter->addItemToPackage($shipmentItem);
        $parameter->orderId = $item->getAttribute('parent_id');
        $shipmentModels = $this->shipmentBuilder->build($parameter);

        foreach ($shipmentModels as $shipmentModel) {
            $this->process($shipmentModel);
        }

        // Should this be outside of the foreach loop?
        $this->eventManager->dispatchEvent(EventService::EVENT_ITEM_SHIPPED, $shipmentModel);

        return $this;
    }

    public function validate()
    {
        return true;
    }
}
