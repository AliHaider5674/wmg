<?php declare(strict_types=1);

namespace App\Printful\Models;

use App\Printful\Enums\PrintfulEventStatus;
use App\Printful\Factory\WebhookItemFactory;
use App\Printful\Service\WebhookItemSerializer;
use App\Printful\Enums\PrintfulEventType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Printful\Structures\Webhook\WebhookItem;

/**
 * Class PrintfulEvent
 * @package App\Printful\Model
 *
 * @method int getId()
 * @method PrintfulEventType getEventType()
 * @method WebhookItem getWebhookItem()
 * @method PrintfulEventStatus getStatus()
 * @method Carbon getCreatedAt()
 * @method Carbon getUpdatedAt()
 */
class PrintfulEvent extends Model
{
    /**
     * @var WebhookItemSerializer
     */
    private $webhookItemSerializer;

    /**
     * @var WebhookItemFactory
     */
    private $webhookItemFactory;

    /**
     * Casts array
     *
     * @var string[]
     */
    protected $casts = [
        'status' => 'int',
    ];

    /**
     * PrintfulEvent constructor.
     * @param array                      $attributes
     * @param WebhookItemSerializer|null $webhookItemSerializer
     * @param WebhookItemFactory|null    $webhookItemFactory
     */
    public function __construct(
        array $attributes = [],
        WebhookItemSerializer $webhookItemSerializer = null,
        WebhookItemFactory $webhookItemFactory = null
    ) {
        $this->webhookItemSerializer = $webhookItemSerializer
            ?? app(WebhookItemSerializer::class);

        $this->webhookItemFactory = $webhookItemFactory
            ?? app(WebhookItemFactory::class);

        parent::__construct($attributes);
    }

    /**
     * Model's fillable properties
     *
     * @var string[]
     */
    protected $fillable = [
        'event_type',
        'webhook_item',
        'status',
    ];

    /**
     * HasMany relationship with PrintfulLog
     *
     * @return HasMany
     */
    public function logs(): HasMany
    {
        return $this->hasMany(PrintfulLog::class, 'event_id', 'id');
    }

    /**
     * @param WebhookItem|array|string $webhookItem
     * @return void
     */
    public function setWebhookItemAttribute($webhookItem): void
    {
        $webhookItem = $this->webhookItemSerializer->serialize($webhookItem);
        $this->attributes['webhook_item'] = $this->webhookItemSerializer->serialize($webhookItem);
    }

    /**
     * Get WebhookItem attribute as a WebhookItem
     *
     * @param string $webhookItem
     * @return WebhookItem
     */
    public function getWebhookItemAttribute(string $webhookItem): WebhookItem
    {
        return $this->webhookItemFactory->createWebhookItem(
            json_decode($webhookItem, true)
        );
    }
}
