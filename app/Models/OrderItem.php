<?php

namespace App\Models;

use App\Core\Enums\OrderItemStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Order item model that reference to
 * order_items table
 *
 * Class OrderItem
 * @category WMG
 * @package  App\Models
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 * @property bool is_shippable
 * @method Builder|self digital()
 * @method Builder|self physical()
 * @method Builder|self shippable()
 * @method Builder|self sourceId(string $sourceId)
 */
class OrderItem extends CustomAttribute
{
    use HasFactory;

    /**
     * Physical product type key
     */
    public const PRODUCT_TYPE_PHYSICAL = 'physical';

    /**
     * Simple product type key
     */
    public const PRODUCT_TYPE_SIMPLE = 'simple';


    public const PRODUCT_TYPE_PRINTFUL = 'printful';

    /**
     * Digital product type key
     */
    public const PRODUCT_TYPE_DIGITAL = 'digital';

    /**
     * Virtual product type key
     */
    public const PRODUCT_TYPE_VIRTUAL = 'virtual';

    /**
     * Downloadable product type key
     */
    public const PRODUCT_TYPE_DOWNLOADABLE = 'downloadable';

    /**
     * Physical product types
     *
     * @var string[]
     */
    public const ALL_PHYSICAL_TYPES = [
        self::PRODUCT_TYPE_PHYSICAL,
        self::PRODUCT_TYPE_SIMPLE,
        self::PRODUCT_TYPE_PRINTFUL
    ];

    /**
     * Digital product types
     *
     * @var string[]
     */
    public const ALL_DIGITAL_TYPES = [
        self::PRODUCT_TYPE_DIGITAL,
        self::PRODUCT_TYPE_VIRTUAL,
        self::PRODUCT_TYPE_DOWNLOADABLE
    ];

    /**
     * Droppable status types
     */
    public const DROPPABLE_STATUS_TYPES = [
        OrderItemStatus::RECEIVED,
        OrderItemStatus::ERROR
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_line_id', 'order_line_number', 'quantity', 'returned_quantity',
        'sku', 'name', 'net_amount',
        'gross_amount', 'tax_amount', 'tax_rate',
        'currency', 'parent_order_line_number', 'item_type',
        'aggregated_line_id', 'source_id'
    ];

    /**
     * Get parent order
     *
     * @return HasOne
     */
    public function order(): HasOne
    {
        return $this->hasOne(Order::class, 'id', 'parent_id');
    }

    /**
     * Get how many quantity need to be shipped.
     *
     * @return int
     */
    public function getShouldShippedQty(): int
    {
        if ($this->getAttribute('quantity_shipped') > $this->quantity) {
            return 0;
        }

        return $this->quantity - $this->getAttribute('quantity_shipped');
    }

    /**
     * Get how many quantity need to be ack.
     *
     * @return int
     */
    public function getShouldAckQty(): int
    {
        if ($this->getAttribute('quantity_ack') > $this->quantity) {
            return 0;
        }

        return $this->quantity - $this->getAttribute('quantity_ack');
    }

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsShippableAttribute(): bool
    {
        return in_array(
            strtolower($this->getAttribute('item_type')),
            self::ALL_PHYSICAL_TYPES,
            true
        );
    }

    /**
     * Scope to digital products
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeDigital(Builder $query): Builder
    {
        return $query->whereIn('item_type', self::ALL_DIGITAL_TYPES);
    }

    /**
     * Scope to physical products
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopePhysical(Builder $query): Builder
    {
        return $query->whereIn('item_type', self::ALL_PHYSICAL_TYPES);
    }

    /**
     * Scope a query to only include active users.
     *
     * This needs to be whereRaw because the quantity is set to a varchar type
     * for some reason
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeShippable($query)
    {
        return $this->scopePhysical($query)
            ->whereRaw('quantity > quantity_shipped');
    }

    /**
     * @param Builder $query
     * @param string  $sourceId
     * @return Builder
     */
    public function scopeSourceId($query, string $sourceId)
    {
        return $query->where('source_id', $sourceId);
    }

    /**
     * @param       $query
     * @param array $sourceIds
     * @return mixed
     */
    public function scopeSourceIdIn($query, array $sourceIds)
    {
        return $query->whereRaw(sprintf(
            'LOWER(source_id) IN ("%s")',
            strtolower(implode('","', $sourceIds))
        ));
    }

    public function scopeNotDropped(Builder $query)
    {
        return $query->whereNull('drop_id');
    }
}
