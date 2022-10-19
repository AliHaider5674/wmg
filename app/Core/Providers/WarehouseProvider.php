<?php

namespace App\Core\Providers;

use App\Core\Handlers\FulfillmentHandlerContainer;
use App\Core\Handlers\IO\IOInterface;
use App\Core\Handlers\IO\NullStream;
use App\Core\Handlers\StockExportHandler;
use App\Core\Mappers\StoreNameMapper;
use App\Core\Mappers\ShippingOrderMapper;
use App\Core\Services\Converters\OrderRawConverterService;
use App\Core\Services\OrderRawMapperService;
use App\Providers\Traits\RegistersFulfillmentHandlers;
use App\Services\WarehouseService;
use Illuminate\Support\ServiceProvider;

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
class WarehouseProvider extends ServiceProvider
{
    use RegistersFulfillmentHandlers;

    /**
     * Order processors tag
     */
    public const ORDER_PROCESSORS_TAG = 'order-processors';

    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton(FulfillmentHandlerContainer::class);
        $this->app->singleton(OrderRawMapperService::class);
        $this->app->singleton(OrderRawConverterService::class);

        $this->registerHandlers();
        $this->registerMappers();
    }

    /**
     * Registers handlers
     */
    private function registerHandlers(): void
    {
        $this->app->singleton(WarehouseService::class);
        $this->app->bind(IOInterface::class, NullStream::class);
        $this->registerFulfillmentHandler('stock_export', StockExportHandler::class);
    }

    /**
     * Register mappers
     */
    private function registerMappers(): void
    {
        $this->app->tag([
            StoreNameMapper::class,
            ShippingOrderMapper::class
        ], self::ORDER_PROCESSORS_TAG);

        $this->app->bind(OrderRawMapperService::class, function () {
            return new OrderRawMapperService($this->app->tagged(self::ORDER_PROCESSORS_TAG));
        });
    }
}
