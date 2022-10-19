<?php

namespace Tests\Feature\IM;

use App\Services\WarehouseService;
use Faker;
use App\Models\OrderItem;
use App\Models\Order;
use App\Models\OrderAddress;
use App\IM\Handler\IO\ApiShipment;
use App\IM\Handler\ShipmentHandler;
use Illuminate\Support\Collection;
use Tests\Feature\WarehouseTestCase;

/**
 * Class ApiShipmentUpdateTest
 * @category WMG
 * @package  Tests\Feature\IM
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License
 * @link     http://www.wmg.com
 */
class ApiShipmentUpdateTest extends WarehouseTestCase
{
    public const API_RESPONSE_STATUS_SUCCESS = true;
    public const API_RESPONSE_STATUS_FAILURE = false;

    public const TEST_WAREHOUSE_HANDLER = 'apiShipment';

    protected $faker;

    /**
     * @var Collection
     */
    protected Collection $orders;

    /**
     * @var WarehouseService
     */
    protected WarehouseService $warehouseService;


    /**
     * setUp
     */
    public function setUp():void
    {
        parent::setUp();

        $this->warehouseService = app()->make(WarehouseService::class);

        $this->faker = Faker\Factory::create();


        //create test orders
        //with physical order item
        //with billing and shipping address
        //to drop at Ingram Micro warehouse
        $this->orders = Order::factory()->count(1)->create()->each(
            function ($order) {
                $order->addresses()->save(OrderAddress::factory()->make([
                    'customer_address_type' => OrderAddress::CUSTOMER_ADDRESS_TYPE_SHIPPING
                ]));
                $order->addresses()->save(OrderAddress::factory()->make([
                        'customer_address_type' => OrderAddress::CUSTOMER_ADDRESS_TYPE_BILLING
                ]));
                $order->orderItems()->save(OrderItem::factory()->make([
                    'item_type' => OrderItem::PRODUCT_TYPE_PHYSICAL,
                    'source_id' => 'im',
                    'sku' => '0016861744229',
                    'quantity' => random_int(1, 9)
                ]));
            }
        );
    }

    /**
     * @param $response
     * @return mixed
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function getMockShipmentHandler($response)
    {
        $apiShipmentIoMock = $this->getMockBuilder(ApiShipment::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getDataFromWarehouse'])
            ->getMock();

        $apiShipmentIoMock->method('getDataFromWarehouse')
            ->willReturn($response);

        return $this->app->make(
            ShipmentHandler::class,
            ['ioAdapter' => $apiShipmentIoMock]
        );
    }

    /**
     * getMockShipmentResponse
     *
     * @param $returnStatus
     * @param $orders
     *
     * @return array
     */
    protected function getMockResponse($returnStatus, Collection $orders): array
    {
        return [
            'HasSucceeded' => $returnStatus,
            'Shipment' => $this->getMockShipments($orders),
            'Messages' => $this->getMockMessages(),
        ];
    }

    /**
     * @param Collection $orders
     * @return array
     */
    protected function getMockShipments(Collection $orders): array
    {
        return $orders->map(fn ($order) => [
            'OrderReference' => $order->order_id,
            'ShipmentMethod' => '',
            'TrackingNumber' => uniqid(),
            'ReturnTrackingNumber' => uniqid(),
            'DateShipped' => $this->getRandomDate(),
            'ShipmentLines' => $this->getOrderLines($order),
        ])->toArray();
    }

    /**
     * @return Faker\Generator|string
     */
    protected function getRandomDate()
    {
        $event = $this->faker->dateTimeBetween('-1 years', 'now');
        return $event->format(DATE_RFC3339_EXTENDED);
    }

    /**
     * @param $order
     * @return array
     */
    protected function getOrderLines($order)
    {
        $orderItems = $order->orderItems;
        $items = [];
        foreach ($orderItems as $orderItem) {
            $item = array();

            $item['LineNumber'] = $orderItem->id;
            $item['SKU'] = $orderItem->sku;
            $item['QuantityShipped'] = $orderItem->quantity;

            $items[] = $item;
        }

        return $items;
    }

    protected function getMockMessages()
    {
        return [];
    }


    /**
     * testSuccessfulShipmentUpdate
     *
     */
    public function testSuccessfulShipmentUpdate()
    {
        $mockHandler = $this->getMockShipmentHandler($this->getMockResponse(
            self::API_RESPONSE_STATUS_SUCCESS,
            $this->orders
        ));

        //trigger warehouse order drop process using API Order Handler
        $this->warehouseService->callHandler($mockHandler);

        //Ensure order items have be shipped
        $orderItems = OrderItem::whereIn('parent_id', $this->orders->pluck('id')->toArray())->get();
        foreach ($orderItems as $item) {
            $this->assertEquals($item->quantity, $item->quantity_shipped, 'Order Item Quantity_Shipped');
        }
    }
}
