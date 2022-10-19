<?php
namespace App\Mdc\Service\Event\ClientHandler;

use App\Models\Service\Event\ClientHandler\HandlerAbstract;
use App\Models\Service\Event\RequestData\RequestDataInterface;
use App\Core\Services\EventService;

/**
 * Ack handler that ack order in MDC via soap
 *
 * Class AckHandler
 * @category WMG
 * @package  App\Mdc\Service\Event\Handlers
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class AckHandler extends HandlerAbstract
{
    protected $handEvents = [
        EventService::EVENT_ITEM_WAREHOUSE_ACK,
    ];

    /**
     * Handle request
     *
     * @param string $eventName
     * @param RequestDataInterface $request
     * @param \SoapClient $client
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function handle(string $eventName, RequestDataInterface $request, $client)
    {
        $requestData = [];
        foreach ($request->data->items as $item) {
            /**@var \App\Models\Service\Model\ShipmentLineChange\Item $item*/
            $requestData[] = $item->toArray(false);
        }
        return $client->fulfillmentAck($request->token, $requestData);
    }
}
