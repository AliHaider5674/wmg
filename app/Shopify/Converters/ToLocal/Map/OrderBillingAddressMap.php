<?php
namespace App\Shopify\Converters\ToLocal\Map;

use App\DataMapper\Map\MapInterface;
use App\Models\OrderAddress;

/**
 * @class OrderAddressMap
 * @package App\Shopify
 * A map for converting shopify address to local address
 */
class OrderBillingAddressMap implements MapInterface
{
    public function getMap(): array
    {
        return [
            'first_name' => 'order.billing_address.first_name',
            'last_name' => 'order.billing_address.last_name',
            'address1' => 'order.billing_address.address1',
            'address2' => 'order.billing_address.address2',
            'city' => 'order.billing_address.city',
            'state' => 'order.billing_address.province',
            'zip' => 'order.billing_address.zip',
            'country_code' => 'order.billing_address.country_code',
            'phone' => 'order.billing_address.phone',
            'email' => 'order.customer.email',
            'customer_address_type' => '|'.OrderAddress::CUSTOMER_ADDRESS_TYPE_BILLING.'|',
        ];
    }
}
