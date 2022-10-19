<?php
namespace App\Models\Service\ModelBuilder;

use App\Core\Constants\BackorderStatusReasonCodes;
use App\Exceptions\NoRecordException;
use App\Models\OrderItem;
use App\Models\Service\Model\ShipmentLineChange;
use App\Models\Service\Model\ShipmentLineChange\Item;
use App\Models\Service\ModelBuilder\ShipmentLineChange\ItemParameter;
use Illuminate\Database\RecordsNotFoundException;

/**
 * Build a model that send to external requests.
 *
 * Class ShipmentLineChangeBuilder
 * @category WMG
 * @package  App\Models\Service\ModelBuilder
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class ShipmentLineChangeBuilder
{
    /**
     * Build by sku and order id
     * @param \App\Models\Service\ModelBuilder\ShipmentLineChangeParameter $parameter
     * @return ShipmentLineChange[]
     * @throws \App\Exceptions\NoRecordException
     */
    public function build(ShipmentLineChangeParameter $parameter)
    {
        $models = [];
        $orderItems = OrderItem::whereIn('id', $parameter->getOrderItemIds())->get();
        foreach ($orderItems as $orderItem) {
            /**@var OrderItem $item */
            $order = $orderItem->order;
            if (empty($order)) {
                throw new RecordsNotFoundException('Order not found for ' . $orderItem->getAttribute('parent_id'));
            }
            $requestId = $orderItem->order->getAttribute('request_id');
            $itemParameter = $parameter->getItemParameter($orderItem->id);
            if (!isset($models[$requestId])) {
                $models[$requestId] = new ShipmentLineChange();
                $models[$requestId]->shipmentRequestId = $requestId;
                $models[$requestId]->setHiddenOrderId($orderItem->order->id);
            }

            /** @var ShipmentLineChange $currentModel */
            $currentModel = $models[$requestId];
            $newItemModel = $currentModel->newItem();
            $newItemModel->fill($orderItem->getAttributes(), false);
            $newItemModel->status = $this->getStatus($itemParameter);
            $newItemModel->quantity = $itemParameter->quantity;
            $newItemModel->backorderQuantity = $itemParameter->backorderQuantity;
            $newItemModel->statusReason = $itemParameter->backOrderReasonCode;
            $newItemModel->setHiddenData('item', $orderItem);
        }

        if (count($models) == 0) {
            throw new NoRecordException(
                'No model build for order ID ' . $parameter->orderId
            );
        }
        return $models;
    }

    /**
     * Get item status
     * @param \App\Models\Service\ModelBuilder\ShipmentLineChange\ItemParameter $item
     * @return string
     */
    private function getStatus(ItemParameter $item)
    {
        switch ($item->backOrderReasonCode) {
            case '3':
            case '4':
            case '5':
            case 'M':
                return Item::STATUS_PICK_DECLINED;
            case BackorderStatusReasonCodes::RETURNED:
                return Item::STATUS_RETURNED;
            case BackorderStatusReasonCodes::ON_HOLD:
                return Item::STATUS_ON_HOLD;
            case BackorderStatusReasonCodes::ERROR:
                return Item::STATUS_ERROR;
            case BackorderStatusReasonCodes::NOT_IN_STOCK:
                return ITEM::NOT_IN_STOCK;
            default:
                return Item::STATUS_RECEIVED_BY_LOGISTICS;
        }
    }
}
