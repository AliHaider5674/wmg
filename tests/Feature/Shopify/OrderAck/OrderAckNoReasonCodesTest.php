<?php

namespace Tests\Feature\Shopify\OrderAck;

use App\MES\Handler\AckHandler;
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
 */
class OrderAckNoReasonCodesTest extends OrderAckCase
{
    /**
     * getExpectedPostData
     * @return string[]
     */
    protected function getExpectedPostData(): array
    {
        return array ('note' =>
            "Received ack from warehouse for: [0010467410823|0030633337921]"
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

        $shopifyServiceMock->method('getExistingShopifyOrder')->willReturn(array());

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
    public function testOrderItemsWithNoReasonCode()
    {
        $orderId = 1;
        $orders = $this->setTestOrders($orderId);

        $this->ackFaker->fake($orders);

        $this->setAckHandler($orderId);
        $this->warehouseService->callHandler(
            $this->app->make(AckHandler::class)
        );
    }
}
