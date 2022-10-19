<?php

namespace App\MES\Providers;

use App\Core\Handlers\AbstractOrderHandler;
use App\Core\Handlers\BatchOrderHandler;
use App\MES\Constants\ConfigConstant;
use App\MES\Faker\AckFaker;
use App\MES\Faker\ShipmentFaker;
use App\MES\FlatIo;
use App\MES\Handler\StockHandler;
use App\MES\Handler\ShipmentHandler;
use App\MES\Handler\DigitalHandler;
use App\MES\Handler\AckHandler;
use WMGCore\Providers\ModuleServiceProvider;
use WMGCore\Providers\Traits\Module\HasCommands;
use WMGCore\Providers\Traits\Module\HasConfig;
use WMGCore\Providers\Traits\Module\HasMigrations;
use App\Providers\Traits\RegistersFulfillmentHandlers;
use App\Services\WarehouseService;
use WMGCore\Services\ConfigService;
use FileDataConverter\File\Flat;
use FileDataConverter\File\Flat\Data\Schema;
use FileDataConverter\File\Flat\Data\FixLength;
use FileDataConverter\File\Flat\Data\SectionDetector;
use App\MES\Handler\IO\FlatOrder;
use App\MES\Handler\IO\FlatShipment;
use App\MES\Handler\IO\FlatAck;
use App\MES\Handler\IO\FlatStock;
use Illuminate\Console\Scheduling\Schedule;
use App\MES\Handler\OrderHandler as MesOrderHandler;

