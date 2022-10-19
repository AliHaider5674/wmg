<?php

namespace App\Console;

use App\Models\ServiceEventCall;
use WMGCore\Services\ConfigService;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

/**
 * Kernel
 *
 * Class Kernel
 * @category WMG
 * @package  App\Console
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        /** @var ConfigService $configService */
        $configService = app()->make(ConfigService::class);

        //SMS STOCK
        $smsStockCron = $configService->get('fulfillment.sms.stock.cron', '0 9 * * *');
        if (!empty($smsStockCron)) {
            $schedule->command('wmg:fulfillment sms_stock')
                ->name('Fulfillment SMS Stock')
                ->cron($smsStockCron);
        }

        //STOCK EXPORT
        $stockExportCron = $configService->get('fulfillment.stock.export.cron', '30 9 * * *');
        if (!empty($stockExportCron)) {
            $schedule->command('wmg:fulfillment stock_export')
                ->name('Stock Export')
                ->cron($stockExportCron);
        }

        //THREAD CLEAN
        $schedule->command('wmg:thread', ['action' => 'clean'])
            ->name('Thread Clean')->everyTenMinutes();


        //ALERT THREAD RUN TOO LONG
        $schedule->command('wmg:thread', ['action' => 'alert'])
            ->name('Send Thread Alerts')->everyTenMinutes();

        //ALERT
        $schedule->command('wmg:alert')
            ->name('alert')
            ->everyFiveMinutes();

        //Move calls
        $callCleanUpHours = $configService->get('service.call.cleanup.hour', 24 * 60);
        if ($callCleanUpHours) {
            $schedule->command('wmg:service:call:cleanup', [
                '--hour' => $callCleanUpHours,
            ])
            ->name('Call Clean Up')
            ->hourly();
        }

        //SERVICE CALL AUDIT
        $serviceCallAuditCron = $configService->get('service.call.audit.cron', '*/5 * * * *');
        if ($serviceCallAuditCron) {
            $hours = intval($configService->get('service.call.audit.cron.hour', 5));
            $schedule->command('wmg:service:call', [
                '--from' => Carbon::now('UTC')->subHour($hours)->toDateTimeString(),
                '--status' => ServiceEventCall::STATUS_HARD_ERROR,
                '--max' => 0
            ])
            ->name('Service Call Audit')
            ->cron($serviceCallAuditCron);
        }
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
