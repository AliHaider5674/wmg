<?php

namespace App\Mom\Providers;

use App\Core\Providers\CoreProvider;
use App\Core\Services\ClientService;
use App\Mom\Models\Service\Event\ClientHandler\DefaultHandler;
use App\Mom\Models\Service\Event\ClientHandler\LineChangeHandler;
use App\Mom\Models\Service\Event\ClientHandler\OrderActionCreatedHandler;
use App\Mom\Models\Service\Event\MomClient;
use WMGCore\Providers\ModuleServiceProvider;
use WMGCore\Providers\Traits\Module\HasMigrations;
use Illuminate\Support\ServiceProvider;

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
class MomServiceProvider extends ModuleServiceProvider
{
    use HasMigrations;

    /**
     * Mom handlers tag
     *
     * Public so that if other service providers or modules wanted to tag a
     * class as an order-action-handler they can access this tag
     */
    public const MOM_HANDLERS_TAG = 'mom-handlers';

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function boot(): void
    {
        parent::boot();
        $clientService = $this->app->make(ClientService::class);
        $clientService->addClient($this->app->make(MomClient::class));
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->register(EventServiceProvider::class);
        $this->registerClients();
    }

    /**
     * Register clients
     */
    private function registerClients(): void
    {
        $this->app->tag([
            LineChangeHandler::class,
            OrderActionCreatedHandler::class,
            DefaultHandler::class,
        ], self::MOM_HANDLERS_TAG);

        $this->app->when(MomClient::class)
            ->needs('$handlers')
            ->give(function ($app) {
                return $app->tagged(self::MOM_HANDLERS_TAG);
            });
    }
}
