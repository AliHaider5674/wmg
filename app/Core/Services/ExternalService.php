<?php
namespace App\Core\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Service;
use App\Models\ServiceEvent;
use App\Exceptions\NoRecordException;

/**
 * Manage services
 *
 * Class ExternalService
 * @category WMG
 * @package  App\Models\Service
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class ExternalService
{
    private $eventManager;
    public function __construct(EventService $eventManager)
    {
        $this->eventManager = $eventManager;
    }

    /**
     * Remove service
     * @param $appId
     * @return void
     * @throws \App\Exceptions\NoRecordException
     * @throws \Exception
     */
    public function removeService($appId)
    {
        /** @var Service $service */
        $service = Service::where('app_id', $appId)->first();
        if (!$service) {
            throw new NoRecordException('App '. $appId. ' not found.');
        }
        $service->delete();
    }

    /**
     * Add/Update Service
     * @param $appId
     * @param       $name
     * @param       $url
     * @param       $client
     * @param       $eventRules
     * @param array $events
     * @param array $addition
     *
     * @return \App\Models\Service
     */
    public function addService(
        $appId,
        $name,
        $url,
        $client,
        array $eventRules = [],
        array $events = ['*'],
        array $addition = []
    ) {
        $service = Service::where('app_id', $appId)->first();
        if (!$service) {
            $service = new Service();
        }
        $service->fill([
            'app_id' => $appId,
            'app_url' => $url,
            'name' => $name,
            'status' => Service::STATUS_ACTIVE,
            'client' => $client,
            'event_rules' => \GuzzleHttp\json_encode($eventRules),
        ]);
        $service->setAddition($addition);
        $events = $this->getEvents($events);
        if ($service->id) {
            $existEvents = $service->events()->pluck('event')->toArray();
            $events = array_diff($events, $existEvents);
        }
        DB::transaction(function () use ($service, $events) {
            $service->save();
            foreach ($events as $event) {
                $newEvent = new ServiceEvent();
                $newEvent->fill([
                    'parent_id' => $service->id,
                    'event' =>  $event,
                    'status' => ServiceEvent::STATUS_ACTIVE
                ]);
                $newEvent->save();
            }
        });
        return $service;
    }

    /**
     * Get Events
     * @param $events
     * @return array
     */
    private function getEvents($events)
    {
        $addEvents = [];
        foreach ($events as $event) {
            if (array_key_exists($event, $this->eventManager->getEvents())) {
                $addEvents[] = $event;
            } elseif ($event === '*') {
                //Register all
                $addEvents = array_keys($this->eventManager->getEvents());
                break;
            }
        }
        return $addEvents;
    }
}
