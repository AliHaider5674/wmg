<?php declare(strict_types=1);

namespace App\Shopify\Enums;

use App\Core\Enums\BaseEnum;

/**
 * Class ShopifyFulfillmentFetchStatus
 * @package App\Core\Enums
 */
class ShopifyFulfillmentFetchStatus extends BaseEnum
{
    /**
     * Order Item Drop Status Received
     */
    public const SUCCESS = 'success';
    public const ERROR = 'error';
    public const SKIPPED = 'skipped';
}
