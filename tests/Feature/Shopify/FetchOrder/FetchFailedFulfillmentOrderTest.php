<?php

namespace Tests\Feature\Shopify\FetchOrder;

use App\Core\Validators\PhysicalOrderValidator;
use App\DataMapper\Exceptions\InvalidMappingException;
use App\Models\Order;
use App\Preorder\Service\ProductService;
use App\Shopify\Converters\ToLocal\OrderBillingAddressConverter;
use App\Shopify\Converters\ToLocal\OrderConverter;
use App\Shopify\Converters\ToLocal\OrderItemConverter;
use App\Shopify\Converters\ToLocal\OrderShippingAddressConverter;
use App\Shopify\Handlers\FetchShipmentOrder\FailOrderProcessor;
use App\Shopify\Handlers\FetchShipmentOrder\FetchOrder;
use App\Shopify\Models\ShopifyFailedFulfillmentOrder;
use App\Shopify\Repositories\ShopifyFSRRepository;
use PHPShopify\Exception\CurlException;

/**
 * Class FetchFailedFulfillmentOrderTest
 * @package Tests\Feature\Shopify\FetchOrder
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2022
 * @link     http://www.wmg.com
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FetchFailedFulfillmentOrderTest extends FetchOrderBaseCase
{

    public function setUp(): void
    {
        parent::setUp();

        $this->failedFulfillmentOrder = $this->getFailedOrders();
    }


    /**
     * testFailedFulfillmentOrder
     * @throws CurlException
     * @throws InvalidMappingException
     */
    public function testProcessFailedFulfillmentOrder()
    {
        /**
         * @var FetchOrder $fetchOrder
         */

        $fetchOrder = $this->app->make(FetchOrder::class);
        $fetchOrder->handler($this->client, $this->service, $this->shipmentOrder, $this->mockProcessor);

        $order = Order::query()
            ->where('request_id', '=', $this->fulfillmentId)->first();

        $this->assertModelExists($order);
        $this->assertModelMissing($this->failedFulfillmentOrder);
    }

    /**
     * @inheritDoc
     */
    protected function getMockProcessor()
    {
        $orderConverter = $this->app->make(OrderConverter::class);
        $orderItemConverter = $this->app->make(OrderItemConverter::class);
        $orderAddressConverter = $this->app->make(OrderShippingAddressConverter::class);
        $billingAddressConverter = $this->app->make(OrderBillingAddressConverter::class);
        $shopifyFSRRepository = $this->app->make(ShopifyFSRRepository::class);
        $physicalOrderValidator = $this->app->make(PhysicalOrderValidator::class);
        $preorderProductService = $this->app->make(ProductService::class);

        $mockProcessor = $this->getMockBuilder(FailOrderProcessor::class)
            ->setConstructorArgs([
                $orderConverter,
                $orderItemConverter,
                $orderAddressConverter,
                $billingAddressConverter,
                $shopifyFSRRepository,
                $physicalOrderValidator,
                $preorderProductService
            ])
            ->onlyMethods(['fetchOrder', 'getWarehouse'])
            ->getMock();

        $mockProcessor->method('fetchOrder')->willReturn($this->fetchOrderReturnValue());

        $mockProcessor->method('getWarehouse')->willReturn($this->getWarehouseReturnValue());

        return $mockProcessor;
    }

    public function getFailedOrders()
    {
        return ShopifyFailedFulfillmentOrder::create(
            [
                'fulfillment_order_id' => $this->fulfillmentId,
                'service_id' => 1,
                'attempts' => 1
            ]
        );
    }
}
