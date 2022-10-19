<?php

namespace App\Core\Providers;

use App\Core\Services\ClientService;
use App\Core\Services\EventService;
use App\Core\Services\ExternalService;
use WMGCore\Providers\ModuleServiceProvider;
use WMGCore\Providers\Traits\Module\HasMigrations;
use App\Core\Services\Mutators\TaxId\TaxIdMutatorFactory;
use App\Models\OrderAddress;
use App\Core\Observers\OrderAddressObserver;
use WMGCore\Services\ConfigService;
use WMGCore\Providers\Traits\Module\HasCommands;

/**
 * Provider for warehouse
 *
 * Class Stock Export Service Provider
 * @category WMG
 * @package  App\Providers
 * @author   Daniel Campbell <daniel@primor.tech>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class CoreProvider extends ModuleServiceProvider
{
    use HasMigrations;
    use HasCommands;
    /**
     * Clients tag
     *
     * Public so that other service providers can tag classes with this
     */
    public const CLIENTS_TAG = 'clients';

    /**
     * Load configuration
     */
    public function boot(): void
    {
        parent::boot();

        $this->mergeConfigFrom(dirname(__DIR__) . '/config/mutators.php', 'mutators');
        OrderAddress::observe(OrderAddressObserver::class);
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->configService = $this->app->make(ConfigService::class);

        $this->app->register(WarehouseProvider::class);
        $this->app->register(EventServiceProvider::class);
        $this->app->singleton(ExternalService::class);
        $this->app->singleton(ClientService::class);

        $this->app->singleton(EventService::class);

        $this->app->bind(TaxIdMutatorFactory::class, static function () {
            return new TaxIdMutatorFactory(
                array_change_key_case(
                    config('mutators.tax-id.formatters'),
                    CASE_UPPER
                ),
                array_change_key_case(
                    config('mutators.tax-id.validators'),
                    CASE_UPPER
                )
            );
        });
    }
}
