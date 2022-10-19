<?php

namespace Tests;

use App\ArgumentValidator\Facades\ArgumentValidator;
use App\ArgumentValidator\TypeConstants;
use App\Core\Services\ClientService;
use App\Mdc\Service\Event\MdcClient;
use App\Mdc\Service\Event\SoapClientManager;
use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\OrderItem;
use App\Models\Service;
use App\Models\ServiceEvent;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Collection;
use League\OAuth2\Client\Token\AccessToken;
use Mockery as M;
use App\Mdc\Clients\SoapClient;
use App\Models\Service\Event\ClientHandler\HandlerAbstract;

/**
 * Class Helper
 * @package Tests
 * @SuppressWarnings(PHPMD)
 * @todo reenable PHPMD
 */
class Helper
{
    /**
     * Test M1 Soap URL
     */
    public const M1_SOAP_TEST_URL = 'https://m1-soap-api/wsdl';

    /**
     * Test M1 Soap Username
     */
    public const M1_SOAP_TEST_USERNAME = 'test-username';

    /**
     * Test M1 Soap API Key
     */
    public const M1_SOAP_TEST_API_KEY = 'test-api-key';

    /**
     * Test M1 Soap Token returned from login
     */
    public const M1_SOAP_TOKEN = 'soap-token';

    /**
     * @var TestCase
     */
    protected $testCase;

    /**
     * Helper constructor.
     *
     * @param TestCase $testCase
     */
    public function __construct(TestCase $testCase)
    {
        $this->testCase = $testCase;
    }

    /**
     * Seed order with order items in order to assure randomness
     *
     * @param int   $orderCount
     * @param int   $orderItemCount
     * @param array $orderData
     * @param array $orderItemData
     * @return Collection
     */
    public function ordersWithItems(
        int $orderCount = 5,
        int $orderItemCount = 5,
        array $orderData = [],
        array $orderItemData = []
    ): Collection {
        return $this->orders($orderData, $orderCount)->each(
            function (Order $order) use ($orderItemData, $orderItemCount) {
                $orderItemData['parent_id'] = $order->id;
                $this->orderItems($orderItemData, $orderItemCount);
            }
        );
    }

    /**
     * Seed order with order items in order to assure randomness
     *
     * @param int   $orderItemCount
     * @param array $orderData
     * @param array $orderItemData
     * @return Order
     */
    public function orderWithItems(
        int $orderItemCount = 5,
        array $orderData = [],
        array $orderItemData = []
    ): Order {
        $order = $this->order($orderData);
        $orderItemData['parent_id'] = $order->id;
        $this->orderItems($orderItemData, $orderItemCount);

        return $order;
    }

    /**
     * Create an Order with the attributes provided
     *
     * @param array $attributes
     * @return Order
     */
    public function createOrder(array $attributes = []): Order
    {
        return Order::factory()->create($attributes);
    }

    /**
     * Create an Order with the attributes provided
     *
     * @param array $attributes
     * @param int   $count
     * @return Order[]|Collection
     */
    public function createOrders(
        array $attributes = [],
        int $count = 5
    ): Collection {
        return Order::factory()->count($count)->create($attributes);
    }

    /**
     * Create an OrderItem with the attributes provided
     *
     * @param array $attributes
     * @return OrderItem
     */
    public function createOrderItem(array $attributes = []): OrderItem
    {
        if (!isset($attributes['parent_id'])) {
            $attributes['parent_id'] = $this->order()->id;
        }

        return OrderItem::factory()->create($attributes);
    }

    /**
     * Create an OrderItem with the attributes provided
     *
     * @param array $attributes
     * @param int   $count
     * @return OrderItem[]|Collection
     */
    public function createOrderItems(
        array $attributes = [],
        int $count = 5
    ): Collection {
        if (!isset($attributes['parent_id'])) {
            $attributes['parent_id'] = $this->order()->id;
        }

        return OrderItem::factory()->count($count)->create($attributes);
    }

