<?php

namespace App\IM\Providers;

use App\IM\Configurations\ImConfig;
use App\IM\Handler\AckHandler;
use App\IM\Constants\ConfigConstant;
use App\IM\Handler\OrderHandler;
use WMGCore\Providers\ModuleServiceProvider;
use WMGCore\Providers\Traits\Module\HasMigrations;
use App\Providers\Traits\RegistersFulfillmentHandlers;
use App\IM\Handler\StockHandler;
use App\IM\Handler\ShipmentHandler;
use WMGCore\Services\ConfigService;
use Illuminate\Console\Scheduling\Schedule;

/**
 * Class IMServiceProvider
 * Provider for Ingram Micro API warehouse
 *
 * @category WMG
 * @package  app\IM\Providers
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License
 * @link     http://www.wmg.com
 */
class IMServiceProvider extends ModuleServiceProvider
{
    use RegistersFulfillmentHandlers, HasMigrations;

    /**
     * Default IM Source Map
     */
    private const DEFAULT_IM_SOURCE_MAP = ['IM'];


    /**
     * Boot services
     *
     * @return void
     */
    public function boot(): void
    {
        parent::boot();

        $this->app->booted([$this, 'scheduleCommands']);
    }

    /**
     * Register services
     */
    public function register(): void
    {
        parent::register();

        $this->app->singleton(ImConfig::class, function () {
            return new ImConfig(
                config('api.ingram.url'),
                config('api.ingram.user'),
                config('api.ingram.password'),
                app()->make(ConfigService::class)
                    ->getJson(ConfigConstant::IM_SOURCE_MAP, self::DEFAULT_IM_SOURCE_MAP)
            );
        });

        //Add Api Handlers
        $this->registerFulfillmentHandler('im.order', OrderHandler::class);
        $this->registerFulfillmentHandler('apiStock', StockHandler::class);
        $this->registerFulfillmentHandler('apiShipment', ShipmentHandler::class);
        $this->registerFulfillmentHandler('im.ack', AckHandler::class);
    }


    public function scheduleCommands(): void
    {
        /**
         * @todo give namespace for the IM jobs
         */

        $schedule = $this->app->make(Schedule::class);

        //API ORDER - EU - Ingram Micro
        $apiOrderCron = $this->getConfig('fulfillment.im.order.cron') ?? '*/5 * * * *';
        if (!empty($apiOrderCron)) {
            $schedule->command('wmg:fulfillment im.order')
                ->name('Fulfillment Api Order')
                ->cron($apiOrderCron);
        }

        //API STOCK - EU - Ingram Micro
        $imStockCron = $this->getConfig('fulfillment.im.stock.cron') ?? '0 3 * * *';
        if (!empty($imStockCron)) {
            $schedule->command('wmg:fulfillment apiStock')
                ->name('Fulfillment Ingram Micro Stock')
                ->cron($imStockCron);
        }

        //API SHIPMENT - EU - Ingram Micro
        $apiShipmentCron = $this->getConfig('fulfillment.im.shipment.cron') ?? '* */2 * * *';
        if (!empty($apiShipmentCron)) {
            $schedule->command('wmg:fulfillment apiShipment')
                ->name('Fulfillment Api Shipment')
                ->cron($apiOrderCron);
        }

        //API Ack - EU - Ingram Micro
        $apiShipmentCron = $this->getConfig('fulfillment.im.ack.cron') ?? '*/5 * * * *';
        if (!empty($apiShipmentCron)) {
            $schedule->command('wmg:fulfillment im.ack')
                ->name('Ingram Micro Ack')
                ->cron($apiOrderCron);
        }
    }
}
