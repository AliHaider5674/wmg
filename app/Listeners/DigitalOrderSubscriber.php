<?php

namespace App\Listeners;

use App\Models\Order;
use App\Models\OrderItem;
use App\MES\Handler\DigitalHandler;
use Exception;

/**
 * Ship Digital products right after received
 *
 * Class OrderReceivedSubscriber
 * @category WMG
 * @package  App\Listeners
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class DigitalOrderSubscriber
{
    public const ORDER_RECEIVED_EVENT = 'internal.order.received';

    /**
     * @var DigitalHandler
     */
    protected DigitalHandler $digitalHandler;

    /**
     * DigitalOrderSubscriber constructor.
     *
     * @param DigitalHandler $digitalHandler
     */
    public function __construct(
        DigitalHandler $digitalHandler
    ) {
        $this->digitalHandler = $digitalHandler;
    }

    /**
     * Ship digital product right after received
     * @param Order $order
     * @return void
     * @throws Exception
     */
    public function handle(Order $order)
    {
        $orderItems = OrderItem::where('parent_id', $order->id)
            ->whereIn('item_type', OrderItem::ALL_DIGITAL_TYPES)
            ->whereRaw('quantity > quantity_shipped')
            ->get();

        if ($orderItems->count() < 1) {
            return;
        }

        //Mark shipped
        try {
            foreach ($orderItems as $item) {
                $this->digitalHandler->processItem($item);
            }
        } catch (Exception $e) {
            $this->digitalHandler->rollback($e);
        }
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
            'App\Listeners\DigitalOrderSubscriber@handle'
        );
    }
}
