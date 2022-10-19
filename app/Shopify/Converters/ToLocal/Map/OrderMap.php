<?php
namespace App\Shopify\Converters\ToLocal\Map;

use App\DataMapper\Map\MapInterface;

/**
 * @class OrderMap
 * @package App\DataMapper
 * a map for converting Shopify Order to local order
 */
class OrderMap implements MapInterface
{
    public function getMap(): array
    {
        return [
            'sales_channel' => 'fulfillment_order.shop_id',
            'request_id' => 'fulfillment_order.id',
            'order_id' => 'order.id',
            'gift_message' => '||',
            'shipping_method' => '|STANDARD|',
            'country_id' => 'order.billing_address.country_code',
            'customer_reference' => 'order.billing_address.country_code',
            'vat_country' => 'order.billing_address.country_code',
            'shipping_net_amount' => [$this, 'calculateShipping'],
            'shipping_gross_amount' => [$this, 'calculateShipping'],
            'shipping_tax_amount' => '|0|',
            'shipping_tax_rate' => '|0|',
        ];
    }

    public function calculateShipping($dataSet)
    {
        $amount = 0;
        foreach ($dataSet['order']['shipping_lines'] as $shippingLine) {
            $amount += $shippingLine['price'];
        }
        return $amount;
    }
}
