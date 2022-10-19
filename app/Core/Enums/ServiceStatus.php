<?php declare(strict_types=1);

namespace App\Core\Enums;

/**
 * Class WarehouseStatus
 * @package App\Core\Enums
 */
class ServiceStatus extends BaseEnum
{
    /**
     * Order Item Drop Status Received
     */
    public const INACTIVE = 0;

    public const ACTIVE = 1;
}
