<?php
namespace App\Shopify\ServiceClients\Handlers;

use App\Exceptions\ServiceException;
use App\Models\Order;
use App\Models\Service\Event\ClientHandler\HandlerAbstract;
use App\Models\Service\Event\RequestData\RequestDataInterface;
use App\Core\Services\EventService;
use App\Models\Service\Model\ShipmentLineChange\Item;
use App\Shopify\ServiceClients\Handlers\Ack\OrderProcessor;
use App\Shopify\ServiceClients\Handlers\Ack\Ack;
use App\Shopify\ServiceClients\Handlers\Ack\ShopifyService;
use WMGCore\Services\ConfigService;
use App\Models\Service\Model\ShipmentLineChange;
use Exception;

/**
 * Ack handler that ack order in MDC via soap
 *
 * Class ProductHandler
 * @category WMG
 * @package  App\Mdc\Service\Event\Handlers
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class AckHandler extends HandlerAbstract
{
    /**
     * @var array
     */
    protected $handEvents = [
        EventService::EVENT_ITEM_WAREHOUSE_ACK,
    ];

    private ConfigService $configService;
    private ShopifyService $shopifyService;
    private OrderProcessor $orderProcessor;

    /**
     * @param ConfigService $configService
     * @param OrderProcessor $orderProcessor
     * @param ShopifyService $shopifyService
     */
    public function __construct(
        ConfigService   $configService,
        OrderProcessor  $orderProcessor,
        ShopifyService  $shopifyService
    ) {
        $this->configService = $configService;
        $this->orderProcessor = $orderProcessor;
        $this->shopifyService = $shopifyService;
    }

    /**
     * handle
     * @param string $eventName
     * @param RequestDataInterface $request
     * @param $client
     * @return array
     * @throws ServiceException
     * @throws Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function handle(string $eventName, RequestDataInterface $request, $client): array
    {
        //get reason code map
        $reasonCodeMap = $this->getReasonCodeMap();

        if (empty($reasonCodeMap)) {
            throw new Exception('Reason Code Map doesnt exist');
        }

        //get order item acknowledgments and reasons/backorders
        $result = $this->getAckLines($reasonCodeMap, $request->data);

        if ($result['hasResults']) {
            $acknowledgements = $result['ack'];


            //get existing shopify order information
            $existingShopifyOrder  =
                $this->shopifyService->getExistingShopifyOrder($client, $acknowledgements->orderId);

            //process new and old data
            $shopifyOrderData = $this->orderProcessor
                ->processData($existingShopifyOrder, $acknowledgements->data);

            //update order
            if (!empty($shopifyOrderData)) {
                $response = $this->shopifyService->updateOrder($client, $acknowledgements->orderId, $shopifyOrderData);
                if (isset($response['error'])) {
                    throw new ServiceException($response['error'], ServiceException::NOT_ALLOW_RETRY);
                }
                return $response;
            }
        }
        return array();
    }

    /**
     * getReasonCodeMap
     * @return array
     */
    public function getReasonCodeMap():array
    {
        return $this->configService->getJson('shopify.reason_codes.map');
    }

    /**
     * getWarehouseAckLines
     * @param array $reasonCodeMap
     * @param ShipmentLineChange $shipmentLineChange
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function getAckLines(array $reasonCodeMap, ShipmentLineChange $shipmentLineChange): array
    {
        $orderStatuses = array();
        $orderItemsWithReasonCodes = array();
        $ackOrderItems = array();
        $results = array();
        $results['hasResults'] = false;

        foreach ($shipmentLineChange->items as $item) {
            //items that have been acknowledged by warehouse
            if (empty($item->statusReason)) {
                $ackOrderItems[] = $item->sku;
                continue;
            }

            //items with reason codes
            if (array_key_exists($item->statusReason, $reasonCodeMap)) {
                $mapItem = $reasonCodeMap[$item->statusReason];

                $this->getOrderStatusTags($mapItem, $orderStatuses);
                $this->getItemReasons($item, $mapItem, $orderItemsWithReasonCodes);
            }
        }

        if (!empty($orderStatuses) || !empty($orderItemsWithReasonCodes) || !empty($ackOrderItems)) {
            $acknowledgment = new Ack();
            $acknowledgment->orderId = $this->getOrderId($shipmentLineChange);
            $acknowledgment->data = array(
                'tags' => implode(',', $orderStatuses),
                'note' => ['reasonLines' => $orderItemsWithReasonCodes, 'ackLines' => $ackOrderItems]
            );

            $results['ack'] = $acknowledgment;
            $results['hasResults'] = true;
        }

        return $results;
    }

    /**
     * get customer order id
     * @param $shipmentLineChange
     * @return int
     */
    protected function getOrderId($shipmentLineChange): int
    {
        $order = Order::where('id', $shipmentLineChange->getHiddenOrderId())->firstOrFail();
        return $order->order_id;
    }

    /**
     * getOrderStatusTags
     * @param array $mapItem
     * @param array $orderStatuses
     */
    protected function getOrderStatusTags(array $mapItem, array &$orderStatuses)
    {
        if (isset($mapItem['order_status']) && !empty($mapItem['order_status'])) {
            if (!in_array($mapItem['order_status'], $orderStatuses)) {
                $orderStatuses[] = $mapItem['order_status'];
            }
        }
    }

    /**
     * getItemReasons
     * @param Item $item
     * @param array $mapItem
     * @param array $orderItemsWithReasonCodes
     */
    protected function getItemReasons(Item $item, array $mapItem, array &$orderItemsWithReasonCodes)
    {
        if (isset($mapItem['reason']) && !empty($mapItem['reason'])) {
            $orderItemsWithReasonCodes[] = sprintf('sku:%s reason:%s', $item->sku, $mapItem['reason']);
        }
    }
}