    /**
     * Create an OrderAddress with the attributes provided
     *
     * @param array $attributes
     * @return OrderAddress
     */
    public function createOrderAddress(array $attributes = []): OrderAddress
    {
        if (!isset($attributes['parent_id'])) {
            $attributes['parent_id'] = $this->order()->id;
        }

        return OrderAddress::factory()->create($attributes);
    }

    /**
     * Find an Order matching the parameters provided or make one if none exist
     *
     * @param array $params
     * @return Order
     */
    public function order(array $params = []): Order
    {
        $query = Order::query();

        foreach ($params as $key => $value) {
            $query = $query->where($key, $value);
        }

        return $query->inRandomOrder()->first()
            ?? $this->createOrder($params);
    }


    /**
     * Find an Order matching the parameters provided or make one if none exist
     *
     * @param array $params
     * @param int   $count
     * @return Order[]|Collection
     */
    public function orders(array $params = [], int $count = 5): Collection
    {
        $query = Order::query();

        foreach ($params as $key => $value) {
            $query = $query->where($key, $value);
        }

        $orders = $query->inRandomOrder()->take($count)->get();
        $ordersLeft = $count - $orders->count();

        return $ordersLeft > 0
            ? $orders->concat($this->createOrders($params, $ordersLeft))
            : $orders;
    }

    /**
     * Find an OrderAddress matching the parameters provided or make one if none
     * exist
     *
     * @param array $params
     * @return OrderItem
     */
    public function orderItem(array $params = []): OrderItem
    {
        $query = OrderItem::query();

        foreach ($params as $key => $value) {
            $query = $query->where($key, $value);
        }

        return $query->inRandomOrder()->first()
            ?? $this->createOrderItem($params);
    }

    /**
     * Find an OrderAddress matching the parameters provided or make one if none
     * exist
     *
     * @param array $params
     * @param int   $count
     * @return Collection
     */
    public function orderItems(array $params = [], int $count = 5): Collection
    {
        $query = OrderItem::query();

        foreach ($params as $key => $value) {
            $query = $query->where($key, $value);
        }

        $orderItems = $query->inRandomOrder()->take($count)->get();
        $orderItemsLeft = $count - $orderItems->count();

        return $orderItemsLeft > 0
            ? $orderItems->concat(
                $this->createOrderItems($params, $orderItemsLeft)
            ) : $orderItems;
    }


    /**
     * Find an OrderAddress matching the parameters provided or make one if none
     * exist
     *
     * @param array $params
     * @return OrderAddress
     */
    public function orderAddress(array $params = []): OrderAddress
    {
        $query = OrderAddress::query();

        foreach ($params as $key => $value) {
            $query = $query->where($key, $value);
        }

        return $query->inRandomOrder()->first()
            ?? $this->createOrderAddress($params);
    }

    /**
     * @param array|string $countryCodes
     * @param bool         $caseSensitive
     * @return string
     */
    public function fakerCountryCodeOtherThan($countryCodes, bool $caseSensitive = false): string
    {
        ArgumentValidator::assureType($countryCodes, [
            TypeConstants::ARRAY,
            TypeConstants::STRING
        ]);

        return $this->fakerAttributeThatDoesNotEqual(
            'countryCode',
            $countryCodes,
            $caseSensitive
        );
    }

    /**
     * @param string      $attribute
     * @param mixed|array $values
     * @param bool        $caseSensitive
     * @return mixed
     */
    public function fakerAttributeThatDoesNotEqual(
        string $attribute,
        $values,
        bool $caseSensitive = false
    ) {
        $caseNormalizer = static function ($value) use ($caseSensitive) {
            return $caseSensitive ? $value : strtolower($value);
        };

        if (!is_array($values)) {
            $values = [$values];
        }

        if (!$caseSensitive) {
            $values = array_map($caseNormalizer, $values);
        }

        do {
            $attributeValue = $this->testCase->getFaker()->$attribute();
        } while (in_array($caseNormalizer($attributeValue), $values, true));

        return $attributeValue;
    }

