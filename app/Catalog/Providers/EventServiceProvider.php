<?php

namespace App\Catalog\Providers;

use App\Catalog\Subscribers\ProductDiscoverSubscriber;
use App\Listeners\AlertEventSubscriber;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Listeners\ServiceEventSubscriber;
use App\Listeners\DigitalOrderSubscriber;

/**
 * Register all events in application
 *
 * Class EventServiceProvider
 * @category WMG
 * @package  App\Providers
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2021
 * @link     http://www.wmg.com
 */
class EventServiceProvider extends ServiceProvider
{
    protected $subscribe = [
        ProductDiscoverSubscriber::class
    ];
}
