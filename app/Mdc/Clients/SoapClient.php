<?php
namespace App\Mdc\Clients;

use App\Core\ServiceEvent\Clients\NetworkClients\ClientInterface;
use App\Core\ServiceEvent\Clients\NetworkClients\ClientTokenInterface;
use SoapClient as SoapClientBase;

/**
 * MDC SOAP Client
 */
class SoapClient extends SoapClientBase implements ClientTokenInterface, ClientInterface
{
    private $token;
    private $config;

    public function config(array $config)
    {
        $this->config = $config;
    }

    public function newToken()
    {
        $token = $this->login(
            $this->config['username'] ?? '',
            $this->config['api_key'] ?? ''
        );
        $this->token = $token;
        return $token;
    }

    public function setToken($token)
    {
        $this->token = $token;
    }

    public function getToken()
    {
        return $this->token;
    }
}
