<?php
namespace App\Core\Exceptions\Handler;

/**
 *
 * Class IOException
 * @category WMG
 * @package  App\Core\Exceptions\Handler
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2020
 * @link     http://www.wmg.com
 */
class IOException extends \Exception
{
    const API_SEND_ERROR = 400;
}
