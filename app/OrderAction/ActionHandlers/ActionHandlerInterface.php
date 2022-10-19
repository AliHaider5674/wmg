<?php
namespace App\OrderAction\ActionHandlers;

use App\Models\Order;
use App\OrderAction\Models\OrderAction;

interface ActionHandlerInterface
{
    public function execute(Order $order, OrderAction $orderAction, $data = null) : void;
    public function cancel(Order $order, OrderAction $orderAction, $data = null) : void;
    public function getName() : String;
}