    /**
     * @param string|HandlerAbstract[] $handlers
     * @return M\LegacyMockInterface|M\MockInterface|SoapClient
     * @throws BindingResolutionException
     */
    public function mockM1SoapClient(iterable $handlers)
    {
        $m1SoapClient = M::mock(SoapClient::class);
        $m1SoapClient->allows([
            'newToken' => self::M1_SOAP_TOKEN,
            'getToken' => self::M1_SOAP_TOKEN,
            'setToken' => null,
            'config'   => null
        ]);
        $m1SoapClient->shouldReceive('salesOrderShipmentAddTrack')->andReturn('message');
        $m1SoapClient->shouldReceive('salesOrderShipmentSendInfo')->andReturn('message');
        $m1SoapClient->shouldReceive('login')->withArgs([
            self::M1_SOAP_TEST_USERNAME,
            self::M1_SOAP_TEST_API_KEY,
        ])->andReturns(self::M1_SOAP_TOKEN);

        $manager = M::mock(SoapClientManager::class)->makePartial();
        $manager->shouldReceive('getClient')->andReturn($m1SoapClient);
        $this->testCase->getApp()->bind(SoapClientManager::class, function ($app) use ($manager) {
            return $manager;
        });
        $this->testCase->getApp()->bind(SoapClient::class, function () use ($m1SoapClient) {
            return $m1SoapClient;
        });

        $mdcClient = $this->testCase->getApp()->make(MdcClient::class);

        foreach ($handlers as $handler) {
            if ($handler instanceof HandlerAbstract) {
                $mdcClient->addHandler($handler);

                continue;
            }

            if (is_string($handler)) {
                $this->testCase->getApp()->make($handler);

                continue;
            }

            throw new \RuntimeException("You must pass an array of class names or instances of HandlerAbstracts");
        }

        /** @var MdcClient $mdcClient */
        $clientManager = $this->testCase->getApp()->make(ClientService::class);
        $clientManager->addClient($mdcClient);

        $this->testCase->getApp()->singleton(
            ClientService::class,
            function () use ($clientManager) {
                return $clientManager;
            }
        );

        return $m1SoapClient;
    }

    /**
     * Set up the M1 service along with the M1 service events.
     *
     * @param array|null $events
     * @return $this
     */
    public function setUpMdcServiceEvents(array $events = null): self
    {
        $service = Service::where('app_id', 'm1')->first();

        if ($service === null) {
            $service = Service::create(
                [
                    'app_id' => 'm1',
                    'app_url' => '',
                    'name' => 'm1',
                    'event_rules' => json_encode(['sales_channel' => '^M113US']),
                    'client' => 'm1',
                    'addition' => json_encode([
                        "wsdl" => self::M1_SOAP_TEST_URL,
                        "username" => self::M1_SOAP_TEST_USERNAME,
                        "api_key" => self::M1_SOAP_TEST_API_KEY,
                    ]),
                    'status' => 1,
                ]
            );
        }

        foreach ([
            ['parent_id' => $service->id, 'status' => 1, 'event' => 'mom.order.action.created'],
            ['parent_id' => $service->id, 'status' => 1, 'event' => 'source.update'],
            ['parent_id' => $service->id, 'status' => 1, 'event' => 'item.warehouse.received'],
            ['parent_id' => $service->id, 'status' => 1, 'event' => 'item.shipped'],
            ['parent_id' => $service->id, 'status' => 1, 'event' => 'item.warehouse.hold'],
            ['parent_id' => $service->id, 'status' => 1, 'event' => 'item.warehouse.returned'],
        ] as $eventData) {
            if (($events === null || in_array($eventData['event'], $events, true))
                && !ServiceEvent::where('event', $eventData['event'])->exists()
            ) {
                ServiceEvent::create($eventData);
            }
        }

        return $this;
    }
}
