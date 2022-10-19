<?php

namespace App\IMMuleSoft\Providers;

use App\Core\Providers\FulfillmentAbstractProvider;
use App\Core\Services\EventService;
use App\IMMuleSoft\Constants\EventConstant;
use App\IMMuleSoft\Handler\OrderHandler;
use App\IMMuleSoft\Constants\ConfigConstant;
use App\Core\Handlers\AbstractOrderHandler;
use App\IMMuleSoft\Handler\ProductHandler;
use App\IMMuleSoft\Handler\StockHandler;
use App\IMMuleSoft\ServiceClients\Handlers\OrderExportHandler;
use App\IMMuleSoft\ServiceClients\Handlers\ResponseHandler;
use App\IMMuleSoft\ServiceClients\RestfulClient;
use App\Providers\Traits\RegistersFulfillmentHandlers;
use Illuminate\Console\Scheduling\Schedule;
use WMGCore\Providers\Traits\Module\HasCommands;
use WMGCore\Services\ConfigService;
use App\IMMuleSoft\Handler\Order\ShippingServiceMapper;
use App\IMMuleSoft\Handler\OrderStatusHandler;

/**
 * Class IMMuleSoftServiceProvider
 * @package App\IMMuleSoft\Providers
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2021
 * @link     http://www.wmg.com
 */
class IMMuleSoftServiceProvider extends FulfillmentAbstractProvider
{
    use RegistersFulfillmentHandlers, HasCommands;

    private const NAMESPACE = 'immulesoft';
    const DEFAULT_ORDER_LIMIT = 100;

    protected function getNamespace(): string
    {
        return self::NAMESPACE;
    }

    /**
     * Fulfillment service events handler
     */
    protected const SERVICE_CLIENTS = [
        [
            'client' => RestfulClient::class,
            'handlers' => [
                ResponseHandler::class,
                OrderExportHandler::class
            ]
        ]
    ];

    /**
     * @inheritDoc
     */
    public function register(): void
    {
        parent::register();

        $this->registerHandlers();

        $this->app->singleton(ShippingServiceMapper::class);

        $this->app->extend(
            EventService::class,
            static function (EventService $eventManager) {
                $eventManager->addEvent(
                    EventConstant::EVENT_IMMULESOFT_RESPONSE_MESSAGE,
                    'Send response messages to IMMuleSoft Ingram'
                );

                $eventManager->addEvent(
                    EventConstant::EVENT_IMMULESOFT_ORDER_EXPORT,
                    'Send orders to IMMuleSoft Ingram'
                );

                return $eventManager;
            }
        );
    }

    public function scheduleCommands(): void
    {
        parent::scheduleCommands();
        $schedule = $this->app->make(Schedule::class);

        $cronSchedule = $this->getConfig(ConfigConstant::ORDER_CRON) ?? '*/5 * * * *';
        $schedule->command('wmg:fulfillment immulesoft.order')
            ->name('Export EU orders to Ingram Micro')
            ->cron($cronSchedule);

        $cronSchedule = $this->getConfig(ConfigConstant::ORDER_STATUS_CRON) ?? '*/10 * * * *';
        $schedule->command('wmg:fulfillment immulesoft.order.status')
            ->name('Process order statuses from Ingram Micro')
            ->cron($cronSchedule);

        $cronSchedule = $this->getConfig(ConfigConstant::PRODUCT_CRON) ?? '0 */1 * * *';
        $schedule->command('wmg:fulfillment immulesoft.product')
            ->name('Process product updates from Ingram Micro')
            ->cron($cronSchedule);

        $cronSchedule = $this->getConfig(ConfigConstant::STOCK_CRON) ?? '0 */1 * * *';
        $schedule->command('wmg:fulfillment immulesoft.stock')
            ->name('Process stock updates from Ingram Micro')
            ->cron($cronSchedule);
    }


    /**
     * Register Handlers
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    private function registerHandlers(): void
    {
        $this->bindDependencies(OrderHandler::class, [
            '$config' => static function () {
                $configService = app()->make(ConfigService::class);
                return [
                    AbstractOrderHandler::CONFIG_SOURCE => $configService
                        ->getJson(ConfigConstant::IMMULESOFT_SOURCE_MAP, ['IM']),
                    AbstractOrderHandler::CONFIG_SIZE => $configService->get(
                        ConfigConstant::IMMULESOFT_ORDER_BATCH_SIZE,
                        self::DEFAULT_ORDER_LIMIT
                    ),
                ];
            }
        ]);

        //Add handlers
        $this->registerFulfillmentHandler('immulesoft.order', OrderHandler::class);
        $this->registerFulfillmentHandler('immulesoft.order.status', OrderStatusHandler::class);
        $this->registerFulfillmentHandler('immulesoft.product', ProductHandler::class);
        $this->registerFulfillmentHandler('immulesoft.stock', StockHandler::class);
    }
}
