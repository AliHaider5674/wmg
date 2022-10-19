<?php

namespace App\IMMuleSoft\ServiceClients;

use App\Core\ServiceEvent\Clients\ClientAbstract;
use App\IMMuleSoft\Clients\IMMuleSoftSDK;
use App\Models\Service\Event\ClientHandler\HandlerInterface;
use App\Models\Service\Event\RequestData\StandardRequest;
use App\Models\ServiceEventCall;
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
    const NAME = 'immulesoft.restful';

    /**
     * @param array|iterable $handlers
     */
    public function __construct(
        NetworkClientService $networkClientService,
        iterable             $handlers = [],
        $networkClientName = IMMuleSoftSDK::class
    ) {
        parent::__construct($handlers, $networkClientName, $networkClientService);
    }

    /**
     * @inheritDoc
     * @param HandlerInterface $handler
     * @param string $eventName
     * @param ServiceEventCall $eventCall
     * @param IMMuleSoftSDK $client
     */
    protected function handle(HandlerInterface $handler, string $eventName, ServiceEventCall $eventCall, $client)
    {
        $retryRequest = 0;
        $maxRetries = 3;

        while ($retryRequest <= $maxRetries) {
            try {
                $request = new StandardRequest($eventCall->getData());
                return $handler->handle($eventName, $request, $client);
            } catch (ClientException $clientException) {
                if ($retryRequest >= $maxRetries) {
                    throw $clientException;
                }
                $retryRequest++;
                continue;
            }
        }

        return '';
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
