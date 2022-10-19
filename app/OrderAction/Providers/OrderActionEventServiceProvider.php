<?php declare(strict_types=1);

namespace App\OrderAction\Providers;

use App\OrderAction\Subscribers\OrderReceivedSubscriber;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

/**
 * Class OrderActionEventServiceProvider
 * @package App\OrderAction\Providers
 */
class OrderActionEventServiceProvider extends ServiceProvider
{
    /**
     * Subscribe to these events
     *
     * @var string[]
     */
    protected $subscribe = [
        OrderReceivedSubscriber::class,
    ];
}
