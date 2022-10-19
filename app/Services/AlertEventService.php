<?php
namespace App\Services;

use App\Models\AlertEvent;

/**
 * Handle alert event
 *
 * Class WarehouseService
 * @category WMG
 * @package  App\Services
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class AlertEventService
{
    /**
     * Description here
     * @param $name
     * @param $content
     * @param $type
     * @param $level
     *
     * @return $this
     */
    public function addEvent($name, $content, $type, $level)
    {
        $data = [
            'name' => $name,
            'content' => $content,
            'type' => $type,
            'level' => $level
        ];
        $alertEvent = new AlertEvent();
        $alertEvent->fill($data);
        $alertEvent->save();
        return $this;
    }
}
