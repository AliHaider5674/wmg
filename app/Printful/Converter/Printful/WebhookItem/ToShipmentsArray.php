<?php declare(strict_types=1);

namespace App\Printful\Converter\Printful\WebhookItem;

use App\Exceptions\NoRecordException;
use App\Exceptions\RecordExistException;
use App\Models\Service\ModelBuilder\ShipmentBuilder;
use Printful\Structures\Webhook\WebhookItem;

/**
 * Class ToShipment
 * @package App\Printful\Converter\Printful\WebhookItem
 */
class ToShipmentsArray implements WebhookConverterInterface
{
    /**
     * @var ShipmentBuilder
     */
    private $shipmentBuilder;

    /**
     * @var ToShipmentParameter
     */
    private $toShipmentParameter;

    /**
     * ToShipment constructor.
     * @param ToShipmentParameter $toShipmentParameter
     * @param ShipmentBuilder     $shipmentBuilder
     */
    public function __construct(
        ToShipmentParameter $toShipmentParameter,
        ShipmentBuilder $shipmentBuilder
    ) {
        $this->toShipmentParameter = $toShipmentParameter;
        $this->shipmentBuilder = $shipmentBuilder;
    }

    /**
     * @param WebhookItem $webhookItem
     * @return iterable
     * @throws NoRecordException|RecordExistException
     */
    public function convert(WebhookItem $webhookItem): iterable
    {
        return $this->shipmentBuilder->build(
            $this->toShipmentParameter->convert($webhookItem)
        );
    }
}
