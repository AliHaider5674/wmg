<?php
namespace Tests\Feature\Shopify\ExpandOrder;

use App\Catalog\Repositories\ProductRepository;
use App\Shopify\Enums\ShopifyOrderStatus;
use App\Shopify\Factories\Shopify\OrderFactory;
use App\Shopify\Repositories\ShopifyOrderRepository;
use Carbon\Carbon;
use Database\Factories\ServiceFactory;
use Tests\TestCase;

/**
 * Test preorder bundle
 */
class ExpandBundleOrderTest extends TestCase
{
    /**
     * @group shopify
     * @dataProvider preorderBundleOrder
     */
    public function testPreorderBundle($orderData)
    {
        /** @var ProductRepository $productRepo */
        $productRepo = $this->app->make(ProductRepository::class);
        /** @var ShopifyOrderRepository $orderRepo */
        $orderRepo = $this->app->make(ShopifyOrderRepository::class);
        $service = ServiceFactory::new()->create();
        $order = OrderFactory::new([
            'data' => json_encode($orderData),
            'status' => ShopifyOrderStatus::FETCHED,
            'order_id' => 4296248590489,
            'service_id' => $service->id
            ])->create();
        $this->artisan('wmg:fulfillment shopify.expand_orders');
        $order = $orderRepo->find($order->id);

        $product = $productRepo->loadBySku('0000002361086');
        $preorder = new Carbon($product->preorder);
        $this->assertNotNull($product, 'has product created');
        $this->assertEquals('2022-03-31', $preorder->format('Y-m-d'), 'Has correct preorder information');
        $this->assertEquals(ShopifyOrderStatus::EXPANDED, $order->status);
    }

    public function preorderBundleOrder()
    {
        $order = json_decode(file_get_contents(__DIR__ . '/../data/preorder_bundle_order.json'));
        return [[$order]];
    }
}
