<?php

namespace App\Console\Commands;

use App\Models\AlertEvent;
use WMGCore\Services\ConfigService;
use Illuminate\Console\Command;
use App\Mail\Alert as MailAlert;
use Illuminate\Support\Facades\Mail;

/**
 * Send out alert emails
 *
 * Class Fulfillment
 * @category WMG
 * @package  App\Console\Commands
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class Alert extends Command
{
    const CONFIG_PATH_ALERT_SEND_TO = 'alert.send.to';
    const CONFIG_PATH_ALERT_SEND_TO_NOTICE = 'alert.send.to.notice';
    const CONFIG_PATH_ALERT_SEND_TO_MEDIUM = 'alert.send.to.medium';
    const CONFIG_PATH_ALERT_SEND_TO_CRITICAL = 'alert.send.to.critical';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wmg:alert';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send alert emails';
    const LEVEL_NOTICE = 'Notice';
    const LEVEL_MEDIUM = 'Medium';
    const LEVEL_CRITICAL = 'Critical';

    /**
     * Send alert emails
     * @param \WMGCore\Services\ConfigService $configService
     * @return void
     */
    public function handle(ConfigService $configService)
    {
        $sentCount = 0;
        $emailList = $this->getEmailLists($configService);
        foreach ($emailList as $level => $list) {
            $alertEvents = AlertEvent::where('level', $level)->get();
            $count = $alertEvents->count();
            if ($count == 0) {
                continue;
            }
            $sentCount += $count;

            Mail::to($list)->send(new MailAlert($alertEvents));
            foreach ($alertEvents as $alertEvent) {
                $alertEvent->delete();
            }
        }
        $this->line("Sent $sentCount alerts.");
    }

    private function getEmailLists(ConfigService $configService)
    {
        $lists = [];
        $defaultList = $configService->get(self::CONFIG_PATH_ALERT_SEND_TO);
        $noticeList = $configService->get(self::CONFIG_PATH_ALERT_SEND_TO_NOTICE, $defaultList);
        $mediumList = $configService->get(self::CONFIG_PATH_ALERT_SEND_TO_MEDIUM, $defaultList);
        $criticalList = $configService->get(self::CONFIG_PATH_ALERT_SEND_TO_CRITICAL, $defaultList);

        if ($noticeList !== null) {
            $lists[AlertEvent::LEVEL_NOTICE] = explode(',', $noticeList);
        }

        if ($mediumList !== null) {
            $lists[AlertEvent::LEVEL_MEDIUM] = explode(',', $mediumList);
        }

        if ($criticalList !== null) {
            $lists[AlertEvent::LEVEL_CRITICAL] = explode(',', $criticalList);
        }
        return $lists;
    }
}