/**
 * Provider for warehouse
 *
 * Class OrderServiceProvider
 * @category WMG
 * @package  App\Providers
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MESServiceProvider extends ModuleServiceProvider
{
    use RegistersFulfillmentHandlers, HasMigrations, HasConfig, HasCommands;

    public $configKey = 'mes';

    /**
     * @var string|null
     */
    private $mesRemoteConnection;

    /**
     * @var string|null
     */
    private $mesLocalConnection;


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
        parent::register();

        $this->app->singleton(WarehouseService::class);
        $this->registerFakers();
        $this->registerHandlers();
    }


    public function scheduleCommands(): void
    {
        $schedule = $this->app->make(Schedule::class);
               //ACK
        $ackCron = $this->getConfig(ConfigConstant::ACK_CRON) ?? '*/5 * * * *';
        if (!empty($ackCron)) {
            $schedule->command('wmg:fulfillment mes.ack')
                ->name('Fulfillment Ack')
                ->cron($ackCron);
        }

        //ORDER
        $orderCron = $this->getConfig(ConfigConstant::ORDER_CRON) ?? '*/5 * * * *';
        if (!empty($orderCron)) {
            $schedule->command('wmg:fulfillment mes.order')
                ->name('Fulfillment Order')
                ->cron($orderCron);
        }

        //Shipment
        $shipmentCron = $this->getConfig(ConfigConstant::SHIPMENT_CRON) ?? '*/5 * * * *';
        if (!empty($shipmentCron)) {
            $schedule->command('wmg:fulfillment mes.shipment')
                ->name('Fulfillment Shipment')
                ->cron($shipmentCron);
        }

        //DIGITAL
        $digitalCron = $this->getConfig(ConfigConstant::DIGITAL_CRON) ?? '*/5 * * * *';
        if (!empty($digitalCron)) {
            $schedule->command('wmg:fulfillment mes.digital')
                ->name('Fulfillment Digital')
                ->cron($digitalCron);
        }

        //MES STOCK
        $mesStockCron = $this->getConfig(ConfigConstant::STOCK_CRON) ?? '0 1 * * *';
        if (!empty($mesStockCron)) {
            $schedule->command('wmg:fulfillment mes.stock')
                ->name('Fulfillment MES Stock')
                ->cron($mesStockCron);
        }
    }

    /**
     * Register fakers
     *
     * @return void
     */
    private function registerFakers(): void
    {
        //DEV TOOLS
        $this->bindDependencies(ShipmentFaker::class, [
            Flat::class => static function () {
                return FlatIo::factoryFlatIo(app_path('MES/Schema/shipment.yml'));
            },
            '$config' => function () {
                return [
                    'dir' => config('mes.directories.shipment.live-fake'),
                    'prefix' => FlatShipment::FILE_PREFIX,
                    'remote_connection' => $this->getMesRemoteConnection()
                ];
            }
        ]);

        $this->bindDependencies(AckFaker::class, [
            Flat::class => static function () {
                return FlatIo::factoryFlatIo(app_path('MES/Schema/ack.yml'));
            },
            '$config' => function () {
                return [
                    'dir' => config('mes.directories.ack.live-fake'),
                    'prefix' => FlatAck::FILE_PREFIX,
                    'remote_connection' => $this->getMesRemoteConnection()
                ];
            }
        ]);
    }

    /**
     * Register Handlers
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    private function registerHandlers(): void
    {
        $this->app->when(FixLength::class)
            ->needs('$lineEncoding')
            ->give(function () {
                return app()->make(ConfigService::class)
                    ->get(ConfigConstant::MES_CHARSET, 'ISO-8859-15');
            });
        $this->app->when(FixLength::class)
            ->needs('$dataEncoding')
            ->give('UTF-8');
        $this->bindDependencies(FlatOrder::class, [
            Flat::class => static function ($app) {
                $schemaFile = app_path('MES/Schema/order.yml');
                $schema = new Schema();
                $schema->loadSchema($schemaFile);
                $sectionDetector = new SectionDetector($schema);
                $fixLengthParser = $app->make(FixLength::class, ['config' => $schema]);

                return new Flat($fixLengthParser, $sectionDetector);
            },
            '$config' => function () {
                return [
                    'tmp_dir' => config('mes.directories.order.tmp'),
                    'live_dir' => config('mes.directories.order.live'),
                    'remote_connection' => $this->getMesRemoteConnection(),
                    'local_connection' => $this->getMesLocalConnection(),
                ];
            }
        ]);
        $this->bindDependencies(MesOrderHandler::class, [
            '$config' => static function () {
                $configService = app()->make(ConfigService::class);

                return [
                    AbstractOrderHandler::CONFIG_SOURCE => $configService
                        ->getJson(ConfigConstant::MES_SOURCE_MAP, ['US', 'GNAR']),
                    AbstractOrderHandler::CONFIG_SIZE => $configService->get(
                        ConfigConstant::MES_ORDER_BATCH_SIZE,
                        BatchOrderHandler::DEFAULT_ORDER_LIMIT
                    ),
                ];
            }
        ]);
        $this->bindDependencies(FlatShipment::class, [
            Flat::class => function ($app) {
                $schemaFile = app_path('MES/Schema/shipment.yml');
                $schema = new Schema();
                $schema->loadSchema($schemaFile);
                $sectionDetector = new SectionDetector($schema);
                $fixLengthParser = $app->make(FixLength::class, ['config' => $schema]);
                return new Flat($fixLengthParser, $sectionDetector);
            },
            '$config' => function () {
                return [
                    'history_dir' => config('mes.directories.shipment.history'),
                    'live_dir' => config('mes.directories.shipment.live'),
                    'tmp_dir' => config('mes.directories.shipment.tmp'),
                    'remote_connection' => $this->getMesRemoteConnection(),
                    'local_connection' => $this->getMesLocalConnection()
                ];
            }
        ]);

        $this->bindDependencies(FlatAck::class, [
            Flat::class => static function ($app) {
                $schemaFile = app_path('MES/Schema/ack.yml');
                $schema = new Schema();
                $schema->loadSchema($schemaFile);
                $sectionDetector = new SectionDetector($schema);
                $fixLengthParser = $app->make(FixLength::class, ['config' => $schema]);
                return new Flat($fixLengthParser, $sectionDetector);
            },
            '$config' => function () {
                return [
                    'history_dir' => config('mes.directories.ack.history'),
                    'live_dir' => config('mes.directories.ack.live'),
                    'tmp_dir' => config('mes.directories.ack.tmp'),
                    'remote_connection' => $this->getMesRemoteConnection(),
                    'local_connection' => $this->getMesLocalConnection()
                ];
            },
        ]);
        $this->bindDependencies(FlatStock::class, [
            Flat::class => static function ($app) {
                $schemaFile = app_path('MES/Schema/stock.yml');
                $schema = new Schema();
                $schema->loadSchema($schemaFile);
                $sectionDetector = new SectionDetector($schema);
                $fixLengthParser = $app->make(FixLength::class, ['config' => $schema]);
                return new Flat($fixLengthParser, $sectionDetector);
            },
            '$config' => function () {
                return [
                    'history_dir' => config('mes.directories.stock.history'),
                    'live_dir' => config('mes.directories.stock.live'),
                    'tmp_dir' => config('mes.directories.stock.tmp'),
                    'remote_connection' => $this->getMesRemoteConnection(),
                    'local_connection' => $this->getMesLocalConnection()
                ];
            }
        ]);
        //Add handlers
        $this->registerFulfillmentHandler('mes.order', MesOrderHandler::class);
        $this->registerFulfillmentHandler('mes.shipment', ShipmentHandler::class);
        $this->registerFulfillmentHandler('mes.digital', DigitalHandler::class);
        $this->registerFulfillmentHandler('mes.ack', AckHandler::class);
        $this->registerFulfillmentHandler('mes.stock', StockHandler::class);
    }

    /**
     * Would be good to get these into config but not possible right now since
     * config is merged in boot method and services are registered in the
     * register method before the boot method is called.
     *
     * @return string
     */
    private function getMesRemoteConnection(): ?string
    {
        return $this->mesRemoteConnection
            ?? $this->mesRemoteConnection = config('mes.connections.remote');
    }

    /**
     * @return string
     */
    private function getMesLocalConnection(): ?string
    {
        return $this->mesLocalConnection
            ?? $this->mesLocalConnection = config('mes.connections.local');
    }
}
