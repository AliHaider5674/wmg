<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Order drop model that reference to
 * order_drops table
 *
 * Class OrderDrop
 * @category WMG
 * @package  App\Models
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class OrderDrop extends Model
{
    const STATUS_PROCESSING = 0;
    const STATUS_DONE = 1;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'status', 'content'];
}