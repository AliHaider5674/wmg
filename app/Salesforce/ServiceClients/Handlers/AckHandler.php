<?php

namespace App\Salesforce\ServiceClients\Handlers;

use App\Core\Services\EventService;
use App\Models\Service\Event\ClientHandler\HandlerAbstract;
use App\Models\Service\Event\RequestData\RequestDataInterface;

/**
 * Handle item ship
 */
class AckHandler extends HandlerAbstract
{
    protected $handEvents = [
        EventService::EVENT_ITEM_WAREHOUSE_ACK
    ];

    /**
     * @param string                                                     $eventName
     * @param \App\Models\Service\Event\RequestData\RequestDataInterface $request
     * @param \App\Salesforce\Clients\SalesforceOmsSDK  $client
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function handle(string $eventName, RequestDataInterface $request, $client)
    {
        /** @var \App\Models\Service\Model\ShipmentLineChange $data */
        $data = $request->getData();
        $requestData = $data->toArray(false);
        $requestData['request_id'] = $data->shipmentRequestId;
        unset($requestData['shipment_request_id']);
        return $client->ack($requestData);
    }
}
