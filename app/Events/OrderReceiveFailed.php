<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;
use Exception;

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
class OrderReceiveFailed
{
    use SerializesModels;

    public $orderData;
    public $exception;

    /**
     * ServiceFailed constructor.
     *
     * @param array      $orderData
     * @param \Exception $exception
     */
    public function __construct(array $orderData, Exception $exception)
    {
        $this->orderData = $orderData;
        $this->exception = $exception;
    }
}
