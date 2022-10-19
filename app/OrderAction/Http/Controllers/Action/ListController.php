<?php
namespace App\OrderAction\Http\Controllers\Action;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\OrderAction\Models\OrderAction;
use App\OrderAction\Services\OrderActionService;

/**
 * List all actions
 *
 * Class ListController
 * @category WMG
 * @package  App\OrderAction\Http\Controllers\Action
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class ListController extends Controller
{
    private $orderActionService;

    public function __construct(OrderActionService $orderActionService)
    {
        $this->orderActionService = $orderActionService;
    }

    public function __invoke()
    {
        $handlers = $this->orderActionService->getHandlers();
        $list = [];
        foreach ($handlers as $handler) {
            $list[] = $handler->getName();
        }
        return $list;
    }
}
