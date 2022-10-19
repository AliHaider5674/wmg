<?php

namespace Tests\Feature\Shopify\FetchOrder;

use App\Core\Validators\PhysicalOrderValidator;
use App\DataMapper\Exceptions\InvalidMappingException;
use App\Preorder\Service\ProductService;
use App\Shopify\Converters\ToLocal\OrderBillingAddressConverter;
use App\Shopify\Converters\ToLocal\OrderConverter;
use App\Shopify\Converters\ToLocal\OrderItemConverter;
use App\Shopify\Converters\ToLocal\OrderShippingAddressConverter;
use App\Shopify\Handlers\FetchShipmentOrder\FetchOrder;
use App\Shopify\Models\ShopifyFailedFulfillmentOrder;
use App\Shopify\Repositories\ShopifyFSRRepository;
use PHPShopify\Exception\CurlException;
use App\Shopify\Handlers\FetchShipmentOrder\Processor;

/**
 * Class FetchOrderExceptionTest
 * @package Tests\Feature\Shopify\FetchOrder
 *
 * @category WMG
 * @package  WMG
 * @author   Test User <Test.User@wmg.com>
 * @license  WMG License 2022
 * @link     http://www.wmg.com
 */
class FetchOrderExceptionTest extends FetchOrderBaseCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @throws CurlException
     * @throws InvalidMappingException
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testFetchOrderFailure()
    {
        /**
         * @var FetchOrder $fetchOrder
         */

        $fetchOrder = $this->app->make(FetchOrder::class);
        $fetchOrder->handler($this->client, $this->service, $this->shipmentOrder, $this->mockProcessor);

        $failedFulfillmentOrder = ShopifyFailedFulfillmentOrder::query()
            ->where('fulfillment_order_id', '=', $this->fulfillmentId)->first();

        $this->assertModelExists($failedFulfillmentOrder);
    }

    protected function getMockProcessor()
    {
        $orderConverter = $this->app->make(OrderConverter::class);
        $orderItemConverter = $this->app->make(OrderItemConverter::class);
        $orderAddressConverter = $this->app->make(OrderShippingAddressConverter::class);
        $billingAddressConverter = $this->app->make(OrderBillingAddressConverter::class);
        $shopifyFSRRepository = $this->app->make(ShopifyFSRRepository::class);
        $physicalOrderValidator = $this->app->make(PhysicalOrderValidator::class);
        $preorderProductService = $this->app->make(ProductService::class);

        $mockProcessor = $this->getMockBuilder(Processor::class)
            ->setConstructorArgs([
                $orderConverter,
                $orderItemConverter,
                $orderAddressConverter,
                $billingAddressConverter,
                $shopifyFSRRepository,
                $physicalOrderValidator,
                $preorderProductService
            ])
            ->onlyMethods(['acceptOrder', 'fetchOrder', 'getWarehouse'])
            ->getMock();

        $mockProcessor->method('acceptOrder')->willThrowException(new CurlException());

        $mockProcessor->method('fetchOrder')->willReturn($this->fetchOrderReturnValue());

        $mockProcessor->method('getWarehouse')->willReturn($this->getWarehouseReturnValue());

        return $mockProcessor;
    }
}
