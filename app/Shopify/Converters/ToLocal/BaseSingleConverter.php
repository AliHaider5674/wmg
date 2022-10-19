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
class BaseSingleConverter
{
    protected MapInterface $map;
    protected Factory $entityFactory;
    protected DataExtractor $dataExtractor;

    /**
     * @param \App\DataMapper\Map\MapInterface                $orderMap
     * @param \Illuminate\Database\Eloquent\Factories\Factory $entityFactory
     * @param \App\DataMapper\DataExtractor                   $dataExtractor
     */
    public function __construct(
        MapInterface $orderMap,
        Factory $entityFactory,
        DataExtractor $dataExtractor
    ) {
        $this->map = $orderMap;
        $this->entityFactory = $entityFactory;
        $this->dataExtractor = $dataExtractor;
    }

    /**
     * @param array                      $shopifyOrderData
     * @param array                      $shopifyFulfillmentOrderData
     * @param \App\Core\Models\Warehouse $warehouse
     * @return \Illuminate\Database\Eloquent\Model
     * @throws \App\DataMapper\Exceptions\InvalidMappingException
     */
    public function convert(array $shopifyOrderData, array $shopifyFulfillmentOrderData, Warehouse $warehouse)
    {
        $shopifyDataSet = [
            'order' => $shopifyOrderData,
            'fulfillment_order' => $shopifyFulfillmentOrderData,
            'warehouse' => $warehouse->toArray()
        ];
        $extractData = $this->dataExtractor->extract($shopifyDataSet, $this->map);
        return $this->entityFactory->newModel($extractData);
    }
}
