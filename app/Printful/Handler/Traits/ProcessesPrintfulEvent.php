<?php declare(strict_types=1);

namespace App\Printful\Handler\Traits;

use App\Core\Handlers\IO\IOInterface;
use App\Printful\Enums\PrintfulEventStatus;
use App\Printful\Exceptions\PrintfulException;
use App\Printful\Handler\IO\PrintfulWebhookIo;

/**
 * Class ProcessesPrintfulEvent
 * @package App\Printful\Handler\Traits
 * @property PrintfulWebhookIo $ioAdapter
 */
trait ProcessesPrintfulEvent
{
    /**
     * @param string $message
     * @throws PrintfulException
     */
    public function currentEventSucceeded(
        string $message
    ): void {
        $event = $this->ioAdapter->getCurrentEvent();

        if ($event === null) {
            throw new PrintfulException("There is no current event");
        }

        $this->logRepository->createEventProcessedLog($event, $message);
        $event->setAttribute('status', PrintfulEventStatus::PROCESSED);
        $event->save();
    }

    /**
     * @param string $message
     * @throws PrintfulException
     */
    protected function currentEventFailed(
        string $message
    ): void {
        $event = $this->ioAdapter->getCurrentEvent();

        if ($event === null) {
            throw new PrintfulException("There is no current event");
        }

        $this->logRepository->createEventFailedLog($event, $message);
        $event->setAttribute('status', PrintfulEventStatus::ERROR);
        $event->save();
    }

     /**
     * @param string $message
     * @throws PrintfulException
     */
    protected function currentEventSoftFailed(
        string $message
    ): void {
        $event = $this->ioAdapter->getCurrentEvent();

        if ($event === null) {
            throw new PrintfulException("There is no current event");
        }

        $this->logRepository->createEventFailedLog($event, $message);
        $event->setAttribute('status', PrintfulEventStatus::SOFT_ERROR);
        $event->save();
    }
}
