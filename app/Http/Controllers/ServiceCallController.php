<?php
namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ServiceEventCall;
use App\Core\Services\EventService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

/**
 * Get services calls
 *
 * Class \App\Http\ServiceCallController
 *
 * @category WMG
 * @package  WMG
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2018
 * @link     http://www.wmg.com
 */
class ServiceCallController extends Controller
{
    const MAX_PER_PAGE = 10;
    private $eventManager;

    public function __construct(EventService $eventManager)
    {
        $this->eventManager = $eventManager;
    }

    /**
     * Check event calls
     * @param $appId
     * @param      $event
     * @param null $status
     * @param int  $limit
     *
     * @return array|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function all($appId, $event, $status = null, $limit = self::MAX_PER_PAGE)
    {
        try {
            $service = Service::where('app_id', '=', $appId)->firstOrFail();

            $calls = ServiceEventCall::with('callResponses');
            switch (strtolower($status)) {
                case 'error':
                    $calls->whereIn('status', [
                        ServiceEventCall::STATUS_HARD_ERROR,
                        ServiceEventCall::STATUS_SOFT_ERROR
                    ]);
                    break;
                case 'ready':
                    $calls->where('status', '=', ServiceEventCall::STATUS_BEING_DELIVERED);
                    break;
                case 'delivered':
                    $calls->where('status', '=', ServiceEventCall::STATUS_BEING_DELIVERED);
                    break;
            }

            if ($event !== '*') {
                $eventIds = $service->events()->where('event', '=', $event)->pluck('id');
                $calls->whereIn('parent_id', $eventIds);
            }
            $result = [];
            $calls->offset(0)->limit($limit)
                ->orderBy('id', 'desc');

            foreach ($calls->get() as $call) {
                $current = $call->toArray();
                $current['data'] = $call->getData()->toArray();
                $result[] = $current;
            }
            return $result;
        } catch (\Exception $e) {
            return response(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * Retry service
     * @param \Illuminate\Support\Facades\Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function retry(Request $request)
    {
        try {
            $parameters = [];
            foreach ($request->all() as $key => $val) {
                $parameters['--'. $key] = $val;
            }
            Artisan::call('wmg:service:call', $parameters);
            $message = str_replace("\n", '.', Artisan::output());
            return response(['status' => 'success', 'message' => $message]);
        } catch (\Exception $e) {
            return response(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }

    public function body($search)
    {
        $calls = ServiceEventCall::with('callResponses')
            ->where('data', 'like', "%$search%");
        return $calls->get()->toArray();
    }
}
