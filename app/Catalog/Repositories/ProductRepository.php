<?php declare(strict_types=1);

namespace App\Catalog\Repositories;

use App\Catalog\Models\Product;
use App\Models\Service;
use WMGCore\Repositories\BaseRepository;
use App\Shopify\Models\ShopifyFulfillmentServiceRegistration;

/**
 * @class ProductRepository
 */
class ProductRepository extends BaseRepository
{
    public function __construct(
        Product $product
    ) {
        parent::__construct($product);
    }

    public function loadBySku(string $sku)
    {
        return $this->modelQuery()->where('sku', $sku)->first();
    }
}
