<?php

namespace App\Shopify\ServiceClients\Handlers;

use App\Core\Services\EventService;
use App\Models\Service\Event\ClientHandler\HandlerAbstract;
use App\Models\Service\Event\RequestData\RequestDataInterface;

/**
 * Class ShipmentRequestHandler
 * @package App\Shopify\ServiceClients\Handlers
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2021
 * @link     http://www.wmg.com
 */
class ShipmentRequestHandler extends HandlerAbstract
{
    const SHOPIFY_FULFILLMENT_ORDER_STATUS_CLOSED = 'closed';

    /**
     * @var array
     */
    protected $handEvents = [
        EventService::EVENT_ITEM_SHIPMENT_REQUEST,
    ];

    /**
     * handle
     * @SuppressWarnings(PHPMD)
     * @param string $eventName
     * @param RequestDataInterface $request
     * @param $client
     * @return array
     */
    public function handle(string $eventName, RequestDataInterface $request, $client)
    {
        $response = array();

        $requestData = $request->getData();

        // verify request data
        if (empty($requestData->shipmentRequestId)) {
            return $response;
        }

        $requestId = $requestData->shipmentRequestId;

        //close existing fulfillment order
        $fulfillmentOrder = $client->FulfillmentOrder($requestId)->close();

        if ($this->isFulfillmentOrderClosed($fulfillmentOrder, $requestId)) {
            //get orderId
            $orderId = $requestData->getHiddenOrderId();

            //get fulfillment orders for an order
            $fulfillmentOrders = $client->FulfillmentOrder($orderId)->fulfillment_orders();

            foreach ($fulfillmentOrders as $fulfillmentOrder) {
                if (self::SHOPIFY_FULFILLMENT_ORDER_STATUS_CLOSED !== $fulfillmentOrder->status) {
                    $response[] = $this->sendFulfillmentRequest($client, $fulfillmentOrder);
                }
            }
        }

        return $response;
    }

    /**
     * sendFulfillmentRequest
     * @param $client
     * @param $fulfillmentOrderId
     * @return mixed
     */
    protected function sendFulfillmentRequest($client, $fulfillmentOrderId)
    {
         return $client->FulfillmentRequest($fulfillmentOrderId)->fulfillment_request();

        //simplify response
//        if ($response) {
//            return $response;
//        }
    }

    /**
     * isFulfillmentOrderCanceled
     * @param $fulfillmentOrder
     * @param $requestId
     * @return bool
     */
    protected function isFulfillmentOrderClosed($fulfillmentOrder, $requestId) : bool
    {
        $isClosed = false;

        //Check that it is the correct $fulfillmentOrder
        if (empty($fulfillmentOrder) || $fulfillmentOrder->id !== $requestId) {
            return $isClosed;
        }

        if (self::SHOPIFY_FULFILLMENT_ORDER_STATUS_CLOSED === $fulfillmentOrder->status) {
            $isClosed = true;
        }

        return $isClosed;
    }
}
