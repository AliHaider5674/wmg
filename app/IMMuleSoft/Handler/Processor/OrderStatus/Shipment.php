<?php

namespace App\IMMuleSoft\Handler\Processor\OrderStatus;

use App\Exceptions\RecordExistException;
use App\IMMuleSoft\Constants\ConfigConstant;
use App\IMMuleSoft\Models\Weight\ItemWeightCalculator;
use App\Models\OrderItem;
use App\Models\Service\ModelBuilder\Shipment\ItemParameter as ShipmentItemParameter;
use App\Models\Service\ModelBuilder\Shipment\Package\Detail;
use App\Models\Service\ModelBuilder\Shipment\PackageParameter;
use App\Models\Service\ModelBuilder\ShipmentLineChange\ItemParameter as LineChangeItemParameter;
use App\Models\Service\ModelBuilder\ShipmentLineChangeParameter;
use App\Models\Service\ModelBuilder\ShipmentParameter;
use App\Services\ShipmentProcessor;
use Exception;

/**
 * Class Shipment
 * @package App\IMMuleSoft\Handler\OrderStatus
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2022
 * @link     http://www.wmg.com
 */
class Shipment
{
    private ShipmentProcessor $shipmentProcessor;
    private ItemWeightCalculator $shipmentWeightCalculator;

    /**
     * @param ShipmentProcessor $shipmentProcessor
     * @param ItemWeightCalculator $shipmentWeightCalculator
     */
    public function __construct(
        ShipmentProcessor $shipmentProcessor,
        ItemWeightCalculator $shipmentWeightCalculator
    ) {
        $this->shipmentProcessor = $shipmentProcessor;
        $this->shipmentWeightCalculator = $shipmentWeightCalculator;
    }

    /**
     * processParameters
     * @param array $parameters
     * @return bool
     */
    public function processParameters(array $parameters) : bool
    {
        $numberOfParameters = count($parameters);
        $numberOfExceptions = 0;

        if ($numberOfParameters == 0) {
            return true;
        }

        foreach ($parameters as $parameter) {
            try {
                if ($parameter instanceof ShipmentLineChangeParameter) {
                    $this->shipmentProcessor->processAckParameter($parameter);
                    continue;
                }
                $this->shipmentProcessor->processShipmentParameter($parameter);
            } catch (Exception $e) {
                $numberOfExceptions++;
            }
        }

        if ($numberOfExceptions == $numberOfParameters) {
            return false;
        }

        return true;
    }



    /**
     * @throws RecordExistException
     * @throws Exception
     */
    public function getParameters($orderStatus): array
    {
        $parameters = array();
        $orderId = (int) $orderStatus->orderConsumerCode;

        $shipmentParameters = $this->handleShipment($orderStatus, $orderId);
        if (!empty($shipmentParameters)) {
            $parameters = array_merge($parameters, $shipmentParameters);
        }

        $backOrderParameter = $this->handleBackorder($orderStatus, $orderId);
        if ($backOrderParameter instanceof ShipmentLineChangeParameter) {
            $parameters[] = $backOrderParameter;
        }

        return $parameters;
    }

