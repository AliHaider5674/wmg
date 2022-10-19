<?php declare(strict_types=1);

namespace Tests\Feature\Printful;

use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\OrderItem;
use App\Models\Service;
use App\Printful\Models\PrintfulEvent;
use Exception;
use Faker\Generator;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Collection;
use Tests\Helper;
use Tests\TestCase;
use Mockery as M;

/**
 * Class Helper
 * @package Tests
 */
class PrintfulApiHelper
{
    /**
     * Country codes that have required state codes
     */
    private const REQUIRED_STATE_CODE_COUNTRIES = ['JP', 'US', 'CA', 'AU'];

    /**
     * Default State Code If None Found
     */
    private const DEFAULT_STATE_CODE = 'NY';

    /**
     * @var Application
     */
    protected $app;

    /**
     * @var Generator
     */
    protected $faker;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var array
     */
    protected $stateCodeMap;

    const PRINTFUL_COUNTRIES_FILE = __DIR__ . '/json/printfulCountries.json';

    /**
     * Helper constructor.
     *
     * @param TestCase $testCase
     */
    public function __construct(TestCase $testCase)
    {
        $this->app = $testCase->getApp();
        $this->helper = $testCase->getHelper();
        $this->faker = $testCase->getFaker();
    }

    /**
     * @return $this
     * @throws Exception
     */
    public function clearPrintfulOrders(): self
    {
        OrderItem::sourceId('PF')->delete();

        return $this;
    }

    /**
     * @param int|null $eventType
     * @param int|null $eventStatus
     * @return $this
     */
    public function clearPrintfulEvents(
        int $eventType = null,
        int $eventStatus = null
    ): self {
        $query = PrintfulEvent::query();

        if ($eventType !== null) {
            $query = $query->where('event_type', $eventType);
        }

        if ($eventStatus !== null) {
            $query = $query->where('status', $eventStatus);
        }

        $query->delete();

        return $this;
    }

    /**
     * Create order by order attributes and get arguments for creating an order
     * with the PrintfulAPIClient
     *
     * @param array $orderAttributes
     * @param array $orderAddressAttributes
     * @param array $orderItemsAttributes
     * @return array
     */
    public function getCreateOrderArgumentsByAttributes(
        array $orderAttributes = [],
        array $orderAddressAttributes = [],
        array $orderItemsAttributes = []
    ): array {
        $order = $this->createPrintfulOrder(
            $orderAttributes,
            $orderAddressAttributes,
            $orderItemsAttributes
        );

        return $this->getCreateOrderArguments($order);
    }

    /**
     * Get PrintfulAPIClient arguments to create an order
     *
     * @param Order|null $order
     * @return array
     */
    public function getCreateOrderArguments(Order $order = null): array
    {
        $orderAddress = $order->getShippingAddress();
        $orderItems = $order->orderItems()->sourceId('PF')->shippable()->get();

        $data = [
            'external_id' => sprintf('%s-%d', $order->order_id, $order->id),
            'shipping' => $order->getCustomAttribute('printful_shipping_code', null),
            'recipient' => [
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
            ],
            'retail_costs' => [
                'discount' => "0.00",
                'shipping' => $this->asAmount($order->shipping_net_amount),
                'tax' => $this->asAmount($order->shipping_tax_amount),
            ],
            'gift' => null,
            'packing_slip' => null,
            'currency' => 'USD',
            'items' => [],
        ];

        if (in_array(
            $data['recipient']['country_code'],
            self::REQUIRED_STATE_CODE_COUNTRIES,
            true
        )) {
            $data['recipient']['state_code'] = $this->getStateCode($orderAddress->country_code, $orderAddress->state);
        }

        foreach ($orderItems as $orderItem) {
            $data['retail_costs']['tax'] += (float) $orderItem->tax_amount;

            $data['items'][] = [
                'external_id' => $orderItem->id,
                'variant_id' => null,
                'quantity' => (int) $orderItem->quantity,
                'retail_price' => $this->asAmount(
                    $orderItem->net_amount / $orderItem->quantity
                ),
                'name' => $orderItem->name,
                'sku' => $orderItem->sku,
                'options' => [],
                'external_variant_id' => json_decode(
                    $orderItem->custom_attributes,
                    true
                )['printful_variant_id'],
            ];
        }

        $data['retail_costs']['tax'] = $this->asAmount(
            $data['retail_costs']['tax']
        );

        return [
            'data' => $data,
            'path' => 'orders',
            'params' => ['confirm' => false],
        ];
    }

