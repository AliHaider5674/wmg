<?php

namespace App\IMMuleSoft\Models\Weight;

use App\Catalog\Models\ProductDimension;
use App\IMMuleSoft\Constants\ProductDimensionConstant;
use App\Models\AlertEvent;

/**
 * Class OrderWeightCalculator
 * @package App\IMMuleSoft\Models\Weight
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2022
 * @link     http://www.wmg.com
 */
class ItemWeightCalculator
{
    const ALERT_NAME = 'Ceva Product Weight ';

    /**
     * calculate
     * @param int $orderId
     * @param array $items
     * @return Weight
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function calculate(int $orderId, array $items) : Weight
    {
        $weight = new Weight();

        if (empty($items)) {
            return $weight;
        }

        $reportSku = array();

        foreach ($items as $item) {
            if (!isset($item['sku']) || !isset($item['qty'])) {
                continue;
            }

            $sku = trim($item['sku']);
            $productWeight = ProductDimension::query()
                ->where('product_sku', '=', $sku)
                ->where('type', '=', ProductDimensionConstant::TYPE_WEIGHT)
                ->where('unit', '=', ProductDimensionConstant::UNIT_WEIGHT_GRAM)
                ->get()->first();

            if ($productWeight === null) {
                $reportSku[] = $sku;
                continue;
            }

            $totalWeight = $productWeight->value * $item['qty'];
            $weight->incrementWeight($totalWeight);
        }


        if (!empty($reportSku)) {
            $message = sprintf('Missing weight: %s', implode(',', $reportSku));
            $weight->setMessage($message);
        }
        return $weight;
    }
}
