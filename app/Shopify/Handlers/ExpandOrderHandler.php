<?php


namespace App\Shopify\Handlers;

use App\Catalog\Subscribers\ProductDiscoverSubscriber;
use App\Core\Handlers\HandlerInterface;
use App\Core\Repositories\WarehouseRepository;
use App\Shopify\Constants\ConfigConstant;
use App\Preorder\Constants\ConfigConstant as PreorderConfigConstant;
use App\Shopify\Enums\ShopifyOrderStatus;
use App\Shopify\Handlers\ExpandOrder\BundleExtractor;
use App\Shopify\Handlers\ExpandOrder\PreorderExtractor;
use App\Shopify\Models\ShopifyOrder;
use App\Shopify\Repositories\ShopifyOrderItemRepository;
use App\Shopify\Repositories\ShopifyOrderLogRepository;
use App\Shopify\Repositories\ShopifyOrderRepository;
use Illuminate\Support\Facades\DB;
use Exception;

/**
 * Class FetchOrderHandler
 * @todo fix coupling issue
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ExpandOrderHandler implements HandlerInterface
{
    protected array $config;
    protected array $supportedWarehouses;
    private ShopifyOrderRepository $shopifyOrderRepository;
    private WarehouseRepository $warehouseRepository;
    private ShopifyOrderItemRepository $shopifyOrderItemRepository;
    private ShopifyOrderLogRepository $shopifyOrderLogRepository;
    private BundleExtractor $bundleExtractor;
    private PreorderExtractor $preorderExtractor;
    public function __construct(
        ShopifyOrderRepository $shopifyOrderRepository,
        ShopifyOrderItemRepository $shopifyOrderItemRepository,
        WarehouseRepository    $warehouseRepository,
        ShopifyOrderLogRepository $shopifyOrderLogRepository,
        BundleExtractor $bundleExtractor,
        PreorderExtractor $preorderExtractor,
        $config
    ) {
        $this->shopifyOrderRepository = $shopifyOrderRepository;
        $this->warehouseRepository = $warehouseRepository;
        $this->shopifyOrderItemRepository = $shopifyOrderItemRepository;
        $this->shopifyOrderLogRepository = $shopifyOrderLogRepository;
        $this->preorderExtractor = $preorderExtractor;
        $this->bundleExtractor = $bundleExtractor;
        $supportedWarehouseCodes = $config[ConfigConstant::SUPPORTED_WAREHOUSES];
        $supportWarehouses = $this->warehouseRepository->getWarehousesByCodes($supportedWarehouseCodes);
        $this->supportedWarehouses = [];
        foreach ($supportWarehouses as $warehouse) {
            $this->supportedWarehouses[strtolower($warehouse->name)] = true;
        }
        $this->preorderExtractor->config(['us_preorder_timezone' => $config[PreorderConfigConstant::US_DROP_TIMEZONE]]);
    }
    public function validate()
    {
        return true;
    }

    public function handle()
    {
        $orders = $this->shopifyOrderRepository->getFetchedOrders();
        $orders->each(function (ShopifyOrder $order) {
            $orderData = $order->getOrderData();
            /**
             * @todo avoid to use event
             */
            try {
                DB::transaction(function () use ($order, $orderData) {
                    event('shopify.order.expanded', $orderData, $order);
                    $bundleInfo = $this->bundleExtractor->extract($orderData['line_items']);
                    foreach ($orderData['line_items'] as $lineData) {
                        $preorder = $this->preorderExtractor->extract($lineData, $bundleInfo);
                        if ($lineData['fulfillable_quantity']<=0) {
                            continue;
                        }
                        event(ProductDiscoverSubscriber::CATALOG_PRODUCT_DISCOVER, [[
                            'sku' => $lineData['sku'],
                            'name' => $lineData['name'],
                            'price' => $lineData['price'],
                            'preorder' => $preorder ? $preorder->setTimezone('UTC')->format('Y-m-d H:i:s') : null
                        ]]);
                        $item = $this->shopifyOrderItemRepository->create([
                            'parent_id' => $order->id,
                            'sku' => $lineData['sku'],
                            'qty'=> $lineData['fulfillable_quantity'],
                            'shopify_line_id' => $lineData['id']
                        ]);
                        event('shopify.order.item.expanded', $lineData, $item);
                    }
                    $this->shopifyOrderRepository->update($order, ['status' => ShopifyOrderStatus::EXPANDED]);
                    $this->shopifyOrderLogRepository->addLog($order->id, 'success', 'order expanded', 'expand_orders');
                });
            } catch (Exception $e) {
                $this->shopifyOrderLogRepository->addLog($order->id, 'error', $e->getMessage(), 'expand_orders');
                $this->shopifyOrderRepository->update($order, ['status' => ShopifyOrderStatus::ERROR]);
            }
        });
    }
}
