<?php
namespace App\MES;

use Illuminate\Database\Eloquent\Model;

/**
 * MES SHIPMENT ITEM
 *
 * Class Order
 * @category WMG
 * @package  App\Models
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2018
 * @link     http://www.wmg.com
 */
class MesShipmentItem extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'parent_id', 'item_id'
    ];

    /**
     * shipments
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function shipment()
    {
        return $this->hasMany('App\MES\MesShipment', 'id', 'parent_id');
    }
}
