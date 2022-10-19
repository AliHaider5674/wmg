<?php
namespace App\Models\Service\ModelBuilder;

/**
 * Parameters for build model request
 *
 * Class ShipmentLineChangeParameter
 * @category WMG
 * @package  App\Models\Service\ModelBuilder
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class Parameter
{
    /**
     * Serialize properties
     *
     * @return string
     */
    public function serialize()
    {
        $data = get_object_vars($this);
        return \GuzzleHttp\json_encode($data);
    }

    /**
     * Deserialize str back to properties
     * @param $str
     * @return $this
     */
    public function deserialize($str)
    {
        $data = \GuzzleHttp\json_decode($str);
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
        return $this;
    }
}
