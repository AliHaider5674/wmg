<?php

namespace App\Catalog\Providers;

use App\Core\Providers\FulfillmentAbstractProvider;
use App\Core\Repositories\ServiceRepository;
use App\Shopify\Actions\Shopify\ActionManager;
use App\Shopify\Actions\Shopify\CreateFulfillmentRequestAction;
use App\Shopify\Actions\Shopify\FetchShipmentOrderAction;
use App\Shopify\Actions\Shopify\ScanOrderAction;
use App\Shopify\Http\Middleware\ShopifyRequestAuth;
use App\Shopify\Constants\ConfigConstant;
use App\Shopify\ServiceClients\Handlers\AckHandler;
use App\Shopify\ServiceClients\Handlers\ShipmentRequestHandler;
use App\Shopify\ServiceClients\RestfulClient;
use App\Shopify\ServiceClients\Handlers\ShipmentHandler;
use Illuminate\Console\Scheduling\Schedule;
use WMGCore\Providers\ModuleServiceProvider;
use WMGCore\Providers\Traits\Module\HasCommands;
use WMGCore\Providers\Traits\Module\HasMigrations;

/**
 * Class ProductServiceProvider
 * @package App\Catalog\Providers
 */
class ProductServiceProvider extends ModuleServiceProvider
{
    use HasMigrations;
    /**
     * Register service providers
     */
    public function register(): void
    {
        $this->app->register(EventServiceProvider::class);
    }
}
