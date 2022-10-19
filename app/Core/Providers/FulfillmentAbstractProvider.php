<?php

namespace App\Core\Providers;

use App\Core\Services\ClientService;
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
 * Class FulfillmentAbstractProvider
 * @package App\Core\Providers
 */
abstract class FulfillmentAbstractProvider extends ModuleServiceProvider
{
    use RegistersFulfillmentHandlers, HasConfig, HasMigrations, HasRoutes;

    /**
     * order processors tag
     */
    private const ORDER_PROCESSORS_TAG = 'order-processors';

    /**
     * cron schedule config format
     */
    private const CRON_CONFIG_FORMAT = 'fulfillment.%s.%s.cron';

    /**
     * command signature format
     */
    private const COMMAND_FORMAT = 'wmg:fulfillment %s.%s';

    /**
     * Warehouse handlers keyed by type
     */

    protected const HANDLERS = [

    ];

    /**
     * SERVICE CLIENTS
     * Sample:
     * [
     *  [
     *   'client' => class,
     *   'handlers' => class
     *   ]
     * ]
     */
    protected const SERVICE_CLIENTS = [];


    abstract protected function getNamespace():String;

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
        $this->registerClients();
    }


    public function scheduleCommands(): void
    {
        $schedule = $this->app->make(Schedule::class);
        $namespace = $this->getNamespace();
        foreach (array_keys(static::HANDLERS) as $type) {
            $commandSignature = sprintf(self::COMMAND_FORMAT, $namespace, $type);
            $commandName = sprintf('Fulfillment %s %s', ucfirst($namespace), ucfirst($type));
            $cronConfigKey = sprintf(self::CRON_CONFIG_FORMAT, $namespace, $type);
            $cronSchedule = $this->getConfig($cronConfigKey) ?? '*/5 * * * *';
            $schedule->command($commandSignature)
                ->name($commandName)
                ->cron($cronSchedule);
        }
    }

    /**
     * Register handlers
     */
    private function registerHandlers(): void
    {
        $namespace = $this->getNamespace();
        foreach (static::HANDLERS as $type => $className) {
            $this->registerFulfillmentHandler($namespace . '.' . $type, $className);
        }
    }

    /**
     * Register client and client handlers
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    private function registerClients()
    {
        foreach (static::SERVICE_CLIENTS as $clientData) {
            $tagName = $clientData['client'] . '-handlers';
            $this->app->tag($clientData['handlers'], $tagName);
            $this->app->when($clientData['client'])
                ->needs('$handlers')
                ->give(function ($app) use ($tagName) {
                    return $app->tagged($tagName);
                });
            $client = $this->app->make($clientData['client']);
            $this->app->make(ClientService::class)->addClient($client);
        }
    }
}
