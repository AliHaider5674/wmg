<?php
namespace App\Models\Service\Model\ShipmentLineChange;

use App\Models\Service\Model\Shipment\Item as ShipmentItem;

/**
 * Shipment line change line item
 *
 * Class PackageModel
 * @category WMG
 * @package  App\Models\Service\Model\Shipment
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class Item extends ShipmentItem
{
    public const STATUS_RECEIVED_BY_LOGISTICS = 'RECEIVEDBYLOGISTICS';
    public const STATUS_SHIPPED = 'SHIPPED';
    public const STATUS_ITEM_PENDING_PICKING = 'ITEM_PENDING_PICKING';
    public const STATUS_PICK_READY = 'PICKREADY';
    public const STATUS_PICK_CONFIRMED = 'PICKCONFIRMED';
    public const STATUS_PICK_DECLINED = 'PICKDECLINED';
    public const STATUS_ON_HOLD = 'ONHOLD';

    public const STATUS_RESOURCED = 'RESOURCED';
    public const STATUS_CANCELLED = 'CANCELLED';
    public const STATUS_RETURNED = self::STATUS_CANCELLED;
    public const STATUS_ERROR = 'ERROR';
    public const NOT_IN_STOCK = 'NOT_IN_STOCK';


    public $parentOrderLineNumber;
    public $status = 'RECEIVEDBYLOGISTICS';
    public $statusReason = 'RECEIVEDBYLOGISTICS';
    public $statusDate;
    public $quantity;
    public $backorderQuantity;
}
