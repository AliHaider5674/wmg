<?php

namespace Tests\Feature\Shopify\OrderAck;

use App\Models\Order;
use App\Models\OrderItem;
use App\Shopify\Clients\ShopifySDK;
use App\User;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\Feature\MES\MesTestCase;
use WMGCore\Services\ConfigService;
use App\Shopify\ServiceClients\Handlers\AckHandler;
use App\Shopify\ServiceClients\Handlers\Ack\ShopifyService;
use App\Shopify\ServiceClients\Handlers\Ack\OrderProcessor;
use Illuminate\Database\Eloquent\Factories\Sequence;

/**
 * Class OrderAckTest
 * @package Tests\Feature
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2021
 * @link     http://www.wmg.com
 */
class OrderAckCase extends MesTestCase
{

    public array $sku = array();

    public function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub

        $this->setTestService();
    }

    /**
     * setTestService
     */
    protected function setTestService()
    {
        $user = User::factory()->create();
        //Register services
        $service = [
            "app_id" => "shopify",
            "app_url" => "https://wmg-sandbox.myshopify.com",
            "name" => "shopify",
            "client" => "shopify.restful",
            "events" => ["*"],
            "event_rules" => [],
            "addition" => [
                "shop_url" => "http://shopify.test",
                "api_key" => "developer",
                "password" => "password1"
            ]
        ];

        $response = $this->actingAs($user, 'api')->json('POST', 'api/1.0/service', $service);
        $response->assertStatus(200);
    }

    /**
     * setTestOrders
     * @return Collection|Model
     */
    public function setTestOrders($orderId)
    {
        return Order::factory()->count(1)->create(
            ['order_id' => $orderId]
        )->each(
            fn($order) => $order->orderItems()->saveMany(
                OrderItem::factory()->count(2)
                    ->state(
                        new Sequence(
                            ['sku' => '0010467410823'],
                            ['sku' => '0030633337921'],
                        )
                    )
                    ->make([
                    'item_type' => OrderItem::PRODUCT_TYPE_PHYSICAL,
                ])
            )
        );
    }

    /**
     * getReasonCodeMap
     * @return mixed
     */
    protected function getReasonCodeMap() : array
    {
        $reasonCodesMap = '{"2":{"order_status":"On Hold","reason":"Title Deleted"},
        "3":{"order_status":null,"reason":"No Stock"},
        "4":{"order_status":null,"reason":"The stock is out"},
        "A":{"order_status":"Drop Error","reason":"Title Unknown"},
        "B":{"order_status":"On Hold","reason":"Trade Restrictions"}}';

        return  json_decode($reasonCodesMap, true);
    }

    /**
     * getExpectedPostData
     * @return string[]
     */
    protected function getExpectedPostData(): array
    {
        return array ();
    }

    /**
     * getShopifyServiceMock
     * @param int $orderId
     * @return ShopifyService|mixed|MockObject
     * @throws BindingResolutionException
     */
    protected function getShopifyServiceMock(int $orderId)
    {
        $shopifyServiceMock = $this->getMockBuilder(ShopifyService::class)
            ->onlyMethods(['getExistingShopifyOrder', 'updateOrder'])
            ->getMock();

        $expectedPostData = $this->getExpectedPostData();

        $shopifyServiceMock->method('getExistingShopifyOrder')->willReturn(array());

        $shopifySDK = $this->app->make(ShopifySDK::class);

        $shopifyServiceMock->expects($this->atLeastOnce())
            ->method('updateOrder')
            ->with($shopifySDK, $orderId, $expectedPostData);
          //  ->willReturn(array());

        $this->app->instance(ShopifyService::class, $shopifyServiceMock);

        return $shopifyServiceMock;
    }

    /**
     * setAckHandler
     * @param int $orderId
     * @throws BindingResolutionException
     */
    protected function setAckHandler(int $orderId)
    {
        //Setup AckHandler
        $configServices = $this->app->make(ConfigService::class);
        $orderProcessor = $this->app->make(OrderProcessor::class);

        $shopifyServiceMock = $this->getShopifyServiceMock($orderId);

        //Setup AckHandler mock objects
        $ackHandlerMock = $this->getMockBuilder(AckHandler::class)
            ->setConstructorArgs([$configServices, $orderProcessor, $shopifyServiceMock])
            ->onlyMethods(['getReasonCodeMap', 'getOrderId'])
            ->getMock();

        $ackHandlerMock->method('getReasonCodeMap')->willReturn($this->getReasonCodeMap());
        $ackHandlerMock->method('getOrderId')->willReturn($orderId);

        $this->app->instance(AckHandler::class, $ackHandlerMock);
    }
}
