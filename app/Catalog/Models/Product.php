<?php declare(strict_types=1);

namespace App\Catalog\Models;

use App\Core\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @class Product
 */
class Product extends Model
{

    use HasFactory;

    protected $guarded = ['id'];
    protected $primaryKey = 'sku';

    /**
     * Indicates if the model's ID is auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * dimensions
     * @return HasMany
     */
    public function dimensions(): HasMany
    {
        return $this->hasMany(ProductDimension::class, 'product_sku', 'sku');
    }
}
