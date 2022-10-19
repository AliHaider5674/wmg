<?php

namespace App\Shopify\ServiceClients;

use App\Core\Services\ServiceEvent\NetworkClientService;
use App\Exceptions\ServiceException;
use App\Core\ServiceEvent\Clients\ClientAbstract;
use App\Models\Service\Event\ClientHandler\HandlerInterface;
use App\Models\ServiceEventCall;
use App\Shopify\Enums\ShopifyClient;
use App\Shopify\Services\ClientService;
use Exception;
use App\Shopify\Clients\ShopifySDK;

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
class RestfulClient extends ClientAbstract
{
    private ClientService $clientService;

    /**
     * @param iterable                            $handlers
     * @param \App\Shopify\Services\ClientService $clientService
     * @param \App\Shopify\Clients\ShopifySDK     $networkClientName
     */
    public function __construct(
        iterable $handlers,
        ClientService $clientService,
        NetworkClientService $networkClientService,
        $networkClientName = ShopifySDK::class
    ) {
        parent::__construct($handlers, $networkClientName, $networkClientService);
        $this->clientService = $clientService;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return ShopifyClient::RESTFUL;
    }

    /**
     * @param ServiceEventCall $eventCall
     * @return ShopifySDK
     * @todo make this to use parent instead of override
     */
    protected function getClient(ServiceEventCall $eventCall) : ShopifySDK
    {
        /** @var \App\Models\Service $service */
        $service = $eventCall->serviceEvent->service;
        return $this->clientService->getClient($service);
    }

    /**
     * Handle requests
     *
     * @param HandlerInterface $handler
     * @param String           $eventName
     * @param ServiceEventCall $eventCall
     * @param                  $client
     * @return mixed
     * @throws ServiceException
     */
    protected function handle(
        HandlerInterface $handler,
        string $eventName,
        ServiceEventCall $eventCall,
        $client
    ) {
        try {
            return parent::handle($handler, $eventName, $eventCall, $client);
        } catch (Exception $e) {
            throw new ServiceException($e->getMessage(), ServiceException::ENDPOINT_ERROR);
        }
    }
}