    /**
     * @return array
     */
    public function getCreateOrderErrorResponse()
    {
        return [
            'code' => 400,
            'result' => 'There was an error',
        ];
    }
    /**
     * Get a PrintfulApiClient response for creating an order with certain
     * arguments
     *
     * @param array $createOrderArguments
     * @return array
     */
    public function getCreateOrderResponse(
        array $createOrderArguments
    ): array {
        $now = time();

        $items = array_map(
            [$this, 'getCreateOrderResponseOrderItem'],
            $createOrderArguments['data']['items']
        );

        $subtotal = $this->asAmount(
            array_reduce($items, static function ($carry, $item) {
                return $carry + $item['price'];
            }, 0.00)
        );

        $response = [
            'id' => $this->faker->numberBetween(100, 10000),
            'external_id' => $createOrderArguments['data']['external_id'],
            'status' => config('printful.order.confirm'),
            'shipping' => $createOrderArguments['data']['shipping'],
            'created' => $now,
            'updated' => $now,
            'recipient' => $createOrderArguments['data']['recipient'],
            'items' => $items,
            'costs' => [
                'subtotal' => $subtotal,
                'discount' => '0.00',
                'shipping' => $this->faker->randomFloat(2, 5, 10),
                'tax' => $this->faker->randomFloat(2, 1, 5),
            ],
            'retail_costs' => $this->getRetailCosts($createOrderArguments),
            'shipments' => [],
        ];

        $response['costs']['total'] = $this->asAmount(
            array_sum($response['costs'])
        );

        return $response;
    }

    /**
     * Get response
     *
     * @param array $itemFromExpectedArguments
     * @return array
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function getCreateOrderResponseOrderItem(
        array $itemFromExpectedArguments
    ): array {
        $variantId = $this->faker->numberBetween(100, 1000);

        $name = sprintf(
            '%s, %s/%s',
            $this->faker->word,
            $this->faker->colorName,
            strtoupper($this->faker->randomLetter)
        );

        return [
            'id' => $this->faker->numberBetween(100, 10000),
            'external_id' => $itemFromExpectedArguments['external_id'],
            'variant_id' => $variantId,
            'quantity' => (int) $itemFromExpectedArguments['quantity'],
            'price' => $this->asAmount($this->faker->randomFloat(2, 5, 20)),
            'retail_price' => $this->asAmount(
                $itemFromExpectedArguments['retail_price']
            ),
            'name' => $name,
            'sku' => null,
            'product' => [
                'variant_id' => $variantId,
                'product_id' => $this->faker->numberBetween(100, 1000),
                'image' => $this->faker->url,
                'name' => $name,
            ],
            'files' => [$this->getExampleFile()],
            'options' => [],
        ];
    }

    /**
     * Get a random file array
     *
     * @return array
     */
    private function getExampleFile(): array
    {
        return [
            'id' => $this->faker->numberBetween(100, 10000),
            'type' => 'default',
            'hash' => null,
            'url' => $this->faker->url,
            'filename' => sprintf(
                '%s.%s',
                $this->faker->word,
                $this->faker->randomElement(['jpg', 'png', 'jpeg'])
            ),
            'mime_type' => $this->faker->mimeType,
            'size' => 1,
            'width' => $this->faker->numberBetween(100, 1000),
            'height' => $this->faker->numberBetween(100, 1000),
            'dpi' => 150,
            'status' => 'waiting',
            'created' => time(),
            'thumbnail_url' => $this->faker->url,
            'preview_url' => $this->faker->url,
            'visible' => true,
        ];
    }

