<?php
namespace App\MES\Handler\IO\Shipment;

use App\MES\Handler\Helper\BackOrderHelper;
use App\Models\Service\ModelBuilder\Shipment\ItemParameter as ShipmentItemParameter;
use App\Models\Service\ModelBuilder\Shipment\PackageParameter;
use App\Models\Service\ModelBuilder\ShipmentParameter;
use WMGCore\Services\ConfigService;
use Carbon\Carbon;
use App\Models\Service\ModelBuilder\ShipmentLineChangeParameter;
use App\Models\Service\ModelBuilder\ShipmentLineChange\ItemParameter as LineChangeItemParameter;
use App\MES\Handler\IO\FlatOrder;
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
    const CONFIG_MES_CARRIER_MAP = 'mes.carrier.map';
    const DEFAULT_CARRIER = 'custom';
    /** @var ShipmentParameter[]*/
    protected $shipments;
    /** @var ShipmentLineChangeParameter[] */
    protected $backorders;
    /** @var Carbon */
    protected $currentTime;

    private $orderNumberMap;

    private $backorderHelper;
    private $configService;

    private $carrierExpMap;
    private $carrierMap;
    public function __construct(BackOrderHelper $backOrderHelper, ConfigService $configService)
    {
        $this->backorderHelper = $backOrderHelper;
        $this->configService = $configService;
        $this->reset();
    }

    public function reset()
    {
        $this->shipments = [];
        $this->backorders = [];
        //EMS order number back to store order number
        $this->orderNumberMap = [];
        $this->currentTime = new Carbon(null, 'UTC');
    }

    public function addOrder($orderData)
    {
        //logistic_order_number;
        $orderNumber = $orderData['order_number'];
        $this->orderNumberMap[$orderNumber] = $orderData[FlatOrder::ORDER_ID_FIELD];
    }

    /**
     * Add Item
     * @param $itemData
     * @return $this
     * @throws \App\Exceptions\RecordExistException | \Exception
     */
    public function addItem($itemData)
    {
        //MES ORDER NUMBER
        $orderNumber = $itemData['order_number'];
        $quantityShipped = floatval($itemData['expected_delivery_quantity']);
        $isBackorder = $this->backorderHelper->isBackOrder($itemData['backorder_reason_code']);
        //Create shipment parameter
        if ($quantityShipped > 0) {
            if (!isset($this->shipments[$orderNumber])) {
                $shipment = new ShipmentParameter();
                $this->shipments[$orderNumber] = $shipment;
                $shipment->orderId = $this->orderNumberMap[$orderNumber];
            }

            $currentOrder = $this->shipments[$orderNumber];
            $packageId = $itemData['nve'];
            if (!$currentOrder->hasPackage($packageId)) {
                $package = new PackageParameter();
                $package->packageId = $packageId;
                $package->carrier = $this->getCarrier($itemData['carrier_name']);
                $package->trackingNumber = $itemData['nve'];
                $package->shippingLabelLink = $itemData['nve'];
                $package->trackingComment = $itemData['carrier_name'];
                $currentOrder->addPackage($package);
            }

            $packageItem = new ShipmentItemParameter();
            $packageItem->orderItemId = $itemData[FlatOrder::ORDER_ITEM_ID_FIELD];
            $packageItem->backOrderReasonCode = $itemData['backorder_reason_code'];
            $packageItem->backorderQuantity = $itemData['backorder_quantity'];
            $packageItem->quantity = $quantityShipped;
            $packageItem->sku = $itemData['catalogue_item_barcode'];
            $currentOrder->addItemToPackage($packageItem, $packageId);
        }

        //Create Backorder Parameter
        if ($isBackorder || $quantityShipped == 0) {
            if (!isset($this->backorders[$orderNumber])) {
                $this->backorders[$orderNumber] = new ShipmentLineChangeParameter();
                $this->backorders[$orderNumber]->orderId = $orderNumber;
            }
            $item = new LineChangeItemParameter();
            $item->orderItemId = $itemData[FlatOrder::ORDER_ITEM_ID_FIELD];
            $item->sku = $itemData['catalogue_item_barcode'];
            $item->quantity = max($itemData['order_quantity'] - $quantityShipped, 0);
            $item->backorderQuantity = intval($itemData['order_quantity'])
                - (intval($itemData['expected_delivery_quantity']));
            $item->backOrderReasonCode = $itemData['backorder_reason_code'];
            $this->backorders[$orderNumber]->addItem($item);
        }

        return $this;
    }

    public function count()
    {
        return count($this->shipments) + count($this->backorders);
    }

    /**
     * Get Iterator
     *
     * @return \ArrayIterator|\Traversable
     */
    public function getIterator()
    {
        return new ArrayIterator(
            array_merge(
                array_values($this->shipments),
                array_values($this->backorders)
            )
        );
    }

    /**
     * Get carrier based on carrier name
     *
     * @param $carrierName
     * @return mixed|string
     */
    private function getCarrier($carrierName)
    {

        if (!isset($this->carrierExpMap)) {
            $this->carrierMap = [];
            $this->carrierExpMap = $this->configService->getJson(self::CONFIG_MES_CARRIER_MAP);
            if (!is_array($this->carrierExpMap)) {
                $this->carrierExpMap = [];
            }

            $this->carrierExpMap[] = [
                'carrier' => self::DEFAULT_CARRIER,
                'exp' => '.*'
            ];
        }

        if (!isset($this->carrierMap[$carrierName])) {
            foreach ($this->carrierExpMap as $map) {
                $exp = $map['exp'];
                $carrier = $map['carrier'];
                if (preg_match("/$exp/i", $carrierName)) {
                    $this->carrierMap[$carrierName] = $carrier;
                    break;
                }
            }
        }
        return isset($this->carrierMap[$carrierName])
            ? $this->carrierMap[$carrierName]
            : self::DEFAULT_CARRIER;
    }
}
