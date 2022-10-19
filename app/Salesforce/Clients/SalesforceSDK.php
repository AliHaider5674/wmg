<?php

namespace App\Salesforce\Clients;

use App\Core\ServiceEvent\Clients\NetworkClients\ClientInterface;
use App\Core\ServiceEvent\Clients\NetworkClients\ClientTokenInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Utils;
use GuzzleHttp\Client;
use Exception;
use League\OAuth2\Client\Token\AccessToken;
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
class SalesforceSDK implements ClientTokenInterface, ClientInterface
{
    const API_AVAILABILITY = 'availability';
    const API_AVAILABILITY_ACTION_BATCH_UPDATE = 'batch-update';

    private Client $client;
    private string $token;

    /**
     * @var array|int[]
     */
    protected array $httpSuccessCodes = array(200, 204);
    private Config $config;


    /**
     * @param Client $client
     * @param $config
     */
    public function __construct(
        Client $client,
        Config $config
    ) {
        $this->client = $client;
        $this->config = $config;
    }

    /**
     * config
     * @param array $config
     * @throws Exception
     */
    public function config(array $config)
    {
        $this->config->setConfig($config);
    }

    public function invalidToken()
    {
        if (isset($this->token)) {
            unset($this->token);
        }
    }

    /**
     * @throws GuzzleException
     */
    public function newToken(): string
    {
        $this->invalidToken();
        return $this->getToken();
    }

    public function setToken($token)
    {
        $this->token = $token;
        if (is_array($token)) {
            $this->token = new AccessToken($token);
        } elseif (is_string($token)) {
            $this->token = new AccessToken([
                'access_token' => $token
            ]);
        }
        $this->token = $token;
    }


    /**
     * @throws GuzzleException
     * @throws Exception
     */
    public function getToken() : string
    {
        $tokenURL = $this->config->getAuthUrl();
        $options = [
            'headers' => ['Content-type' => 'application/x-www-form-urlencoded'],
            'auth' => [
                $this->config->getUsername(),
                $this->config->getPassword()
            ],
            'form_params' => [
                'grant_type' => $this->config->getGrantType(),
                'scope' => $this->config->getAuthScope()
            ]
        ];

        $response = $this->client->post($tokenURL, $options);

        if (!in_array($response->getStatusCode(), $this->httpSuccessCodes)) {
            throw new Exception("Failed to get access token");
        }

        $responseData = Utils::jsonDecode(
            $response->getBody(),
            true,
            512,
            JSON_OBJECT_AS_ARRAY
        );

        if (!isset($responseData['access_token']) || empty($responseData['access_token'])) {
            throw new Exception("Auth access token not present");
        }

        return new AccessToken($responseData);
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

        return $this->client->request($method, $uri, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
                'Accept' => 'application/json',
                'Content-type' => 'application/json'
            ],
            'json' => $data
        ]);
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
     * batchInventoryUpdate
     * @param array $data
     * @throws GuzzleException
     */
    public function batchInventoryUpdate(array $data)
    {
        $this->post(
            $this->getAvailabilityInventoryURLByAction(self::API_AVAILABILITY_ACTION_BATCH_UPDATE),
            $data
        );
    }

    /**
     * getAvailabilityInventoryURLByAction
     * @param string $action
     * @return string
     */
    public function getAvailabilityInventoryURLByAction(string $action): string
    {
        return sprintf(
            "%s/inventory/%s/v1/organizations/%s/availability-records/actions/%s",
            $this->config->getBaseUrl(),
            self::API_AVAILABILITY,
            $this->config->getOrganizationId(),
            $action
        );
    }
}
