<?php
namespace App\Shopify\Handlers\ExpandOrder;

use Illuminate\Support\Carbon;

/**
 * Extract preorder information from order line
 */
class PreorderExtractor implements ExtractorInterface
{
    private string $usPreorderTimezone;
    public function config($config)
    {
        $this->usPreorderTimezone = $config['us_preorder_timezone'];
    }

    /**
     * @param array $lineData
     * @param array $bundleInfo
     * @return \Carbon\Carbon|false|null
     */
    public function extract(...$args)
    {
        $lineData = $args[0];
        $bundleInfo = $args[1];
        $productBundleInfo = $bundleInfo[$lineData['product_id']] ?? [];
        if (!empty($productBundleInfo)) {
            $variantId = $productBundleInfo['variant_id'] ?? null;
            if (isset($lineData['variant_id'])
                && $lineData['variant_id'] == $variantId
                && isset($productBundleInfo['Pre-order released on'])) {
                return Carbon::createFromFormat(
                    'm/d/Y',
                    $productBundleInfo['Pre-order released on'],
                    $this->usPreorderTimezone
                );
            }
            return null;
        }
        if (!isset($lineData['properties'])) {
            return null;
        }
        foreach ($lineData['properties'] as $property) {
            if ($property['name'] == 'Pre-order released on') {
                return Carbon::createFromFormat('m/d/Y', $property['value'], $this->usPreorderTimezone);
            }
        }
        return null;
    }
}
