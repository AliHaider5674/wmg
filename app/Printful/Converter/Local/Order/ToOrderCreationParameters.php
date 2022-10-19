<?php declare(strict_types=1);

namespace App\Printful\Converter\Local\Order;

use App\Core\Models\RawData\Order;
use App\Printful\Converter\AbstractRawDataConverter;
use App\Printful\Converter\Local\OrderAddress\ToRecipientCreationParameters;
use App\Printful\Converter\Local\OrderItem\ToOrderItemCreationParameters;
use App\Printful\Service\PrintfulExternalIdParser;
use Printful\Structures\Order\OrderCreationParameters;

/**
 * Class ToOrderCreationParameters
 * @package App\Printful\Converter\Order
 */
class ToOrderCreationParameters extends AbstractRawDataConverter
{
    /**
     * @var ToOrderItemCreationParameters
     */
    private $toOrderItemConverter;

    /**
     * @var ToRecipientCreationParameters
     */
    private $toRecipientConverter;

    /**
     * @var PrintfulExternalIdParser $externalIdParser
     */
    private $externalIdParser;

    /**
     * ToOrderCreationParameters constructor.
     * @param ToOrderItemCreationParameters $toOrderItemConverter
     * @param ToRecipientCreationParameters $toRecipientConverter
     * @param PrintfulExternalIdParser $externalIdParser
     */
    public function __construct(
        ToOrderItemCreationParameters $toOrderItemConverter,
        ToRecipientCreationParameters $toRecipientConverter,
        PrintfulExternalIdParser $externalIdParser
    ) {
        $this->toOrderItemConverter = $toOrderItemConverter;
        $this->toRecipientConverter = $toRecipientConverter;
        $this->externalIdParser = $externalIdParser;
    }

    /**
     * @param Order $order
     * @return OrderCreationParameters
     */
    public function convert(Order $order): OrderCreationParameters
    {
        $orderCreationParameters = new OrderCreationParameters();
        $orderCreationParameters->externalId = $this->externalIdParser
            ->createPrintfulExternalIdFromOrder($order);

        $orderCreationParameters->shipping = $order->shippingMethod;
        $orderCreationParameters->addRecipient(
            $this->toRecipientConverter->convert($order->shippingAddress)
        );

        $orderItems = collect($order->items);

        $orderItems->each(function ($orderItem) use ($orderCreationParameters) {
            $orderCreationParameters->addItem(
                $this->toOrderItemConverter->convert($orderItem)
            );
        });

        $discount = (float)($order->customAttributes['order_discount_amount'] ?? 0.00);
        $totalTax = (float)($order->customAttributes['order_tax_amount'] ?? 0.00);
        $currency = $orderItems->first()->currency;

        if ($currency) {
            $orderCreationParameters->currency = $currency;
        }

        $this->addRetailCostsToOrderParameters(
            $orderCreationParameters,
            $order,
            $totalTax,
            $discount
        );

        return $orderCreationParameters;
    }

    /**
     * @param OrderCreationParameters $orderCreationParameters
     * @param Order                   $order
     * @param float                   $totalTax
     * @return $this
     */
    protected function addRetailCostsToOrderParameters(
        OrderCreationParameters $orderCreationParameters,
        Order $order,
        float $totalTax,
        float $discount
    ): self {
        $shipping = $order->shippingNetAmount;

        $orderCreationParameters->addRetailCosts(
            number_format($discount, 2),
            number_format((float) $shipping, 2),
            number_format($totalTax, 2)
        );

        return $this;
    }
}
