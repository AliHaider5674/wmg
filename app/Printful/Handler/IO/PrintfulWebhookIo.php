<?php declare(strict_types=1);

namespace App\Printful\Handler\IO;

use App\Printful\Enums\PrintfulEventStatus;
use App\Printful\Exceptions\InvalidPrintfulItemException;
use App\Printful\Handler\IO\Tracker\WebhookItemTracker;
use App\Printful\Models\PrintfulEvent;
use App\Printful\Repositories\PrintfulEventRepository;
use App\Printful\Repositories\PrintfulLogRepository;
use Throwable;

/**
 * Class PrintfulWebhookIo
 * @package App\Printful\Handler\IO
 */
abstract class PrintfulWebhookIo extends BasePrintfulStream
{
    /**
     * @var PrintfulEventRepository
     */
    protected $eventRepository;

    /**
     * @var PrintfulLogRepository
     */
    protected $logRepository;

    /**
     * @var WebhookItemTracker
     */
    protected $webhookTracker;

    /**
     * PrintfulWebhookIo constructor.
     * @param PrintfulEventRepository   $eventRepository
     * @param PrintfulLogRepository     $logRepository
     * @param WebhookItemTracker $webhookTracker
     */
    public function __construct(
        PrintfulEventRepository $eventRepository,
        PrintfulLogRepository $logRepository,
        WebhookItemTracker $webhookTracker
    ) {
        $this->webhookTracker = $webhookTracker;
        $this->eventRepository = $eventRepository;
        $this->logRepository = $logRepository;
    }

    /**
     * Receive parameter and process with callback
     *
     * @param $callback
     */
    public function receive($callback): void
    {
        $events = $this->getEvents();
        if (!$events->valid()) {
            return;
        }
        $this->webhookTracker
            ->reset()
            ->setWebhookItems($events)
            ->each($callback, [$this, 'receiveFailed']);
    }

    public function receiveFailed(PrintfulEvent $event, Throwable  $exception) : void
    {
        if ($exception instanceof InvalidPrintfulItemException) {
            $this->eventRepository->updateEventStatus($event, PrintfulEventStatus::INVALID_PARAM);
            $this->logRepository->createEventFailedLog($event, $exception->getMessage());
            return;
        }
        $this->eventRepository->updateEventStatus($event, PrintfulEventStatus::ERROR);
        $this->logRepository->createEventFailedLog($event, $exception->getMessage());
    }


    /**
     * @return PrintfulEvent|null
     */
    public function getCurrentEvent(): ?PrintfulEvent
    {
        return $this->webhookTracker->getCurrentEvent();
    }

    /**
     * @return iterable
     */
    abstract protected function getEvents(): iterable;
}
