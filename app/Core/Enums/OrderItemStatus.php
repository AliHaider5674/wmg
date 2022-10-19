<?php declare(strict_types=1);

namespace App\Core\Enums;

/**
 * Class OrderItemStatus
 * @package App\Core\Enums
 */
class OrderItemStatus extends BaseEnum
{
    /**
     * Order Item Drop Status Received
     */
    public const RECEIVED = 0;
    /**
     * Order Item Drop Status Dropped
     */
    public const DROPPED  = 1;
    /**
     * Order Item Drop Status Error
     */
    public const ERROR = 2;
    /**
     * Order Item Drop Status On Hold
     */
    public const ONHOLD = 3;

    /*
    * Got rejected by downstream system
    */
    public const SOFT_ERROR = 4;

    /**
     * Order item is queued to be dropped
     */
    public const QUEUED_DROPPED = 5;

    /**
     * Order item is selected for processing
     */
    public const PROCESSING = 6;
}
