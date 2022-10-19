<?php

namespace App\Core\Handlers\IO;

/**
 * Interface for doing warehouse IO/API
 *
 * Interface IOInterface
 * @package App\Models\Warehouse\Handler
 */
interface RuntimeInterface
{
    public function start(array $data = null);
    public function finish(array $data = null);
    public function rollback(...$args);
}
