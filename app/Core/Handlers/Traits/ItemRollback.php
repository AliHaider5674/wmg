<?php declare(strict_types=1);

namespace App\Core\Handlers\Traits;

use App\Core\Models\Shipment;
use App\Models\FailedParameter;
use App\Models\OrderItem;

/**
 * Class ItemRollback
 */
trait ItemRollback
{
    /**
     * @param $item
     * @param ...$args
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function rollbackItem($item, ...$args): void
    {
        if ($item instanceof Shipment) {
            $item->delete();
        } elseif ($item instanceof OrderItem) {
            $item->setAttribute('quantity_ack', $item->getOriginal('quantity_ack'));
            $item->setAttribute('quantity_backordered', $item->getOriginal('quantity_backordered'));
            $item->save();
        } elseif ($item instanceof FailedParameter) {
            $item->delete();
        }
    }
}
