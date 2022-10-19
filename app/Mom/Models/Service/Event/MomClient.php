<?php
namespace App\Mom\Models\Service\Event;

use App\Core\Services\ServiceEvent\NetworkClientService;
use App\Exceptions\ServiceException;
use App\Core\ServiceEvent\Clients\ClientAbstract;
use App\Mdc\Clients\SoapClient;
use App\Models\Service\Event\ClientHandler\HandlerInterface;
use MomApi\Client;
use App\Models\ServiceEventCall;

/**
 * A client that distribute event data to MOM
 *
 * Class MomClient
 * @category WMG
 * @package  App\Mom\Service\Event
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class MomClient extends ClientAbstract
{
    protected $client;
    protected $eventMap;

    /**
     * MomClient constructor.
     *
     * @param Client   $client
     * @param EventMap $eventMap
     * @param iterable $handlers
     */
    public function __construct(
        Client $client,
        EventMap $eventMap,
        iterable $handlers,
        NetworkClientService $networkClientService
    ) {
        parent::__construct($handlers, MomClient::class, $networkClientService);
        $this->client = $client;
        $this->eventMap = $eventMap;
    }

    public function getName()
    {
        return 'mom';
    }

    protected function getClient(ServiceEventCall $eventCall)
    {
        return $this->client;
    }

    /**
     * Handle request
     *
     * @param \App\Models\Service\Event\ClientHandler\HandlerInterface $handler
     * @param string                       $eventName
     * @param \App\Models\ServiceEventCall $eventCall
     * @param                              $client
     *
     * @return mixed
     * @throws \App\Exceptions\ServiceException
     */
    protected function handle(HandlerInterface $handler, string $eventName, ServiceEventCall $eventCall, $client)
    {
        try {
            return parent::handle($handler, $eventName, $eventCall, $client);
        } catch (\Exception $e) {
            throw new ServiceException($e->getMessage(), ServiceException::ENDPOINT_ERROR);
        }
    }
}
