<?php
namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use App\Core\Services\ExternalService;

/**
 * Add, remove and get services
 *
 * Class \App\Http\ShipController
 *
 * @category WMG
 * @package  WMG
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2018
 * @link     http://www.wmg.com
 */
class ServiceController extends Controller
{
    protected $serviceManager;
    public function __construct(ExternalService $manager)
    {
        $this->serviceManager = $manager;
    }

    /**
     * Get services
     * @param null $appId
     * @return array
     */
    public function all($appId = null)
    {
        $services = Service::with('events');
        if ($appId) {
            $services = $services->where('app_id', $appId);
        }
        $result = [];
        foreach ($services->get() as $item) {
            $data = $item->toArray();
            $data['event_rules'] = $item->getEventRules();
            $result[] = $data;
        }
        return $result;
    }

    /**
     * add service
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function add(Request $request)
    {
        try {
            $request->validate([
                'app_id' => 'required',
                'name' => 'required',
                'client' => 'required',
                'events' => 'required|array',
                'event_rules' => 'array'
            ]);

            $service = $this->serviceManager->addService(
                $request->get('app_id'),
                $request->get('name'),
                $request->get('url', ''),
                $request->get('client'),
                $request->get('event_rules'),
                $request->get('events'),
                $request->get('addition', [])
            );
            $serviceData = $service->toArray();
            $serviceData['events'] = $service->events()->pluck('event');
            return ['status' => 'success', 'data' => $serviceData];
        } catch (\Exception $e) {
            return response(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }


    /**
     * Remove app
     *
     * @param $appId
     * @return array|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function remove($appId)
    {
        try {
            $service = Service::where('app_id', $appId)->firstOrFail();
            $service->delete();
            return ['status' => 'success'];
        } catch (\Exception $e) {
            return response(['status' => 'error', 'message' => 'Unable to delete service.'], 403);
        }
    }
}
