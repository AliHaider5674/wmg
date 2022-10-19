<?php declare(strict_types=1);

namespace App\Printful\Converter\Local\OrderItem;

use App\Core\Models\RawData\OrderItem;
use App\Printful\Converter\AbstractRawDataConverter;
use App\Printful\Structures\OrderItemCreationParameters;

/**
 * Class ToOrderItemCreationParameters
 * @package App\Printful\Converter\Order
 */
class ToOrderItemCreationParameters extends AbstractRawDataConverter
{
    /**
     * Mapping between OrderItem attributes and OrderItemCreationParameters
     * attributes
     */
    private const ORDER_ITEM_ATTRIBUTE_MAP = [
        'id'                 => 'externalId',
        'name'               => 'name',
        'sku'                => 'sku',
    ];

    /**
     * @param OrderItem $orderItem
     * @return OrderItemCreationParameters
     */
    public function convert(OrderItem $orderItem): OrderItemCreationParameters
    {
        $orderItemParameters = new OrderItemCreationParameters();
        $orderItemParameters->setExternalVariantId($orderItem->customAttributes['printful_variant_id']);
        $orderItemParameters->setQuantity((int) $orderItem->quantity);
        $orderItemParameters->setRetailPrice(
            number_format((float) $orderItem->retailPricePerItem, 2)
        );
        return $this->mapParameters(
            $orderItem,
            $orderItemParameters,
            self::ORDER_ITEM_ATTRIBUTE_MAP
        );
    }
}
