<?php

namespace App\IMMuleSoft\Handler\Order;

use App\Exceptions\OrderDropException;
use App\IMMuleSoft\Models\ImMulesoftShippingServiceMapper;
use App\IMMuleSoft\Models\Weight\ItemWeightCalculator;
use App\IMMuleSoft\Models\Weight\Weight;
use App\Models\Order;
use App\Models\OrderAddress;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class shippingServiceMapper
 * @package App\IMMuleSoft\Handler\Order
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2022
 * @link     http://www.wmg.com
 */
class ShippingServiceMapper
{
    const SHIPPING_SERVICE_MAPPING_TABLE = 'im_mulesoft_shipping_service_mappers';
    const SHIPPING_CARRIER_TABLE = 'im_mulesoft_shipping_carrier_services';
    private ItemWeightCalculator $itemWeightCalculator;

    public function __construct(
        ItemWeightCalculator $itemWeightCalculator
    ) {
        $this->itemWeightCalculator = $itemWeightCalculator;
    }

    /**
     * map
     * @param Order $order
     * @param Collection $orderItems
     * @return void
     * @throws OrderDropException
     */
    public function mapShippingMethodToService(
        Order $order,
        Collection $orderItems
    ) {
        $shippingMethod = $this->getShippingMethod($order);

        if (empty($shippingMethod)) {
            return ;
        }

        $orderProductItems = array();

        foreach ($orderItems as $item) {
            $orderProductItems[] =  ['sku' => $item->sku, 'qty' => $item->quantity - $item->quantity_shipped];
        }

        //get order weight  by order items
        $orderWeight = $this->itemWeightCalculator->calculate($order->id, $orderProductItems);

        if ($orderWeight->getTotalWeight() === Weight::ZERO_WEIGHT) {
            throw new OrderDropException($orderWeight->getMessage());
        }

        //get shipping service by country code, order weight and method
        $shippingCarrierService = $this->getShippingCarrierService(
            $order->getShippingAddress(),
            $shippingMethod,
            $orderWeight->getTotalWeightInKg()
        );

        if ($shippingCarrierService) {
            $this->setOrderShippingCarrierService($order, $shippingCarrierService);
        }
    }

    /**
     * setOrderShippingService
     * @param Order $order
     * @param ShippingCarrierService $shippingCarrierService
     */
    public function setOrderShippingCarrierService(Order $order, ShippingCarrierService $shippingCarrierService)
    {
        $customAttributes = array();
        $customAttributes['im_carrier_code'] = $shippingCarrierService->getCarrierCode();
        $customAttributes['im_carrier_name'] = $shippingCarrierService->getCarrierName();
        $customAttributes['im_service_code'] = $shippingCarrierService->getServiceCode();
        $customAttributes['im_service_name'] = $shippingCarrierService->getServiceName();
        $customAttributes['im_dispatch_offset'] = $shippingCarrierService->getDispatchOffset();

        $order->addCustomAttribute($customAttributes);
        $order->save();
    }

//    /**
//     * validate
//     * @param Order $order
//     * @return bool
//     */
//    protected function isValid(Order $order)
//    {
//        //todo validate data
//        return true;
//    }


    /**
     * getShippingService
     * @param OrderAddress $shippingAddress
     * @param string $shippingMethod
     * @param float $orderWeight
     * @return ShippingCarrierService|null
     */
    protected function getShippingCarrierService(
        OrderAddress $shippingAddress,
        string $shippingMethod,
        float $orderWeight = 0.00
    ) : ?ShippingCarrierService {
        //query database for shipping service based on the destination and shipping method
        $service = ImMulesoftShippingServiceMapper::query()
            ->select(
                [
                    self::SHIPPING_CARRIER_TABLE . '.carrier_code'
                    , self::SHIPPING_CARRIER_TABLE . '.carrier_name'
                    , self::SHIPPING_CARRIER_TABLE . '.service_code'
                    , self::SHIPPING_CARRIER_TABLE . '.service_name'
                    , self::SHIPPING_SERVICE_MAPPING_TABLE . '.dispatch_offset'
                ]
            )
            ->join(self::SHIPPING_CARRIER_TABLE, function ($join) {
                $join->on(
                    self::SHIPPING_CARRIER_TABLE . '.service_code',
                    '=',
                    self::SHIPPING_SERVICE_MAPPING_TABLE . '.service_code'
                )->on(
                    self::SHIPPING_CARRIER_TABLE . '.carrier_code',
                    '=',
                    self::SHIPPING_SERVICE_MAPPING_TABLE . '.carrier_code'
                );
            })
            ->where(
                self::SHIPPING_SERVICE_MAPPING_TABLE . '.country_code',
                '=',
                $shippingAddress->country_code
            )
            ->where(
                self::SHIPPING_SERVICE_MAPPING_TABLE . '.delivery_type',
                '=',
                $shippingMethod
            )
            ->where(
                self::SHIPPING_SERVICE_MAPPING_TABLE . '.condition_from_value',
                '<=',
                $orderWeight
            )
            ->where(
                self::SHIPPING_SERVICE_MAPPING_TABLE . '.condition_to_value',
                '>=',
                $orderWeight
            )
            ->orderBy(self::SHIPPING_SERVICE_MAPPING_TABLE . '.condition_from_value')
            ->first();

        if ($service) {
            $shippingCarrierService = new ShippingCarrierService();
            $shippingCarrierService->setCarrierCode($service->carrier_code);
            $shippingCarrierService->setCarrierName($service->carrier_name);
            $shippingCarrierService->setServiceCode($service->service_code);
            $shippingCarrierService->setServiceName($service->service_name);
            $shippingCarrierService->setDispatchOffset($service->dispatch_offset);

            return $shippingCarrierService;
        }

        return null;
    }

    /**
     * getShippingMethod
     * @param Order $order
     * @return string
     */
    public function getShippingMethod(Order $order): string
    {
        $orderShippingMethod = '';

        //get order shipping method
        $orderShippingMethod = $order->shipping_method;

        if (empty($orderShippingMethod)) {
            return $orderShippingMethod;
        }

        /**
         * Expect format
         * <ENVIRONMENT>_SHIPPING_METHOD_<CURRENCY>
         *
         * e.g.
         * EU_STANDARD_MAIL_GBP
         *
         */
        $shippingMethodPattern = "/^[A-Z]{2}_.*_[A-Z]{3}/";

        //check the shipping method matches pattern
        if (preg_match($shippingMethodPattern, $orderShippingMethod)) {
            $patterns = array();
            //environment pattern
            $patterns[0] = "/^[A-Z]{2}_/";

            //currency pattern
            $patterns[1] = "/_[A-Z]{3}$/";

            //strip off environment and currency values from shipping method
            $orderShippingMethod = preg_replace($patterns, '', $orderShippingMethod);
        }

        return strtolower($orderShippingMethod);
    }
}
