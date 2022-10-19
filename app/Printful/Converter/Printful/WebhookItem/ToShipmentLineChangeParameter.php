<?php declare(strict_types=1);

namespace App\Printful\Converter\Printful\WebhookItem;

use App\Core\Constants\BackorderStatusReasonCodes;
use App\Models\Service\ModelBuilder\ShipmentLineChangeParameter;
use App\Models\Service\ModelBuilder\ShipmentLineChange\ItemParameter;
use App\Printful\Service\PrintfulExternalIdParser;
use Printful\Structures\Webhook\WebhookItem;

/**
 * Class ShipmentLineChangeParameterConverter
 * @package App\Printful\Converter
 */
class ToShipmentLineChangeParameter implements WebhookConverterInterface
{
    /**
     * Backorder event type
     */
    private const TYPE_ON_HOLD = 'order_put_hold';

    /**
     * Backorder event type
     */
    private const TYPE_RETURNED = 'package_returned';

    /**
     * @var PrintfulExternalIdParser $externalIdParser
     */
    protected $externalIdParser;

    /**
     * ToOrderCreationParameters constructor.
     * @param PrintfulExternalIdParser $externalIdParser
     */
    public function __construct(PrintfulExternalIdParser $externalIdParser)
    {
        $this->externalIdParser = $externalIdParser;
    }

    /**
     * @param WebhookItem $webhookItem
     * @return ShipmentLineChangeParameter
     */
    public function convert(WebhookItem $webhookItem): ShipmentLineChangeParameter
    {
        $backorderReasonCode = $this->getBackorderReasonCode($webhookItem->type);
        $parameter = new ShipmentLineChangeParameter();
        $parameter->orderId = $this->externalIdParser->getLocalOrderId(
            $webhookItem->order->externalId
        );

        foreach ($webhookItem->order->items as $item) {
            $isReturn = $backorderReasonCode === BackorderStatusReasonCodes::RETURNED;
            $itemParameter = new ItemParameter();
            $itemParameter->orderItemId = $item->externalId;
            $itemParameter->sku = $item->sku;
            $itemParameter->quantity = $item->quantity;
            $itemParameter->returnedQuantity = $isReturn ? $item->quantity : 0;
            $itemParameter->backOrderReasonCode = $backorderReasonCode;
            $parameter->addItem($itemParameter);
        }

        return $parameter;
    }

    /**
     * @param string $webhookItemType
     * @return string|null
     */
    private function getBackorderReasonCode(string $webhookItemType): ?string
    {
        switch ($webhookItemType) {
            case self::TYPE_ON_HOLD:
                return BackorderStatusReasonCodes::ON_HOLD;
            case self::TYPE_RETURNED:
                return BackorderStatusReasonCodes::RETURNED;
            default:
                return null;
        }
    }
}
