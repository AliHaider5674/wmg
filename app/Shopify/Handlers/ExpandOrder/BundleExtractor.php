<?php
namespace App\Shopify\Handlers\ExpandOrder;

use Illuminate\Support\Carbon;

/**
 * Extract bundle information from order
 */
class BundleExtractor implements ExtractorInterface
{
    /**
     * @param array $orderLines
     * @return array
     */
    public function extract(...$args)
    {
        $orderLines = $args[0];
        $bundleInfo = [];
        foreach ($orderLines as $lineData) {
            if (!isset($lineData['properties'])) {
                continue;
            }
            foreach ($lineData['properties'] as $property) {
                if (strpos($property['name'], '_line_item_data_for_product_') === 0) {
                    $productId = substr($property['name'], strlen('_line_item_data_for_product_'));
                    $bundleInfo[$productId] = array_merge(
                        $bundleInfo[$productId] ?? [],
                        json_decode($property['value'], true)
                    );
                    continue;
                }
                if (strpos($property['name'], '_bundle_selectedVariantOnProduct_') === 0) {
                    $productId = substr($property['name'], strlen('_bundle_selectedVariantOnProduct_'));
                    $bundleInfo[$productId]['variant_id'] = $property['value'];
                }
            }
        }
        return $bundleInfo;
    }
}
