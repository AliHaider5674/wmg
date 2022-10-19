<?php declare(strict_types=1);

namespace App\Printful\Handler\IO\Tracker;

use App\Printful\Converter\Printful\WebhookItem\ToShipmentParameter;

/**
 * Class ShipmentTracker
 * @package App\Printful\Handler\IO\Tracker
 */
class ShipmentTracker extends WebhookItemTracker
{
    /**
     * ShipmentTracker constructor.
     * @param ToShipmentParameter $webhookConverter
     */
    public function __construct(ToShipmentParameter $webhookConverter)
    {
        parent::__construct($webhookConverter);
    }
}
