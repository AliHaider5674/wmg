<?php

namespace App\Listeners;

use App\Events\FileSystemFailed;
use App\Events\ServiceFailed;
use App\Events\OrderReceiveFailed;
use App\Models\AlertEvent;
use App\Core\Services\EventService;
use App\Models\ServiceEventCall;
use App\Services\AlertEventService;

/**
 * Subscriber that observer all service.events
 *
 * Class ServiceEventSubscriber
 * @category WMG
 * @package  App\Listeners
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class AlertEventSubscriber
{


    protected $alertEventService;

    /**
     * ServiceEventSubscriber constructor.
     *
     * @param AlertEventService $alertEventService
     */
    public function __construct(AlertEventService $alertEventService)
    {
        $this->alertEventService = $alertEventService;
    }

    /**
     * Handle the event.
     *
     * @param $serviceFailed
     *
     * @return void
     * @throws \Exception
     */
    public function serviceFailed(ServiceFailed $serviceFailed)
    {
        $name = $serviceFailed->eventCall->serviceEvent->service->name . ':'.
                $serviceFailed->eventCall->serviceEvent->event. ':'.
                $serviceFailed->eventCall->id;
        $content = $serviceFailed->exception->getMessage();
        if ($serviceFailed->eventCall->serviceEvent->event === EventService::EVENT_ITEM_SHIPPED) {
            $content .= ", Order ID: " . $serviceFailed->eventCall->getData()->getHiddenOrderNumber();
        }


        $level = AlertEvent::LEVEL_NOTICE;
        $type = AlertEvent::TYPE_REQUEST_ERROR;
        if ($serviceFailed->eventCall->status === ServiceEventCall::STATUS_HARD_ERROR) {
            $level = AlertEvent::LEVEL_CRITICAL;
            $type = AlertEvent::TYPE_CONNECTION_ERROR;
        }
        $this->alertEventService->addEvent(
            $name,
            $content,
            $type,
            $level
        );
    }

    /**
     * File system error
     * @param \App\Events\FileSystemFailed $fileSystemFailed
     * @return void
     */
    public function fileSystemFailed(FileSystemFailed $fileSystemFailed)
    {
        $this->alertEventService->addEvent(
            $fileSystemFailed->connectionName,
            $fileSystemFailed->exception->getMessage(),
            AlertEvent::TYPE_CONNECTION_ERROR,
            AlertEvent::LEVEL_CRITICAL
        );
    }

    /**
     * Error on order received
     * @param \App\Events\OrderReceiveFailed $orderReceiveFailed
     * @return void
     */
    public function orderReceiveFailed(OrderReceiveFailed $orderReceiveFailed)
    {
        $this->alertEventService->addEvent(
            'Order Received Error',
            $orderReceiveFailed->exception->getMessage(),
            AlertEvent::TYPE_RECEIVE_ERROR,
            AlertEvent::LEVEL_MEDIUM
        );
    }

    public function subscribe($events)
    {
        $events->listen(
            ServiceFailed::class,
            'App\Listeners\AlertEventSubscriber@serviceFailed'
        );

        $events->listen(
            FileSystemFailed::class,
            'App\Listeners\AlertEventSubscriber@fileSystemFailed'
        );

        $events->listen(
            OrderReceiveFailed::class,
            'App\Listeners\AlertEventSubscriber@orderReceiveFailed'
        );
    }
}
