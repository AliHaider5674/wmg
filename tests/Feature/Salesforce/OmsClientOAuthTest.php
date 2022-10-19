<?php
namespace Tests\Feature\Salesforce;

use App\Models\Order;
use App\Models\OrderItem;
use App\Salesforce\Clients\SalesforceOmsSDK;
use GuzzleHttp\Psr7\Response;
use Mockery as M;

/**
 * Test renew oauth token
 */
class OmsClientOAuthTest extends OmsEventBase
{
    private $sdk;
    public function setUp(): void
    {
        parent::setUp();
        $this->sdk = M::mock(SalesforceOmsSDK::class)->makePartial();
        $this->app->bind(SalesforceOmsSDK::class, function () {
            return $this->sdk;
        });
    }

    public function test401UnAuth()
    {
        $this->sdk->shouldReceive('request')->andReturn(new Response(401, [], 'Unauthorized'));
        $orders = Order::factory()->count(1)->create()->each(
            fn($order) => $order->orderItems()->saveMany(
                OrderItem::factory()->count(1)->make([
                    'item_type' => OrderItem::PRODUCT_TYPE_PHYSICAL
                ])
            )
        );
        $this->shipmentFaker->fake($orders);
        $this->sdk->expects('invalidToken');
        $this->artisan('wmg:fulfillment mes.shipment');
    }
}
