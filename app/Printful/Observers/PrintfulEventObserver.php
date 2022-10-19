<?php declare(strict_types=1);

namespace App\Printful\Observers;

use App\Printful\Models\PrintfulEvent;

/**
 * Class PrintfulEventObserver
 * @package App\Printful\Observers
 */
class PrintfulEventObserver
{
    /**
     * Handle the printful event "updating" event.
     *
     * @param  PrintfulEvent  $printfulEvent
     * @return void
     */
    public function updating(PrintfulEvent $printfulEvent)
    {
        if ($printfulEvent->isDirty('webhook_item')) {
            $printfulEvent->setRawAttributes([
                'webhook_item' => $printfulEvent->getOriginal('webhook_item')
            ]);
        }
    }
}
