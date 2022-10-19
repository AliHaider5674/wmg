<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Collection;

/**
 * Order Model that communicate with database
 *
 * Class Order
 * @property mixed $shipping_method
 * @category WMG
 * @package  App\Models
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2018
 * @link     http://www.wmg.com
 * @method Order|EloquentBuilder|QueryBuilder hasStatusIn(array $statuses)
 * @method Order|EloquentBuilder hasDroppableOrderItems(array $sourceIds = null)
 * @method Order|EloquentBuilder droppable()
 */
class Order extends CustomAttribute
{
    use HasFactory;

    public const STATUS_RECEIVED = 0;
    public const STATUS_DROPPED  = 1;
    public const STATUS_ERROR    = 2;
    public const STATUS_ONHOLD   = 3;

    private const DROPPABLE_STATUSES = [
        Order::STATUS_RECEIVED,
        Order::STATUS_ERROR
    ];

    /**
     * Address types
     */
    const CUSTOMER_ADDRESS_TYPE_SHIPPING = 'shipping';
    const CUSTOMER_ADDRESS_TYPE_BILLING  = 'billing';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Get Items
     *
     * @return HasMany|Collection|OrderItem
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'parent_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(OrderAddress::class, 'parent_id', 'id');
    }

    /**
     * @return HasOne
     */
    public function orderDrops(): HasOne
    {
        return $this->hasOne(OrderDrop::class, 'id', 'drop_id');
    }

    /**
     * getShippingAddress
     *
     * @return OrderAddress|Model|null
     */
    public function getShippingAddress(): ?OrderAddress
    {
        $result = $this->getShippingAddresses()->first();

        //add support for old data
        if (!$result) {
            $result = $this->addresses()->first();
        }

        return $result;
    }

    /**
     * getBillingAddress
     * @return OrderAddress|Model|null
     */
    public function getBillingAddress(): ?OrderAddress
    {
        return $this->getBillingAddresses()->first();
    }

    /**
     * Get all shipping addresses
     *
     */
    public function getShippingAddresses()
    {
        return $this->addresses()->where(
            'customer_address_type',
            OrderAddress::CUSTOMER_ADDRESS_TYPE_SHIPPING
        );
    }

    /**
     * Get all billing addresses
     *
     */
    public function getBillingAddresses()
    {
        return $this->addresses()->where(
            'customer_address_type',
            OrderAddress::CUSTOMER_ADDRESS_TYPE_BILLING
        );
    }

    /**
     * @param QueryBuilder|EloquentBuilder $query
     * @param array $statuses
     * @return EloquentBuilder|Order
     */
    public function scopeHasStatusIn($query, array $statuses)
    {
        return $query->whereIn('status', $statuses);
    }

    /**
     * @param $query
     * @return Order|EloquentBuilder
     */
    public function scopeDroppable($query)
    {
        return $this->scopeHasStatusIn($query, self::DROPPABLE_STATUSES);
    }


    /**
     * Get orders that have droppable order items and optionally also have
     * specific source ID values
     *
     * @param QueryBuilder|EloquentBuilder $query
     * @param array|null                   $sourceIds
     * @return EloquentBuilder|Order
     */
    public function scopeHasDroppableOrderItems($query, array $sourceIds = null): EloquentBuilder
    {
        return $query->whereHas(
            'orderItems',
            static function ($query) use ($sourceIds) {
                //Ensure order only contains items that can be dropped.
                $query->whereIn('drop_status', OrderItem::DROPPABLE_STATUS_TYPES)
                    ->whereIn('item_type', OrderItem::ALL_PHYSICAL_TYPES)
                    ->whereRaw('quantity > quantity_shipped');
                if ($sourceIds !== null) {
                    $query->whereRaw(sprintf(
                        'LOWER(order_items.source_id) IN ("%s")',
                        strtolower(implode('","', $sourceIds))
                    ));
                }
            }
        );
    }
}
