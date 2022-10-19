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
class AlertEvent extends Model
{
    const LEVEL_NOTICE = 'Notice';
    const LEVEL_MEDIUM = 'Medium';
    const LEVEL_CRITICAL = 'Critical';

    const TYPE_CONNECTION_ERROR = 'Connection Error';
    const TYPE_RECEIVE_ERROR = 'Receive Error';
    const TYPE_INTERNAL_ERROR = 'Internal Error';
    const TYPE_REQUEST_ERROR = 'Request Error';
    const TYPE_NO_RECORDS = "No Records";
    const TYPE_ORDER_DROP_ERROR = "Order Drop Error";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id', 'created_at', 'updated_at'];
}
