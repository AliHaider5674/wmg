<?php
namespace App\Shopify\Converters\ToLocal;

use App\Core\Models\Warehouse;
use App\DataMapper\DataExtractor;
use App\DataMapper\Map\MapInterface;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @class BaseConverter
 * base converter, support order, order item and order address
 */
class BaseCollectionConverter extends BaseSingleConverter
{
    public function convert(array $shopifyOrderData, array $shopifyFulfillmentOrderData, Warehouse $warehouse) : array
    {
        $shopifyDataSet = [
            'order' => $shopifyOrderData,
            'fulfillment_order' => $shopifyFulfillmentOrderData,
            'warehouse' => $warehouse->toArray()
        ];
        $extractData = $this->dataExtractor->extract($shopifyDataSet, $this->map);
        $result = [];
        foreach ($extractData as $item) {
            $result[] = $this->entityFactory->newModel($item);
        }
        return $result;
    }
}
