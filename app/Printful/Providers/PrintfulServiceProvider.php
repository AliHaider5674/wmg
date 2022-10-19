<?php

namespace App\Printful\Providers;

use App\Printful\Mappers\StateCodeMapper;
use App\Printful\Configurations\PrintfulConfig;
use App\Printful\Console\Commands\RegisterWebhooks;
use App\Printful\Constants\ConfigConstant;
use App\Printful\Mappers\OrderItemRetailPriceMapper;
use App\Printful\Mappers\ShippingOrderMapper;
use App\Printful\Service\PrintfulOrderMapper;
use App\Printful\Handler\OrderCreatedHandler;
use App\Printful\Handler\OrderHoldCreatedHandler;
use App\Printful\Handler\ShipmentCreatedHandler;
use App\Printful\Handler\ShipmentReturnedHandler;
use App\Printful\Service\WebhookApiService;
use WMGCore\Providers\ModuleServiceProvider;
use WMGCore\Providers\Traits\Module\HasConfig;
use WMGCore\Providers\Traits\Module\HasMigrations;
use WMGCore\Providers\Traits\Module\HasRoutes;
use App\Providers\Traits\RegistersFulfillmentHandlers;
use Closure;
use Printful\Exceptions\PrintfulException;
use Printful\PrintfulApiClient;
use Illuminate\Console\Scheduling\Schedule;

/**
 * Class PrintfulServiceProvider
 * @package App\Printful\Providers
 */
class PrintfulServiceProvider extends ModuleServiceProvider
{
    use RegistersFulfillmentHandlers, HasConfig, HasMigrations, HasRoutes;

    /**
     * Printful order processors tag
     */
    private const PRINTFUL_ORDER_PROCESSORS_TAG = 'printful-order-processors';

    /**
     * Printful cron schedule config format
     */
    private const PRINTFUL_CRON_CONFIG_FORMAT = 'fulfillment.printful.%s.cron';

    /**
     * Printful command signature format
     */
    private const PRINTFUL_COMMAND_FORMAT = 'wmg:fulfillment pf.%s';

    /**
     * Printful handlers keyed by type
     */
    private const PRINTFUL_HANDLERS = [
        'order' => OrderCreatedHandler::class,
        'shipment' => ShipmentCreatedHandler::class,
        'return' => ShipmentReturnedHandler::class,
        'hold' => OrderHoldCreatedHandler::class,
    ];

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
     * Register services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->registerHandlers();
        $this->registerMappers();
        $this->registerCommands();

        $this->app->singleton(
            PrintfulApiClient::class,
            Closure::fromCallable([$this, 'makePrintfulApiClient'])
        );

        $this->app->singleton(
            PrintfulConfig::class,
            Closure::fromCallable([$this, 'makePrintfulConfig'])
        );

        $this->app->when(WebhookApiService::class)
            ->needs('$defaultAppUrl')
            ->give(function () {
                return config('app.url');
            });
    }

    /**
     * @return PrintfulApiClient
     * @throws PrintfulException
     */
    public function makePrintfulApiClient(): PrintfulApiClient
    {
        $client = new PrintfulApiClient(
            $this->getConfig(ConfigConstant::API_KEY)
        );

        $apiUrl = $this->getConfig(ConfigConstant::API_URL);

        if ($apiUrl) {
            $client->url = $apiUrl;
        }

        return $client;
    }

    /**
     * @return PrintfulConfig
     */
    public function makePrintfulConfig(): PrintfulConfig
    {
        return new PrintfulConfig(
            $this->getConfig(ConfigConstant::API_URL),
            $this->getConfig(ConfigConstant::SHOULD_CONFIRM_ORDER),
            $this->getConfigJson(ConfigConstant::ENABLED_WEBHOOKS),
            $this->getConfigJson(ConfigConstant::CARRIER_MAP),
            $this->getConfig(ConfigConstant::WEBHOOK_KEY),
            $this->getConfigJson(ConfigConstant::CUSTOM_COUNTRY_STATE_MAP)
        );
    }

    public function scheduleCommands(): void
    {
        $schedule = $this->app->make(Schedule::class);

        foreach (array_keys(self::PRINTFUL_HANDLERS) as $type) {
            $commandSignature = sprintf(self::PRINTFUL_COMMAND_FORMAT, $type);
            $commandName = sprintf('Fulfillment Printful %s', ucfirst($type));
            $cronConfigKey = sprintf(self::PRINTFUL_CRON_CONFIG_FORMAT, $type);
            $cronSchedule = $this->getConfig($cronConfigKey) ?? '*/5 * * * *';

            $schedule->command($commandSignature)
                ->name($commandName)
                ->cron($cronSchedule);
        }
    }

    /**
     * Register Printful handlers
     */
    private function registerHandlers(): void
    {
        foreach (self::PRINTFUL_HANDLERS as $type => $className) {
            $this->registerFulfillmentHandler('pf.' . $type, $className);
        }
    }

    /**
     * Register mappers
     */
    private function registerMappers(): void
    {
        $this->app->tag([
            ShippingOrderMapper::class,
            StateCodeMapper::class,
            OrderItemRetailPriceMapper::class,
        ], self::PRINTFUL_ORDER_PROCESSORS_TAG);

        $this->app->bind(PrintfulOrderMapper::class, function () {
            return new PrintfulOrderMapper($this->app->tagged(self::PRINTFUL_ORDER_PROCESSORS_TAG));
        });
    }

    /**
     * Register commands
     */
    private function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                RegisterWebhooks::class,
            ]);
        }
    }
}
