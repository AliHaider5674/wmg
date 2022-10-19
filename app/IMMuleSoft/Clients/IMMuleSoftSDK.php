<?php

namespace App\IMMuleSoft\Clients;

use App\Core\ServiceEvent\Clients\NetworkClients\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\Client;
use Exception;
use Psr\Http\Message\ResponseInterface;

/**
 * Class SalesforceSDK
 * @package App\IMMuleSoft\Clients
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2022
 * @link     http://www.wmg.com
 */
class IMMuleSoftSDK implements ClientInterface
{
    private Client $client;

    /**
     * @var array|int[]
     */
    protected array $httpSuccessCodes = array(200, 204);
    /**
     * @var mixed
     */
    private $baseUrl;
    /**
     * @var mixed
     */
    private $password;
    /**
     * @var mixed
     */
    private $username;

    /**
     * @param Client $client
     * @param $config
     */
    public function __construct(
        Client $client
    ) {
        $this->client = $client;
    }

    /**
     * config
     * @param array $config
     * @throws Exception
     */
    public function config(array $config)
    {
        if (!isset($config['username'])
            || !isset($config['password'])
            || !isset($config['base_url'])
        ) {
            throw new Exception('Missing API credentials');
        }

        $this->username = $config['username'];
        $this->password = $config['password'];
        $this->baseUrl = $config['base_url'];
    }

    /**
     * request
     * @param string $method
     * @param string $uri
     * @param $data
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function request(string $method, string $uri, $data) : ResponseInterface
    {
        $endpoint = $this->getEndpoint($uri);

        $options = array();

        $options[RequestOptions::AUTH] = [
            $this->username,
            $this->password
        ];

        if (is_string($data)) {
            $options[RequestOptions::HEADERS] = ['Content-type' => 'application/json'];
            $options[RequestOptions::BODY] = $data;
        }

        if (is_array($data) || is_object($data)) {
            $options[RequestOptions::JSON] = $data;
        }

        return $this->client->request($method, $endpoint, $options);
    }

    /**
     * @throws GuzzleException
     */
    public function post($uri, $data) : ResponseInterface
    {
        return $this->request('POST', $uri, $data);
    }

    /**
     * @throws GuzzleException
     */
    public function patch($uri, $data) : ResponseInterface
    {
        return $this->request('PATCH', $uri, $data);
    }

    /**
     * @throws GuzzleException
     */
    public function get($uri, $data): ResponseInterface
    {
        return $this->request('GET', $uri, $data);
    }

    /**
     * getEndpoint
     * @param $uri
     * @param string $format
     * @return string
     */
    protected function getEndpoint($uri, string $format = "%s%s") : string
    {
        return sprintf(
            $format,
            $this->baseUrl,
            $uri
        );
    }
}
