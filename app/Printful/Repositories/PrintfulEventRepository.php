<?php declare(strict_types=1);

namespace App\Printful\Repositories;

use WMGCore\Repositories\BaseRepository;
use App\Printful\Enums\PrintfulEventStatus;
use App\Printful\Enums\PrintfulEventType;
use App\Printful\Exceptions\InvalidWebhookTypeException;
use App\Printful\Models\PrintfulEvent;
use BenSampo\Enum\Exceptions\InvalidEnumKeyException;
use Generator;
use Illuminate\Database\Eloquent\Builder;
use Printful\Structures\Webhook\WebhookItem;

/**
 * Class PrintfulEventRepository
 * @package App\Printful\Repository
 * @SuppressWarnings(PHPMD)
 */
class PrintfulEventRepository extends BaseRepository
{
    /**
     * Default value for max amount of tries (logs) when retrieving events
     */
    private const MAX_TRIES = 3;

    /**
     * PrintfulEventRepository constructor.
     * @param PrintfulEvent $printfulEvent
     */
    public function __construct(
        PrintfulEvent $printfulEvent
    ) {
        parent::__construct($printfulEvent);
    }

    /**
     * Yield unprocessed PackageShipped events loading one at a time
     *
     * @return Generator
     */
    public function newPackageShippedEvents(): Generator
    {
        yield from $this->modelQuery()
            ->where(PrintfulEventStatus::received()->filter())
            ->where(PrintfulEventType::packageShipped()->filter())
            ->cursor();
    }

    /**
     * Yield unprocessed PackageShipped events loading one at a time
     *
     * @param int $maxFails Filter events that have this amount of failed logs
     * @return Generator
     */
    public function failedPackageShippedEvents(
        int $maxFails = self::MAX_TRIES
    ): Generator {
        $query = $this->modelQuery()
            ->where(PrintfulEventStatus::error()->filter())
            ->where(PrintfulEventType::packageShipped()->filter());

        yield from $this->addMaxFailLogs($query, $maxFails)->cursor();
    }

    /**
     * Yield unprocessed PackageReturned events loading one at a time
     *
     * @return Generator
     */
    public function newPackageReturnedEvents(): Generator
    {
        yield from $this->modelQuery()
            ->where(PrintfulEventStatus::received()->filter())
            ->where(PrintfulEventType::packageReturned()->filter())
            ->cursor();
    }

    /**
     * Yield unprocessed PackageReturned events loading one at a time
     *
     * @param int $maxFails
     * @return Generator
     */
    public function failedPackageReturnedEvents(
        int $maxFails = self::MAX_TRIES
    ): Generator {
        $query = $this->modelQuery()
            ->where(PrintfulEventStatus::error()->filter())
            ->where(PrintfulEventType::packageReturned()->filter());

        yield from $this->addMaxFailLogs($query, $maxFails)->cursor();
    }

    /**
     * Yield unprocessed OrderPutHold events loading one at a time
     *
     * @return Generator
     */
    public function newOrderPutHoldEvents(): Generator
    {
        yield from $this->modelQuery()
            ->where(PrintfulEventStatus::received()->filter())
            ->where(PrintfulEventType::orderPutHold()->filter())
            ->cursor();
    }

    /**
     * Yield unprocessed OrderPutHold events loading one at a time
     *
     * @param int $maxFails
     * @return Generator
     */
    public function failedOrderPutHoldEvents(
        int $maxFails = self::MAX_TRIES
    ): Generator {
        $query = $this->modelQuery()
            ->where(PrintfulEventStatus::error()->filter())
            ->where(PrintfulEventType::orderPutHold());

        yield from $this->addMaxFailLogs($query, $maxFails)->cursor();
    }

    /**
     * Get unprocessed OrderRemoveHold events
     *
     * @return Generator
     */
    public function newOrderRemoveHoldEvents(): Generator
    {
        yield from $this->modelQuery()
            ->where(PrintfulEventStatus::received()->filter())
            ->where(PrintfulEventType::orderRemoveHold()->filter())
            ->cursor();
    }

    /**
     * @param int|null $maxFails
     * @return Generator
     */
    public function failedOrderRemoveHoldEvents(
        int $maxFails = null
    ): Generator {
        $query = $this->modelQuery()
            ->where(PrintfulEventStatus::error()->filter())
            ->where(PrintfulEventType::orderRemoveHold()->filter());

        yield from $this->addMaxFailLogs($query, $maxFails)->cursor();
    }

    /**
     * @param Builder  $query
     * @param int|null $maxFails
     * @return Builder
     */
    private function addMaxFailLogs(Builder $query, ?int $maxFails): Builder
    {
        return $maxFails === null ? $query
            : $query->whereHas('logs', static function ($query) {
                $query->where('success', false);
            }, '<', $maxFails);
    }

    /**
     * @param PrintfulEvent       $printfulEvent
     * @param int $printfulEventStatus
     * @return bool
     */
    public function updateEventStatus(
        PrintfulEvent $printfulEvent,
        int $printfulEventStatus
    ): bool {
        return $this->update($printfulEvent, [
            'status' => $printfulEventStatus
        ]);
    }

    /**
     * Create PrintfulEvent with webhook data
     *
     * @param array $webhookData
     * @return PrintfulEvent
     * @throws InvalidWebhookTypeException
     */
    public function createEvent(array $webhookData): PrintfulEvent
    {
        try {
            $eventType = PrintfulEventType::fromKey(
                strtoupper($webhookData['type'] ?? null)
            );
        } catch (InvalidEnumKeyException $exception) {
            throw new InvalidWebhookTypeException(
                $webhookData['type'] ?? "",
                "Invalid webhook type",
                0,
                $exception
            );
        }

        return $this->create([
            'event_type' => $eventType,
            'webhook_item' => $webhookData,
        ]);
    }
}
