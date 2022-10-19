<?php declare(strict_types=1);

namespace App\Printful\Service;

use InvalidArgumentException;
use Printful\Structures\Webhook\WebhookItem;

/**
 * Class WebhookItemSerializer
 * @package App\Printful\Service
 */
class WebhookItemSerializer
{
    /**
     * Serialize event to JSON
     *
     * @param WebhookItem|array|string $webhookItem
     * @return string
     */
    public function serialize($webhookItem): string
    {
        switch (true) {
            case $webhookItem instanceof WebhookItem:
                return $this->serializeWebhookItem($webhookItem);
            case is_array($webhookItem):
                return json_encode($webhookItem);
            case is_string($webhookItem):
                return $webhookItem;
            default:
                throw new InvalidArgumentException("WebhookItem must be a WebhookItem object, array, or string");
        }
    }

    /**
     * @param WebhookItem $printfulEvent
     * @return string
     */
    protected function serializeWebhookItem(WebhookItem $printfulEvent): string
    {
        $printfulEventArray = (array) $printfulEvent;
        $printfulEventArray['data'] = $printfulEventArray['rawData'];

        unset(
            $printfulEventArray['rawData'],
            $printfulEventArray['reason'],
            $printfulEventArray['order'],
            $printfulEventArray['shipment']
        );

        return json_encode($printfulEventArray, JSON_PRETTY_PRINT);
    }
}
