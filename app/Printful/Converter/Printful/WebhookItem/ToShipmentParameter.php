<?php declare(strict_types=1);

namespace App\Printful\Converter\Printful\WebhookItem;

use App\Exceptions\RecordExistException;
use App\Models\Service\ModelBuilder\Shipment\ItemParameter;
use App\Models\Service\ModelBuilder\Shipment\PackageParameter;
use App\Models\Service\ModelBuilder\ShipmentParameter;
use App\Printful\Configurations\PrintfulConfig;
use App\Printful\Exceptions\PrintfulException;
use App\Printful\Service\PrintfulExternalIdParser;
use Exception;
use Printful\Structures\Order\OrderLineItem;
use Printful\Structures\Webhook\WebhookItem;
use App\Printful\Exceptions\InvalidPrintfulItemException;

/**
 * Class ShippingParameterConverter
 * @package App\Printful\Converter
 */
class ToShipmentParameter implements WebhookConverterInterface
{
    /**
     * Default carrier
     */
    private const DEFAULT_CARRIER = 'USPS';

    /**
     * @var array
     */
    protected $carrierMap = [];

    /**
     * @var PrintfulConfig
     */
    protected $printfulConfig;

    /**
     * @var PrintfulExternalIdParser $externalIdParser
     */
    protected $externalIdParser;

    /**
     * ToOrderCreationParameters constructor.
     * @param PrintfulConfig $printfulConfig
     * @param PrintfulExternalIdParser $externalIdParser
     */
    public function __construct(
        PrintfulConfig $printfulConfig,
        PrintfulExternalIdParser $externalIdParser
    ) {
        $this->printfulConfig = $printfulConfig;
        $this->externalIdParser = $externalIdParser;
    }

    /**
     * @param WebhookItem $webhookItem
     * @return ShipmentParameter
     * @throws RecordExistException
     * @throws Exception
     */
    public function convert(WebhookItem $webhookItem): ShipmentParameter
    {
        $shipment = new ShipmentParameter();
        if (empty($webhookItem->order->externalId)) {
            throw new InvalidPrintfulItemException(
                get_class($webhookItem),
                $webhookItem->rawData,
                'No external ID provided'
            );
        }
        $shipment->orderId = $this->externalIdParser->getLocalOrderId(
            $webhookItem->order->externalId
        );
        $shipmentPackage = $this->packageParameterFromWebhookItem($webhookItem);
        $shipment->addPackage($shipmentPackage);
        $this->addEventItemsToPackage($webhookItem, $shipment, $shipmentPackage);

        return $shipment;
    }

    /**
     * @param WebhookItem       $event
     * @param ShipmentParameter $shipment
     * @param PackageParameter  $package
     * @return ShipmentParameter
     * @throws Exception
     */
    private function addEventItemsToPackage(
        WebhookItem $event,
        ShipmentParameter $shipment,
        PackageParameter $package
    ): ShipmentParameter {
        foreach ($event->shipment->items as $item) {
            $packageItem = new ItemParameter();
            $packageItem->quantity = $item->quantity;
            $orderItem = $this->getOrderItemFromEvent($event, $item->itemId);
            $packageItem->orderItemId = $orderItem->externalId;

            if ($orderItem) {
                $packageItem->sku =  $orderItem->sku;
            }

            $shipment->addItemToPackage($packageItem, $package->packageId);
        }

        return $shipment;
    }

    /**
     * @param WebhookItem $event
     * @param int         $lineItemId
     * @return OrderLineItem
     * @throws PrintfulException
     */
    private function getOrderItemFromEvent(
        WebhookItem $event,
        int $lineItemId
    ): OrderLineItem {
        $orderItem = collect($event->order->items)->filter(
            function ($item) use ($lineItemId) {
                return $item->id === $lineItemId;
            }
        )->first();

        if ($orderItem === null) {
            throw new PrintfulException("Order item referenced by Printful does not exist in order items table");
        }

        return $orderItem;
    }

    /**
     * @param WebhookItem $event
     * @return PackageParameter
     */
    private function packageParameterFromWebhookItem(
        WebhookItem $event
    ): PackageParameter {
        $package = new PackageParameter();
        $package->packageId = $event->shipment->id;
        $package->carrier = $this->getCarrier($event->shipment->carrier);
        $package->trackingNumber = $event->shipment->trackingNumber;
        $package->trackingLink = $event->shipment->trackingUrl;

        return $package;
    }

    /**
     * @param string $carrierName
     * @return string
     */
    private function getCarrier(string $carrierName): string
    {
        return $this->carrierMap[$carrierName]
            ?? $this->carrierMap[$carrierName] = $this->findCarrier($carrierName);
    }

    /**
     * @param string $carrierName
     * @return string
     */
    private function findCarrier(string $carrierName): string
    {
        foreach ($this->printfulConfig->getCarrierExpMap() as $map) {
            if (preg_match(sprintf("/%s/i", $map['exp']), $carrierName)) {
                return $map['carrier'];
            }
        }

        return self::DEFAULT_CARRIER;
    }
}
