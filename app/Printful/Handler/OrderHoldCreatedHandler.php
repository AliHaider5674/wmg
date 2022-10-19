<?php

namespace App\Printful\Handler;

use App\Core\Handlers\AckHandler;
use App\Core\Services\EventService;
use App\Models\Service\ModelBuilder\ShipmentLineChangeBuilder;
use App\Models\Service\ModelBuilder\ShipmentLineChangeParameter;
use App\Printful\Handler\IO\PrintfulOrderHoldCreated;
use App\Printful\Handler\Traits\ProcessesPrintfulEvent;
use App\Printful\Repositories\PrintfulLogRepository;
use Illuminate\Support\Facades\Log;
use App\Exceptions\NoRecordException;
use Exception;

/**
 * Class OrderHoldCreatedHandlerHandler
 *
 * Handle OrderHoldCreatedHandler Events
 */
class OrderHoldCreatedHandler extends AckHandler
{
    use ProcessesPrintfulEvent;

    /**
     * @var PrintfulLogRepository
     */
    private $logRepository;

    /**
     * OrderHoldCreatedHandler constructor.
     *
     * @param PrintfulOrderHoldCreated  $ioAdapter
     * @param EventService              $eventManager
     * @param ShipmentLineChangeBuilder $shipmentLineChangeBuilder
     * @param Log                       $logger
     * @param PrintfulLogRepository     $logRepository
     */
    public function __construct(
        PrintfulOrderHoldCreated $ioAdapter,
        EventService $eventManager,
        ShipmentLineChangeBuilder $shipmentLineChangeBuilder,
        Log $logger,
        PrintfulLogRepository $logRepository
    ) {
        $this->logRepository = $logRepository;

        parent::__construct(
            $ioAdapter,
            $eventManager,
            $shipmentLineChangeBuilder,
            $logger
        );
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
}
