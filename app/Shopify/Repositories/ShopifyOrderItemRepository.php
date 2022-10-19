<?php declare(strict_types=1);

namespace App\Shopify\Repositories;

use App\Models\Service;
use App\Shopify\Enums\ShopifyOrderStatus;
use App\Shopify\Models\ShopifyOrder;
use App\Shopify\Models\ShopifyOrderItem;
use WMGCore\Repositories\BaseRepository;

/**
 * Shopify Fulfillment Registration Repository
 */
class ShopifyOrderItemRepository extends BaseRepository
{
    public function __construct(
        ShopifyOrderItem $shopifyOrderItem
    ) {
        parent::__construct($shopifyOrderItem);
    }
}
