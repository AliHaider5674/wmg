<?php
namespace App\Models\Service\ModelBuilder\Shipment;

/**
 * Item Parameter
 *
 * Class ItemParameter
 * @category WMG
 * @package  App\Models\Service\ModelBuilder\ShipmentLineChange
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class ItemParameter
{
    //Internal order item id
    public $orderItemId;
    //Product Sku
    public $sku;
    //Qty processed
    public $quantity = 0;
    //Qty backordered
    public $backorderQuantity = 0;

    /**
     * Returned Quantity;
     *
     * @var int
     */
    public $returnedQuantity = 0;

    //Back reason code
    public $backOrderReasonCode;
}
