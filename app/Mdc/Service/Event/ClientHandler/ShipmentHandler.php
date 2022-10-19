<?php
namespace App\Mdc\Service\Event\ClientHandler;

use App\Models\Service\Event\ClientHandler\HandlerAbstract;
use App\Models\Service\Event\RequestData\RequestDataInterface;
use App\Core\Services\EventService;
use App\Models\Service\Model\Shipment;
use App\Models\Service\Model\Shipment\Package;
use App\Models\Service\Model\Shipment\Package\AggregatedItem;

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
     * Handle request
     *
     * @param string $eventName
     * @param RequestDataInterface $request
     * @param \SoapClient $client
     *
     * @return array
     */
    public function handle(string $eventName, RequestDataInterface $request, $client)
    {
        $shipment = $request->getData();
        $shipmentIds = [];
        /**@var Shipment $shipment*/
        $shipment->getHiddenOrderId();

        foreach ($shipment->packages as $package) {
            $shippedItem = [];
            /**@var Package $package*/
            foreach ($package->aggregatedItems as $item) {
                /**@var AggregatedItem $item */
                $lineQtyMap = $item->getHiddenData('line_qty_map');
                $shippedItem = array_merge($shippedItem, $lineQtyMap);
            }
            $orderId = $shipment->getHiddenOrderNumber();
            $shipmentId = $client->salesOrderShipmentCreate(
                $request->token,
                $orderId,
                $shippedItem,
                null,
                false
            );

            //Add tracking info;
            $client->salesOrderShipmentAddTrack(
                $request->token,
                $shipmentId,
                $package->carrier,
                $package->trackingComment,
                $package->trackingNumber
            );

            //Send Email Notification
            $client->salesOrderShipmentSendInfo(
                $request->token,
                $shipmentId
            );

            $shipmentIds[] = $shipmentId;
        }
        return $shipmentIds;
    }
}
