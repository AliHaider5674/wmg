<?php
namespace App\OrderAction\Models;

use App\Models\Order;
use Illuminate\Database\Eloquent\Model;

/**
 * Order action module
 *
 * @category WMG
 * @package  WMG
 * @author   Daniel Campbell <daniel@primor.tech>
 * @license  WMG License
 * @link     http://www.wmg.com
 */
class OrderAction extends Model
{
    /**
     * @var array
     */
    protected $guarded = ['created_at', 'updated_at'];
    protected $fillable = ['order_id', 'sales_channel', 'action'];

    public function updateExecData($key, $value)
    {
        $data = $this->getExecDatas();
        $data[$key] = $value;
        $this->setAttribute('exec_data', json_encode($data));
        return $this;
    }

    public function updateSetting($key, $value)
    {
        $data = $this->getExecDatas();
        $data[$key] = $value;
        $this->setAttribute('setting', json_encode($data));
        return $this;
    }

    public function getExecData($key)
    {
        $data = $this->getExecDatas();
        return array_key_exists($key, $data)
            ? $data[$key]
            : null;
    }

    public function getSetting($key)
    {
        $data = $this->getSettings();
        return array_key_exists($key, $data)
            ? $data[$key]
            : null;
    }

    public function getExecDatas()
    {
        $execData = $this->getAttribute('exec_data');
        if ($execData === null) {
            return [];
        }
        return json_decode($execData, true);
    }

    public function getSettings()
    {
        $setting = $this->getAttribute('setting');
        if ($setting === null) {
            return [];
        }
        return json_decode($setting, true);
    }

    public function orders()
    {
        $salesChannel = $this->getAttribute('sales_channel');
        $result = $this->hasMany(Order::class, 'order_id', 'order_id');
        if ($salesChannel !==null && $salesChannel != '*') {
            $result->where('sales_channel', $this->getAttribute('sales_channel'));
        }
        return $result;
    }
}
