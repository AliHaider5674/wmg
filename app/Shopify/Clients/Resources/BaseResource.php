<?php

namespace App\Shopify\Clients\Resources;

use App\Shopify\Clients\Traits\ResourceLoader;
use PHPShopify\ShopifyResource;

/**
 * @class BaseResource
 * @package App\Shopify
 * Base shopify resource
 */
class BaseResource extends ShopifyResource
{
    use ResourceLoader;
}
