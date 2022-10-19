<?php

namespace App\Printful\Handler\IO;

use App\Printful\Handler\IO\Tracker\AckTracker;
use App\Printful\Repositories\PrintfulEventRepository;
use App\Printful\Repositories\PrintfulLogRepository;

/**
 * Class PrintfulOrderHoldCreatedHandler
 *
 * IO Stream for handling PrintfulOrderHoldCreatedHandler
 */
class PrintfulOrderHoldCreated extends PrintfulWebhookIo
{
    /**
     * PrintfulOrderHoldCreatedHandler constructor.
     *
     * @param PrintfulEventRepository $eventRepository
     * @param PrintfulLogRepository   $logRepository
     * @param AckTracker              $ackTracker
     */
    public function __construct(
        PrintfulEventRepository $eventRepository,
        PrintfulLogRepository $logRepository,
        AckTracker $ackTracker
    ) {
        parent::__construct($eventRepository, $logRepository, $ackTracker);
    }

    /**
     * @return iterable
     */
    public function getEvents(): iterable
    {
        return $this->eventRepository->newOrderPutHoldEvents();
    }
}
