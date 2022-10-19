<?php

namespace App\IMMuleSoft\Faker;

use App\Models\Order;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Model;
use \App\IMMuleSoft\Models\ImMulesoftShippingCarrierService;

/**
 * Class ShipmentMap
 * @package App\IMMuleSoft\Faker
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2022
 * @link     http://www.wmg.com
 */
class ShipmentMap
{
    public const ORDER_STATUS_COMPLETE = 'Complete';
    public const ORDER_STATUS_PROCESSING = 'Processing';
    const ADDRESS_TYPE_SHIPMENT = 'shipment';
    const ADDRESS_TYPE_BILLING = 'invoice';
    private array $data;
    private Order $order;
    /**
     * @var void
     */
    private $options;

    /**
     * resetData
     */
    protected function resetData()
    {
        unset($this->data);
        $this->data = array();
    }

    /**
     * handle
     * @param Order $order
     * @param array $options
     * @return array
     * @throws Exception
     */
    public function handle(Order $order, array $options): array
    {
        $this->order = $order;
        $this->options = $options;

        $this->resetData();
        $this->setOrderInfo();
        $this->setAddresses();
        $this->setOrderAttributes();
        $this->setShipments();
        $this->setSalesOrderLines();

        $this->updateOrderStatus();

        return $this->data;
    }

    /**
     * @throws Exception
     */
    private function setOrderInfo()
    {
        $this->data['code'] = rand();
        $this->data['orderCode'] = $this->order->order_id;
        $this->data['orderConsumerCode'] = $this->order->id;
        $this->data['orderPortalCode'] = rand();
        $this->data['salesChannelCode'] = 'default';
        $this->data['status'] = (isset($this->options['orderStatus']))
            ? $this->options['orderStatus'] : self::ORDER_STATUS_COMPLETE;
    }

    private function setAddresses()
    {
        $shipmentAddress = $this->getShipmentAddress();

        if (!empty($shipmentAddress)) {
            $this->data['addresses'][] = $shipmentAddress;
        }

        $billingAddress =  $this->getBillingAddress();

        if (!empty($billingAddress)) {
            $this->data['addresses'][] = $billingAddress;
        }
    }

    private function setOrderAttributes()
    {
    }

    private function setShipments()
    {
        if (!isset($this->data['status']) || $this->data['status'] !== self::ORDER_STATUS_COMPLETE) {
            return;
        }

        $shipments = $this->getShipments();
        if (!empty($shipments)) {
            $this->data['shipments'] = $shipments;
        }
    }

    private function setSalesOrderLines()
    {
        $this->data['salesOrderLines'] = $this->getOrderLines();
    }

    /**
     * getShipmentAddress
     * @return array|null
     */
    private function getShipmentAddress()
    {
        $model = $this->order->getShippingAddress();

        if ($model !== null) {
            return $this->getAddressInfo($model, self::ADDRESS_TYPE_SHIPMENT);
        }

        return null;
    }

    /**
     * getBillingAddress
     * @return array|null
     */
    private function getBillingAddress() : ?array
    {
        $model = $this->order->getBillingAddress();

        if ($model !== null) {
            return $this->getAddressInfo($model, self::ADDRESS_TYPE_BILLING);
        }

        return null;
    }

    /**
     * getAddressInfo
     * @param Model $model
     * @param string $addressType
     * @return array
     */
    private function getAddressInfo(Model $model, string $addressType): array
    {
        $address = array();
        $address['type'] = $addressType;
        $address['firstName'] = $model->first_name;
        $address['lastName'] = $model->last_name;
        $address['fullName'] = $model->first_name . $model->last_name ;
        $address['addressLine1'] = $model->address1;
        $address['addressLine2'] = $model->address2;
        $address['addressLine3'] = $model->address3;
        $address['postalCode'] = $model->zip;
        $address['city'] = $model->city;
        $address['state'] = $model->state;
        $address['countryCode'] = $model->country_code;
        $address['phoneNumber1'] = $model->phone;
        $address['email'] = $model->email;

        return $address;
    }

