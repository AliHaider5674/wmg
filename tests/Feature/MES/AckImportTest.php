<?php

namespace Tests\Feature\MES;

use App\MES\Faker\AckFaker;
use App\Models\FailedParameter;
use App\Models\ServiceEventCall;
use App\User;
use App\MES\Handler\AckHandler;
use App\Models\OrderItem;
use App\Models\Order;
use Exception;

/**
 * Shipment shipment files
 *
 * Class ShipmentImportTest
 * @category WMG
 * @package  Tests\Feature\MES
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AckImportTest extends MesTestCase
{
    /**
     * Test all order shipped
     *
     * @return void
     * @throws Exception
     */
    public function testStandardShipmentImport()
    {
        $user = User::factory()->create();
        $service = [
            "app_id" => "m1",
            "name" => "m1",
            "client" => "rest",
            "events" => ["*"],
            "event_rules" => [],
            "addition" => [
                "wsdl" => "https://admin.localhost.wmiecom.com/api/v2_soap?wsdl=1",
                "username" => "developer",
                "api_key" => "password1"
            ]
        ];
        $response = $this->actingAs($user, 'api')->json('POST', 'api/1.0/service', $service);
        $response->assertStatus(200);

        $orders = Order::factory()->count(3)->create()->each(
            fn($order) => $order->orderItems()->saveMany(
                OrderItem::factory()->count(2)->make([
                    'item_type' => OrderItem::PRODUCT_TYPE_PHYSICAL
                ])
            )
        );

        $this->ackFaker->fake($orders);
        $this->warehouseService->callHandler($this->app->make(AckHandler::class));

        //Verify all ack
        $this->assertTrue($this->isAllAck($orders));


        //Verify service data generate correctly
        $map = [];
        foreach ($orders as $order) {
            $map[$order->id] = $order->getAttribute('order_id');
        }
        $serviceCalls = ServiceEventCall::get();
        foreach ($serviceCalls as $serviceCall) {
            /**@var ServiceEventCall $serviceCall*/
            $id = $serviceCall->getData()->getHiddenData('order_id');
            $this->assertTrue(array_key_exists($id, $map));
            unset($map[$id]);
        }
    }



    /**
     * Test ack order not exist
     *
     * @return void
     * @throws \App\Exceptions\NoRecordException
     * @throws Exception
     */
    public function testAckOrderNotExist()
    {
        $orders = Order::factory()->count(3)->create()->each(
            fn($order) => $order->orderItems()->saveMany(
                OrderItem::factory()->count(2)->make([
                    'item_type' => OrderItem::PRODUCT_TYPE_PHYSICAL
                ])
            )
        );
        $fakeData = $this->ackFaker->fake($orders);

        //Failed Parameter Recorded.
        $deletedOrder = $orders->shift();
        $deletedOrder->delete();
        $this->warehouseService->callHandler($this->app->make(AckHandler::class));
        $failedParamCount = FailedParameter::get()->count();
        $this->assertEquals(1, $failedParamCount);

        //Is file got removed
        $isFileExist = $this->fileSystem->exists($fakeData['file']);
        $this->assertFalse($isFileExist);

        //Ack others
        $this->assertTrue($this->isAllAck($orders));
    }

    /**
     * Test all item got marked as backorder
     *
     * @return void
     * @throws \App\Exceptions\NoRecordException
     */
    public function testAckBackOrders()
    {
        $orders = Order::factory()->count(3)->create()->each(
            fn($order) => $order->orderItems()->saveMany(
                OrderItem::factory()->count(2)->make([
                    'item_type' => OrderItem::PRODUCT_TYPE_PHYSICAL
                ])
            )
        );
        $this->ackFaker->fake($orders, 4);

        $this->warehouseService->callHandler($this->app->make(AckHandler::class));
        $this->assertFalse($this->isAllBackOrdered($orders));
    }
}
