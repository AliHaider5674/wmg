<?php declare(strict_types=1);

namespace App\Shopify\Enums;

use App\Core\Enums\BaseEnum;

/**
 * Class WarehouseStatus
 * @package App\Core\Enums
 */
class ShopifyClientUri extends BaseEnum
{
    /**
     * Order Item Drop Status Received
     */
    public const FULFILLMENT_SERVICE_CREATE = 'fulfillment_services.json';
    public const FULFILLMENT_SERVICE_GET = 'fulfillment_services.json';
}
