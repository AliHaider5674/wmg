<?php declare(strict_types=1);

namespace App\Core\Mappers;

use App\Core\Models\RawData\Order;

/**
 * Interface ConditionallyRuns
 * @package App\Core\Mappers
 */
interface ConditionallyRuns
{
    /**
     * Checks whether or not a processor should run
     *
     * @param Order $order
     * @return bool
     */
    public function shouldRun(Order $order): bool;
}
