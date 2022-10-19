<?php

namespace App\Shopify\Providers;

use App\Core\Providers\FulfillmentAbstractProvider;
use App\Core\Repositories\ServiceRepository;
use App\Shopify\Actions\Shopify\ActionManager;
use App\Shopify\Actions\Shopify\CreateFulfillmentRequestAction;
use App\Shopify\Actions\Shopify\ExpandOrderAction;
use App\Shopify\Actions\Shopify\FetchShipmentOrderAction;
use App\Shopify\Actions\Shopify\ScanOrderAction;
use App\Shopify\Handlers\ExpandOrderHandler;
use App\Shopify\Handlers\FetchFailedFulfillmentOrderHandler;
use App\Shopify\Handlers\FetchOrderHandler;
use App\Shopify\Handlers\FulfillmentRequestHandler;
use App\Shopify\Handlers\ScanOrderHandler;
use App\Shopify\Http\Middleware\ShopifyRequestAuth;
use App\Shopify\Constants\ConfigConstant;
use App\Shopify\ServiceClients\Handlers\AckHandler;
use App\Shopify\ServiceClients\Handlers\ShipmentRequestHandler;
use App\Shopify\ServiceClients\RestfulClient;
use App\Shopify\ServiceClients\Handlers\ShipmentHandler;
use Illuminate\Console\Scheduling\Schedule;
use WMGCore\Providers\Traits\Module\HasCommands;
use App\Preorder\Constants\ConfigConstant as PreorderConfigConstant;

/**
 * Class ShopifyServiceProvider
 * @package App\Shopify\Providers
 */
class ShopifyServiceProvider extends FulfillmentAbstractProvider
{
    use HasCommands;

    /**
     * Fulfillment service events handler
     */
    protected const SERVICE_CLIENTS = [
        [
            'client' => RestfulClient::class,
            'handlers' => [
                AckHandler::class,
                ShipmentHandler::class,
                ShipmentRequestHandler::class
            ]
        ]
    ];

    protected const HANDLERS = [
        'fetch_orders' => FetchOrderHandler::class,
        'fulfillment_requests' => FulfillmentRequestHandler::class,
        'expand_orders' => ExpandOrderHandler::class,
        'scan_orders' => ScanOrderHandler::class,
        'fetch_failed_fulfillment_orders' => FetchFailedFulfillmentOrderHandler::class
    ];

    protected function getNamespace(): string
    {
        return 'shopify';
    }

    /**
     * Register service providers
     */
    public function register(): void
    {
        //Handlers
        $config = function () {
            return [
                ConfigConstant::SUPPORTED_WAREHOUSES => $this->getConfigJson(ConfigConstant::SUPPORTED_WAREHOUSES),
                PreorderConfigConstant::US_DROP_DAYS_ADVANCE =>
                    $this->getConfig(PreorderConfigConstant::US_DROP_DAYS_ADVANCE) ?? 4,
                PreorderConfigConstant::US_DROP_TIMEZONE =>
                    $this->getConfig(PreorderConfigConstant::US_DROP_TIMEZONE) ?? 'PST',
                ConfigConstant::SHOPIFY_FULFILLMENT_REQUEST_SIZE =>
                    $this->getConfig(ConfigConstant::SHOPIFY_FULFILLMENT_REQUEST_SIZE) ?? 800
            ];
        };

        foreach (self::HANDLERS as $handler) {
            $this->app->when($handler)
                ->needs('$config')
                ->give($config);
        }


        parent::register();
        $this->app->singleton(ShopifyRequestAuth::class, function () {
            return new ShopifyRequestAuth(
                $this->app->make(ServiceRepository::class),
                $this->getConfig('shopify.routes.signature-header')
            );
        });
    }
}
