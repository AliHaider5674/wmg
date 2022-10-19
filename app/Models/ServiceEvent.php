<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
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
class ServiceEvent extends Model
{
    use HasFactory;

    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'parent_id', 'event', 'status'
    ];

    /**
     * Description here
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function service()
    {
        return $this->hasOne('App\Models\Service', 'id', 'parent_id');
    }

    public function calls()
    {
        return $this->hasMany('App\Models\ServiceEventCall', 'parent_id', 'id');
    }
}
