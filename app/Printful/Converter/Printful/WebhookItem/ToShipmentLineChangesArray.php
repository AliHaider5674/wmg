<?php declare(strict_types=1);

namespace App\Printful\Converter\Printful\WebhookItem;

use App\Exceptions\NoRecordException;
use App\Models\Service\ModelBuilder\ShipmentLineChangeBuilder;
use Printful\Structures\Webhook\WebhookItem;

/**
 * Class ToShipmentLineChange
 * @package App\Printful\Converter\Printful\WebhookItem
 */
class ToShipmentLineChangesArray implements WebhookConverterInterface
{
    /**
     * @var ShipmentLineChangeBuilder
     */
    private $shipmentLineChangeBuilder;

    /**
     * @var ToShipmentLineChangeParameter
     */
    private $toShipmentLineChangeParameter;

    /**
     * ToShipmentLineChange constructor.
     * @param ToShipmentLineChangeParameter $toShipmentLineChangeParameter
     * @param ShipmentLineChangeBuilder     $shipmentLineChangeBuilder
     */
    public function __construct(
        ToShipmentLineChangeParameter $toShipmentLineChangeParameter,
        ShipmentLineChangeBuilder $shipmentLineChangeBuilder
    ) {
        $this->toShipmentLineChangeParameter = $toShipmentLineChangeParameter;
        $this->shipmentLineChangeBuilder = $shipmentLineChangeBuilder;
    }

    /**
     * @param WebhookItem $webhookItem
     * @return iterable
     * @throws NoRecordException
     */
    public function convert(WebhookItem $webhookItem): iterable
    {
        return $this->shipmentLineChangeBuilder->build(
            $this->toShipmentLineChangeParameter->convert($webhookItem)
        );
    }
}
