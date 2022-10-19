<?php
namespace App\Shopify\Converters\ToLocal;

use App\Core\Models\Warehouse;
use App\DataMapper\DataExtractor;
use App\Models\OrderAddress;
use App\Shopify\Converters\ToLocal\Map\OrderShippingAddressMap;

/**
 * @class OrderAddressConverter
 * Convert shopify address to local address
 * @method OrderAddress convert(array $shopifyOrderData, array $shopifyFulfillmentOrderData, Warehouse $warehouse)
 */
class OrderShippingAddressConverter extends BaseSingleConverter
{
    /**
     * @param \App\Shopify\Converters\ToLocal\Map\OrderShippingAddressMap $map
     * @param \App\DataMapper\DataExtractor                               $dataExtractor
     * @param \App\Models\OrderAddress                                    $orderAddress
     */
    public function __construct(
        OrderShippingAddressMap $map,
        DataExtractor           $dataExtractor,
        OrderAddress            $orderAddress
    ) {
        parent::__construct($map, $orderAddress::factory(), $dataExtractor);
    }
}
