<?php

namespace App\Catalog\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class ProductDimension
 * @property mixed|string $product_sku
 * @property mixed|string $type
 * @property mixed|string $unit
 * @property mixed $value
 * @package App\Models
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2022
 * @link     http://www.wmg.com
 */
class ProductDimension extends Model
{
    use HasFactory;

    /**
     * product
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_sku', 'sku');
    }
}
