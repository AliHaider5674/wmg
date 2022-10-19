<?php
namespace App\OrderAction\ActionHandlers;

use App\Models\Order;
use App\OrderAction\Models\OrderAction;

/**
 * Order action - handle putting order on hold
 *
 * Class OnHoldHandler
 * @category WMG
 * @package  App\OrderAction\ActionHandlers
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class OnHoldHandler implements ActionHandlerInterface
{
    const NAME = 'On Hold';
    const SETTING_ORDER_IDS = 'on_hold_handler_order_ids';
    public function getName() : String
    {
        return self::NAME;
    }

    public function execute(Order $order, OrderAction $orderAction, $data = null) : void
    {
        if ($order->status != Order::STATUS_RECEIVED) {
            return;
        }
        $orderIds = $orderAction->getSetting(self::SETTING_ORDER_IDS);
        if ($orderIds === null) {
            $orderIds = [];
        }
        $orderIds[$order->id] = $order->status;
        $orderAction->updateSetting(self::SETTING_ORDER_IDS, $orderIds);
        $orderAction->save();
        $order->status = Order::STATUS_ONHOLD;
        $order->save();
    }


    public function cancel(Order $order, OrderAction $orderAction, $data = null) : void
    {
        $orderIds = $orderAction->getSetting(self::SETTING_ORDER_IDS);
        if ($orderIds === null) {
            $orderIds = [];
        }
        if (!array_key_exists($order->id, $orderIds)) {
            return;
        }
        $order->status = $orderIds[$order->id];
        $order->save();
        unset($orderIds[$order->id]);
        $orderAction->updateSetting(self::SETTING_ORDER_IDS, $orderIds);
        $orderAction->save();
    }
}
