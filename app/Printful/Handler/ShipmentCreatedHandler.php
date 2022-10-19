<?php

namespace App\Printful\Handler;

use App\Core\Handlers\AbstractShipmentHandler;
use App\Core\Repositories\ShipmentRepository;
use App\Core\Services\EventService;
use App\Exceptions\NoRecordException;
use App\Models\Service\ModelBuilder\ShipmentBuilder;
use App\Models\Service\ModelBuilder\ShipmentParameter;
use App\Printful\Exceptions\PrintfulException;
use App\Printful\Handler\IO\PrintfulShipmentCreated;
use App\Printful\Handler\Traits\ProcessesPrintfulEvent;
use App\Printful\Repositories\PrintfulLogRepository;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Class ShipmentCreatedHandlerHandler
 *
 * Handle ShipmentCreatedHandler Events
 * @property PrintfulShipmentCreated $ioAdapter
 */
class ShipmentCreatedHandler extends AbstractShipmentHandler
{
    use ProcessesPrintfulEvent;

    /**
     * @var PrintfulLogRepository
     */
    private $logRepository;

    /**
     * ShipmentCreatedHandler constructor.
     * @param PrintfulShipmentCreated   $ioAdapter
     * @param EventService              $eventManager
     * @param Log                       $logger
     * @param ShipmentBuilder           $shipmentBuilder
     * @param PrintfulLogRepository     $logRepository
     */
    public function __construct(
        PrintfulShipmentCreated $ioAdapter,
        EventService $eventManager,
        Log $logger,
        ShipmentBuilder $shipmentBuilder,
        PrintfulLogRepository $logRepository,
        ShipmentRepository $shipmentRepository
    ) {

        parent::__construct(
            $ioAdapter,
            $eventManager,
            $shipmentRepository,
            $shipmentBuilder,
            $logger
        );

        $this->shipmentBuilder = $shipmentBuilder;
        $this->logRepository = $logRepository;
    }

    /**
     * Handle digital item shipment
     *
     * @return void
     * @throws Exception
     */
    public function handle(): void
    {
        try {
            $this->removeAllRecordedProcessed();
            $this->ioAdapter->start();
            $this->ioAdapter->receive([$this, 'processShipmentParameter']);
            $this->ioAdapter->finish();
        } catch (\Throwable $e) {
            $this->currentEventFailed(
                "Event failed processing. Error: " . $e->getMessage()
            );
            throw $e;
        }
    }

    /**
     * @param ShipmentParameter $parameter
     * @return $this
     * @throws NoRecordException
     * @throws PrintfulException
     */
    public function processShipmentParameter(ShipmentParameter $parameter): self
    {
        $shipmentModels = $this->shipmentBuilder->build($parameter);
        foreach ($shipmentModels as $shipmentModel) {
            $this->process($shipmentModel);
            $this->eventManager->dispatchEvent(EventService::EVENT_ITEM_SHIPPED, $shipmentModel);
        }

        $this->currentEventSucceeded("Shipment created event processed");

        return $this;
    }

    public function validate()
    {
        return true;
    }
}
