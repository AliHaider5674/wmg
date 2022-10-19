<?php

namespace App\IMMuleSoft\Handler\Order;

use App\Core\Enums\OrderItemStatus;
use App\Core\Enums\OrderStatus;
use App\Core\Models\RawData\Order;
use App\Core\Models\RawData\OrderAddress;
use App\Exceptions\NoRecordException;
use App\Models\AlertEvent;
use App\Models\OrderItem;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class OrderMap
 * @package App\IMMuleSoft\Handler\Order
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2022
 * @link     http://www.wmg.com
 */
class OrderMap
{
    use \App\IMMuleSoft\Models\Traits\Order;

    const ADDRESS_TYPE_SHIPMENT = 'shipment';
    const ADDRESS_TYPE_BILLING = 'invoice';
    const MAX_PHONE_LENGTH = 15;
    private array $data;

    /**
     * resetData
     */
    protected function resetData()
    {
        unset($this->data);
        $this->data = array();
    }

    /**
     * build
     * Map RawOrder Ingram API Order Request
     * @throws Exception
     */
    public function handle(Order $rawOrder, $orderItems): array
    {
        $this->resetData();
        $this->setOrderInfo($rawOrder);
        $this->setDeliveryInfo($rawOrder);
        $this->setCurrency($orderItems);
        $this->setProperties();
        $this->setAddresses($rawOrder);
        $this->setOrderItems($orderItems);

        return $this->data;
    }

    protected function setOrderInfo(Order $rawOrder)
    {
        //order level info
        $this->data['code'] = (string) $rawOrder->orderId;
        $this->data['consumerCode'] = (string) $rawOrder->id;
        $this->data['type'] = 'WEB';
        $this->data['salesChannelCode'] = 'default'; //not used always set to default
        $this->data['dateTime'] = Carbon::parse($rawOrder->createdAt)->toIso8601ZuluString();
    }

    /**
     * setDeliveryInfo
     * @param Order $rawOrder
     * @throws Exception
     */
    protected function setDeliveryInfo(Order $rawOrder)
    {
        if (!isset($rawOrder->customAttributes['im_carrier_code'])
            || !isset($rawOrder->customAttributes['im_service_code'])
        ) {
            $this->updateOrderStatus(
                array($rawOrder->id),
                OrderStatus::SOFT_ERROR,
                OrderItemStatus::SOFT_ERROR
            );

            $message = "Order Id: $rawOrder->orderId Missing Ingram Carrier Service Codes";
            //raise alert for errored orders
            throw new NoRecordException($message);
        }

        $this->data['carrierCode'] = $rawOrder->customAttributes['im_carrier_code'];
        $this->data['carrierService'] = $rawOrder->customAttributes['im_service_code'];
        $this->data['isExpressDelivery'] = false; //?
        $this->data['deliveryInstruction'] = ''; //todo future use
    }

    /**
     * setProperties
     */
    protected function setProperties()
    {
        $this->data['properties'] = [
            'isBackorderAllowed' => true, //todo dynamically set
            'isSplitOrderAllowed' => false //todo dynamically set
        ];
    }

    /**
     * setAddresses
     * @param Order $rawOrder
     */
    protected function setAddresses(Order $rawOrder)
    {
        if ($rawOrder->shippingAddress) {
            $this->data['addresses'][] =
                $this->setAddress($rawOrder->shippingAddress, self::ADDRESS_TYPE_SHIPMENT);
        }

        if ($rawOrder->billingAddress) {
            $this->data['addresses'][] =
                $this->setAddress($rawOrder->billingAddress, self::ADDRESS_TYPE_BILLING);
        }
    }

    /**
     * shippingAddress
     * @param OrderAddress $address
     * @param string $type
     * @return array
     */
    protected function setAddress(OrderAddress $address, string $type): array
    {
        $data = array();
        $data['type'] = $type;
        $data['firstName'] = (string) $address->firstName;
        $data['lastName'] = (string) $address->lastName;
        $data['addressLine1'] = (string) $address->address1;
        $data['addressLine2'] = (string) $address->address2;
        $data['postalCode'] = (string) $address->zip;
        $data['city'] = (string) $address->city;
        $data['state'] = (string) $address->state;
        $data['countryCode'] = (string) $address->countryCode;

        if (!empty($address->phone)) {
            $phone1 = $this->validatePhone($address->phone);

            if (!empty($phone1)) {
                $data['phoneNumber1'] = $phone1;
            }
        }

        $data['email'] = (string) $address->email;

        return $data;
    }

    /**
     * setOrderItems
     * @param Collection $orderItems
     */
    protected function setOrderItems(Collection $orderItems)
    {
        /**
         * @var OrderItem $orderItem
         */
        foreach ($orderItems as $orderItem) {
            $line = array();
            $line['lineNumber'] = $orderItem->id;
            $line['sku'] = (string) $orderItem->sku;
            $line['description'] = (string) $orderItem->name;
            $line['quantity'] = (int) $orderItem->quantity - $orderItem->quantity_shipped;
           // $line['omitFromPaperwork'] = false;
            $line['unitPrices'][] = [
                'type'=> 'price',
                'priceInclVat' => round($orderItem->gross_amount, 2),
                'priceExclVat' => round($orderItem->net_amount, 2),
                'priceVat'  => round($orderItem->tax_amount, 2),
                'vatRate' => round($orderItem->tax_rate, 2)
            ];
            $line['isAGift'] = false;
            $this->data['lines'][] = $line;
            unset($line);
        }
    }

    /**
     * setCurrency
     * @param $orderItems
     */
    private function setCurrency($orderItems)
    {
        //as currency code is set at order item, we set the order currency code from the first available
        //currency code from the items.
        $this->data['currencyCode'] = '';

        foreach ($orderItems as $orderItem) {
            if (!empty($orderItem->currency)) {
                $this->data['currencyCode'] = $orderItem->currency;
            }
        }
    }

    /**
     * validatePhone
     * @param string $phone
     * @return string
     */
    private function validatePhone(string $phone) : string
    {
        if (strlen($phone) > self::MAX_PHONE_LENGTH) {
            return '';
        }

        return $phone;
    }
}
