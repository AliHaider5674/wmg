<?php declare(strict_types=1);

namespace App\Shopify\Enums;

use App\Core\Enums\BaseEnum;

/**
 * Class ShopifyRawOrderStatus
 * @package App\Shopify\Enums
 */
class ShopifyOrderStatus extends BaseEnum
{
    public const FETCHED = 0;
    public const EXPANDED = 1;
    public const ERROR = 5;
}
