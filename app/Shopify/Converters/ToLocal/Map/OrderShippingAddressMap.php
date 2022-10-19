<?php
namespace App\Shopify\Converters\ToLocal\Map;

use App\DataMapper\Map\MapInterface;
use App\Models\OrderAddress;

/**
 * @class OrderAddressMap
 * @package App\Shopify
 * A map for converting shopify address to local address
 */
class OrderShippingAddressMap implements MapInterface
{
    public function getMap(): array
    {
        return [
            'first_name' => 'order.shipping_address.first_name',
            'last_name' => 'order.shipping_address.last_name',
            'address1' => 'order.shipping_address.address1',
            'address2' => 'order.shipping_address.address2',
            'city' => 'order.shipping_address.city',
            'state' => 'order.shipping_address.province',
            'zip' => 'order.shipping_address.zip',
            'country_code' => 'order.shipping_address.country_code',
            'phone' => 'order.shipping_address.phone',
            'email' => 'order.customer.email',
            'customer_address_type' => '|'.OrderAddress::CUSTOMER_ADDRESS_TYPE_SHIPPING.'|',
        ];
    }
}
