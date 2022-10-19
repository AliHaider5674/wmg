<?php
namespace App\Core\ServiceEvent\Clients;

use App\Models\Service;
use App\Models\ServiceEventCall;

/**
 * Interface ClientInterface
 * @package App\Models\Service\Event
 */
interface ClientInterface
{
    public function request(ServiceEventCall $serviceEventCall);
    public function getName();
}
