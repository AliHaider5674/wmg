<?php
namespace App\Core\Models\RawData;

use App\Models\Service\Model\Serialize;

/**
 * Raw order item model for Fulfillment IO to use
 *
 * Class OrderItem
 * @category WMG
 * @package  App\Core\Models\RawData
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2020
 * @link     http://www.wmg.com
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class OrderItem extends Serialize
{
    public $id;
    public $orderLineId;
    public $orderLineNumber;
    public $sku;
    public $name;
    public $sourceId;
    public $aggregatedLineId;

    /**
     * Retail price per item
     *
     * @var string
     */
    public $retailPricePerItem;
    public $netAmount;
    public $grossAmount;
    public $taxAmount;
    public $taxRate;
    public $currency;
    public $itemType;
    public $parentOrderLineNumber;
    public $quantity;
    public $quantityShipped;
    public $quantityAck;
    public $quantityBackordered;
    public $customAttributes = [];
    public $createdAt;
    public $updatedAt;
}
