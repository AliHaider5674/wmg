<?php declare(strict_types=1);

namespace App\Printful\Handler\IO\Tracker;

use App\Printful\Converter\Printful\WebhookItem\ToShipmentLineChangeParameter;

/**
 * Class AckTracker
 * @package App\Printful\Handler\IO\Tracker
 */
class AckTracker extends WebhookItemTracker
{
    /**
     * AckTracker constructor.
     * @param ToShipmentLineChangeParameter $webhookConverter
     */
    public function __construct(ToShipmentLineChangeParameter $webhookConverter)
    {
        parent::__construct($webhookConverter);
    }
}
