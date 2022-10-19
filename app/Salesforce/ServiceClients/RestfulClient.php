<?php

namespace App\Salesforce\ServiceClients;

use App\Core\ServiceEvent\TokenProvider;
use App\Core\ServiceEvent\Clients\ClientAbstract;
use App\Models\Service\Event\ClientHandler\HandlerInterface;
use App\Models\Service\Event\RequestData\StandardRequest;
use App\Models\ServiceEventCall;
use App\Salesforce\Clients\SalesforceSDK;
use App\Core\Services\ServiceEvent\NetworkClientService;
use GuzzleHttp\Exception\ClientException;

/**
 * Class RestfulClient
 * @package App\Salesforce\ServiceClients
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2021
 * @link     http://www.wmg.com
 */
class RestfulClient extends ClientAbstract
{
    const NAME = 'salesforce.restful';
    private TokenProvider $tokenProvider;

    /**
     * @param array|iterable $handlers
     */
    public function __construct(
        NetworkClientService $clientService,
        TokenProvider        $tokenProvider,
        NetworkClientService $networkClientService,
        iterable             $handlers = [],
        $networkClientName = SalesforceSDK::class
    ) {
        parent::__construct($handlers, $networkClientName, $networkClientService);
        $this->clientService = $clientService;
        $this->tokenProvider = $tokenProvider;
    }


    /**
     * @inheritDoc
     * @param HandlerInterface $handler
     * @param string $eventName
     * @param ServiceEventCall $eventCall
     * @param SalesforceSDK $client
     */
    protected function handle(HandlerInterface $handler, string $eventName, ServiceEventCall $eventCall, $client)
    {
        $retryRequest = 0;
        $maxRetries = 3;

        while ($retryRequest <= $maxRetries) {
            try {
                $service = $eventCall->serviceEvent->service;
                $this->tokenProvider->getToken($service, $client);
                $request = new StandardRequest($eventCall->getData());
                return $handler->handle($eventName, $request, $client);
            } catch (ClientException $clientException) {
                if ($retryRequest >= $maxRetries) {
                    throw $clientException;
                }
                $retryRequest++;
                $this->tokenProvider->newToken($service, $client);
                continue;
            }
        }
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
