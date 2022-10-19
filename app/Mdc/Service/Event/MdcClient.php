<?php

namespace App\Mdc\Service\Event;

use App\Core\Services\ServiceEvent\NetworkClientService;
use App\Exceptions\ServiceException;
use App\Mdc\Service\SoapFaultErrorParser;
use App\Core\ServiceEvent\TokenProvider;
use App\Core\ServiceEvent\Clients\ClientAbstract;
use App\Models\Service\Event\ClientHandler\HandlerInterface;
use App\Models\Service\Event\RequestData\TokenRequest;
use App\Models\ServiceEventCall;
use App\Mdc\Clients\SoapClient;
use SoapFault;

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
class MdcClient extends ClientAbstract
{
    /**
     * @var SoapClient
     */
    protected $tokenCache;

    /**
     * @var TokenProvider
     */
    protected $tokenProvider;

    /**
     * @var SoapClientManager
     */
    private $soapClientManager;

    /**
     * @var SoapFaultErrorParser
     */
    private $errorParser;

    /**
     * MdcClient constructor.
     *
     * @param TokenProvider        $tokenProvider
     * @param SoapClientManager    $soapClientManager
     * @param SoapFaultErrorParser $errorParser
     * @param iterable             $handlers
     * @todo use networkclientname
     */
    public function __construct(
        TokenProvider $tokenProvider,
        SoapClientManager $soapClientManager,
        SoapFaultErrorParser $errorParser,
        iterable $handlers,
        NetworkClientService $networkClientService
    ) {
        parent::__construct($handlers, SoapClient::class, $networkClientService);

        $this->tokenProvider = $tokenProvider;
        $this->soapClientManager = $soapClientManager;
        $this->errorParser = $errorParser;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'm1';
    }

    /**
     * @param ServiceEventCall $eventCall
     * @return SoapClient
     */
    protected function getClient(ServiceEventCall $eventCall): SoapClient
    {
        return $this->soapClientManager->getClient($eventCall->serviceEvent->service);
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
        $retry = true;
        $maxRetries = 1;
        $service = $eventCall->serviceEvent->service;
        $result = null;
        while ($retry) {
            try {
                if (!isset($token)) {
                    $token = $this->tokenProvider->getToken($service, $client);
                }

                $clientRequest = new TokenRequest($token, $eventCall->getData());
                $result = $handler->handle($eventName, $clientRequest, $client);
                $retry = false;
            } catch (SoapFault $exception) {
                //Renew token
                if ((int) $exception->faultcode === 5 && $maxRetries > 0) {
                    $token = $this->tokenProvider->newToken($service, $client);
                    $maxRetries--;
                    $retry = true;

                    continue;
                }

                $message = $exception->getMessage();

                if (isset($client) && $client->__getLastResponse()) {
                    $message .= ':' . $client->__getLastResponse();
                }

                throw $this->errorParser->convertToServiceException(
                    $exception,
                    $message
                );
            }
        }

        return $result;
    }
}
