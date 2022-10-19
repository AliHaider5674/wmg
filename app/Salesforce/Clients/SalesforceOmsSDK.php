<?php

namespace App\Salesforce\Clients;

use App\Core\ServiceEvent\Clients\NetworkClients\ClientInterface;
use App\Core\ServiceEvent\Clients\NetworkClients\ClientTokenInterface;
use GuzzleHttp\Client;
use App\Core\ServiceEvent\Clients\Traits\RestOAuth;
use Psr\Http\Message\ResponseInterface;

/**
 * Class SalesforceSDK
 * @package App\Salesforce\Clients
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2021
 * @link     http://www.wmg.com
 */
class SalesforceOmsSDK implements ClientTokenInterface, ClientInterface
{
    use RestOAuth;
    private Client $client;
    private string $baseUrl;
    /**
     * @param Client $client
     * @param $config
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function newToken()
    {
        $this->invalidToken();
        return $this->getToken();
    }

    public function config(array $config)
    {
        $this->configOAuth($config);
        $this->baseUrl = $config['url'];
    }

    public function ack($data)
    {
        return $this->post('/services/apexrest/FulfillmentAcknowledgement', $data);
    }

    public function shipment($data)
    {
        return $this->patch('/services/apexrest/FulfillmentConfirmation', $data);
    }

    /**
     * @param $method
     * @param $uri
     * @param $data
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function request($method, $uri, $data) : ResponseInterface
    {
        $response = $this->client->request($method, $this->baseUrl . $uri, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token->getToken(),
                'Accept' => 'application/json'
            ],
            'json' => $data
        ]);
        return $response;
    }

    public function post($uri, $data) : ResponseInterface
    {
        return $this->request('POST', $uri, $data);
    }

    public function patch($uri, $data) : ResponseInterface
    {
        return $this->request('PATCH', $uri, $data);
    }

    public function get($uri, $data): ResponseInterface
    {
        return $this->request('GET', $uri, $data);
    }
}
