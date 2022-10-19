<?php
namespace App\Core\ServiceEvent\Clients\NetworkClients;

/**
 * Interface ClientTokenInterface
 * @package App\Core
 */
interface ClientTokenInterface
{
    public function newToken();
    public function getToken();
    public function setToken($token);
}
