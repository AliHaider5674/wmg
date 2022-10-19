<?php

namespace App\IMMuleSoft\Handler\IO;

use App\Core\Handlers\IO\IOInterface;
use App\Exceptions\MethodNotImplementedException;

/**
 * Class Stock
 * @package App\IMMuleSoft\Handler\IO
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2022
 * @link     http://www.wmg.com
 */
class Dummy implements IOInterface
{

    /**
     * @throws MethodNotImplementedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function receive($callback)
    {
        throw new MethodNotImplementedException(
            "The %s method is not implemented in this IO class"
        );
    }

    /**
     * @throws MethodNotImplementedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function send($data, $callback = null)
    {
        throw new MethodNotImplementedException(
            "The %s method is not implemented in this IO class"
        );
    }

    /**
     * @throws MethodNotImplementedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function start(array $data = null)
    {
        throw new MethodNotImplementedException(
            "The %s method is not implemented in this IO class"
        );
    }

    /**
     * @throws MethodNotImplementedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function finish(array $data = null)
    {
        throw new MethodNotImplementedException(
            "The %s method is not implemented in this IO class"
        );
    }

    /**
     * @throws MethodNotImplementedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function rollback(...$args)
    {
        throw new MethodNotImplementedException(
            "The %s method is not implemented in this IO class"
        );
    }
}
