<?php

namespace App\IM\Models;

use Illuminate\Support\Carbon;

/**
 * Class     Order
 * @category WMG
 * @package  App\Models\IM
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License
 * @link     http://www.wmg.com
 */
class Order
{

    protected $data;
    protected $orderReference;

    const FIELD_ORDER_DATE = 'OrderDate';
    const FIELD_ORDER_STATUS = 'OrderStatus';
    const FIELD_BILLING_ADDRESS = 'BillingAddress';
    const FIELD_SHIPPING_ADDRESS = 'DeliveryAddress';
    const FIELD_SHIPMENT_METHOD = 'ShipmentMethod';
    const FIELD_SHIPPING_COST_GROSS = 'ShippingCostGross';
    const FIELD_ORDER_LINES = 'OrderLines';


    /**
     * setOrderReference
     *
     * @param string $orderReference
     */
    public function setOrderReference(string $orderReference)
    {
        $this->orderReference = $orderReference;
    }

    /**
     * getOrderReference
     * @return mixed
     */
    public function getOrderReference()
    {
        return $this->orderReference;
    }

    /**
     * setOrderDate
     * @param $orderDate
     */
    public function setOrderDate($orderDate)
    {
        $formattedDate = date("c", strtotime($orderDate));
        $this->data[self::FIELD_ORDER_DATE] = $formattedDate;
    }

    /**
     * setOrderStatus
     * @param string $status
     */
    public function setOrderStatus(string $status)
    {
        $this->data[self::FIELD_ORDER_STATUS] = $status;
    }

    /**
     * setBillingAddress
     * @param array $billingAddress
     */
    public function setBillingAddress(array $billingAddress)
    {
        $this->data[self::FIELD_BILLING_ADDRESS] = $billingAddress;
    }

    /**
     * setShippingAddress
     * @param array $shippingAddress
     */
    public function setShippingAddress(array $shippingAddress)
    {
        $this->data[self::FIELD_SHIPPING_ADDRESS] = $shippingAddress;
    }

    /**
     * setShippingMethod
     * @param string $shippingMethod
     */
    public function setShippingMethod(string $shippingMethod)
    {
        $this->data[self::FIELD_SHIPMENT_METHOD] = $shippingMethod;
    }

    /**
     * setShippingCostGross
     * @param float\null $shippingCostGross
     */
    public function setShippingCostGross($shippingCostGross)
    {
        $this->data[self::FIELD_SHIPPING_COST_GROSS] = $shippingCostGross;
    }

    /**
     * setOrderLines
     * @param array $orderLines
     */
    public function setOrderLines(array $orderLines)
    {
        $this->data[self::FIELD_ORDER_LINES] = $orderLines;
    }

    /**
     * getAPIData
     *
     * @return mixed
     */
    public function getAPIData()
    {
        return $this->data;
    }
}
