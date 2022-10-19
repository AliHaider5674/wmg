<?php declare(strict_types=1);

namespace Tests\Feature\Printful;

use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\OrderItem;
use App\Printful\Enums\PrintfulEventType;
use App\Printful\Models\PrintfulEvent;
use Illuminate\Support\Collection;
use Tests\TestCase;

/**
 * Class PrintfulEventGenerator
 * @package Tests\Feature\Printful
 */
class PrintfulEventGenerator
{
    /**
     * Event status received
     */
    public const EVENT_STATUS_RECEIVED = 0;

    /**
     * Event status processed
     */
    public const EVENT_STATUS_PROCESSED = 1;

    /**
     * Event status error
     */
    public const EVENT_STATUS_ERROR = 2;

    /**
     * State Code Map
     */
    private const STATE_CODE_MAP = [
        'New York' => 'NY',
        'California' => 'CA',
        'Florida' => 'FL',
    ];

    /**
     * Country codes that have required state codes
     */
    private const REQUIRED_STATE_CODE_COUNTRIES = ['JP', 'US', 'CA', 'AU'];

    /**
     * Default State Code If None Found
     */
    private const DEFAULT_STATE_CODE = 'NY';

    /**
     * @var \Faker\Generator
     */
    private $faker;

    /**
     * PrintfulEventGenerator constructor.
     * @param TestCase $testCase
     */
    public function __construct(TestCase $testCase)
    {
        $this->faker = $testCase->getFaker();
    }

    /**
     * @param Order       $order
     * @param string|null $trackingNumber
     * @param string|null $carrier
     * @param string|null $service
     * @param string|null $location
     * @return PrintfulEvent
     */
    public function createReturnEvent(
        Order $order,
        string &$trackingNumber = null,
        string &$carrier = null,
        string &$service = null,
        string &$location = null
    ): PrintfulEvent {
        return $this->createPackageEvent(
            $order,
            PrintfulEventType::PACKAGE_RETURNED,
            'package_returned',
            $trackingNumber,
            $carrier,
            $service,
            $location
        );
    }

    /**
     * @param Order       $order
     * @param string|null $trackingNumber
     * @param string|null $carrier
     * @param string|null $service
     * @param string|null $location
     * @return PrintfulEvent
     */
    public function createShipmentEvent(
        Order $order,
        string &$trackingNumber = null,
        string &$carrier = null,
        string &$service = null,
        string &$location = null
    ): PrintfulEvent {
        return $this->createPackageEvent(
            $order,
            PrintfulEventType::PACKAGE_SHIPPED,
            'package_shipped',
            $trackingNumber,
            $carrier,
            $service,
            $location
        );
    }

    /**
     * @param Order       $order
     * @param string|null $trackingNumber
     * @param string|null $carrier
     * @param string|null $service
     * @param string|null $location
     * @return PrintfulEvent
     */
    public function createHoldEvent(
        Order $order,
        string &$trackingNumber = null,
        string &$carrier = null,
        string &$service = null,
        string &$location = null
    ): PrintfulEvent {
        return $this->createPackageEvent(
            $order,
            PrintfulEventType::ORDER_PUT_HOLD,
            'order_put_hold',
            $trackingNumber,
            $carrier,
            $service,
            $location
        );
    }

    /**
     * @param Order       $order
     * @param int         $eventModelType
     * @param string      $webhookEventType
     * @param string|null $trackingNumber
     * @param string|null $carrier
     * @param string|null $service
     * @param string|null $location
     * @return PrintfulEvent
     */
    private function createPackageEvent(
        Order $order,
        int $eventModelType,
        string $webhookEventType,
        string &$trackingNumber = null,
        string &$carrier = null,
        string &$service = null,
        string &$location = null
    ): PrintfulEvent {
        $printfulEvent = new PrintfulEvent([
            'event_type' => $eventModelType,
            'status' => 0,
            'webhook_item' => json_encode(
                $this->packageEventForOrder(
                    $order,
                    $webhookEventType,
                    $trackingNumber,
                    $carrier,
                    $service,
                    $location
                ),
                JSON_PRETTY_PRINT
            )
        ]);
        $printfulEvent->save();

        return $printfulEvent;
    }

