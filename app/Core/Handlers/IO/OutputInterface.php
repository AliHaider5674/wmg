<?php
namespace App\Core\Handlers\IO;

interface OutputInterface extends RuntimeInterface
{
    public function send($data, $callback = null);
}
