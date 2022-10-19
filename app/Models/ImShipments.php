<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ImShipments
 * Timestamps shipment API calls and logs number of shipments returned
 *
 * Timestamps are used to only collect shipments from last successful run
 *
 * @category WMG
 * @package  App\Models
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License
 * @link     http://www.wmg.com
 */
class ImShipments extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['filter_from', 'filter_to', 'count', 'status'];
}
