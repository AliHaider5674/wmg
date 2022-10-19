<?php

namespace App\Events;

use App\Models\ServiceEventCall;
use Illuminate\Queue\SerializesModels;
use Throwable;

/**
 * Service Failed Event
 * When fulfillment try to send request to registered services
 *
 * Class ServiceFailed
 * @category WMG
 * @package  App\Events
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class ServiceFailed
{
    use SerializesModels;

    public $eventCall;
    public $exception;

    /**
     * ServiceFailed constructor.
     *
     * @param \App\Models\ServiceEventCall $eventCall
     * @param \Throwable                   $exception
     */
    public function __construct(ServiceEventCall $eventCall, Throwable $exception)
    {
        $this->eventCall = $eventCall;
        $this->exception = $exception;
    }
}
