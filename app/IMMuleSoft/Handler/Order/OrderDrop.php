<?php

namespace App\IMMuleSoft\Handler\Order;

use App\Core\Enums\OrderItemStatus;
use App\Core\Enums\OrderStatus;
use App\Core\Handlers\IO\IOInterface;
use App\Core\Services\EventService;
use App\Exceptions\NoRecordException;
use App\Exceptions\OrderDropException;
use App\IMMuleSoft\Constants\EventConstant;
use App\IMMuleSoft\Constants\ResourceConstant;
use App\Models\AlertEvent;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\AlertEventService;
use Exception;
use WMGCore\Services\ConfigService;
use App\Core\Services\Converters\OrderRawConverterService;
use App\Core\Services\OrderRawMapperService;
use App\IMMuleSoft\Models\Service\Model\Order as ServiceOrder;

/**
 * Class OrderDrop
 * @package App\IMMuleSoft\Handler\Order
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2022
 * @link     http://www.wmg.com
 */
class OrderDrop
{
    use \App\IMMuleSoft\Models\Traits\Order;

    const ORDER_REQUEST_MAX_SIZE_DEFAULT = 100;
    const ORDER_REQUEST_MAX_SIZE = 'immulesoft.order.request.max.size';
    const ALERT_NAME = 'CevaOrderExport';
    private OrderTracker $orderTracker;
    private OrderMap $orderMap;
    private ConfigService $configService;
    private OrderRawConverterService $orderRawConverterService;
    private OrderRawMapperService $orderRawMapperService;
    private ShippingServiceMapper $shippingServiceMapper;
    private EventService $eventService;
    private AlertEventService $alertEventService;

    public function __construct(
        OrderTracker  $orderTracker,
        OrderMap      $orderMap,
        ConfigService $configService,
        OrderRawConverterService $orderRawConverterService,
        OrderRawMapperService $orderRawMapperService,
        ShippingServiceMapper $shippingServiceMapper,
        EventService $eventService,
        AlertEventService $alertEventService
    ) {
        $this->orderTracker = $orderTracker;
        $this->orderMap = $orderMap;
        $this->configService = $configService;
        $this->orderRawConverterService = $orderRawConverterService;
        $this->orderRawMapperService = $orderRawMapperService;
        $this->shippingServiceMapper = $shippingServiceMapper;
        $this->eventService = $eventService;
        $this->alertEventService = $alertEventService;
    }

    /**
     * Drop orders to the warehouse
     * @param $orders
     * @param array $sourceIdFilter
     * @throws Exception
     */
    public function handle($orders, array $sourceIdFilter): void
    {
        //ensure we only the correct number of orders that mulesoft can accept in one request
        $orderRequestMaxSize = $this->configService
            ->getJson(self::ORDER_REQUEST_MAX_SIZE, self::ORDER_REQUEST_MAX_SIZE_DEFAULT);

        //split orders into appropriate size
        $chunkedOrders = $orders->chunk($orderRequestMaxSize);

        //iterate through chunk
        foreach ($chunkedOrders as $chunkedOrder) {
            //reset order tracker
            $this->orderTracker->reset();

            /**
             * @var Order $orderModel
             */
            foreach ($chunkedOrder as $orderModel) {
                // Todo possible optimisation - iterate through chunk order and get all order id
                // Send only one query to get all shippable order items. Instead of querying order items for each order

                try {
                    //get shippable order items for order
                    $orderItems = $orderModel->orderItems()
                        ->sourceIdIn($sourceIdFilter)
                        ->shippable()
                        ->get();

                    $exportOrder = $this->build($orderModel, $orderItems);
                    $this->orderTracker->add($exportOrder, $orderModel);
                } catch (NoRecordException $e) {
                    $this->alertEventService->addEvent(
                        self::ALERT_NAME,
                        $e->getMessage(),
                        AlertEvent::TYPE_NO_RECORDS,
                        AlertEvent::LEVEL_CRITICAL
                    );
                } catch (OrderDropException $dropException) {
                    $this->alertEventService->addEvent(
                        self::ALERT_NAME,
                        sprintf(
                            "order.id:%d - %s",
                            $orderModel->id,
                            $dropException->getMessage()
                        ),
                        AlertEvent::TYPE_ORDER_DROP_ERROR,
                        AlertEvent::LEVEL_CRITICAL
                    );
                }
            }

            //set order status to process
            $this->updateOrderStatus(
                $this->orderTracker->getOrderIds(),
                OrderStatus::PROCESSING,
                OrderItemStatus::PROCESSING
            );

            if ($this->orderTracker->getCount()) {
                $orderIds = $this->orderTracker->getOrderIds();
                if (!empty($this->orderTracker->getOrders())) {
                    $serviceOrder  = new ServiceOrder();
                    $serviceOrder->orders = $this->orderTracker->getOrders();
                    $serviceOrder->setHiddenOrderIds($orderIds);

                    $this->eventService->dispatchEvent(
                        EventConstant::EVENT_IMMULESOFT_ORDER_EXPORT,
                        $serviceOrder
                    );
                }
            }
        }
    }

    /**
     * build
     * @param Order $order
     * @param $orderItems
     * @return array
     * @throws Exception
     */
    protected function build(Order $order, $orderItems): array
    {
        $this->shippingServiceMapper->mapShippingMethodToService($order, $orderItems);

        $rawOrder = $this->orderRawConverterService->getRawOrder($order, $orderItems);
        $rawOrder = $this->orderRawMapperService->processOrder($rawOrder);

        return $this->orderMap->handle($rawOrder, $orderItems);
    }
}
