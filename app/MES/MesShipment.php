<?php
namespace App\MES;

use Illuminate\Database\Eloquent\Model;

/**
 * MES SHIPMENT
 *
 * Class Order
 * @category WMG
 * @package  App\Models
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2018
 * @link     http://www.wmg.com
 */
class MesShipment extends Model
{
    const STATUS_PROCESSING = 0;
    const STATUS_PROCESSED  = 1;
    const STATUS_ERROR = 2;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'file', 'status', 'order_count'
    ];

    /**
     * Shipment Items
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items()
    {
        return $this->hasMany('App\MES\MesShipmentItem', 'parent_id', 'id');
    }
}
