<?php
namespace App\Shopify\Converters\ToLocal;

use App\DataMapper\DataExtractor;
use App\Models\OrderItem;
use App\Shopify\Converters\ToLocal\Map\OrderItemMap;

/**
 * @class OrderItemConverter
 * @package App\Shopify
 *          convert shopify order item to local order item model
 */
class OrderItemConverter extends BaseCollectionConverter
{
    /**
     * @param \App\Shopify\Converters\ToLocal\Map\OrderItemMap $map
     * @param \App\DataMapper\DataExtractor                    $dataExtractor
     * @param \App\Models\OrderItem                            $orderItem
     */
    public function __construct(
        OrderItemMap $map,
        DataExtractor $dataExtractor,
        OrderItem $orderItem
    ) {
        parent::__construct($map, $orderItem::factory(), $dataExtractor);
    }
}