    /**
     * Get retail costs array
     *
     * @param array $createOrderArguments
     * @return array
     */
    private function getRetailCosts(array $createOrderArguments): array
    {
        $retailCosts = $createOrderArguments['data']['retail_costs'];

        $retailCosts['subtotal'] = (string) array_reduce(
            $createOrderArguments['data']['items'],
            static function ($carry, $item) {
                return $carry + ($item['quantity'] * $item['retail_price']);
            },
            0.00
        );

        $retailCosts['total'] = $this->asAmount(array_sum($retailCosts));

        return $retailCosts;
    }

    /**
     * Get Fulfillment ack data from order items
     *
     * @param Collection  $orderItems
     * @param string|null $reason
     * @param string      $status
     * @param bool        $backorder
     * @return array
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function getFulfillmentAckDataFromOrderItems(
        Collection $orderItems,
        string $reason = null,
        string $status = "RECEIVEDBYLOGISTICS",
        bool $backorder = false
    ): array {
        return $orderItems->map(
            function (OrderItem $orderItem) use ($reason, $status, $backorder) {
                return [
                    "status" => $status,
                    "quantity" => $orderItem->quantity,
                    "sku" => $orderItem->sku,
                    "name" => $orderItem->name,
                    "parent_order_line_number" => null,
                    "status_reason" => $reason,
                    "status_date" => null,
                    "backorder_quantity" => $backorder ? $orderItem->quantity : 0,
                    "order_line_id" => $orderItem->order_line_id,
                    "order_line_number" => $orderItem->order_line_number,
                    "item_type" => $orderItem->item_type,
                    "custom_details" => null,
                    "image_url" => null,
                    "product_url" => null,
                    "order_line_price" => null,
                    "order_line_promotions_info" => null,
                ];
            }
        )->toArray();
    }

    /**
     * Create a printful order with an address and items
     *
     * @param array $orderAttributes
     * @param array $orderAddressAttributes
     * @param array $orderItemsAttributes
     * @return Order
     */
    public function createPrintfulOrder(
        array $orderAttributes = [],
        array $orderAddressAttributes = [],
        array $orderItemsAttributes = []
    ): Order {
        $order = $this->helper->createOrder($orderAttributes);

        $orderAddressAttributes['parent_id'] = $order->id;
        $this->helper->createOrderAddress($orderAddressAttributes);
        $tax = $order->getAttribute('shipping_tax_amount');
        foreach ($orderItemsAttributes as $orderItemAttributes) {
            $item = $this->helper->createOrderItem(
                array_merge($orderItemAttributes, [
                    'parent_id' => $order->id,
                ])
            );
            $tax += $item->getAttribute('tax_amount');
        }
        $attributes = $order->getCustomAttributes();
        $attributes[] = ['name' => 'order_tax_amount', 'value' => $tax];
        $attributes[] = ['name' => 'printful_shipping_code', 'value' => 'STANDARD'];
        $order->setCustomAttributes($attributes);
        $order->save();
        return $order;
    }

    /**
     * @param int|float|string $number
     * @return string
     */
    private function asAmount($number): string
    {
        return number_format((float) $number, 2);
    }

    /**
     * @param string $country
     * @param string $stateCode
     * @return string
     */
    private function getStateCode(string $country, string $stateCode): string
    {

        if (!isset($this->stateCodeMap)) {
            $countries = json_decode(file_get_contents(self::PRINTFUL_COUNTRIES_FILE), true);
            $this->stateCodeMap = [];
            foreach ($countries as $c) {
                if (empty($c['states'])) {
                    continue;
                }
                $this->stateCodeMap[$c['code']] = [];
                foreach ($c['states'] as $state) {
                    $this->stateCodeMap[$c['code']][$state['name']] = $state['code'];
                }
            }
        }

        $state = self::DEFAULT_STATE_CODE;
        if (isset($this->stateCodeMap[$country])) {
            $state = $this->stateCodeMap[$country][$stateCode] ?? self::DEFAULT_STATE_CODE;
        }
        return $state;
    }

    /**
     * @return array
     */
    public function getPrintfulCountries(): array
    {
        return json_decode(file_get_contents(self::PRINTFUL_COUNTRIES_FILE), true);
    }
}
