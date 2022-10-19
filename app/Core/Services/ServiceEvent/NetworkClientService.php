<?php

namespace App\Core\Services\ServiceEvent;

use App\Core\ServiceEvent\Clients\NetworkClients\ClientInterface;
use App\Mdc\Clients\SoapClient;
use App\Models\Service;
use App\Core\Services\ClientService as ServiceClientService;

/**
 * Class ClientService
 * @package App\Salesforce\Services
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2021
 * @link     http://www.wmg.com
 */
class NetworkClientService
{
    private array $clients;
    public function __construct()
    {
        $this->clients = [];
    }

    public function getClient(Service $service, $networkClientName)
    {
        if (!isset($this->clients[$service->id])) {
            $config = $service->getAddition();
            $client = app()->make($networkClientName);
            if ($client instanceof ClientInterface) {
                if (isset($service->app_url)) {
                    $config['url'] = $service->app_url;
                }
            }
            $this->clients[$service->id] = $client;
            $client->config($config);
        }
        return $this->clients[$service->id];
    }
}
