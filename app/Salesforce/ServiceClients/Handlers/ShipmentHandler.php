<?php

namespace App\Salesforce\ServiceClients\Handlers;

use App\Core\Services\EventService;
use App\Models\Service\Event\ClientHandler\HandlerAbstract;
use App\Models\Service\Event\RequestData\RequestDataInterface;

/**
 * Handle item ship
 */
class ShipmentHandler extends HandlerAbstract
{
    protected $handEvents = [
        EventService::EVENT_ITEM_SHIPPED
    ];

    /**
     * @param string                                                     $eventName
     * @param \App\Models\Service\Event\RequestData\RequestDataInterface $request
     * @param \App\Salesforce\Clients\SalesforceOmsSDK  $client
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function handle(string $eventName, RequestDataInterface $request, $client)
    {
        return $client->shipment($request->getData()->toArray(false));
    }
}