    /**
     * @param Order       $order
     * @param string      $type
     * @param string|null $trackingNumber
     * @param string|null $carrier
     * @param string|null $service
     * @param string|null $location
     * @return array
     */
    public function packageEventForOrder(
        Order $order,
        string $type,
        string &$trackingNumber = null,
        string &$carrier = null,
        string &$service = null,
        string &$location = null
    ): array {
        $orderItems = $this->getPrintfulOrderItems($order);
        $shipment = $this->shipmentForItems(
            $orderItems,
            $trackingNumber,
            $carrier,
            $service,
            $location
        );
        return [
            'data' => [
                'order' => $this->orderToOrderArray(
                    $order,
                    $orderItems
                ),
                'shipment' => $shipment
            ],
            'type' => $type,
            'store' => $this->faker->numberBetween(1, 1000),
            'created' => time(),
            'retries' => $this->faker->numberBetween(0, 10),
        ];
    }

    /**
     * @param Order       $order
     * @param iterable|null  $orderItems
     * @param array       $shipments
     * @param string|null $status
     * @return array
     */
    private function orderToOrderArray(
        Order $order,
        iterable $orderItems = null,
        array $shipments = [],
        string $status = null
    ): array {
        $orderItems = $orderItems !== null
            ? collect($orderItems)
            : $this->getPrintfulOrderItems($order);
        $orderAddress = $order->getShippingAddress();
        $status = $status ?? $this->faker->randomElement([
            'pending', 'failed', 'canceled', 'inprocess',
            'onhold', 'partial', 'fulfilled',
        ]);
        $now = time();

        return [
            'id' => $this->faker->numberBetween(0, 1000),
            'gift' => null,
            'costs' => $this->randomCosts(),
            'error' => null,
            'items' => $orderItems->map([$this, 'orderItemToOrderItemArray'])
                ->toArray(),
            'notes' => null,
            'store' => 0,
            'status' => $status,
            'created' => $now,
            'updated' => $now,
            'shipping' => json_decode($order->custom_attributes ?? '', true)['shipping_method'] ?? null,
            'is_sample' => false,
            'recipient' => $this->getRecipientFromAddress($orderAddress),
            'shipments' => $shipments,
            'activities' => $this->getRandomActivities(),
            'not_synced' => false,
            'external_id' => (string) $order->id,
            'packing_slip' => null,
            'retail_costs' => $this->getRetailCosts($order, $orderItems),
            'dashboard_url' => 'https://www.printful.com/',
            'needs_approval' => false,
            'can_change_hold' => false,
            'estimated_fulfillment' => $now,
            'has_discontinued_items' => false,
        ];
    }

    /**
     * @param OrderItem $orderItem
     * @return array
     */
    public function orderItemToOrderItemArray(OrderItem $orderItem): array
    {
        $variantId = $this->faker->numberBetween(1, 10000);

        return [
            'id' => $this->getPrintfulItemIdFromOrderItem($orderItem),
            'sku' => $orderItem->sku,
            'name' => $orderItem->name,
            'files' => [],
            'price' => number_format($this->faker->randomFloat(2, 5, 20)),
            'options' => [],
            'product' => [
                'name' => $this->faker->name,
                'image' => 'https://www.printful.com/',
                'product_id' => $this->faker->numberBetween(10, 100),
                'variant_id' => $variantId,
            ],
            'quantity' => $orderItem->quantity,
            'variant_id' => $variantId,
            'external_id' => (string) $orderItem->id,
            'discontinued' => false,
            'out_of_stock' => false,
            'retail_price' => $this->asAmount(
                $orderItem->net_amount / $orderItem->quantity
            ),
        ];
    }

    /**
     * @param OrderAddress $orderAddress
     * @return array[]
     */
    private function getRecipientFromAddress(OrderAddress $orderAddress): array
    {
        $recipientArray = [
            'name' => sprintf(
                '%s %s',
                $orderAddress->first_name,
                $orderAddress->last_name
            ),
            'address1' => $orderAddress->address1,
            'address2' => $orderAddress->address2,
            'city' => $orderAddress->city,
            'state_name' => $orderAddress->state,
            'country_code' => $orderAddress->country_code,
            'zip' => $orderAddress->zip,
            'phone' => $orderAddress->phone,
            'email' => $orderAddress->email,
        ];

        if (in_array(
            $recipientArray['country_code'],
            self::REQUIRED_STATE_CODE_COUNTRIES,
            true
        )) {
            $recipientArray['state_code'] = self::STATE_CODE_MAP[$orderAddress->state]
                ?? self::DEFAULT_STATE_CODE;
        }

        return $recipientArray;
    }

    /**
     * @param int $min
     * @param int $max
     * @return string
     */
    private function randomAmount(int $min = 1, int $max = 20): string
    {
        return $this->asAmount($this->faker->randomFloat(2, $min, $max));
    }

    /**
     * @param $number
     * @return string
     */
    private function asAmount($number): string
    {
        return number_format((float) $number, 2);
    }

