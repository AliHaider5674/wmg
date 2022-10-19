<?php
namespace App\OrderAction\Http\Controllers\OrderAction;

use App\OrderAction\Services\OrderActionService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\OrderAction\Models\OrderAction;

/**
 * Delete order action
 *
 * Class DeleteController
 * @category WMG
 * @package  App\OrderAction\Http\Controllers\OrderAction
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class DeleteController extends Controller
{
    private $orderActionService;
    public function __construct(OrderActionService $orderActionService)
    {
        $this->orderActionService = $orderActionService;
    }

    public function __invoke($id)
    {
        try {
            $action = OrderAction::where('id', '=', $id)->first();
            if (!$action) {
                return response()->json(['status' => 'error', 'message' => 'Order Action do not exist.'], 400);
            }
            $this->orderActionService->cancel($action);
            $action->delete();
            return response()->json(['status' => 'success', 'message' => 'Order Action has been deleted.'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Internal error.' . $e->getMessage()], 500);
        }
    }
}
