<?php

namespace App\Printful\Handler\IO;

use App\Printful\Handler\IO\Tracker\AckTracker;
use App\Printful\Repositories\PrintfulEventRepository;
use App\Printful\Repositories\PrintfulLogRepository;

/**
 * Class PrintfulShipmentReturnedHandler
 *
 * IO Stream for handling PrintfulShipmentReturnedHandler
 */
class PrintfulShipmentReturned extends PrintfulWebhookIo
{
    /**
     * PrintfulOrderHoldCreatedHandler constructor.
     * @param PrintfulEventRepository       $eventRepository
     * @param PrintfulLogRepository         $logRepository
     * @param AckTracker                    $ackTracker
     */
    public function __construct(
        PrintfulEventRepository $eventRepository,
        PrintfulLogRepository $logRepository,
        AckTracker $ackTracker
    ) {
        parent::__construct(
            $eventRepository,
            $logRepository,
            $ackTracker
        );
    }

    /**
     * @return iterable
     */
    protected function getEvents(): iterable
    {
        return $this->eventRepository->newPackageReturnedEvents();
    }
}
