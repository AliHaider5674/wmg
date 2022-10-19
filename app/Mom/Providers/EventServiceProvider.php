<?php

namespace App\Mom\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Mom\Constants\EventConstant;
use App\Core\Services\EventService;
use App\Mom\Subscribers\WarehouseEventSubscriber;

/**
 * Provider for MOM activities
 *
 * Class MomServiceProvider
 * @category WMG
 * @package  App\Providers
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class EventServiceProvider extends ServiceProvider
{
    /**
     * Events to subscribe
     *
     * @var string[]
     */
    protected $subscribe = [
        WarehouseEventSubscriber::class
    ];

    /**
     * Extend EventService to add an event with a description to it when resolving
     *
     * The reason we can't use tagged here is that the event and description
     * are not classes but just simply strings.
     */
    public function register(): void
    {
        parent::register();

        $this->app->extend(
            EventService::class,
            static function (EventService $eventManager) {
                $eventManager->addEvent(
                    EventConstant::EVENT_ORDER_ACTION_CREATED,
                    'Order action created for Mom orders.'
                );

                return $eventManager;
            }
        );
    }
}
