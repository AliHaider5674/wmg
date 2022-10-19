<?php

namespace App\Printful\Handler\IO;

use App\Core\Handlers\IO\IOInterface;
use App\Printful\Exceptions\MethodNotImplementedException;

/**
 * Class BasePrintfulIo
 *
 * Abstract IO Stream for handling Printful events
 */
abstract class BasePrintfulStream implements IOInterface
{
    /**
     * Start IO process
     *
     * @param array|null $data
     */
    public function start(array $data = null): void
    {
        // Not implemented
    }

    /**
     * Receive parameter and process with callback
     *
     * @param $callback
     * @throws MethodNotImplementedException
     */
    public function receive($callback): void
    {
        $this->notImplementedException('receive');
    }

    /**
     * Send data and then call callback
     *
     * @param      $data
     * @param null $callback
     * @throws MethodNotImplementedException
     */
    public function send($data, $callback = null): void
    {
        $this->notImplementedException('send');
    }

    /**
     * Finish IO Process
     *
     * @param array|null $data
     */
    public function finish(array $data = null): void
    {
        // No implementation
    }

    /**
     * Rollback IO Process
     *
     * @param mixed ...$args
     * @throws MethodNotImplementedException
     */
    public function rollback(...$args): void
    {
        $this->notImplementedException('rollback');
    }

    /**
     * @param string $methodName
     * @throws MethodNotImplementedException
     */
    private function notImplementedException(string $methodName): void
    {
        throw new MethodNotImplementedException(
            sprintf("The %s method is not implemented in this IO class", $methodName)
        );
    }
}
