<?php

namespace Tests\Feature\OrderAction;

use App\OrderAction\Services\OrderActionService;
use Tests\TestCase;
use App\Models\Order;
use App\OrderAction\Models\OrderAction;

/**
 * Test Digital Shipment
 *
 * Class DigitalHandlerTest
 * @category WMG
 * @package  Tests\Feature\Warehouse
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 * @group orderAction
 * @testdox Test order action operation
 */
class CancelOrderActionTest extends TestCase
{
    /** @var OrderActionService */
    private $orderActionService;
    public function setUp():void
    {
        parent::setUp();
        $this->orderActionService = app()->make(OrderActionService::class);
    }

    /**
     * Cancel on hold action
     *
     * @return void
     * @throws \Exception
     * @testdox cancel an on hold action -> put order back to original status
     */
    public function testOnHoldCancellation()
    {
        $order = Order::factory()->create();
        $orderAction = new OrderAction();
        $orderAction->fill([
            'order_id' => $order->getAttribute('order_id'),
            'sales_channel' => $order->getAttribute('sales_channel'),
            'action' => 'On Hold'
        ]);
        $orderAction->save();
        event('internal.order.received', $order);
        $order->refresh();
        $this->assertEquals(Order::STATUS_ONHOLD, $order->status);
        $orderAction->refresh();

        $this->orderActionService->cancel($orderAction);
        $order->refresh();
        $this->assertEquals(Order::STATUS_RECEIVED, $order->status);
    }
}
