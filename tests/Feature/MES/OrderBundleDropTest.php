<?php

namespace Tests\Feature\MES;

use App\Core\Enums\OrderItemStatus;
use App\Core\Handlers\BatchOrderHandler;
use App\Exceptions\NoRecordException;
use App\MES\Constants\ConfigConstant;
use App\MES\FlatIo;
use App\MES\Handler\IO\FlatOrder;
use App\MES\Handler\OrderHandler;
use App\Models\OrderDrop;
use App\Models\OrderItem;
use App\Models\Order;
use App\Models\OrderAddress;
use Exception;
use FileDataConverter\File\Flat;
use WMGCore\Services\FileSystemService;

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
class OrderBundleDropTest extends MesTestCase
{
    /**
     * Test if order got dropped
     *
     * @return void
     * @throws Exception
     */
    public function testNormalOrderDrop()
    {
        $goodOrder = Order::factory()->count(1)->create()->each(
            function ($order) {
                $order->addresses()->save(OrderAddress::factory()->make([
                        'customer_address_type' => OrderAddress::CUSTOMER_ADDRESS_TYPE_SHIPPING
                    ]));
                $order->orderItems()->saveMany(OrderItem::factory()->count(1)
                    ->make(['item_type' => OrderItem::PRODUCT_TYPE_PHYSICAL, 'source_id' => 'us']));
            }
        )->first();

        Order::factory()->count(1)->create()->each(
            function ($order) {
                $order->addresses()->save(OrderAddress::factory()->make([
                    'customer_address_type' => OrderAddress::CUSTOMER_ADDRESS_TYPE_SHIPPING
                ]));
                $order->orderItems()->saveMany(OrderItem::factory()->count(1)
                    ->make([
                        'item_type' => OrderItem::PRODUCT_TYPE_PHYSICAL, 'source_id' => 'us',
                        'quantity' => 0,
                        'quantity_shipped' => 0
                    ]));
            }
        )->first();
        $this->warehouseService->callHandler($this->app->make(OrderHandler::class));
        $drop = OrderDrop::first();


        /** @var Flat $orderIO */
        $orderIO = FlatIo::factoryFlatIo(app_path('MES/Schema/order.yml'));
        /** @var FileSystemService $fileService */
        $fileService = $this->app->make(FileSystemService::class);
        $file = $fileService->useConnection(config('mes.connections.remote'))
            ->getFullPath(config('mes.directories.order.live') . '/'. $drop->content);
        $orderId = null;
        $count = 1;
        $orderIO->read($file, function ($data, $section) use (&$orderId, &$count) {
            if (in_array($section, ['order'])) {
                $orderId = $data['customer_order_reference'];
                $this->assertEquals($count, 1);
                $count--;
            }
        });
        $this->assertEquals($orderId, $goodOrder->order_id);
    }
}
