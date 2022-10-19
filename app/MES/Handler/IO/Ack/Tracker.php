<?php
namespace App\MES\Handler\IO\Ack;

use App\Models\Service\ModelBuilder\ShipmentLineChange\ItemParameter;
use App\Models\Service\ModelBuilder\ShipmentLineChangeParameter;
use ArrayIterator;

/**
 * Shipment tracker for grouping the order and items together
 *
 * Class Tracker
 * @category WMG
 * @package  App\MES\Handler\IO\Shipment
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class Tracker implements \IteratorAggregate
{
    const ORDER_ID_FIELD = 'additional_customer_reference';
    const ORDER_NUMBER_FIELD = 'customer_order_reference';
    const LINE_ORDER_ID_FIELD = 'delivery_special_instruction_2';

    /** @var ShipmentLineChangeParameter[] */
    private $parameters;

    public function reset()
    {
        $this->parameters = [];
    }

    public function addOrder($orderData)
    {
        $parameter = new ShipmentLineChangeParameter();
        $this->parameters[$orderData[self::ORDER_NUMBER_FIELD]] = $parameter;
        $parameter->orderId = $orderData[self::ORDER_ID_FIELD];
        return $this;
    }

    public function addItem($itemData)
    {
        $orderNumber = $itemData[self::ORDER_NUMBER_FIELD];
        if (!isset($this->parameters[$orderNumber])) {
            $this->addOrder([
                self::ORDER_NUMBER_FIELD => $orderNumber,
                self::ORDER_ID_FIELD => $itemData[self::LINE_ORDER_ID_FIELD]
            ]);
        }
        $itemParameter = new ItemParameter();
        $itemParameter->orderItemId = $itemData['reference_line_number'];
        $itemParameter->sku = $itemData['item_number'];
        $itemParameter->quantity = $itemData['order_quantity'];
        //Always treat ack file as ack because DESADV file contains backorder anyway
        $itemParameter->backorderQuantity = 0;
        $itemParameter->backOrderReasonCode = null;
        $this->parameters[$orderNumber]->addItem($itemParameter);
        return $this;
    }

    public function getIterator()
    {
        return new ArrayIterator($this->parameters);
    }
}
