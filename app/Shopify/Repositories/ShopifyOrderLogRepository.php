<?php declare(strict_types=1);

namespace App\Shopify\Repositories;

use App\Shopify\Models\ShopifyOrderLog;
use WMGCore\Repositories\BaseRepository;

/**
 * Shopify Fulfillment Fetch Log Repository
 */
class ShopifyOrderLogRepository extends BaseRepository
{
    public function __construct(
        ShopifyOrderLog $model
    ) {
        parent::__construct($model);
    }

    public function addLog($shopifyOrderId, $status, $message, $type)
    {
        return $this->create([
            'parent_id' => $shopifyOrderId,
            'status' => $status,
            'message' => strlen($message) > 255 ? substr($message, 0, 255) : $message,
            'type' => $type
        ]);
    }
}
