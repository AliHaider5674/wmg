<?php
namespace App\Models\Service\Model\Shipment;

use App\Models\Service\Model\Serialize;

/**
 * Item model
 *
 * Class PackageModel
 * @category WMG
 * @package  App\Models\Service\Model\Shipment
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class Item extends Serialize
{
    public $orderLineId;
    public $orderLineNumber;
    public $itemType;
    public $sku;
    public $name;
    public $customDetails;
    public $imageUrl;
    public $productUrl;
    public $orderLinePrice;
    public $orderLinePromotionsInfo;
}
