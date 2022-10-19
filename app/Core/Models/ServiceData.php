<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Service data
 *
 * Class OrderDrop
 * @category WMG
 * @package  App\Models
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class ServiceData extends Model
{
    protected $table = 'service_datas';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'parent_id', 'key', 'value'
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

    public function getObject()
    {
        return json_decode($this->value);
    }

    public function setObject(array $object)
    {
        $this->value = json_encode($object);
        return $this;
    }
}
