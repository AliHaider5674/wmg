<?php
namespace App\Services;

use App\Models\ServiceEventCall;
use App\Jobs\ServiceEvent as ServiceEventJob;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Core\Services\EventService;
use WMGCore\Services\ConfigService;

/**
 * Event Services
 *
 * Class EventService
 * @category WMG
 * @package  App\Services
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class ServiceEventCallService
{
    use DispatchesJobs;

    const DEFAULT_RETRY_TIME_GAP = 60 ; // 60 Minutes
    private $configService;
    public function __construct(ConfigService $configService)
    {
        $this->configService = $configService;
    }

    public function retryHardErrorCalls()
    {
        $timeGap = $this->configService->get('service.event.call.retry.time.gap', self::DEFAULT_RETRY_TIME_GAP);
        $serviceEventCalls = ServiceEventCall::where('status', '=', ServiceEventCall::STATUS_HARD_ERROR)
            ->whereRaw("updated_at >= SUB_DATE(NOW() INTERVAL $timeGap MINUTE)")
            ->get();
        foreach ($serviceEventCalls as $serviceEventCall) {
            $job = new ServiceEventJob($serviceEventCall);
            $job->onQueue(EventService::QUEUE_NAME);
            $this->dispatch($job);
        }
    }
}
