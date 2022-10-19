<?php declare(strict_types=1);

namespace App\Shopify\Enums;

use App\Core\Enums\BaseEnum;

/**
 * Class WarehouseStatus
 * @package App\Core\Enums
 */
class ShopifyClient extends BaseEnum
{
    /**
     * Order Item Drop Status Received
     */
    public const RESTFUL = 'shopify.restful';

    public const GRAPHQL = 'shopify.graphql';
}
