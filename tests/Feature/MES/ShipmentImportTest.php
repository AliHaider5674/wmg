<?php

namespace Tests\Feature\MES;

use App\MES\Handler\IO\FlatShipment;
use App\Models\FailedParameter;
use App\Models\ServiceEventCall;
use App\Models\OrderItem;
use App\Models\Order;
use App\Models\OrderAddress;
use App\MES\Faker\ShipmentFaker;
use App\Models\Service\Model\ShipmentLineChange;
use App\MES\Handler\ShipmentHandler;
use App\User;
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
class ShipmentImportTest extends MesTestCase
{

    /**
     * Test all order shipped
     *
     * @return void
     * @throws \Exception
     */
    public function testStandardShipmentImport()
    {
        $orders = Order::factory()->count(3)->create()->each(
            function ($order) {
                $order->addresses()->save(OrderAddress::factory()->make([
                        'customer_address_type' => OrderAddress::CUSTOMER_ADDRESS_TYPE_SHIPPING
                    ]));
                $order->orderItems()->saveMany(OrderItem::factory()->count(2)
                    ->make(['item_type' => OrderItem::PRODUCT_TYPE_PHYSICAL]));
            }
        );
        $this->shipmentFaker->fake($orders);
        $shipmentHandler = $this->app->make(ShipmentHandler::class);
        $this->warehouseService->callHandler($shipmentHandler);

        //Verify all shipped
        $this->assertTrue($this->isAllShipped($orders));
    }

    /**
     * Test import order that do
     * not exist
     *
     * @return void
     * @throws \App\Exceptions\NoRecordException
     * @throws \Exception
     */
    public function testOrderNotExist()
    {
        $orders = Order::factory()->count(3)->create()->each(
            fn($order) => $order->orderItems()->saveMany(
                OrderItem::factory()->count(2)->make([
                    'item_type' => OrderItem::PRODUCT_TYPE_PHYSICAL
                ])
            )
        );

        //Is file exist
        $fakeData = $this->shipmentFaker->fake($orders);

        //Is recorded error parameter
        $deletedOrder = $orders->shift();
        $deletedOrder->delete();
        $this->warehouseService->callHandler(
            $this->app->make(ShipmentHandler::class)
        );
        $failedParamCount = FailedParameter::get()->count();
        $this->assertEquals(1, $failedParamCount);

        //Is file removed;
        $isFileExist = $this->fileSystem->exists($fakeData['file']);
        $this->assertFalse($isFileExist);

        //Is ALL Shipped
        $this->assertTrue($this->isAllShipped($orders));
    }


    public function testBackorderShipments()
    {
        $user = User::factory()->create();
        //Register services
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
        $this->shipmentFaker->fake($orders, 0);

        //Is ALL Backordered
        $this->warehouseService->callHandler(
            $this->app->make(ShipmentHandler::class)
        );
        $this->assertTrue($this->isAllBackOrdered($orders));

        //Check if services request file
        $calls = ServiceEventCall::get();
        $this->assertEquals(3, $calls->count());
        foreach ($calls as $call) {
            $this->assertInstanceOf(ShipmentLineChange::class, $call->getData());
        }
    }

    public function testConnectionError()
    {
        $flatShipmentIoMock = $this->getMockBuilder(FlatShipment::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['start'])
            ->getMock();
        $flatShipmentIoMock->method('start')
            ->will($this->throwException(new Exception('Unable to connect')));

        $shipmentHandler = $this->app->make(ShipmentHandler::class, [
            'ioAdapter' => $flatShipmentIoMock
        ]);

        $orders = Order::factory()->count(3)->create()->each(
            function ($order) {
                $order->addresses()->save(OrderAddress::factory()->make([
                        'customer_address_type' => OrderAddress::CUSTOMER_ADDRESS_TYPE_SHIPPING
                    ]));
                $order->orderItems()->saveMany(OrderItem::factory()->count(2)
                    ->make(['item_type' => OrderItem::PRODUCT_TYPE_PHYSICAL]));
            }
        );
        $this->shipmentFaker->fake($orders);
        try {
            $this->warehouseService->callHandler($shipmentHandler);
        } catch (\Exception $e) {
            foreach ($orders as $order) {
                foreach ($order->orderItems as $item) {
                    $this->assertEquals(0, $item->getAttribute('quantity_shipped'), 'Order Shipped');
                }
            }
        }
    }
}
