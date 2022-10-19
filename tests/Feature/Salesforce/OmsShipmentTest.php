<?php
namespace Tests\Feature\Salesforce;

use App\Models\Order;
use App\Models\OrderItem;
use App\Salesforce\Clients\SalesforceOmsSDK;

/**
 * Test Oms Shipment
 */
class OmsShipmentTest extends OmsEventBase
{
    protected $omsSdk;

    public function testStandardShipment()
    {
        $this->omsSdk->shouldReceive('config', 'setToken');
        $this->app->instance(SalesforceOmsSDK::class, $this->omsSdk);
        $orders = Order::factory()->count(1)->create()->each(
            fn($order) => $order->orderItems()->saveMany(
                OrderItem::factory()->count(1)->make([
                    'item_type' => OrderItem::PRODUCT_TYPE_PHYSICAL
                ])
            )
        );
        $this->shipmentFaker->fake($orders);
        $this->omsSdk->expects('shipment');
        $this->artisan('wmg:fulfillment mes.shipment');
    }
}
