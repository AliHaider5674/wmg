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
class ServiceException extends Exception
{
    const NOT_ALLOW_RETRY = 403;
    const NETWORK_ERROR = 404;
    const ENDPOINT_ERROR = 502;
    const ENDPOINT_SOFT_ERROR = 500;
}
