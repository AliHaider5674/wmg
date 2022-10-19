<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

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
class Service extends Model
{
    use HasFactory;
    private const DEFAULT_ADDITION_VERSION = '1';
    public const STATUS_INACTIVE = 0;
    public const STATUS_ACTIVE = 1;

    private $eventRules;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'app_id', 'app_url', 'name', 'client', 'addition', 'status', 'event_rules'
    ];

    protected $casts = [
        'status' => 'integer',
    ];

    protected $hidden = ['addition'];

    /**
     * Get events
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function events()
    {
        return $this->hasMany('App\Models\ServiceEvent', 'parent_id', 'id');
    }

    /**
     * Get events
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function datas()
    {
        return $this->hasMany('App\Core\Models\ServiceData', 'parent_id', 'id');
    }

    public function getAddition()
    {
        $data = json_decode($this->addition, true);
        if (isset($data['version'])) {
            switch ($data['version']) {
                case '1':
                    return json_decode(Crypt::decrypt($data['data']), true);
            }
        }
        return $data;
    }

    public function setAddition(array $addition, $version = self::DEFAULT_ADDITION_VERSION)
    {
        if (!empty($version)) {
            $addition = ['version' => $version, 'data' => Crypt::encrypt(json_encode($addition))];
        }
        $this->addition = json_encode($addition);
    }

    /**
     * Get event rules
     *
     * @return array
     */
    public function getEventRules()
    {
        if (!isset($this->eventRules)) {
            $this->eventRules = json_decode($this->getAttribute('event_rules'), true);
        }
        return $this->eventRules;
    }

    /**
     * Set event rules
     *
     * @param array $rules
     * @return $this
     */
    public function setEventRules(array $rules)
    {
        $this->eventRules = $rules;
        $this->setAttribute('event_rules', json_encode($this->eventRules));
        return $this;
    }
}
