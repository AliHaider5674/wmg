<?php


namespace App\Shopify\Handlers;

use App\Core\Handlers\HandlerInterface;
use App\Preorder\Constants\ConfigConstant as PreorderConfigConstant;
use App\Shopify\Clients\ShopifySDK;
use App\Shopify\Constants\ConfigConstant;
use App\Shopify\Enums\ShopifyOrderItemStatus;
use App\Shopify\Models\ShopifyOrderItem;
use App\Shopify\Repositories\ShopifyOrderItemRepository;
use App\Shopify\Repositories\ShopifyOrderRepository;
use App\Shopify\Services\ClientService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Class FulfillmentRequestHandler
 * @todo break the logic by using IOAdapter
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FulfillmentRequestHandler implements HandlerInterface
{
    private ShopifyOrderRepository $orderRepository;
    private ClientService $clientService;
    /** @var \App\Models\Service[] */
    private $preorderMap;
    private ShopifyOrderItemRepository $shopifyOrderItemRepository;
    private $usDropDays;
    private $dropSize;
    public function __construct(
        ShopifyOrderRepository $orderRepository,
        ClientService          $clientService,
        ShopifyOrderItemRepository $shopifyOrderItemRepository,
        $config
    ) {
        $this->orderRepository = $orderRepository;
        $this->clientService = $clientService;
        $this->preorderMap = [];
        $this->shopifyOrderItemRepository = $shopifyOrderItemRepository;
        $this->usDropDays = $config[PreorderConfigConstant::US_DROP_DAYS_ADVANCE];
        $this->dropSize = $config[ConfigConstant::SHOPIFY_FULFILLMENT_REQUEST_SIZE];
    }

    public function validate()
    {
        return true;
    }

    /**
     * @throws \PHPShopify\Exception\ApiException
     * @throws \PHPShopify\Exception\CurlException
     * @todo break the method
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function handle()
    {
        $readyToShipOrders = $this->orderRepository->getReadyToShipOrders($this->dropSize, $this->usDropDays);
        foreach ($readyToShipOrders as $order) {
            /** @var \App\Shopify\Models\ShopifyOrder $order */
            $client = $this->clientService->getClientByOrder($order);
            $shopifyOrder = $client->Order($order->order_id)->get();
            if (!$this->isOrderValidToDrop($shopifyOrder)) {
                continue;
            }

            $itemMap = [];
            foreach ($order->items as $item) {
                $itemMap[$item->shopify_line_id] = $item;
            }
            $fulfillmentOrders = $client->Order($order->order_id)->FulfillmentOrder->get();
            $proceedCount = 0;
            foreach ($fulfillmentOrders as $fulfillmentOrder) {
                if (!in_array('request_fulfillment', $fulfillmentOrder['supported_actions'])) {
                    continue;
                }
                $fulfillmentItems = [];
                foreach ($fulfillmentOrder['line_items'] as $fulfillmentItem) {
                    if (!isset($itemMap[$fulfillmentItem['line_item_id']])
                    || !$this->isItemValidToDrop($itemMap[$fulfillmentItem['line_item_id']])) {
                        continue;
                    }
                    $fulfillmentItems[] = [
                        'id' => $fulfillmentItem['id'],
                        'quantity' => $fulfillmentItem['fulfillable_quantity']
                    ];
                }

                if (empty($fulfillmentItems)) {
                    continue;
                }

                $this->commit($item, $client, $fulfillmentOrder, $fulfillmentItems);
                $proceedCount++;
            }

            if ($proceedCount  === 0) {
                foreach ($order->items as $item) {
                    if ($item->status === ShopifyOrderItemStatus::READY && !$this->isPreorder($item)) {
                        $this->shopifyOrderItemRepository->update($item, ['status' => ShopifyOrderItemStatus::SKIPPED]);
                    }
                }
            }
        }
    }

    private function commit($item, ShopifySDK $client, $fulfillmentOrder, $fulfillmentItems)
    {
        DB::transaction(function () use ($item, $client, $fulfillmentOrder, $fulfillmentItems) {
            $this->shopifyOrderItemRepository->update(
                $item,
                ['status' => ShopifyOrderItemStatus::SHIPMENT_REQUESTED]
            );
            $client->FulfillmentOrder($fulfillmentOrder['id'])->FulfillmentRequest->post([
                'message' => 'Fulfillment requests shipment',
                'fulfillment_order_line_items' => $fulfillmentItems
            ]);
        });
    }

    private function isItemValidToDrop($item) : bool
    {

        $ans = $item->status === ShopifyOrderItemStatus::READY;
        return $ans && !($this->isPreorder($item));
    }

    /**
     * @param $item
     * @return bool
     */
    private function isPreorder($item) :bool
    {
        $preorder = $this->getPreoder($item);
        if ($preorder) {
            $preorder = new Carbon($preorder);
            $now = Carbon::now('UTC')->addDays($this->usDropDays);
            return $now->diffInSeconds($preorder, false) > 0;
        }
        return false;
    }

    /**
     * @param $shopifyOrder
     * @return bool
     */
    private function isOrderValidToDrop($shopifyOrder)
    {
        if ($shopifyOrder['tags']) {
            $tags = explode(',', $shopifyOrder['tags']);
            $tags = array_map('trim', $tags);
            if (in_array('On Hold', $tags)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param \App\Shopify\Models\ShopifyOrderItem $item
     * @return \App\Models\Service|bool|mixed|null
     * @todo use product service
     */
    private function getPreoder(ShopifyOrderItem $item)
    {
        if (!isset($this->preorderMap[$item->sku])) {
            $product = $item->product;
            $this->preorderMap[$item->sku] = $product && $product->preorder ? $product->preorder : null;
        }
        return $this->preorderMap[$item->sku];
    }
}
