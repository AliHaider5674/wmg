<?php
namespace App\Mdc\Providers;

use App\Core\Services\ClientService;
use App\Mdc\Service\Event\ClientHandler\AckHandler;
use App\Mdc\Service\Event\ClientHandler\ShipmentHandler;
use App\Mdc\Service\Event\ClientHandler\StockHandler;
use App\Mdc\Service\Event\MdcClient;
use App\Mom\Models\Service\Event\ClientHandler\DefaultHandler;
use App\Mom\Models\Service\Event\ClientHandler\LineChangeHandler;
use App\Mom\Models\Service\Event\ClientHandler\OrderActionCreatedHandler;
use App\Mom\Models\Service\Event\MomClient;
use WMGCore\Providers\ModuleServiceProvider;
use WMGCore\Providers\Traits\Module\HasMigrations;
use Illuminate\Contracts\Container\BindingResolutionException;
use WMGCore\Services\ConfigService;

/**
 * Mdc service provider
 *
 * Class MdcProvider
 * @category WMG
 * @package  App\Mdc\Providers
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2020
 * @link     http://www.wmg.com
 */
class MdcProvider extends ModuleServiceProvider
{
    use HasMigrations;

    /**
     * Register service providers
     */
    public function register(): void
    {
        parent::register();

        $this->registerClients();
    }

    /**
     * Register clients
     */
    private function registerClients(): void
    {
        $this->app->tag([
            ShipmentHandler::class,
            AckHandler::class,
            StockHandler::class,
        ], 'mcd-handlers');

        $this->app->when(MdcClient::class)
            ->needs('$handlers')
            ->give(function ($app) {
                return $app->tagged('mcd-handlers');
            });

        $mdcClient = $this->app->make(MdcClient::class);
        $this->app->make(ClientService::class)->addClient($mdcClient);
    }
}
