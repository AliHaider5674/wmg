<?php

namespace App\IM\Handler\IO;

use App\Core\Handlers\IO\IOInterface;
use App\IM\Configurations\ImConfig;
use GuzzleHttp\Client;
use App\Models\AlertEvent;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

/**
 * Class ApiAbstract
 *
 *
 * @category WMG
 * @package  App\IM\Handler\IO
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License
 * @link     http://www.wmg.com
 */
abstract class ApiAbstract implements IOInterface
{
    /**
     * @var string
     */
    protected $baseEndPoint;

    /**
     * @var \GuzzleHttp\Client
     */
    protected $apiClient;

    /**
     * @var mixed
     */
    protected $apiUsername;

    /**
     * @var mixed
     */
    protected $apiPassword;

    /**
     * e.g. rest/v1/Stock
     * @var string
     */
    protected $apiURI;

    /**
     * @var string
     */
    protected $apiName;

    protected $apiUriFilters = array();

    /**
     * @var ImConfig
     */
    protected $config;


    /**
     * constructor.
     *
     * @param ImConfig $config
     */
    public function __construct(ImConfig $config)
    {
        $this->config = $config;
    }

    /**
     * @inheritdoc
     * @param array|null $data
     */
    public function start(array $data = null)
    {
        if (!$this->config) {
            return;
        }
        //setup API client
        $this->apiClient = new Client(['base_uri' => $this->config->getBaseApiEndPoint()]);
    }

    /**
     * Get API endpoint with filters
     * @return string
     */
    protected function getApiEndpoint()
    {
        //append Filters
        if (!empty($this->apiUriFilters)) {
            $filter = implode('&', $this->apiUriFilters);
            return sprintf("%s?%s", $this->apiURI, $filter);
        }

        return $this->apiURI;
    }

    /**
     * Add any api filters
     *
     * @param $filter
     */
    public function addApiFilter($filter)
    {
        $this->apiUriFilters[] = $filter;
    }

    /**
     * getDataFromWarehouse
     *
     * Get updates from the warehouse filter by the endpoint uri
     * i.e. stock, shipment
     * @return mixed
     */
    protected function getDataFromWarehouse()
    {
        try {
            $response = $this->apiClient->get($this->getApiEndpoint(), [
                'headers' => ['Content-type' => 'application/json'],
                'auth' => [
                    $this->config->getApiUsername(),
                    $this->config->getApiPassword()
                ]
            ]);

            return \GuzzleHttp\json_decode(
                $response->getBody(),
                true,
                512,
                JSON_OBJECT_AS_ARRAY
            );
        } catch (RequestException $e) {
            $data = [
                'name' => $this->apiName,
                'content' => $e->getMessage(),
                'type' => AlertEvent::TYPE_CONNECTION_ERROR,
                'level' => AlertEvent::LEVEL_CRITICAL
            ];
            $alertEvent = new AlertEvent();
            $alertEvent->fill($data);
            $alertEvent->save();
        }
    }
}
