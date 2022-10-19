<?php declare(strict_types=1);

namespace App\Printful\Repositories;

use WMGCore\Repositories\BaseRepository;
use App\Printful\Models\PrintfulEvent;
use App\Printful\Models\PrintfulLog;

/**
 * Class PrintfulLogRepository
 * @package App\Printful\Repositories
 */
class PrintfulLogRepository extends BaseRepository
{
    /**
     * PrintfulEventRepository constructor.
     * @param PrintfulLog $printfulEvent
     */
    public function __construct(PrintfulLog $printfulEvent)
    {
        parent::__construct($printfulEvent);
    }

    /**
     * Create an event failed log for a PrintfulEvent
     *
     * @param PrintfulEvent $event
     * @param string|null   $output
     * @return PrintfulLog
     */
    public function createEventFailedLog(
        PrintfulEvent $event,
        string $output = null
    ): PrintfulLog {
        return $this->createEventFailedLogWithEventId($event->id, $output);
    }

    /**
     * create an event failed log for a PrintfulEvent ID
     *
     * @param int           $eventId
     * @param string|null   $output
     * @return PrintfulLog
     */
    public function createEventFailedLogWithEventId(
        int $eventId,
        string $output = null
    ): PrintfulLog {
        return $this->create([
            'event_id' => $eventId,
            'event_output' => $output,
            'success' => false,
        ]);
    }

    /**
     * Create an event processed log for a PrintfulEvent
     *
     * @param PrintfulEvent $event
     * @param string|null   $output
     * @return PrintfulLog
     */
    public function createEventProcessedLog(
        PrintfulEvent $event,
        string $output = null
    ): PrintfulLog {
        return $this->createEventProcessedLogWithEventId($event->id, $output);
    }

    /**
     * @param int           $eventId
     * @param string|null   $output
     * @return PrintfulLog
     */
    public function createEventProcessedLogWithEventId(
        int $eventId,
        string $output = null
    ): PrintfulLog {
        return $this->create([
            'event_id' => $eventId,
            'event_output' => $output,
            'success' => true,
        ]);
    }
}
