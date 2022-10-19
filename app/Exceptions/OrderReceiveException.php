<?php

namespace App\Exceptions;

use Exception;

/**
 * OrderReceiveException
 *
 * Class OrderReceiveException
 * @category WMG
 * @package  App\Exceptions
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class OrderReceiveException extends Exception
{
    const QUANTITY_AGGREGATE_ERROR = 1;
}
