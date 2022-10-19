<?php declare(strict_types=1);

namespace App\Printful\Handler\IO;

use App\Printful\Handler\IO\Tracker\ShipmentTracker;
use App\Printful\Repositories\PrintfulEventRepository;
use App\Printful\Repositories\PrintfulLogRepository;

/**
 * Class PrintfulShipmentCreatedHandler
 *
 * IO Stream for handling PrintfulShipmentCreatedHandler
 */
class PrintfulShipmentCreated extends PrintfulWebhookIo
{
    /**
     * PrintfulShipmentCreated constructor.
     * @param PrintfulEventRepository $eventRepository
     * @param PrintfulLogRepository   $logRepository
     * @param ShipmentTracker         $shipmentTracker
     */
    public function __construct(
        PrintfulEventRepository $eventRepository,
        PrintfulLogRepository $logRepository,
        ShipmentTracker $shipmentTracker
    ) {
        parent::__construct($eventRepository, $logRepository, $shipmentTracker);
    }

    /**
     * @return iterable
     */
    protected function getEvents(): iterable
    {
        return $this->eventRepository->newPackageShippedEvents();
    }
}
