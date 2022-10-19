<?php

namespace App\Models;

use App\Models\Service\Model\Serialize;
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
class ServiceEventCall extends Model
{
    use HasFactory;

    const STATUS_BEING_DELIVERED = 0;
    const STATUS_DELIVERED = 1;
    const STATUS_SOFT_ERROR = 2; //Endpoint return error code
    const STATUS_HARD_ERROR = 3; //Network issue, haven't connect to the endpoint yet
    const STATUS_NOT_RETRYABLE = 5; //NOT ALLOW TO RETRY
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'parent_id', 'status', 'data', 'response'
    ];

    /**
     * Events
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function serviceEvent()
    {
        return $this->hasOne('App\Models\ServiceEvent', 'id', 'parent_id');
    }

    /**
     * Get request data
     *
     * @return Serialize
     */
    public function getData()
    {
        return unserialize($this->data);
    }

    public function setData(Serialize $data)
    {
        $this->data = serialize($data);
    }

    public function callResponses()
    {
        return $this->hasMany(ServiceEventCallResponse::class, 'parent_id', 'id');
    }
}
