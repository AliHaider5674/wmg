<?php

namespace App\SMS\Providers;

use WMGCore\Providers\ModuleServiceProvider;
use WMGCore\Providers\Traits\Module\HasConfig;
use WMGCore\Providers\Traits\Module\HasMigrations;
use App\Providers\Traits\RegistersFulfillmentHandlers;
use App\SMS\Constants\ConfigConstant;
use App\SMS\Handler\StockHandler;
use FileDataConverter\File\Csv;
use Symfony\Component\Filesystem\Filesystem;
use WMGCore\Services\ConfigService;
use App\SMS\Handler\IO\Stock;

/**
 * Provider for warehouse
 *
 * Class SMS Service Provider
 * @category WMG
 * @package  App\Providers
 * @author   Daniel Campbell <daniel@primor.tech>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class SMSServiceProvider extends ModuleServiceProvider
{
    use RegistersFulfillmentHandlers, HasConfig, HasMigrations;

    /**
     * Prefix for config key
     *
     * @var string
     */
    protected $configKeyPrefix = 'filesystems';

    /**
     * Register services
     */
    public function register(): void
    {
        parent::register();
        $this->registerHandlers();
    }

    /**
     * Register Handlers
     *
     * @return void
     */
    private function registerHandlers(): void
    {
        //USE FLAT FILE AND MES ENDPOINT
        $this->app->bind(Csv::class, static function ($app) {
            return new Csv(
                ['has_header' => true],
                $app->make(Filesystem::class)
            );
        });

        $this->bindDependencies(Stock::class, [
            '$config' => static function ($app) {
                $configService = $app->get(ConfigService::class);
                return [
                    'history_dir' => $configService->get(ConfigConstant::SMS_HISTORY_STOCK_DIR, null),
                    'live_dir' => $configService->get(ConfigConstant::SMS_LIVE_STOCK_DIR, 'dropoff'),
                    'tmp_dir' => $configService->get(ConfigConstant::SMS_TMP_STOCK_DIR, 'dropoff'),
                    'remote_connection' => $configService->get(ConfigConstant::SMS_REMOTE_CONNECTION, 'sms_sftp'),
                    'local_connection' => $configService->get(ConfigConstant::SMS_LOCAL_CONNECTION, 'sms_local'),
                    'file_pattern' => $configService->get(ConfigConstant::SMS_FILE_PATTERN, null)
                ];
            },
        ]);
        $this->registerFulfillmentHandler('sms_stock', StockHandler::class);
    }
}
