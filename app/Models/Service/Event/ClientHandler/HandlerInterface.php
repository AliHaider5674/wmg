<?php
namespace App\Models\Service\Event\ClientHandler;

use App\Models\Service\Event\RequestData\RequestDataInterface;

interface HandlerInterface
{
    public function canHandle(string $eventName);
    public function handle(string $eventName, RequestDataInterface $request, $client);
}
