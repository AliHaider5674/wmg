<?php
namespace App\OrderAction\Subscribers;

use App\Models\Order;
use App\Models\OrderItem;
use App\OrderAction\Services\OrderActionService;
use App\OrderAction\Models\OrderAction;

/**
 * A subscriber that monitor orders coming in
 *
 * Class OrderReceivedSubscriber
 * @category WMG
 * @package  App\OrderAction\Subscribers
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class OrderReceivedSubscriber
{
    const CHUNK_SIZE = 500;
    const ORDER_RECEIVED_EVENT = 'internal.order.received';
    private $orderActionService;

    public function __construct(OrderActionService $orderActionService)
    {
        $this->orderActionService = $orderActionService;
    }

    /**
     * Ship digital product right after received
     * @param \App\Models\Order $order
     * @return void
     * @throws \Exception
     */
    public function handle(Order $order)
    {
        $actions = $this->getAction($order);
        $actions->chunk(self::CHUNK_SIZE, function ($chunk) use ($order) {
            foreach ($chunk as $action) {
                $this->orderActionService->execute($action);
            }
        });
    }

    private function getAction(Order $order)
    {
        return OrderAction::where(function ($query) use ($order) {
            $query->whereIn('sales_channel', [ '*', $order->getAttribute('sales_channel')])
                ->where('order_id', '=', $order->getAttribute('order_id'));
        });
    }

    /**
     * Events that listen to
     *
     * @param $events
     * @return void
     */
    public function subscribe($events)
    {
        $events->listen(
            self::ORDER_RECEIVED_EVENT,
            self::class . '@handle'
        );
    }
}