    /**
     * handleShipment
     * @param $orderStatus
     * @param $orderId
     * @return array
     * @throws RecordExistException
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function handleShipment($orderStatus, $orderId): array
    {
        $shipmentParameters = array();
        //handle shipments
        if (isset($orderStatus->shipments)) {
            foreach ($orderStatus->shipments as $shipment) {
                $shipmentParameter = new ShipmentParameter();
                $shipmentParameter->orderId = $orderId;

                $newShipmentOrderItemIds = $this->getNewShipments($shipment);

                if (empty($newShipmentOrderItemIds)) {
                    continue;
                }

                $shipmentProductItems = array();

                foreach ($shipment->shipmentLines as $item) {
                    if (in_array($item->orderLineNumber, $newShipmentOrderItemIds)) {
                        $shipmentProductItems[] =  ['sku' => $item->sku, 'qty' => $item->quantity];
                    }
                }

                //shipment weight calculator
                $shipmentWeight = $this->shipmentWeightCalculator
                    ->calculate(
                        $orderId,
                        $shipmentProductItems
                    );

                $parcelId = 0;
                foreach ($shipment->parcels as $parcel) {
                    $package = new PackageParameter();
                    $package->packageId = ++$parcelId;
                    $package->carrier = $shipment->carrier->name;
                    $package->trackingNumber = $parcel->trackingCode;

                    //Since the warehouse does not expose what items are in which parcel
                    //add the total calculated shipment weight to the first parcel.
                    if ($parcelId === 1) {
                        $detail = new Detail();
                        $detail->weight = $shipmentWeight->getTotalWeight();
                        $detail->weightUnit = $shipmentWeight->getWeightUnit();
                        $package->details = $detail;
                    }

                    $shipmentParameter->addPackage($package);
                }

                foreach ($shipment->shipmentLines as $shipmentLine) {
                    if (in_array($shipmentLine->orderLineNumber, $newShipmentOrderItemIds)) {
                        $packageItem = new ShipmentItemParameter();
                        $packageItem->orderItemId = $shipmentLine->orderLineNumber;
                        $packageItem->quantity = $shipmentLine->quantity;
                        $packageItem->sku = $shipmentLine->sku;
                        $shipmentParameter->addItemToPackage($packageItem, 1);
                    }
                }

                $shipmentParameters[] = $shipmentParameter;
            }

            return $shipmentParameters;
        }
        return $shipmentParameters;
    }

    /**
     * getNewShipments
     * @param $shipment
     * @return array
     */
    protected function getNewShipments($shipment): array
    {
        //check for new shipments
        $orderItemIds = array();
        $shippedItems = array();
        $newShipmentOrderItemIds = array();

        foreach ($shipment->shipmentLines as $item) {
            $orderItemIds[] = $item->orderLineNumber;
            $shippedItems[$item->orderLineNumber] = $item->quantity;
        }

        $orderItems = OrderItem::query()->whereIn('id', $orderItemIds)->get();

        foreach ($orderItems as $orderItem) {
            if ($orderItem->quantity_shipped < $shippedItems[$orderItem->id]) {
                $newShipmentOrderItemIds[] = $orderItem->id;
            }
        }

        return $newShipmentOrderItemIds;
    }


    /**
     * handleBackorder
     * @param $orderStatus
     * @param $orderId
     * @return ShipmentLineChangeParameter|null
     */
    private function handleBackorder($orderStatus, $orderId): ?ShipmentLineChangeParameter
    {
        //handle backorders
        if (!isset($orderStatus->salesOrderLines)) {
            return null;
        }

        $orderItems = $this->getOrderItems($orderStatus);

        if (empty($orderItems)) {
            return null;
        }

        foreach ($orderStatus->salesOrderLines as $salesOrderLine) {
            if (isset($salesOrderLine->quantityBackorder) && $salesOrderLine->quantityBackorder > 0) {
                //Prevent duplicate updates
                if (round((int) $orderItems[$salesOrderLine->lineNumber]) == $salesOrderLine->quantityBackorder) {
                    continue;
                }

                $shipmentLineChangeParameter = new ShipmentLineChangeParameter();
                $shipmentLineChangeParameter->orderId = $orderId;

                $item = new LineChangeItemParameter();
                $item->orderItemId = $salesOrderLine->lineNumber;
                $item->sku = $salesOrderLine->sku;
                $item->quantity = $salesOrderLine->quantityOrdered;
                $item->backorderQuantity = $salesOrderLine->quantityBackorder;
                $item->backOrderReasonCode = ConfigConstant::BACKORDER_REASON_CODE_NO_STOCK;
                $shipmentLineChangeParameter->addItem($item);
                return $shipmentLineChangeParameter;
            }
        }


        return null;
    }

    /**
     * getOrderItems
     * @param $orderStatus
     * @return mixed
     */
    protected function getOrderItems($orderStatus)
    {
        $orderItemIds = array();
        $orderItems = array();

        foreach ($orderStatus->salesOrderLines as $salesOrderLine) {
            $orderItemIds[] = $salesOrderLine->lineNumber;
        }

        if (!empty($orderItemIds)) {
            $orderItems = OrderItem::whereIn('id', $orderItemIds)
                ->pluck('quantity_backordered', 'id');
        }

        return $orderItems->all();
    }
}
