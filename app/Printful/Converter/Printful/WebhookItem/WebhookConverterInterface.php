<?php declare(strict_types=1);

namespace App\Printful\Converter\Printful\WebhookItem;

use Printful\Structures\Webhook\WebhookItem;

/**
 * Interface WebhookConverterInterface
 * @package App\Printful\Converter\Printful\WebhookItem
 */
interface WebhookConverterInterface
{
    /**
     * @param WebhookItem $webhookItem
     * @return mixed
     */
    public function convert(WebhookItem $webhookItem);
}
