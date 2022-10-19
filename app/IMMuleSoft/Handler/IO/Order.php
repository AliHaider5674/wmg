<?php

namespace App\IMMuleSoft\Handler\IO;

use App\Core\Handlers\IO\IOInterface;
use App\Exceptions\MethodNotImplementedException;

/**
 * Class Order
 * @package App\IMMuleSoft\Handler\IO
 * @SuppressWarnings(PHPMD)
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2022
 * @link     http://www.wmg.com
 */
class Order implements IOInterface
{
    /**
     * send
     * @param $data
     * @param null $callback
     * @return void
     * @throws MethodNotImplementedException
     */
    public function send($data, $callback = null)
    {
        throw new MethodNotImplementedException(
            "The %s method is not implemented in this IO class"
        );
    }

    /**
     * start
     * @param array|null $data
     * @throws MethodNotImplementedException
     */
    public function start(array $data = null)
    {
        throw new MethodNotImplementedException(
            "The %s method is not implemented in this IO class"
        );
    }

    /**
     * @throws MethodNotImplementedException
     */
    public function receive($callback)
    {
        throw new MethodNotImplementedException(
            "The %s method is not implemented in this IO class"
        );
    }

    /**
     * @throws MethodNotImplementedException
     */
    public function finish(array $data = null)
    {
        throw new MethodNotImplementedException(
            "The %s method is not implemented in this IO class"
        );
    }

    /**
     * @throws MethodNotImplementedException
     */
    public function rollback(...$args)
    {
        throw new MethodNotImplementedException(
            "The %s method is not implemented in this IO class"
        );
    }
}
