<?php

namespace App\Core\Services;

use App\Core\ServiceEvent\Clients\ClientInterface;
use Exception;

/**
 * A manager that control all clients such as
 * soap, mom, rest and more...
 *
 * Class ClientService
 * @category WMG
 * @package  App\Core\Service
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class ClientService
{
    protected $clients;

    /**
     * ClientService constructor.
     * @param ?array $clients
     */
    public function __construct(array $clients = null)
    {
        if ($clients !== null) {
            $this->clients = $clients;
        }
    }

    /**
     * Add a client
     *
     * @param \App\Core\ServiceEvent\Clients\ClientInterface $client
     * @return $this
     */
    public function addClient(ClientInterface $client)
    {
        $this->clients[$client->getName()] = $client;
        return $this;
    }

    /**
     * Get client by name
     * @param $name
     * @return ClientInterface
     * @throws \Exception
     */
    public function getClient($name)
    {
        if (!isset($this->clients[$name])) {
            throw new Exception('Client do not exist');
        }
        return app()->make(get_class($this->clients[$name]));
    }
}
