<?php

namespace App\Salesforce\ServiceClients;

use App\Exceptions\ServiceException;
use App\Core\ServiceEvent\Clients\ClientAbstract;
use App\Models\Service\Event\ClientHandler\HandlerInterface;
use App\Models\Service\Event\RequestData\StandardRequest;
use App\Models\ServiceEventCall;
use App\Core\Services\ServiceEvent\NetworkClientService;
use App\Salesforce\Clients\SalesforceOmsSDK;
use App\Core\ServiceEvent\TokenProvider;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Client to handle OMS communication
 */
class OmsRestfulClient extends ClientAbstract
{
    const NAME = 'salesforce.oms.restful';
    private TokenProvider $tokenProvider;

    /**
     * @param array|iterable $handlers
     */
    public function __construct(
        NetworkClientService $clientService,
        TokenPRovider        $tokenProvider,
        NetworkClientService $networkClientService,
        iterable             $handlers = [],
        $networkClientName = SalesforceOmsSDK::class
    ) {
        parent::__construct($handlers, $networkClientName, $networkClientService);

        $this->clientService = $clientService;
        $this->tokenProvider = $tokenProvider;
    }


    /**
     * @param \App\Models\Service\Event\ClientHandler\HandlerInterface $handler
     * @param string                                                   $eventName
     * @param \App\Models\ServiceEventCall                             $eventCall
     * @param                                                          $client
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function handle(HandlerInterface $handler, string $eventName, ServiceEventCall $eventCall, $client)
    {
        try {
            $response = $this->handleSingle($handler, $eventName, $eventCall, $client);
        } catch (GuzzleException $e) {
            if ($e->getCode() != 401) {
                throw $e;
            }
            $this->tokenProvider->newToken($eventCall->serviceEvent->service, $client);
            $response = $this->handleSingle($handler, $eventName, $eventCall, $client);
        }
        return $response->getBody()->getContents();
    }

    /**
     * @param \App\Models\Service\Event\ClientHandler\HandlerInterface $handler
     * @param string                                                   $eventName
     * @param \App\Models\ServiceEventCall                             $eventCall
     * @param                                                          $client
     * @return mixed
     */
    protected function handleSingle(HandlerInterface $handler, string $eventName, ServiceEventCall $eventCall, $client)
    {
        $service = $eventCall->serviceEvent->service;
        $this->tokenProvider->getToken($service, $client);
        $request = new StandardRequest($eventCall->getData());
        return $handler->handle($eventName, $request, $client);
    }

    /**
     * getName
     * @return string
     */
    public function getName(): string
    {
        return self::NAME;
    }
}
