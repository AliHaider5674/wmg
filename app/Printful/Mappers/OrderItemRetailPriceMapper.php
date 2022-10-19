<?php declare(strict_types=1);

namespace App\Printful\Mappers;

use App\Core\Mappers\OrderProcessorInterface;
use App\Core\Models\RawData\Order;

/**
 * Class FreeOrderItemFixer
 * @package App\Printful\Mappers
 */
class OrderItemRetailPriceMapper implements OrderProcessorInterface
{
    /**
     * Free item cost
     */
    private const FREE_ITEM_COST = "0.0100";

    /**
     * @param Order $order
     * @return Order
     */
    public function processOrder(Order $order): Order
    {
        foreach ($order->items as $orderItem) {
            $quantity = (int) $orderItem->quantity;
            $netAmount = (float) $orderItem->netAmount;

            if ($quantity > 0) {
                $netAmount /= $quantity;
                $orderItem->retailPricePerItem = $this->formatRetailPrice(
                    $netAmount
                );
            }

            if ($netAmount < 0.01) {
                $orderItem->retailPricePerItem = self::FREE_ITEM_COST;
            }
        }

        return $order;
    }

    /**
     * Format retail price
     *
     * @param float $retailPrice
     * @return string
     */
    protected function formatRetailPrice(float $retailPrice): string
    {
        return number_format(round($retailPrice, 2), 4);
    }
}
