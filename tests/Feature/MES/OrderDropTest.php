<?php

namespace Tests\Feature\MES;

use App\Core\Enums\OrderItemStatus;
use App\Core\Handlers\BatchOrderHandler;
use App\Exceptions\NoRecordException;
use App\MES\Constants\ConfigConstant;
use App\MES\Handler\IO\FlatOrder;
use App\MES\Handler\OrderHandler;
use App\Models\OrderItem;
use App\Models\Order;
use App\Models\OrderAddress;
use Exception;

/**
 * Test order drop
 *
 * Class OrderDropTest
 * @category WMG
 * @package  Tests\Feature
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OrderDropTest extends MesTestCase
{
    /**
     * Test if order got dropped
     *
     * @return void
     * @throws Exception
     */
    public function testNormalOrderDrop()
    {
        $orders = Order::factory()->count(3)->create()->each(
            function ($order) {
                $order->addresses()->save(OrderAddress::factory()->make([
                        'customer_address_type' => OrderAddress::CUSTOMER_ADDRESS_TYPE_SHIPPING
                    ]));
                $order->orderItems()->saveMany(OrderItem::factory()->count(2)
                    ->make(['item_type' => OrderItem::PRODUCT_TYPE_PHYSICAL, 'source_id' => 'us']));
            }
        );

        $this->warehouseService->callHandler($this->app->make(OrderHandler::class));

        $orders = Order::whereIn('id', $orders->pluck('id')->toArray())->get();
        foreach ($orders as $order) {
            $order->orderItems->each(static function (OrderItem $orderItem) {
                self::assertEquals(
                    OrderItemStatus::DROPPED,
                    $orderItem->drop_status,
                    'Order status'
                );
            });
        }
    }

    /**
     * @todo FlatOrder::getName does not exist. Are we sure this test is correct?
     */
    public function testOrderDropWithConnectionError()
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
        $flatOrderIoMock = $this->getMockBuilder(FlatOrder::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['finish'])->addMethods(['getName'])
            ->getMock();
        $flatOrderIoMock->method('finish')
            ->will($this->throwException(new Exception('Unable to connect')));

        $orderHandler = $this->app->make(BatchOrderHandler::class, [
            'config' => [ConfigConstant::MES_SOURCE_MAP => ['US', 'GNAR']],
            'ioAdapter' => $flatOrderIoMock
        ]);

        try {
            $this->warehouseService->callHandler($orderHandler);
        } catch (Exception $e) {
            $orders = Order::whereIn('id', $orders->pluck('id')->toArray())->get();
            foreach ($orders as $order) {
                $this->assertNotEquals(Order::STATUS_DROPPED, $order->status, 'Order status');
            }
        }
    }

    /**
     * Test no order drop exception
     *
     * @return void
     * @throws Exception
     */
    public function testNoOrderExport()
    {
        $this->expectException(NoRecordException::class);
        $this->expectExceptionMessage('No orders are ready to be dropped.');

        $handler = $this->app->make(OrderHandler::class);

        $this->warehouseService->callHandler($handler);
    }

    /**
     * Assert if order model is equal to file order
     * @param $orders
     * @param $file
     *
     * @return void
     */
    protected function assertOrdersEqual($orders, $file)
    {
        $numberOfLines = 0;
        $fileOrders = [];
        $this->orderIo->read($file, function ($data, $section) use (&$numberOfLines, &$fileOrders) {
            if (in_array($section, ['header', 'order', 'item', 'footer'])) {
                $numberOfLines++;

                switch ($section) {
                    case 'order':
                        $fileOrders[$data['order_index']] = $data;
                        $fileOrders[$data['order_index']]['items'] = [];
                        break;
                    case 'item':
                        $fileOrders[$data['order_index']]['items'][] = $data;
                        break;
                }
            }
        });
        $index = 0;
        foreach ($orders as $order) {
            $index++;
            $fileOrder = $fileOrders[$index];
            $this->assertEquals($fileOrder['customer_order_reference'], $order->order_id, 'Order number');
            $itemIndex = 0;
            foreach ($order->orderItems as $item) {
                $fileOrderItem = $fileOrder['items'][$itemIndex];
                $this->assertEquals(
                    [
                        'sku' => $fileOrderItem['sku'],
                    ],
                    [
                        'sku' => $item->sku
                    ],
                    'Item attributes'
                );
                $itemIndex++;
            }
        }
    }
}
