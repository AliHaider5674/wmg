<?php declare(strict_types=1);

namespace Tests\Unit\Printful\Converter\RawData\Order;

use App\Core\Models\RawData\Order;
use App\Core\Models\RawData\OrderAddress;
use App\Core\Models\RawData\OrderItem;
use App\Printful\Converter\Local\Order\ToOrderCreationParameters;
use App\Printful\Converter\Local\OrderAddress\ToRecipientCreationParameters;
use App\Printful\Converter\Local\OrderItem\ToOrderItemCreationParameters;
use App\Printful\Exceptions\PrintfulException;
use App\Printful\Service\PrintfulExternalIdParser;
use App\Printful\Structures\OrderItemCreationParameters;
use Generator;
use App\Printful\Structures\RecipientCreationParameters;
use Tests\TestCase;
use Mockery as M;

/**
 * Class ToOrderCreationParametersTest
 * @package Tests\Unit\Printful
 */
class ToOrderCreationParametersTest extends TestCase
{
    /**
     * Order data array
     */
    private const ORDER_DATA = [
        [
            'id' => 19443,
            'status' => 0,
            'sales_channel' => 'M1_US_Warner',
            'request_id' => 1858320,
            'order_id' => '0008825632476236',
            'gift_message' => null,
            'drop_id' => 2389576,
            'shipping_method' => 'STANDARD',
            'customer_id' => 2983746,
            'customer_reference' => null,
            'vat_country' => null,
            'custom_attributes' => [],
            'shipping_gross_amount' => 4.63,
            'shipping_net_amount' => 4.10,
            'shipping_tax_amount' => 0.53,
            'customer_name' => 'John Smith',
            'store_name' => 'Warner Music Store',
            'currency' => 'USD',
        ],
        [
            'id' => 43921,
            'status' => 1,
            'sales_channel' => 'M113_EU_store',
            'request_id' => 30472,
            'order_id' => '0008885639321136',
            'gift_message' => null,
            'drop_id' => 2389576,
            'shipping_method' => 'EXPRESS',
            'customer_id' => 194920,
            'customer_reference' => null,
            'vat_country' => null,
            'custom_attributes' => [
                'tax_id' => 'CPF/CNPJ:123452678-90'
            ],
            'shipping_gross_amount' => 48.48,
            'shipping_net_amount' => 5.82,
            'shipping_tax_amount' => 0.64,
            'customer_name' => 'Susan Mitchell',
            'store_name' => 'EU Store - M1',
            'currency' => 'EUR',
        ],
    ];

    /**
     * Basic order item data necessary for calculating fields on the order
     */
    private const ORDER_ITEM_DATA = [
        'name_format' => 'Order Item %d',
        'tax' => 0.5,
        'count' => 5,
    ];

    /**
     * Shipping Type
     */
    private const BILLING_TYPE = 'billing';

    /**
     * Currency
     */
    private const CURRENCY = 'USD';

    /**
     * Billing Type
     */
    private const SHIPPING_TYPE = 'shipping';

    /**
     * @var ToOrderItemCreationParameters|M\LegacyMockInterface|M\MockInterface
     */
    private $orderItemConverter;

    /**
     * @var ToRecipientCreationParameters|M\LegacyMockInterface|M\MockInterface
     */
    private $orderAddressConverter;

    /**
     * @var ToOrderCreationParameters
     */
    private $orderConverter;

    /**
     * Set up tests
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->orderItemConverter = M::mock(ToOrderItemCreationParameters::class);
        $this->orderAddressConverter = M::mock(ToRecipientCreationParameters::class);
        $printfulExternalIdParser = new PrintfulExternalIdParser();

        $this->orderConverter = new ToOrderCreationParameters(
            $this->orderItemConverter,
            $this->orderAddressConverter,
            $printfulExternalIdParser
        );
    }

    /**
     * Test convert method
     *
     * @param Order $order
     * @param array $orderData
     * @param array $orderItems
     * @param array $recipient
     * @throws PrintfulException
     * @group        Printful
     * @group        Converter
     * @dataProvider orderDataProvider
     */
    public function testConvert(
        Order $order,
        array $orderData,
        array $orderItems,
        array $recipient
    ): void {
        $orderItemsParameters = [];

        foreach ($orderItems as $orderItemArray) {
            $orderItemsParameters[] = $orderItemArray['order_item_parameters'];
            $orderItem = $orderItemArray['order_item'];

            $this->orderItemConverter
                ->shouldReceive('convert')
                ->once()
                ->with($orderItem)
                ->andReturn($orderItemArray['order_item_parameters']);
        }

        $shippingAddress = $recipient['address'];
        $recipientParameters = $recipient['recipient_parameters'];

        $this->orderAddressConverter
            ->shouldReceive('convert')
            ->once()
            ->with($shippingAddress)
            ->andReturn($recipientParameters);

        $orderParameters = $this->orderConverter->convert($order);

        self::assertSame(
            sprintf(
                '%s-%d',
                $orderData['order_id'],
                $orderData['id']
            ),
            $orderParameters->externalId
        );
        self::assertSame($orderData['shipping_method'], $orderParameters->shipping);
        self::assertSame($orderData['currency'], $orderParameters->currency);
        self::assertSame($recipient['recipient_parameters'], $orderParameters->getRecipient());
        self::assertEqualsCanonicalizing(
            $orderItemsParameters,
            $orderParameters->getItems()
        );
        $paramRetailCosts = $orderParameters->getRetailCosts();
        $paramRetailCostDetail = [
            'discount' => (float)$paramRetailCosts['discount'],
            'shipping' => (float)$paramRetailCosts['shipping'],
            'tax' => (float)$paramRetailCosts['tax'],
        ];
        self::assertEqualsCanonicalizing(
            $orderData['retail_costs'],
            $paramRetailCostDetail
        );
    }

