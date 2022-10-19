<?php
namespace App\Shopify\Converters\ToLocal;

use App\Core\Models\Warehouse;
use App\DataMapper\DataExtractor;
use App\Models\Order;
use App\Shopify\Converters\ToLocal\Map\OrderMap;

/**
 * @class OrderConverter
 * @package App\Shopify
 *          convert shopify order + fulfillment order data into Fulfillment
 *          order model
 * @method Order convert(array $shopifyOrderData, array $shopifyFulfillmentOrderData, Warehouse $warehouse)
 */
class OrderConverter extends BaseSingleConverter
{
    /**
     * @param \App\Shopify\Converters\ToLocal\Map\OrderMap $orderMap
     * @param \App\DataMapper\DataExtractor                $dataExtractor
     * @param \App\Models\Order                            $order
     */
    public function __construct(
        OrderMap $orderMap,
        DataExtractor $dataExtractor,
        Order $order
    ) {
        parent::__construct($orderMap, $order::factory(), $dataExtractor);
    }
}
