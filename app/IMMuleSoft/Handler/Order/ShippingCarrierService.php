<?php

namespace App\IMMuleSoft\Handler\Order;

/**
 * Class ShippingCarrierService
 * @package App\IMMuleSoft\Handler\Order
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2022
 * @link     http://www.wmg.com
 */
class ShippingCarrierService
{
    protected string $carrierCode;
    protected string $carrierName;
    protected string $serviceCode;
    protected string $serviceName;
    protected int $dispatchOffset;

    /**
     * @return int
     */
    public function getDispatchOffset(): int
    {
        return $this->dispatchOffset;
    }

    /**
     * @param int $dispatchOffset
     * @return ShippingCarrierService
     */
    public function setDispatchOffset(int $dispatchOffset): ShippingCarrierService
    {
        $this->dispatchOffset = $dispatchOffset;
        return $this;
    }

    /**
     * @return string
     */
    public function getCarrierCode(): string
    {
        return $this->carrierCode;
    }

    /**
     * @param string $carrierCode
     * @return ShippingCarrierService
     */
    public function setCarrierCode(string $carrierCode): ShippingCarrierService
    {
        $this->carrierCode = $carrierCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getCarrierName(): string
    {
        return $this->carrierName;
    }

    /**
     * @param string $carrierName
     * @return ShippingCarrierService
     */
    public function setCarrierName(string $carrierName): ShippingCarrierService
    {
        $this->carrierName = $carrierName;
        return $this;
    }

    /**
     * @return string
     */
    public function getServiceCode(): string
    {
        return $this->serviceCode;
    }

    /**
     * @param string $serviceCode
     * @return ShippingCarrierService
     */
    public function setServiceCode(string $serviceCode): ShippingCarrierService
    {
        $this->serviceCode = $serviceCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getServiceName(): string
    {
        return $this->serviceName;
    }

    /**
     * @param string $serviceName
     * @return ShippingCarrierService
     */
    public function setServiceName(string $serviceName): ShippingCarrierService
    {
        $this->serviceName = $serviceName;
        return $this;
    }
}
