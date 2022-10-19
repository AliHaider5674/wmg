<?php
namespace App\Core\Handlers;

use App\Core\Handlers\IO\IOInterface;
use App\Models\OrderLog;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderDrop as OrderDropModel;
use App\Exceptions\NoRecordException;

/**
 * Batch Order handler
 * process order in batch
 *
 * Class Order
 * @category WMG
 * @package  App\Core
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2020
 * @link     http://www.wmg.com
 */
class BatchOrderHandler extends AbstractOrderHandler
{
    /**
     * Handler type
     */
    public const HANDLER_TYPE = 'order';

    /**
     * Handle order drop
     *
     * @return void
     * @throws \Exception
     */
    public function handle()
    {
        $this->removeAllRecordedProcessed();
        $sourceIds = $this->getSourceIds();

        //apply warehouse filter
        $this->setSourceIdFilter($sourceIds);

        $readyOrderCollection = $this->getOrders();

        if (!$readyOrderCollection->count()) {
            throw new NoRecordException('No orders are ready to be dropped.');
        }

        $orderDrop = new OrderDropModel();
        $orderDrop->setRawAttributes([
            'content' => '',
            'status' => OrderDropModel::STATUS_PROCESSING
        ]);
        $orderDrop->save();
        $this->recordProcessed($orderDrop);

        $this->ioAdapter->start([IOInterface::DATA_FIELD_ORDER_DROP => $orderDrop]);
        foreach ($readyOrderCollection as $order) {
            /**@var Order $order */
            $orderItems = $order->orderItems()
                ->sourceIdIn($this->sourceIdFilter)
                ->shippable()
                ->notDropped()
                ->get();

            $rawOrder = $this->orderRawConverterService->getRawOrder($order, $orderItems);
            $rawOrder = $this->orderRawMapperService->processOrder($rawOrder);

            $this->ioAdapter->send([
                IOInterface::DATA_FIELD_ORDER => $order,
                IOInterface::DATA_FIELD_ORDER_ITEMS => $orderItems,
                IOInterface::DATA_FIELD_RAW_ORDER => $rawOrder]);

            $this->processOrder($order, $orderItems, $orderDrop);
        }

        DB::transaction(function () use ($orderDrop) {
            $orderDrop->setAttribute('status', OrderDropModel::STATUS_DONE);
            $orderDrop->save();
            $this->ioAdapter->finish([IOInterface::DATA_FIELD_ORDER_DROP => $orderDrop]);
        });
    }

    /**
     * Rollback changes
     *
     * @param $object
     * @param $args
     * @return void
     * @throws \Exception
     */
    protected function rollbackItem($object, ...$args): void
    {
        if ($object instanceof OrderDropModel) {
            $object->delete();
        } elseif ($object instanceof Order) {
            $object->setAttribute('status', Order::STATUS_ERROR);
            $object->save();
        } elseif ($object instanceof OrderLog) {
            $newLog = new OrderLog();
            $newLog->setRawAttributes([
                'message' => $args[0],
                'status' => Order::STATUS_ERROR,
                'parent_id' => $object->getAttribute('parent_id')
            ]);
            $newLog->save();
            $object->delete();
        }
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

    /**
     * Record processed orders in order to do a rollback
     * if there is error
     * @param \App\Models\Order     $order
     * @param Collection            $orderItems
     * @param \App\Models\OrderDrop $orderDrop
     * @return $this
     */
    protected function processOrder(
        Order $order,
        Collection $orderItems,
        OrderDropModel $orderDrop
    ): self {
        $orderItems->each(function ($item) use ($orderDrop) {
            $item->drop_status = Order::STATUS_DROPPED;
            $item->drop_id = $orderDrop->id;
            $item->save();
            $this->recordProcessed($item);
        });

        $this->updateOrderStatusIfAllItemsMatch($order, Order::STATUS_DROPPED);
        $this->saveOrderLog($order, Order::STATUS_DROPPED, 'Drop order.');

        return $this;
    }
}
