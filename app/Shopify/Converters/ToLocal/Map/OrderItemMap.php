<?php
namespace App\Shopify\Converters\ToLocal\Map;

use App\DataMapper\Map\MapInterface;

/**
 * @class OrderItemMap
 * @package App\DataMapper
 * a map for converting Shopify order items to local order items
 */
class OrderItemMap implements MapInterface
{
    /**
     * @return string[]
     */
    public function getMap(): array
    {
        return [
            'order_line_id' => 'fulfillment_order.line_items[].id',
            'sku' => 'fulfillment_order.line_items.line_item_id->order.line_items.id->order.line_items.sku',
            'name' => 'fulfillment_order.line_items.line_item_id->order.line_items.id->order.line_items.name',
            'source_id' => 'warehouse.code',
            'aggregated_line_id' => '|0|',
            'net_amount' => 'fulfillment_order.line_items.line_item_id->order.line_items.id->order.line_items.price',
            'gross_amount' => 'fulfillment_order.line_items.line_item_id->order.line_items.id->order.line_items.price',
            'tax_amount' => [ 'fulfillment_order.line_items.line_item_id->'.
                'order.line_items.id->order.line_items.tax_lines',
                [$this, 'calculateTaxAmount']],
            'tax_rate' => [ 'fulfillment_order.line_items.line_item_id->'.
                'order.line_items.id->order.line_items.tax_lines',
                            [$this, 'calculateTaxRate']],
            'currency' => 'order.currency',
            'item_type' => '|PHYSICAL|',
            'order_line_number' => '|1|',
            'parent_order_line_number' => '|0|',
            'quantity' => 'fulfillment_order.line_items[].fulfillable_quantity',
            '__type' => 'collection' //Indicate this is a collection
        ];
    }

    public function calculateTaxRate($taxLines)
    {
        $rate = 0;
        foreach ($taxLines as $line) {
            $rate += $line['rate'];
        }
        return $rate;
    }

    public function calculateTaxAmount($taxLines)
    {
        $rate = 0;
        foreach ($taxLines as $line) {
            $rate += $line['price'];
        }
        return $rate;
    }
}
