<?php declare(strict_types=1);

namespace App\Printful\Service;

use App\Core\Models\RawData\Order;

/**
 * Class PrintfulExternalIdParser
 * @package App\Printful\Service
 */
class PrintfulExternalIdParser
{
    /**
     * Printful external ID separator
     */
    private const EXTERNAL_ID_SEPARATOR = '-';

    /**
     * Printful external ID format
     */
    private const EXTERNAL_ID_FORMAT = '%s-%d';

    /**
     * @param string $printfulOrderExternalId
     * @return int
     */
    public function getLocalOrderId(string $printfulOrderExternalId): int
    {
        $ids = explode(self::EXTERNAL_ID_SEPARATOR, $printfulOrderExternalId);

        return (int) array_pop($ids);
    }

    /**
     * @param Order $order
     * @return string
     */
    public function createPrintfulExternalIdFromOrder(Order $order): string
    {
        return $this->createPrintfulExternalId($order->id, $order->orderId);
    }

    /**
     * @param int    $orderId
     * @param string $orderNumber
     * @return string
     */
    public function createPrintfulExternalId(
        int $orderId,
        string $orderNumber
    ): string {
        return sprintf(self::EXTERNAL_ID_FORMAT, $orderNumber, $orderId);
    }
}
