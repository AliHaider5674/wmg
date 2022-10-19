<?php
namespace App\Models\Service\ModelBuilder\Shipment\Package;

use App\Models\Service\Model\Serialize;

/**
 * Package detail that send to external services
 *
 * Class Detail
 * @category WMG
 * @package  App\Models\Service\Model\Shipment\Package
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class Detail extends Serialize
{
    public $name = 'default';
    public $weight;
    public $weightUnit; //kg, gr, pounds
    public $length = '0';
    public $width = '0';
    public $height = '0';
    public $dimensionsUnit = 'inches'; //'cm, inches'
}
