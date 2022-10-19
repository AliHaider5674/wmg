<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Order log model that reference to
 * order_logs table
 *
 * Class OrderLog
 * @category WMG
 * @package  App\Models
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class OrderLog extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];
}
