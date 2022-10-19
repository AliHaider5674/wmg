<?php

namespace App\OrderAction\Jobs;

use App\Events\ServiceFailed;
use App\Exceptions\ServiceException;
use App\Services\AlertEventService;
use App\Services\ServiceEventCallService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\ServiceEventCall;
use App\Core\Services\ClientService;
use App\Models\ServiceEventCallResponse;

/**
 * Queue worker to send out events
 *
 * Class ServiceEvent
 * @category WMG
 * @package  App\Jobs
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class OrderActionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var \App\Models\ServiceEventCall */
    protected $serviceEventCall;

    /**
     * ServiceEvent constructor.
     *
     * @param \App\Models\ServiceEventCall $serviceEventCall
     */
    public function __construct(ServiceEventCall $serviceEventCall)
    {
        $this->serviceEventCall = $serviceEventCall;
    }

    /**
     * Send events
     *
     * @param \App\Core\Services\ClientService $clientManager
     *
     * @return void
     * @throws \Exception
     * @SuppressWarnings(PHPMD)
     */
    public function handle(ClientService $clientManager)
    {
    }

    /**
     * Fail job
     * @param \Exception $exception
     * @return void
     * @SuppressWarnings(PHPMD)
     */
    public function failed($exception)
    {
    }
}
