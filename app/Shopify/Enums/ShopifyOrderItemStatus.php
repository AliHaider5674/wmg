<?php declare(strict_types=1);

namespace App\Shopify\Enums;

use App\Core\Enums\BaseEnum;

/**
 * Class ShopifyOrderItemStatus
 * @package App\Shopify\Enums
 */
class ShopifyOrderItemStatus extends BaseEnum
{
    public const READY = 0;
    public const SHIPMENT_REQUESTED = 1;
    public const SKIPPED = 2;
}
