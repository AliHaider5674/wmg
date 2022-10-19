<?php

namespace App\Core\Services;

use App\Models\Service;
use App\Models\ServiceEvent;
use App\Models\ServiceEventCall;
use App\Models\Service\Model\Serialize;
use App\Jobs\ServiceEvent as ServiceEventJob;
use App\Models\Service\Event\ServiceRuleValidator;
use Exception;
use Illuminate\Foundation\Bus\DispatchesJobs;
use InvalidArgumentException;

/**
 * Manage service events.
 * It two major method:
 * 1. dispatch events
 * 2. listen to events
 *
 * Class EventService
 * @category WMG
 * @package  App\Models\Service
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class EventService
{
    use DispatchesJobs;

    public const QUEUE_NAME = 'service';
    public const EVENT_PREFIX = 'service.events';
    public const EVENT_ITEM_SHIPPED = 'item.shipped';
    public const EVENT_ITEM_WAREHOUSE_ACK = 'item.warehouse.received';
    public const EVENT_ITEM_ON_HOLD = 'item.warehouse.hold';
    public const EVENT_ITEM_RETURNED = 'item.warehouse.returned';
    public const EVENT_SOURCE_UPDATE = 'source.update';
    public const EVENT_ITEM_SHIPMENT_REQUEST = 'item.shipment.request';

    /**
     * @var array
     */
    private $events;

    /**
     * @var ServiceRuleValidator
     */
    private $eventRuleValidator;

    /**
     * EventService constructor.
     * @param ServiceRuleValidator $validator
     */
    public function __construct(ServiceRuleValidator $validator)
    {
        $this->eventRuleValidator = $validator;
        $this->initSupportEvents();
    }

    /**
     * @param $name
     * @param $description
     * @return $this
     */
    public function addEvent($name, $description): self
    {
        $this->events[$name] = $description;

        return $this;
    }

    /**
     * @return array
     */
    public function getEvents(): array
    {
        return $this->events;
    }

    /**
     * Dispatch events
     *
     * @param $event
     * @param $data
     *
     * @return $this
     */
    public function dispatchEvent($event, Serialize $data): self
    {
        event(self::EVENT_PREFIX . '.'. $event, $data);

        return $this;
    }

    /**
     * Process events
     *
     * @param string $event
     * @param $data
     *
     * @return void
     * @throws Exception
     */
    public function processEvent(string $event, $data): void
    {
        if (count($data) !== 1 || !$data[0] instanceof Serialize) {
            throw new InvalidArgumentException('Invalid type.');
        }

        /** @var Serialize $serviceModel */
        $serviceModel = $data[0];
        $event = substr($event, strlen(self::EVENT_PREFIX . '.'));
        $serviceEvents = ServiceEvent::whereIn('event', [$event, '*'])
            ->where('status', ServiceEvent::STATUS_ACTIVE)
            ->get();

        foreach ($serviceEvents as $serviceEvent) {
            /**@var ServiceEvent $serviceEvent */
            if ($serviceEvent->service->status !== Service::STATUS_ACTIVE) {
                continue;
            }

            //CHECK IF THE SERVICE'S EVENT RULES MATCHED
            if (!$this->eventRuleValidator->isPassed($serviceEvent->service, $serviceModel)) {
                continue;
            }

            //Process
            $serviceEventCall = new ServiceEventCall();
            $serviceEventCall->fill([
                'parent_id' => $serviceEvent->id,
                'status' => ServiceEventCall::STATUS_BEING_DELIVERED,
            ]);
            $serviceEventCall->setData($serviceModel);
            $serviceEventCall->save();

            $this->queueEventCall($serviceEventCall);
        }
    }

    /**
     * @param ServiceEventCall $serviceEventCall
     * @return $this
     */
    public function queueEventCall(ServiceEventCall $serviceEventCall): self
    {
        $serviceEventJob = new ServiceEventJob($serviceEventCall);
        $serviceEventJob->onQueue(self::QUEUE_NAME);
        $this->dispatch($serviceEventJob);

        return $this;
    }

    /**
     * Initiate events
     */
    private function initSupportEvents(): void
    {
        $this->events = [
            self::EVENT_ITEM_SHIPPED => 'Items are shipped by warehouse',
            self::EVENT_ITEM_WAREHOUSE_ACK => 'Items are ack by warehouse. Could be backordered or received',
            self::EVENT_SOURCE_UPDATE => 'Inventory source update',
            self::EVENT_ITEM_ON_HOLD => 'Items are put on hold by the warehouse',
            self::EVENT_ITEM_RETURNED => 'Items are returned to the warehouse',
            self::EVENT_ITEM_SHIPMENT_REQUEST => 'Request shipments for items'
        ];
    }
}
