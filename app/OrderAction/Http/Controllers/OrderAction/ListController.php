<?php
namespace App\OrderAction\Http\Controllers\OrderAction;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\OrderAction\Models\OrderAction;

/**
 * List order action
 *
 * Class ListController
 * @category WMG
 * @package  App\OrderAction\Http\Controllers\OrderAction
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class ListController extends Controller
{
    public function __invoke($id = null)
    {
        if ($id !== null) {
            return OrderAction::where('id', $id)->first();
        }
        return OrderAction::get();
    }
}
