<?php

namespace Tests\Feature\Warehouse;

use App\Models\OrderItem;
use App\Models\Order;
use App\MES\Handler\DigitalHandler;
use Exception;
use Tests\Feature\WarehouseTestCase;

/**
 * Test Digital Shipment
 *
 * Class DigitalHandlerTest
 * @category WMG
 * @package  Tests\Feature\Warehouse
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class DigitalShipmentTest extends WarehouseTestCase
{
    /**
     * @var DigitalHandler
     */
    private DigitalHandler $digitalHandler;

    /**
     * Set up test
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->digitalHandler = app()->make(DigitalHandler::class);
    }

    /**
     * Test normal shipment
     *
     * @return void
     * @throws Exception
     */
    public function testNormalShipment()
    {
        $orders = Order::factory()->count(3)->create()->each(
            static fn($order) => $order->orderItems()->saveMany(
                OrderItem::factory()
                    ->count(2)
                    ->make(['item_type' => OrderItem::PRODUCT_TYPE_DIGITAL])
            )
        );

        $this->digitalHandler->handle();
        $this->assertTrue($this->isAllShipped($orders));
    }
}
