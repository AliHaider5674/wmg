<?php declare(strict_types=1);

namespace App\Shopify\Repositories;

use WMGCore\Repositories\BaseRepository;
use App\Shopify\Models\ShopifyFulfillmentFetchLog;

/**
 * Shopify Fulfillment Fetch Log Repository
 */
class ShopifyFFetchLogRepository extends BaseRepository
{
    public function __construct(
        ShopifyFulfillmentFetchLog $model
    ) {
        parent::__construct($model);
    }

    public function addLog($fulfillmentOrderId, $status, $message)
    {
        return $this->create([
            'shopify_fulfillment_id' => $fulfillmentOrderId,
            'status' => $status,
            'message' => $message
        ]);
    }
}
