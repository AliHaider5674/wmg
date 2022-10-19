<?php
namespace App\Mom\Models\Service\Event\ClientHandler;

use App\Models\Order;
use App\Models\Service\Event\RequestData\RequestDataInterface;
use App\Core\Services\EventService;
use App\Models\Service\Model\ShipmentLineChange;
use App\Mom\Constants\ConfigurationConstant;
use App\Mom\Constants\EventConstant;
use App\Mom\Helpers\ReasonCodeHelper;
use MomApi\Client;

/**
 * Default MOM request handler
 *
 * Class DefaultHandler
 * @category WMG
 * @package  App\Mom\Service\Event\Handlers
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class LineChangeHandler extends DefaultHandler
{
    const DEFAULT_LINE_ITEM_STATUS = 'RECEIVEDBYLOGISTICS';
    protected $handEvents = [
        EventService::EVENT_ITEM_WAREHOUSE_ACK
    ];

    /**
     * Handle all requests
     * @param string $eventName
     * @param \App\Models\Service\Event\RequestData\RequestDataInterface $request
     * @param Client                                                     $client
     *
     * @return mixed
     * @throws \Exception
     */
    public function handle(string $eventName, RequestDataInterface $request, $client)
    {
        $lineChange = $request->getData();
        /** @var Order $order */
        $order = Order::where('id', '=', $lineChange->getHiddenOrderId())->first();
        if (!$order) {
            return;
        }
        $addCommentEvent = $this->eventMap->getMomEvent(EventConstant::EVENT_ORDER_ACTION_CREATED);
        $lineChangeEvent = $this->eventMap->getMomEvent(EventService::EVENT_ITEM_WAREHOUSE_ACK);


        foreach ($lineChange->items as $item) {
            $newOrderStatus = $this->reasonCodeHelper->getStatusByCode($item->statusReason);
            $statusCodeName = $this->reasonCodeHelper->getStatusCodeName($item->statusReason);
            if ($this->reasonCodeHelper->isBackorder($item->statusReason)) {
                //Add comment
                $client->publish($addCommentEvent, [
                    'order_comment' => [
                        'order_id' => $order->getAttribute('order_id'),
                        'sales_channel_id' => $order->getAttribute('sales_channel'),
                        'created_date' => date('Y-m-d\TH:i:sP'),
                        'user' => 'Fulfillment',
                        'comment' => sprintf(
                            'Received %s(%s-%s)',
                            $item->sku,
                            $item->statusReason,
                            $statusCodeName
                        )
                    ]
                ], '*');
            } elseif ($statusCodeName == ReasonCodeHelper::UNKNOWN_STATUS_CODE) {
                //Add comment
                $client->publish($addCommentEvent, [
                    'order_comment' => [
                        'order_id' => $order->getAttribute('order_id'),
                        'sales_channel_id' => $order->getAttribute('sales_channel'),
                        'created_date' => date('Y-m-d\TH:i:sP'),
                        'user' => 'Fulfillment',
                        'comment' => sprintf(
                            'Warehouse received %s',
                            $item->sku
                        )
                    ]
                ], '*');
            }

            $newLineChange = new ShipmentLineChange();
            $newLineChange->shipmentRequestId = $lineChange->shipmentRequestId;
            $newLineChange->setHiddenOrderId($order->getAttribute('order_id'));
            $newItem = $newLineChange->newItem();
            $newItem->fill($item->toArray());
            $newItem->statusReason = $newOrderStatus;
            $newItem->status = $newOrderStatus;
            $client->publish(
                $lineChangeEvent,
                $newLineChange->toArray(false),
                '*'
            );
        }
    }
}
