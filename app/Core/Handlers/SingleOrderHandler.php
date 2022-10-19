<?php

namespace App\Core\Handlers;

use App\Core\Handlers\IO\IOInterface;
use App\Core\Repositories\OrderLogRepository;
use App\Core\Services\Converters\OrderRawConverterService;
use App\Core\Services\OrderRawMapperService;
use App\Models\Order;
use App\Exceptions\NoRecordException;
use App\Core\Services\EventService;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use App\Core\Enums\OrderStatus;
use Throwable;
use Illuminate\Support\Facades\DB;

/**
 * Class ApiOrderHandler
 * Handler order drops to warehouse
 * @category WMG
 * @package  App\Models\Warehouse\Handler
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License
 * @link     http://www.wmg.com
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SingleOrderHandler extends AbstractOrderHandler
{
    /** @var EventService  */
    protected EventService $eventManager;
    protected OrderLogRepository $orderLogRepository;
    /**
     * Method to use as callback for send method, if it is set using.
     *
     * @var callable
     */
    protected $sendCallback;

    /**
     * @param \App\Core\Handlers\IO\IOInterface                      $ioAdapter
     * @param \App\Core\Services\OrderRawMapperService               $orderDataMapperService
     * @param \App\Core\Services\Converters\OrderRawConverterService $orderRawConverterService
     * @param \App\Core\Repositories\OrderLogRepository              $orderLog
     * @param \Illuminate\Support\Facades\Log                        $logger
     * @param array                                                  $config
     */
    public function __construct(
        IOInterface $ioAdapter,
        OrderRawMapperService $orderDataMapperService,
        OrderRawConverterService $orderRawConverterService,
        OrderLogRepository $orderLog,
        Log $logger,
        array $config
    ) {
        parent::__construct(
            $ioAdapter,
            $orderDataMapperService,
            $orderRawConverterService,
            $logger,
            $config
        );
        $this->orderLogRepository = $orderLog;
    }

    /**
     * Handle order drop
     *
     * @return void
     * @throws Exception
     */
    public function handle()
    {
        //get warehouse Id to filter orders by
        $orderBySourceIds = $this->getSourceIds();
        //apply warehouse filter
        $this->setSourceIdFilter($orderBySourceIds);

        //get orders that are ready to drop to the warehouse
        $orders = $this->getOrders();

        //return if no orders to drop
        if (!$orders->count()) {
            throw new NoRecordException('No orders are ready to be dropped.');
        }

        //Start order drop process
        $this->ioAdapter->start();

        //Drop orders to warehouse
        $this->dropOrders($orders);

        $this->ioAdapter->finish();
    }

    /**
     * Drop orders to the warehouse
     * @param $orders
     */
    protected function dropOrders($orders): void
    {
        foreach ($orders as $order) {
            $this->dropOrder($order);
        }
    }

    /**
     * @param Order $order
     */
    protected function dropOrder(Order $order): void
    {
        $orderItems = $order->orderItems()
            ->sourceIdIn($this->sourceIdFilter)
            ->shippable()
            ->get();
        $status = OrderStatus::DROPPED;
        try {
            DB::transaction(function () use (&$order, &$orderItems, &$status) {
                $status = $this->processOrderSuccessful($order, $orderItems);
                $this->sendOrder($order, $orderItems);
            });
        } catch (Throwable $e) {
            $status = $this->processOrderWithError($order, $orderItems, $e);
        }
        $this->finishOrderDrop($order, $orderItems, $status);
    }

    /**
     * Will be called after each order drop
     * @param $order Order
     * @param $orderItems Collection
     * @param $status int
     */
    protected function finishOrderDrop(Order $order, Collection $orderItems, int $status)
    {
        $itemIds = implode(',', $orderItems->pluck('id')->toArray());
        $message = sprintf(
            'Items %s of order %s were drop with status %s',
            $itemIds,
            $order->id,
            $status
        );
        $this->orderLogRepository->addLog($order->id, $message, $status);
    }


    /**
     * @param $order
     * @param $orderItems
     */
    protected function sendOrder($order, $orderItems): void
    {
        $rawOrder = $this->orderRawConverterService->getRawOrder($order, $orderItems);
        $rawOrder = $this->orderRawMapperService->processOrder($rawOrder);

        $this->ioAdapter->send([
            IOInterface::DATA_FIELD_ORDER => $order,
            IOInterface::DATA_FIELD_ORDER_ITEMS => $orderItems,
            IOInterface::DATA_FIELD_RAW_ORDER => $rawOrder
        ], $this->sendCallback);
    }

    /**
     * Record processed orders in order to do a rollback
     *
     * @param Order       $order
     * @param Collection  $orderItems
     * @param int         $orderStatus
     * @param string|null $message
     *
     * @return $this
     */
    protected function processOrder(
        Order $order,
        Collection $orderItems,
        int $orderStatus,
        ?string $message
    ) {
        $orderItems->each(function ($item) use ($orderStatus) {
            $item->drop_status = $orderStatus;
            $item->save();
            $this->recordProcessed($item);
        });

        if ($orderStatus === OrderStatus::DROPPED) {
            $message = 'Drop order';
        }

        $this->updateOrderStatusIfAllItemsMatch($order, $orderStatus);
        $this->saveOrderLog($order, $orderStatus, $message);

        return $this;
    }

    /**
     * Process order successful
     * @param Order       $order
     * @param Collection  $orderItems
     * @return int        new status
     */
    protected function processOrderSuccessful(
        Order $order,
        Collection $orderItems
    ):int {
        $this->processOrder(
            $order,
            $orderItems,
            OrderStatus::DROPPED,
            null
        );
        return OrderStatus::DROPPED;
    }

    /**
     * Process order with error
     * @param Order       $order
     * @param Collection  $orderItems
     * @param Throwable   $e
     */
    protected function processOrderWithError(
        Order $order,
        Collection $orderItems,
        Throwable $exception
    ):int {
        if ($exception->getCode()>= 400 && $exception->getCode()< 500) {
            //Soft error
            $this->processOrder(
                $order,
                $orderItems,
                OrderStatus::SOFT_ERROR,
                $exception->getMessage()
            );
            return OrderStatus::SOFT_ERROR;
        }
        $this->processOrder(
            $order,
            $orderItems,
            OrderStatus::ERROR,
            $exception->getMessage()
        );
        return OrderStatus::ERROR;
    }

    /**
     * Rollback changes
     *
     * @param $object
     * @param $args
     * @return void
     * @throws Exception
     * @SuppressWarnings(unused)
     */
    protected function rollbackItem($object, ...$args): void
    {
    }

    /**
     * Validate
     *
     * @return bool
     */
    public function validate()
    {
        return true;
    }
}
