<?php
namespace App\OrderAction\Services;

use App\OrderAction\ActionHandlers\ActionHandlerInterface;
use App\OrderAction\Models\OrderAction;
use App\OrderAction\Exceptions\NoActionHandlerException;

/**
 * Order action services that manage
 * execution and cancellation
 *
 * Class OrderActionService
 * @category WMG
 * @package  App\OrderAction\Services
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class OrderActionService
{
    private $handlers;

    /**
     * OrderActionService constructor.
     * @param iterable|array $handlers
     */
    public function __construct(iterable $handlers = [])
    {
        $this->handlers = $handlers;
    }

    public function addHandler(ActionHandlerInterface $handler)
    {
        $this->handlers[] = $handler;
    }

    public function getHandlers()
    {
        return $this->handlers;
    }

    public function getHandler($action)
    {
        if (!array_key_exists($action, $this->handlers)) {
            throw new NoActionHandlerException('No action handler for '. $action);
        }
        return $this->handlers[$action];
    }

    public function execute(OrderAction $orderAction)
    {
        $this->run('execute', $orderAction);
    }

    public function cancel(OrderAction $orderAction)
    {
        $this->run('cancel', $orderAction);
    }

    private function run($method, $orderAction)
    {
        $orders = $orderAction->orders;
        $action = $orderAction->action;
        $hasOrderHandler = false;
        foreach ($this->handlers as $handler) {
            if ($handler->getName() === $action) {
                $hasOrderHandler = true;
                foreach ($orders as $order) {
                    $handler->$method($order, $orderAction, null);
                }
            }
        }
        if (!$hasOrderHandler) {
            throw new NoActionHandlerException('No action handler for '. $action);
        }
    }
}
