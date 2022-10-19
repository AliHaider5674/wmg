<?php
namespace Tests\Feature\OrderAction;

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
*  @group orderAction
 * @testdox Test order action when receiving orders from external sys.
 */
class OrderReceivedTest extends TestCase
{
    /**
     * Test single action
     *
     * @return void
     * @throws \Exception
     * @testdox Create an order action with saleschannel -> Put order on hold after receive
     */
    public function testPutOnHoldStatusWithSingleOrder()
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
    }

    /**
     * Test multiple orders with single action
     * @testdox Create an order action + multiple action -> Put one order on hold
     * @return void
     */
    public function testPutOneOrderOnHoldWithMultipleOrders()
    {
        $orderOnHold = Order::factory()->create();
        $order = Order::factory()->create();
        $similarOnHold = Order::factory()->create([
            'order_id' => $orderOnHold->getAttribute('order_id'),
            'sales_channel' => 'DO NOT EXIST'
        ]);

        $orderAction = new OrderAction();
        $orderAction->fill([
            'order_id' => $orderOnHold->getAttribute('order_id'),
            'sales_channel' => $orderOnHold->getAttribute('sales_channel'),
            'action' => 'On Hold'
        ]);
        $orderAction->save();

        event('internal.order.received', $orderOnHold);
        event('internal.order.received', $order);
        event('internal.order.received', $similarOnHold);

        $orderOnHold->refresh();
        $order->refresh();
        $similarOnHold->refresh();

        $this->assertEquals(Order::STATUS_ONHOLD, $orderOnHold->status);
        $this->assertEquals(Order::STATUS_RECEIVED, $order->status);
        $this->assertEquals(Order::STATUS_RECEIVED, $similarOnHold->status);
    }

    /**
     * Test order with same order id, but different saleschannel
     * @testdox Create an order action without saleschannel -> Put order onhold
     * @return void
     */
    public function testPutOnHoldStatusWithOnlyOrderIdGiven()
    {
        $order = Order::factory()->create();
        $orderAction = new OrderAction();
        $orderAction->fill([
            'order_id' => $order->getAttribute('order_id'),
            'action' => 'On Hold'
        ]);
        $orderAction->save();
        event('internal.order.received', $order);
        $order->refresh();
        $this->assertEquals(Order::STATUS_ONHOLD, $order->status);
    }

    /**
     * Test same order id but different saleschannel,
     * Both put on hold
     * @testdox Create two orders with same order ID -> put both onhold
     * @return void
     */
    public function testPutBothOrderOnHoldFromDifferentSC()
    {
        $orderOnHold = Order::factory()->create();
        $orderOnHold2 = Order::factory()->create([
            'order_id' => $orderOnHold->getAttribute('order_id')
        ]);
        $orderAction = new OrderAction();
        $orderAction->fill([
            'order_id' => $orderOnHold->getAttribute('order_id'),
            'action' => 'On Hold'
        ]);
        $orderAction->save();

        event('internal.order.received', $orderOnHold);
        event('internal.order.received', $orderOnHold2);
        $orderOnHold->refresh();
        $orderOnHold2->refresh();

        $this->assertEquals(Order::STATUS_ONHOLD, $orderOnHold->status);
        $this->assertEquals(Order::STATUS_ONHOLD, $orderOnHold2->status);
    }
}
