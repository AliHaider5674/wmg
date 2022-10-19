<?php

namespace Tests\Feature\Shopify\OrderAck;

use App\MES\Handler\ShipmentHandler;
use App\Shopify\Clients\ShopifySDK;
use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use PHPUnit\Framework\MockObject\MockObject;
use App\Shopify\ServiceClients\Handlers\Ack\ShopifyService;

/**
 * Class OrderAckWithSameReasonsTest
 *
 * @package Tests\Feature
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2021
 * @link     http://www.wmg.com
 *
 * @SuppressWarnings(LongClassName)
 */
class OrderAckWithSameReasonsAndExistingInfoTest extends OrderAckCase
{

    /**
     * getReasonCodeMap
     * @return mixed
     */
    protected function getReasonCodeMap() : array
    {
        $reasonCodesMap = '{"2":{"order_status":"On Hold","reason":"Title Deleted"},
        "3":{"order_status":null,"reason":"No Stock"},
        "4":{"order_status":"Drop Error","reason":"The stock is out"},
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
        return array ('note' =>
            "Prefix Reason: [sku:0010467410823 reason:The stock is out|sku:0030633337921 reason:The stock is out] Suffix",//phpcs:ignore
            'tags' => 'On Hold,Drop Error'
        );
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

        $existingShopifyData = array(
            'note' => 'Prefix Reason: [sku:0010467410823 reason:The stock is out] Suffix',
            'tags' => 'On Hold'
        );
        $shopifyServiceMock->method('getExistingShopifyOrder')->willReturn($existingShopifyData);

        $shopifySDK = $this->app->make(ShopifySDK::class);

        $shopifyServiceMock->expects($this->atLeastOnce())
            ->method('updateOrder')
            ->with($shopifySDK, $orderId, $expectedPostData);
            //->willReturn(array());

        $this->app->instance(ShopifyService::class, $shopifyServiceMock);

        return $shopifyServiceMock;
    }

    /**
     * @throws BindingResolutionException
     * @throws Exception
     */
    public function testOrderItemsWithSameReasonCode()
    {
        $orderId = 1;
        $orders = $this->setTestOrders($orderId);
        $this->shipmentFaker->fake($orders, 0);

        $this->setAckHandler($orderId);
        $this->warehouseService->callHandler(
            $this->app->make(ShipmentHandler::class)
        );
    }
}
