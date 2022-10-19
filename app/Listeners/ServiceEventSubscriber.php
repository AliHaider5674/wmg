<?php

namespace App\Listeners;

use App\Core\Services\EventService;

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
class ServiceEventSubscriber
{
    const EVENT_PREFIX = 'service.events';
    const EVENT_ITEM_SHIPPED = 'magento.logistics.warehouse_management.lines_shipped';

    protected $eventManager;

    /**
     * ServiceEventSubscriber constructor.
     *
     * @param \App\Core\Services\EventService $eventManager
     */
    public function __construct(EventService $eventManager)
    {
        $this->eventManager = $eventManager;
    }

    /**
     * Handle the event.
     *
     * @param $event
     * @param $data
     *
     * @return void
     * @throws \Exception
     */
    public function handle($event, $data)
    {
        $this->eventManager->processEvent($event, $data);
    }

    public function subscribe($events)
    {
        $events->listen(
            self::EVENT_PREFIX . '.*',
            'App\Listeners\ServiceEventSubscriber@handle'
        );
    }
}