    /**
     * @param Collection  $orderItems
     * @param string|null $trackingNumber
     * @param string|null $carrier
     * @param string|null $service
     * @param string|null $location
     * @return array
     */
    private function shipmentForItems(
        Collection $orderItems,
        string &$trackingNumber = null,
        string &$carrier = null,
        string &$service = null,
        string &$location = null
    ): array {
        $trackingNumber = $trackingNumber
            ?? '00000' . $this->faker->randomNumber(7);
        $carrier = $carrier ?? 'USPS';
        $service = $service ?? 'USPS Priority Mail';
        $location = $location ?? 'USA';

        $now = time();

        return [
            'id' => $this->faker->numberBetween(1, 1000),
            'items' => $orderItems->map(function (OrderItem $item) {
                return [
                    'picked' => 1,
                    'item_id' => $this->getPrintfulItemIdFromOrderItem($item),
                    'printed' => 1,
                    'quantity' => $item->quantity,
                    'is_started' => true,
                ];
            })->toArray(),
            'status' => 'shipped',
            'carrier' => $carrier,
            'created' => $now,
            'service' => $service,
            'location' => $location,
            'ship_date' => date('Y-m-d'),
            'reshipment' => false,
            'shipped_at' => $now,
            'tracking_url' => 'https://www.printful.com/',
            'tracking_number' => $trackingNumber,
            'packing_slip_url' => 'https://www.printful.com/',
            'estimated_delivery_dates' => [
                'to' => $now + 1000,
                'from' => $now + 100000,
            ],
        ];
    }

    /**
     * Get a printful item id from an OrderItem, as we will need to get the
     * same ID from an OrderItem more than once so this must produce the same
     * unique ID each time the same OrderItem is passed to it.
     *
     * @param OrderItem $orderItem
     * @return int
     */
    private function getPrintfulItemIdFromOrderItem(OrderItem $orderItem): int
    {
        return $orderItem->id * 2;
    }

    /**
     * @param Order      $order
     * @param Collection $orderItems
     * @return array
     */
    private function getRetailCosts(Order $order, Collection $orderItems): array
    {
        $retailCosts = [
            'tax' => $this->asAmount($order->shipping_tax_amount),
            'vat' => $this->asAmount($order->shipping_tax_amount + 6),
            'discount' => "0.00",
            'shipping' => $this->asAmount($order->shipping_net_amount),
            'subtotal' => $this->getSubtotal($orderItems),
        ];

        foreach ($orderItems as $orderItem) {
            $retailCosts['tax'] += (float) $orderItem->tax_amount;
        }

        $retailCosts['total'] = $this->asAmount(array_sum($retailCosts));

        return $retailCosts;
    }

    /**
     * @param Collection $orderItems
     * @return float
     */
    private function getSubtotal(Collection $orderItems): float
    {
        // Make sure to divide as it might be rounded up or down and then multiply the retail
        // price by the quantity so that we have the same number Printful would send
        return $orderItems->reduce(
            function (float $carry, OrderItem $orderItem) {
                return $carry + (
                    $this->asAmount(
                        $orderItem->net_amount
                        / $orderItem->quantity
                    ) * $orderItem->quantity
                );
            },
            0.00
        );
    }

    /**
     * Random costs
     *
     * @return string[]
     */
    private function randomCosts(): array
    {
        return [
            'tax' => $this->randomAmount(),
            'vat' => $this->randomAmount(),
            'total' => $this->randomAmount(),
            'discount' => $this->randomAmount(),
            'shipping' => $this->randomAmount(),
            'subtotal' => $this->randomAmount(),
            'digitization' => $this->randomAmount(),
            'additional_fee' => $this->randomAmount(),
            'fulfillment_fee' => $this->randomAmount(),
        ];
    }

    /**
     * @return array[]
     */
    private function getRandomActivities(): array
    {
        $now = time();
        return [
            [
                'note' => null,
                'time' => $now,
                'type' => 'started',
                'message' => 'Fulfillment was started',
            ],
            [
                'note' => null,
                'time' => $now,
                'type' => 'transaction',
                'message' => 'Printful Wallet (via Credit Card] was charged for $100.00',
            ],
            [
                'note' => null,
                'time' => $now,
                'type' => 'created',
                'message' => 'Order placed automatically via Shopify',
            ],
        ];
    }

    /**
     * @param Order $order
     * @return Collection
     */
    private function getPrintfulOrderItems(Order $order): Collection
    {
        return $order->orderItems()->sourceId('PF')->whereIn(
            'item_type',
            OrderItem::ALL_PHYSICAL_TYPES
        )->get();
    }
}
