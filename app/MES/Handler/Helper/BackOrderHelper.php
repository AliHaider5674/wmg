<?php
namespace App\MES\Handler\Helper;

/**
 * MES helper that if an order is
 * back order
 *
 * Class BackOrderHelper
 * @category WMG
 * @package  App\MES\Handler\Helper
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class BackOrderHelper
{
    public function isBackOrder($reasonCode)
    {
        return in_array($reasonCode, [
            '0', '1', '3', '4', '5', 'M'
        ]);
    }
}
