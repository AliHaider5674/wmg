<?php

namespace App\OrderAction\Providers;

use WMGCore\Providers\ModuleServiceProvider;
use WMGCore\Providers\Traits\Module\HasMigrations;
use WMGCore\Providers\Traits\Module\HasRoutes;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\OrderAction\Subscribers\OrderReceivedSubscriber;
use App\OrderAction\Services\OrderActionService;
use App\OrderAction\ActionHandlers\OnHoldHandler;

/**
 * Register all events in application
 *
 * Class EventServiceProvider
 * @category WMG
 * @package  App\Providers
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class OrderActionServiceProvider extends ModuleServiceProvider
{
    use HasMigrations, HasRoutes;

    /**
     * Order Action Handlers Tag
     *
     * Public so that if other service providers or modules wanted to tag a
     * class as an order-action-handler they can access this tag
     */
    public const ORDER_ACTION_HANDLERS_TAG = 'order-action-handlers';

    /**
     * Middleware for all API routes
     *
     * @var string
     */
    protected $apiMiddlewareGroup = 'auth:api';

    /**
     * Controller namespace for all API routes
     *
     * @var string
     */
    protected $apiRouteNamespace = '\App\OrderAction\Http\Controllers';

    /**
     * Register services
     */
    public function register(): void
    {
        parent::register();
        $this->registerEventServiceProvider();

        app()->singleton(OrderActionService::class);

        $this->app->tag(OnHoldHandler::class, self::ORDER_ACTION_HANDLERS_TAG);
        $this->app->when(OrderActionService::class)
            ->needs('$handlers')
            ->give(function ($app) {
                return $app->tagged(self::ORDER_ACTION_HANDLERS_TAG);
            });
    }

    /**
     * Register event service provider
     */
    protected function registerEventServiceProvider(): void
    {
        $this->app->register(OrderActionEventServiceProvider::class);
    }
}
