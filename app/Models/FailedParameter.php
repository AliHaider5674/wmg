<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Fail parameters
 *
 * Class OrderDrop
 * @category WMG
 * @package  App\Models
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class FailedParameter extends Model
{
    const TYPE_ACK = 'ack';
    const TYPE_SHIPMENT = 'shipment';
    const TYPE_ORDER = 'order';
    const TYPE_STOCK = 'stock';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'attempts', 'type', 'data', 'last_error'
    ];
}
