<?php declare(strict_types=1);

namespace App\Shopify\Repositories;

use App\Shopify\Enums\ShopifyOrderItemStatus;
use App\Shopify\Enums\ShopifyOrderStatus;
use App\Shopify\Models\ShopifyOrder;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use WMGCore\Repositories\BaseRepository;

/**
 * Shopify Fulfillment Registration Repository
 */
class ShopifyOrderRepository extends BaseRepository
{
    public function __construct(
        ShopifyOrder $shopifyRawOrder
    ) {
        parent::__construct($shopifyRawOrder);
    }

    public function getFetchedOrders()
    {
        return $this->modelQuery()->where('status', ShopifyOrderStatus::FETCHED)
            ->orderBy('ordered_at', 'desc')->cursor();
    }

    public function getReadyToShipOrders($limit = null, $daysAdvance = null)
    {
        $now = Carbon::now();
        if ($daysAdvance) {
            $now->addDays($daysAdvance);
        }
        $ans = $this->modelQuery()
            ->select(['shopify_orders.*'])
            ->where('shopify_orders.status', '=', ShopifyOrderStatus::EXPANDED)
            ->join('shopify_order_items', 'shopify_orders.id', '=', 'shopify_order_items.parent_id')
            ->join('products', 'shopify_order_items.sku', '=', 'products.sku')
            ->where(function (Builder $query) use ($now) {
                return $query->whereNull('products.preorder')
                    ->orWhere('products.preorder', '<=', $now);
            })
            ->where('shopify_order_items.status', '=', ShopifyOrderItemStatus::READY)
            ->orderBy('shopify_orders.ordered_at', 'desc')
            ->groupBy('shopify_orders.id');
        if ($limit) {
            $ans->limit($limit);
        }
        return $ans->cursor();
    }



    /**
     * @param $data
     * @return \App\Shopify\Models\ShopifyOrder
     */
    public function updateOrCreate($data)
    {
        $model = $this->modelQuery()->where('order_id', '=', $data['order_id'])
            ->where('service_id', '=', $data['service_id'])
            ->first();
        if (!$model) {
            return $this->create($data);
        }
        return $this->update($model, $data);
    }
}
