<?php declare(strict_types=1);

namespace App\Shopify\Models;

use App\Core\Models\Warehouse;
use Illuminate\Database\Eloquent\Model;

/**
 * @class ShopifyFulfillmentServiceRegistration
 */
class ShopifyFulfillmentServiceRegistration extends Model
{
    protected $guarded = ['id'];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id', 'id');
    }
}