    /**
     * Order data provider
     *
     * @return Generator
     */
    public function orderDataProvider(): Generator
    {
        foreach (self::ORDER_DATA as $orderData) {
            $order = new Order();
            $order->id = $orderData['id'];
            $order->status = $orderData['status'];
            $order->salesChannel = $orderData['sales_channel'];
            $order->requestId = $orderData['request_id'];
            $order->orderId = $orderData['order_id'];
            $order->giftMessage = $orderData['gift_message'];
            $order->dropId = $orderData['drop_id'];
            $order->shippingMethod = $orderData['shipping_method'];
            $order->customerId = $orderData['customer_id'];
            $order->customerReference = $orderData['customer_reference'];
            $order->vatCountry = $orderData['vat_country'];
            $order->customAttributes = $orderData['custom_attributes'];
            $order->shippingGrossAmount = $orderData['shipping_gross_amount'];
            $order->shippingNetAmount = $orderData['shipping_net_amount'];
            $order->shippingTaxAmount = $orderData['shipping_tax_amount'];
            $order->items = [];

            $orderItemsArguments = [];
            $itemTax = 0;
            for ($i = 0; $i < self::ORDER_ITEM_DATA['count']; $i++) {
                $thisOrderItem = new OrderItem();
                $thisOrderItem->name = $this->getOrderItemName($i);
                $thisOrderItem->taxAmount = self::ORDER_ITEM_DATA['tax'];
                $thisOrderItem->currency = $orderData['currency'];
                $order->items[] = $thisOrderItem;

                $orderItemParameters = new OrderItemCreationParameters();
                $orderItemParameters->setName($thisOrderItem->name);

                $orderItemsArguments[] = [
                    'order_item' => $thisOrderItem,
                    'order_item_parameters' => $orderItemParameters,
                ];
                $itemTax += $thisOrderItem->taxAmount;
            }

            $order->shippingAddress = new OrderAddress();
            $order->shippingAddress->customerAddressType = self::SHIPPING_TYPE;
            $orderRecipient = new RecipientCreationParameters();
            $orderRecipient->name = $orderData['customer_name'];
            $order->billingAddress = new OrderAddress();
            $order->billingAddress->customerAddressType = self::BILLING_TYPE;
            /** @var OrderAddress */
            $order->customerName = $orderData['customer_name'];
            $order->storeName = $orderData['store_name'];
            $recipient = [
                'address' => $order->shippingAddress,
                'recipient_parameters' => $orderRecipient,
            ];

            $orderData['retail_costs'] = $this->getRetailCosts($orderData);
            $order->customAttributes['order_tax_amount'] = $order->shippingTaxAmount + $itemTax;
            yield [
                $order,
                $orderData,
                $orderItemsArguments,
                $recipient
            ];
        }
    }

    /**
     * Get order item name by index
     *
     * @param int $number
     * @return string
     */
    private function getOrderItemName(int $number): string
    {
        return sprintf(self::ORDER_ITEM_DATA['name_format'], $number + 1);
    }

    /**
     * Get retail costs of order
     *
     * @param array $orderData
     * @return array
     */
    private function getRetailCosts(array $orderData): array
    {
        return [
            'discount' => 0,
            'shipping' => $orderData['shipping_net_amount'],
            'tax' => $this->getOrderTotalTax($orderData['shipping_tax_amount']),
        ];
    }

    /**
     * Get the total tax for the order
     *
     * @param float $shippingTax
     * @return float
     */
    private function getOrderTotalTax(float $shippingTax): float
    {
        return (
            self::ORDER_ITEM_DATA['count'] * self::ORDER_ITEM_DATA['tax']
        ) + $shippingTax;
    }
}
