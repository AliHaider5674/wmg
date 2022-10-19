<?php declare(strict_types=1);

namespace App\Shopify\Models;

use App\Core\Models\Warehouse;
use Illuminate\Database\Eloquent\Model;

/**
 * @class ShopifyOrder
 */
class ShopifyOrder extends Model
{
    protected $guarded = ['id'];

    public function getOrderData()
    {
        return json_decode($this->getAttribute('data'), true) ?? true;
    }

    public function items()
    {
        return $this->hasMany(ShopifyOrderItem::class, 'parent_id', 'id');
    }
}
