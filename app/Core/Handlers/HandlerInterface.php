<?php
namespace App\Core\Handlers;

interface HandlerInterface
{
    public function handle();
    public function validate();
}
