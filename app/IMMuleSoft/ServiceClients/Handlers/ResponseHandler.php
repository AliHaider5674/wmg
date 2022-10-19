<?php

namespace App\IMMuleSoft\ServiceClients\Handlers;

use App\IMMuleSoft\Clients\IMMuleSoftSDK;
use App\IMMuleSoft\Constants\EventConstant;
use App\Models\Service\Event\ClientHandler\HandlerAbstract;
use App\Models\Service\Event\RequestData\RequestDataInterface;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class ResponseHandler
 * @package App\IMMuleSoft\ServiceClients\Handlers
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2022
 * @link     http://www.wmg.com
 */
class ResponseHandler extends HandlerAbstract
{
    protected $handEvents = [
       EventConstant::EVENT_IMMULESOFT_RESPONSE_MESSAGE
    ];

    const URI = 'responses';

    /**
     * handle
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param string $eventName
     * @param RequestDataInterface $request
     * @param IMMuleSoftSDK $client
     * @return string
     * @throws GuzzleException
     */
    public function handle(string $eventName, RequestDataInterface $request, $client): string
    {
        $results =  $client->post(self::URI, $request->getData());
        return $results->getReasonPhrase();
    }
}
