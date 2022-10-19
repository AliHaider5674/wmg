<?php
namespace App\OrderAction\Http\Controllers\OrderAction;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\OrderAction\Models\OrderAction;

/**
 * Save order action
 *
 * Class SaveController
 * @category WMG
 * @package  App\OrderAction\Http\Controllers\OrderAction
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class SaveController extends Controller
{
    public function __invoke(Request $request)
    {
        try {
            $id = $request->get('id');
            $orderAction = new OrderAction();
            if ($id) {
                $orderAction = OrderAction::where('id', '=', $id)->first();
            }

            if ($orderAction === null) {
                return response()->json(['status' => 'error', 'message' => 'Order action do not exist.'], 400);
            }
            $orderAction->fill($request->all());
            $orderAction->save();
            return $orderAction;
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Internal error.'], 500);
        }
    }
}
