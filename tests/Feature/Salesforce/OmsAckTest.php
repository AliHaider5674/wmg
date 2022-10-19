<?php
namespace Tests\Feature\Salesforce;

use App\Models\Order;
use App\Models\OrderItem;
use GuzzleHttp\Psr7\Response;
use League\OAuth2\Client\Token\AccessToken;

/**
 * Test OMS ACK calls
 */
class OmsAckTest extends OmsEventBase
{
    protected $omsSdk;

    public function testStandardAck()
    {
        $this->omsSdk->shouldReceive('config', 'setToken', 'newToken');
        $orders = Order::factory()->count(1)->create()->each(
            fn($order) => $order->orderItems()->saveMany(
                OrderItem::factory()->count(2)->make([
                    'item_type' => OrderItem::PRODUCT_TYPE_PHYSICAL
                ])
            )
        );
        $this->ackFaker->fake($orders);
        $this->omsSdk->expects('ack');
        $this->artisan('wmg:fulfillment mes.ack');
    }
}
