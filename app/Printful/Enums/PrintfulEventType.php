<?php declare(strict_types=1);

namespace App\Printful\Enums;

use App\Core\Enums\BaseEnum;
use App\Enums\FilterEnum;
use App\Printful\Enums\PrintfulEventType as EventTypeEnum;
use BenSampo\Enum\Exceptions\InvalidEnumKeyException;
use BenSampo\Enum\Exceptions\InvalidEnumMemberException;
use Illuminate\Contracts\Routing\UrlRoutable;
use Printful\Structures\Webhook\WebhookItem;

/**
 * @method static packageShipped(): int
 * @method static packageReturned(): int
 * @method static orderPutHold(): int
 * @method static orderRemoveHold(): int
 */
final class PrintfulEventType extends FilterEnum
{
    /**
     * @var string
     */
    protected $column = 'event_type';

    /**
     * Package has been shipped
     */
    public const PACKAGE_SHIPPED = 0;

    /**
     * Package has been returned to Printful
     */
    public const PACKAGE_RETURNED = 1;

    /**
     * Order has been put on hold
     */
    public const ORDER_PUT_HOLD = 2;

    /**
     * Order has been removed from hold
     */
    public const ORDER_REMOVE_HOLD = 3;

    /**
     * @param WebhookItem $webhookItem
     * @return static
     * @throws InvalidEnumKeyException
     */
    public static function fromWebhookItem(WebhookItem $webhookItem): self
    {
        return self::fromKey(strtoupper($webhookItem->type));
    }
}
