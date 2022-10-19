<?php
namespace App\Core\Handlers\IO;

interface InputInterface extends RuntimeInterface
{
    public function receive($callback);
}
