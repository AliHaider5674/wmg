<?php
namespace App\Shopify\ServiceClients\Handlers;

use App\Models\Service\Event\ClientHandler\HandlerAbstract;
use App\Models\Service\Event\RequestData\RequestDataInterface;
use App\Core\Services\EventService;
use App\Exceptions\ServiceException;

/**
 * Shipment handler that create shipment in MDC via SOAP
 *
 * Class ShipmentHandler
 * @category WMG
 * @package  App\Mdc\Service\Event\Handlers
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class ShipmentHandler extends HandlerAbstract
{
    protected $handEvents = [
        EventService::EVENT_ITEM_SHIPPED
    ];

    /**
     * @param string                                                     $eventName
     * @param \App\Models\Service\Event\RequestData\RequestDataInterface $request
     * @param                                                            $client
     * @return array
     * @throws \App\Exceptions\ServiceException
     * @throws \PHPShopify\Exception\ApiException
     * @throws \PHPShopify\Exception\CurlException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function handle(string $eventName, RequestDataInterface $request, $client)
    {
        /** @var \App\Models\Service\Model\Shipment $data */
        $data = $request->getData();
        /**@var \App\Shopify\Clients\ShopifySDK $client */

        $shippedItem = [];
        $result = [];
        foreach ($data->packages as $package) {
            foreach ($package->aggregatedItems as $aggregatedItem) {
                foreach ($aggregatedItem->getHiddenData('line_qty_map') as $lineQtyMap) {
                    $shippedItem[] = [
                        'id' => $lineQtyMap['order_item_id'],
                        'quantity' => $lineQtyMap['qty']
                    ];
                }
            }
            $requestData = [
                'message' => 'Test',
                'notify_customer' => true,
                'tracking_info' => [
                    "number" => $package->trackingNumber,
                    "url" => $package->trackingLink,
                    "company" => $package->carrier
                ],
                'line_items_by_fulfillment_order' => [
                    [
                        'fulfillment_order_id' => $data->requestId,
                        'fulfillment_order_line_items' => $shippedItem
                    ]
                ]
            ];
            $response = $client->Fulfillment->post($requestData);
            if (isset($response['error'])) {
                throw new ServiceException($response['error'], ServiceException::NOT_ALLOW_RETRY);
            }
            $result[] = $response;
        }
        return $result;
    }
}
