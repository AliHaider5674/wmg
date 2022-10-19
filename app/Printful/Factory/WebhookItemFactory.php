<?php declare(strict_types=1);

namespace App\Printful\Factory;

use Printful\Structures\Webhook\WebhookItem;
use Printful\Structures\Webhook\WebhooksInfoItem;

/**
 * Class WebhookItemFactory
 * @package App\Printful\Factory
 */
class WebhookItemFactory
{
    /**
     * Create a webhook item
     *
     * @param array $rawData
     * @return WebhookItem
     */
    public function createWebhookItem(array $rawData): WebhookItem
    {
        return WebhookItem::fromArray($rawData);
    }

    /**
     * Create webhooks info item
     *
     * @param array $rawData
     * @return WebhooksInfoItem
     */
    public function createWebhooksInfoItem(array $rawData): WebhooksInfoItem
    {
        return WebhooksInfoItem::fromArray($rawData);
    }
}
