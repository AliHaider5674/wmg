<?php declare(strict_types=1);

namespace App\Core\Handlers\Traits;

use App\Core\Services\EventService;
use App\Models\OrderItem;
use App\Models\Service\Model\ShipmentLineChange\Item as LineChangeItem;
use App\Models\Service\ModelBuilder\ShipmentLineChangeParameter;
use Exception;

/**
 * This requires class extends AbstractHandler
 */
trait AckHandler
{
    /**
     * Process parameters
     * @param ShipmentLineChangeParameter $parameter
     * @return void
     * @throws Exception
     */
    public function processAckParameter(ShipmentLineChangeParameter $parameter) : void
    {
        try {
            $this->processParameter($parameter);
        } catch (Exception $e) {
            $this->logger::info($e->getMessage());
        }
    }

    /**
     * Process parameters
     * @param ShipmentLineChangeParameter $parameter
     * @return void
     * @throws Exception
     */
    protected function processParameter(ShipmentLineChangeParameter $parameter) : void
    {
        try {
            $models = $this->shipmentLineChangeBuilder->build($parameter);
        } catch (Exception $e) {
            $failedParameter = $this->recordFailedParameter($parameter, $e->getMessage());
            $this->recordProcessed($failedParameter);
            throw $e;
        }
        foreach ($models as $model) {
            foreach ($model->items as $item) {
                $this->processAckItem($item);
            }

            $this->eventManager->dispatchEvent(
                EventService::EVENT_ITEM_WAREHOUSE_ACK,
                $model
            );
        }
    }

    /**
     * Process item
     *
     * @param LineChangeItem $item
     *
     * @return $this
     * @throws Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function processAckItem(LineChangeItem $item)
    {
        /** @var OrderItem $orderItem */
        $orderItem = $item->getHiddenData('item');
        $this->recordProcessed($orderItem);
        $ackQty = $orderItem->getAttribute('quantity_ack') ? : 0;
        $ackQty += (float) $item->quantity;
        $backorderQty = $orderItem->getAttribute('quantity_backordered') ? : 0;
        $backorderQty += (float) $item->backorderQuantity;
        $orderItem->setAttribute('quantity_ack', $ackQty);
        $orderItem->setAttribute('quantity_backordered', $backorderQty);
        $orderItem->save();
        return $this;
    }
}
