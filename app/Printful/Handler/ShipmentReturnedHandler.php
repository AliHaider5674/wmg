<?php

namespace App\Printful\Handler;

use App\Core\Handlers\AckHandler;
use App\Core\Services\EventService;
use App\Models\OrderItem;
use App\Models\Service\Model\ShipmentLineChange\Item as LineChangeItem;
use App\Models\Service\ModelBuilder\ShipmentLineChangeBuilder;
use App\Printful\Exceptions\PrintfulException;
use App\Printful\Handler\IO\PrintfulShipmentReturned;
use App\Printful\Handler\Traits\ProcessesPrintfulEvent;
use App\Printful\Repositories\PrintfulLogRepository;
use Illuminate\Support\Facades\Log;
use App\Models\Service\ModelBuilder\ShipmentLineChangeParameter;
use App\Exceptions\NoRecordException;
use Exception;

/**
 * Class ShipmentReturnedHandler
 *
 * Handle ShipmentReturned Events
 */
class ShipmentReturnedHandler extends AckHandler
{
    use ProcessesPrintfulEvent;

    /**
     * @var PrintfulLogRepository
     */
    private $logRepository;

    /**
     * ShipmentReturnedHandler constructor.
     * @param PrintfulShipmentReturned  $ioAdapter
     * @param EventService              $eventManager
     * @param ShipmentLineChangeBuilder $shipmentLineChangeBuilder
     * @param Log                       $logger,
     * @param PrintfulLogRepository     $logRepository
     */
    public function __construct(
        PrintfulShipmentReturned $ioAdapter,
        EventService $eventManager,
        ShipmentLineChangeBuilder $shipmentLineChangeBuilder,
        Log $logger,
        PrintfulLogRepository $logRepository
    ) {
        parent::__construct(
            $ioAdapter,
            $eventManager,
            $shipmentLineChangeBuilder,
            $logger
        );

        $this->logRepository = $logRepository;
    }

    /**
     * Process parameters
     * @param ShipmentLineChangeParameter $parameter
     * @return void
     * @throws Exception
     */
    protected function processParameter(ShipmentLineChangeParameter $parameter): void
    {
        try {
            parent::processParameter($parameter);
        } catch (NoRecordException $e) {
            $this->currentEventSoftFailed(
                "Event failed processing. Error: " . $e->getMessage()
            );
            throw $e;
        } catch (Exception $e) {
            $this->currentEventFailed(
                "Event failed processing. Error: " . $e->getMessage()
            );
            throw $e;
        }
        $this->currentEventSucceeded("Shipment return event processed");
    }

    /**
     * @param LineChangeItem $item
     * @return $this
     * @throws \Exception
     */
    public function processAckItem(LineChangeItem $item)
    {
        parent::processAckItem($item);

        /** @var OrderItem $orderItem */
        $orderItem = $item->getHiddenData('item');
        $orderItem->setAttribute('quantity_returned', $item->quantity);
        $orderItem->save();
    }
}
