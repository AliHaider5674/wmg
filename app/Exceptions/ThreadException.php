<?php

namespace App\Exceptions;

use Exception;

/**
 * Thread Exception
 *
 * Class RecordExistException
 * @category WMG
 * @package  App\Exceptions
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class ThreadException extends Exception
{
    const MAX_THREAD_REACH = 100;
    const THREAD_NOT_EXIST = 400;
}
