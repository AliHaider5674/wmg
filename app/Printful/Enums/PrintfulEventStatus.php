<?php declare(strict_types=1);

namespace App\Printful\Enums;

use App\Enums\FilterEnum;

/**
 * @method static received(): int
 * @method static processed(): int
 * @method static error(): int
 * @method static invalidParam(): int
 */
final class PrintfulEventStatus extends FilterEnum
{
    /**
     * @var string
     */
    protected $column = 'status';

    /**
     * The event has been received and is ready to be processed
     */
    public const RECEIVED = 0;

    /**
     * The event has been processed successfully
     */
    public const PROCESSED = 1;

    /**
     * There was an error processing the event
     */
    public const ERROR = 2;

    /**
     * Error was rejected by application
     */
    public const SOFT_ERROR = 3;

    /**
     * Invalid parameters
     */
    public const INVALID_PARAM = 4;
}