    /**
     * getShipments
     * @param int $numberOfShipments
     * @return array
     */
    private function getShipments(int $numberOfShipments = 1): array
    {
        $shipments = array();
        for ($i=0; $i < $numberOfShipments; $i++) {
            $shipment = array();
            $shipment['code'] = rand();
            $shipment['shippingDate'] = Carbon::now()->toDateString();
            $orderCarrier = $this->getOrderCarrierInfo();
            $shipment['carrier'] = [
                'code' => $orderCarrier['carrierCode'],
                'name' => $orderCarrier['carrierName'],
                'serviceCode' => $orderCarrier['serviceCode'],
                'serviceName' => $orderCarrier['serviceName']
            ];
            $shipment['wmsReference'] = rand();

            $hasShipments = false;
            foreach ($this->order->orderItems()->get() as $item) {
                if ($this->isItemOnBackOrder($item)) {
                    continue;
                }

                $shipmentLine = array();
                $shipmentLine['orderLineNumber'] = $item->id;
                $shipmentLine['sku'] = $item->sku;
                $shipmentLine['quantity'] =
                    (
                        $item->quantity
                        - $item->quantity_shipped
                        - $item->quantity_backordered
                        - $item->quantity_returned
                    );

                if ($shipmentLine['quantity']) {
                    $shipment['shipmentLines'][] = $shipmentLine;
                    $hasShipments = true;
                }
            }

            if ($hasShipments) {
                $shipment['parcels'][] =
                    [
                        'trackingCode' => rand(),
                        'statusDateTime' => Carbon::parse(now())->toIso8601ZuluString()
                    ];

                $shipments[] = $shipment;
            }
        }

        return $shipments;
    }

    /**
     * getOrderCarrierInfo
     * @return array
     */
    private function getOrderCarrierInfo(): array
    {
        $orderCarrier = array();
        //get order carrier info
        $orderCarrier['carrierCode'] = $this->order->getCustomAttribute("im_carrier_code");
        $orderCarrier['carrierName'] = $this->order->getCustomAttribute("im_carrier_name");
        $orderCarrier['serviceCode'] = $this->order->getCustomAttribute("im_service_code");
        $orderCarrier['serviceName'] = $this->order->getCustomAttribute("im_service_name");


        if (empty($orderCarrier['carrierCode'])) {
            //fake carrier if not present
            $shippingCarrierService = ImMulesoftShippingCarrierService::query()->first();

            $orderCarrier['carrierCode'] = $shippingCarrierService->carrier_code;
            $orderCarrier['carrierName'] = $shippingCarrierService->carrier_name;
            $orderCarrier['serviceCode'] = $shippingCarrierService->service_code;
            $orderCarrier['serviceName'] = $shippingCarrierService->service_name;
        }

        return $orderCarrier;
    }

    private function getOrderLines(): array
    {
        $orderLines = array();

        foreach ($this->order->orderItems()->get() as $item) {
            $orderLine = array();
            $orderLine['lineNumber'] = $item->id;
            $orderLine['sku'] = $item->sku;
            $orderLine['description'] = $item->name;
            $orderLine['quantityOrdered'] = (float) $item->quantity;
            $orderLine['quantityProcessing'] = 0;
            $orderLine['quantityShipped'] = (float) $item->quantity;
            $orderLine['quantityCancelled'] = 0;
            $orderLine['quantityBackorder'] = 0;

            if ($this->isItemOnBackOrder($item)) {
                $orderLine['quantityShipped'] = 0;
                $orderLine['quantityBackorder'] = (float) $item->quantity;
            }

            $orderLines[] = $orderLine;
        }

        return $orderLines;
    }

    /**
     * isItemOnBackOrder
     * @param $item
     * @return bool|void
     */
    protected function isItemOnBackOrder($item)
    {
        if (isset($this->options['backorder_sku'])
            && in_array($item->sku, $this->options['backorder_sku'])
        ) {
            return true;
        }

        if (isset($this->options['backorder_order_item_ids'])
            && in_array($item->id, $this->options['backorder_order_item_ids'])
        ) {
            return true;
        }
    }

    private function updateOrderStatus()
    {
        if ($this->data['salesOrderLines'] && !empty($this->data['salesOrderLines']) !== null) {
            foreach ($this->data['salesOrderLines'] as $salesOrderLine) {
                if ($salesOrderLine['quantityProcessing'] > 0
                || $salesOrderLine['quantityCancelled'] > 0
                || $salesOrderLine['quantityBackorder'] > 0
                ) {
                    $this->data['status'] = self::ORDER_STATUS_PROCESSING;
                }
            }
        }
    }
}
