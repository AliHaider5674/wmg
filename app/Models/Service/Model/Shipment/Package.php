<?php
namespace App\Models\Service\Model\Shipment;

use App\Models\Service\Model\Serialize;
use App\Models\Service\Model\Shipment\Package\AggregatedItem;
use App\Models\Service\Model\Shipment\Package\Detail;

/**
 * Package model
 *
 * Class PackageModel
 * @category WMG
 * @package  App\Models\Service\Model\Shipment
 * @author   Darren Chen <darren.chen@wmg.com>
 * @license  WMG License 2019
 * @link     http://www.wmg.com
 */
class Package extends Serialize
{
    /** @var string */
    public $id;
    /** @var [\App\Models\Service\Model\Shipment\Package\DetailModel] */
    public $details;
    /** @var [int] */
    public array $items = []; //References to order_line_numbers which were packed in the package
    /** @var [\App\Models\Service\Model\Shipment\Package\AggregatedItem] */
    public array $aggregatedItems = [];
    /** @var string */
    public ?string $carrier = null;
    /** @var string */
    public ?string $trackingNumber = null;
    /** @var string */
    public ?string $trackingLink = null;
    /** @var string */
    public ?string $trackingComment = null;
    /** @var string */
    public ?string $shippingLabelLink;
    /** @var [] */
    public ?array $customAttributes;


    public function __construct()
    {
        $this->details = new Detail();
    }

    public function newAggregatedItem($aggregatedId)
    {
        if (isset($this->aggregatedItems[$aggregatedId])) {
            return $this->aggregatedItems[$aggregatedId];
        }
        $item = new AggregatedItem();
        $item->aggregatedLineId = $aggregatedId;
        $this->aggregatedItems[$aggregatedId] = $item;
        return $item;
    }

    /**
     * Override parent for fixing aggregated items data
     * structure
     *
     * @param bool $isCamelCase
     * @param null $data
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     * @return array
     */
    public function toArray($isCamelCase = true, $data = null)
    {
        //Transform back to array
        if ($data === null) {
            $items = $this->aggregatedItems;
            $this->aggregatedItems = [];
            foreach ($items as $item) {
                $this->aggregatedItems[] = $item;
            }
        }
        return parent::toArray($isCamelCase, $data);
    }
}
