<?php
namespace App\Shopify\Handlers\FetchShipmentOrder;

use App\Core\Exceptions\Mutators\ValidationException;
use App\Core\Models\Warehouse;
use App\Core\Validators\PhysicalOrderValidator;
use App\DataMapper\Exceptions\InvalidMappingException;
use App\Models\Order;
use App\Shopify\Models\ShopifyFailedFulfillmentOrder;
use App\Preorder\Service\ProductService;
use App\Shopify\Clients\ShopifySDK;
use App\Shopify\Converters\ToLocal\OrderBillingAddressConverter;
use App\Shopify\Converters\ToLocal\OrderShippingAddressConverter;
use App\Shopify\Converters\ToLocal\OrderConverter;
use App\Shopify\Converters\ToLocal\OrderItemConverter;
use App\Shopify\Repositories\ShopifyFSRRepository;
use App\Shopify\Structures\Shopify\FulfillmentOrder;
use Illuminate\Support\Facades\DB;
use PHPShopify\Exception\ApiException;
use \PHPShopify\Exception\CurlException;

/**
 * @class Prosessor
 * @package App\Shopify
 * Fetch Shopify orders processor
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @todo break the class to void CouplingBetweenObjects
 */
class Processor
{
    private OrderShippingAddressConverter $orderAddressConverter;
    private OrderBillingAddressConverter $orderBillingAddressConverter;
    private OrderConverter $orderConverter;
    private OrderItemConverter $orderItemConverter;
    private ShopifyFSRRepository $shopifyFSRRepository;
    private PhysicalOrderValidator $physicalOrderValidator;
    private ProductService $preorderProductService;

    private array $warehouses;
    public function __construct(
        OrderConverter                $orderConverter,
        OrderItemConverter            $orderItemConverter,
        OrderShippingAddressConverter $orderAddressConverter,
        OrderBillingAddressConverter  $billingAddressConverter,
        ShopifyFSRRepository          $shopifyFSRRepository,
        PhysicalOrderValidator        $physicalOrderValidator,
        ProductService                $preorderProductService
    ) {
        $this->orderConverter = $orderConverter;
        $this->orderItemConverter = $orderItemConverter;
        $this->orderAddressConverter = $orderAddressConverter;
        $this->shopifyFSRRepository = $shopifyFSRRepository;
        $this->orderBillingAddressConverter = $billingAddressConverter;
        $this->physicalOrderValidator = $physicalOrderValidator;
        $this->preorderProductService = $preorderProductService;
    }

    /**
     * process
     * @param FulfillmentOrder $fulfillmentOrder
     * @param ShopifySDK $client
     * @param string $salesChannel
     * @param string $storeName
     * @param int $serviceId
     * @return Order
     * @throws ApiException
     * @throws CurlException
     * @throws InvalidMappingException
     * @throws ValidationException
     */
    public function process(
        FulfillmentOrder $fulfillmentOrder,
        ShopifySDK $client,
        string $salesChannel,
        string $storeName,
        int $serviceId
    ) {
        $orderData = $this->fetchOrder($fulfillmentOrder->orderId, $client);
        $fulfillmentOrderData = $fulfillmentOrder->toArray(false);
        $warehouse = $this->getWarehouse($fulfillmentOrder->assignedLocationId);
        $order = $this->orderConverter->convert($orderData, $fulfillmentOrderData, $warehouse);
        $orderItems = $this->orderItemConverter->convert($orderData, $fulfillmentOrderData, $warehouse);
        $shippingAddress = $this->orderAddressConverter->convert($orderData, $fulfillmentOrderData, $warehouse);
        $billingAddress = $this->orderBillingAddressConverter->convert($orderData, $fulfillmentOrderData, $warehouse);

        $validator = $this->physicalOrderValidator->validate($order, $orderItems, $shippingAddress, $billingAddress);
        if ($validator->fails()) {
            $message = $validator->errors()->all();
            $this->rejectOrder($client, $fulfillmentOrder, $message);
            throw new ValidationException($message);
        }

        try {
            DB::transaction(function () use (
                $order,
                $orderItems,
                $shippingAddress,
                $billingAddress,
                $fulfillmentOrder,
                $client,
                $salesChannel,
                $storeName,
                $serviceId
            ) {
                $order->setAttribute('sales_channel', $salesChannel);
                $order->setCustomAttributes([
                    ['name' => 'store_name',
                        'value' => $storeName]
                ]);
                $order->save();
                $shippingAddress->setAttribute('parent_id', $order->id);
                $shippingAddress->save();
                $billingAddress->setAttribute('parent_id', $order->id);
                $shippingAddress->save();
                foreach ($orderItems as $orderItem) {
                    /** @var \App\Models\OrderItem $orderItem */
                    $preorder = $this->preorderProductService->getPreorderBySku($orderItem->sku);
                    if ($preorder) {
                        $orderItem->setCustomAttributes([['name' => 'release_date', 'value' => $preorder]]);
                    }
                    $orderItem->setAttribute('parent_id', $order->id);
                    $orderItem->save();
                }
                event('internal.order.received', $order);
                $this->acceptOrder($client, $fulfillmentOrder);
            });
        } catch (\Exception $e) {
            $this->recordOrderFailure($fulfillmentOrder, $serviceId);
        }

        return $order;
    }

    /**
     * recordOrderFailure
     * @param $fulfillmentOrder
     * @param $serviceId
     */
    public function recordOrderFailure($fulfillmentOrder, $serviceId)
    {
        $failedOrder = ShopifyFailedFulfillmentOrder::firstOrNew([
            'fulfillment_order_id' => $fulfillmentOrder->id,
            'service_id' => $serviceId
        ]);

        $failedOrder->attempts = $failedOrder->attempts + 1;
        $failedOrder->save();
    }

    /**
     * acceptOrder
     * @param $client
     * @param $fulfillmentOrder
     */
    public function acceptOrder($client, $fulfillmentOrder)
    {
        $client->FulfillmentOrder($fulfillmentOrder->id)
            ->FulfillmentRequest->accept(['message' => 'Fulfillment received']);
    }

    /**
     * rejectOrder
     * @param $client
     * @param $fulfillmentOrder
     * @param $message
     */
    public function rejectOrder($client, $fulfillmentOrder, $message)
    {
        $client->FulfillmentOrder($fulfillmentOrder->id)
            ->FulfillmentRequest->reject(['message' => implode(';', $message)]);
    }

    /**
     * @param                                 $orderId
     * @param ShopifySDK $client
     * @return array|false[]
     * @throws ApiException
     * @throws CurlException
     */
    protected function fetchOrder($orderId, ShopifySDK $client): array
    {
        return $client->Order($orderId)->get();
    }

    /**
     * @param $shopifyLocationId
     * @return Warehouse
     */
    protected function getWarehouse($shopifyLocationId) : Warehouse
    {
        if (!isset($this->warehouses[$shopifyLocationId])) {
            $this->warehouses[$shopifyLocationId] = $this->shopifyFSRRepository
                ->getRegistrationByLocationId($shopifyLocationId)
                ->warehouse;
        }
        return $this->warehouses[$shopifyLocationId];
    }

    /**
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function handlePostSave(Order $order)
    {
    }
}
