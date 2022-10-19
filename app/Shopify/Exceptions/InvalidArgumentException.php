<?php declare(strict_types=1);

namespace App\Shopify\Exceptions;

use Throwable;
use Exception;

/**
 * Class InvalidArgumentException
 * @package App\Shopify\Exceptions
 */
class InvalidArgumentException extends Exception
{
    const INVALID_URL = 100;
}
