<?php declare(strict_types=1);

namespace App\Shopify\Models;

use App\Catalog\Models\Product;
use App\Core\Models\Warehouse;
use Illuminate\Database\Eloquent\Model;

/**
 * @class ShopifyFulfillmentServiceRegistration
 */
class ShopifyOrderItem extends Model
{
    protected $guarded = ['id'];

    public function product()
    {
        return $this->hasOne(Product::class, 'sku', 'sku');
    }
}
