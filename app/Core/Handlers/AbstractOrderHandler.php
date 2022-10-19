<?php

namespace App\Core\Handlers;

use App\Core\Handlers\IO\IOInterface;
use App\Core\Services\Converters\OrderRawConverterService;
use App\Core\Services\OrderRawMapperService;
use App\Models\Order;
use App\Models\OrderLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use App\Exceptions\ConfigException;

/**
 * Class AbstractOrderHandler
 * @category WMG
 * @package  App\Models\Warehouse\Handler
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License
 * @link     http://www.wmg.com
 */
abstract class AbstractOrderHandler extends AbstractHandler
{
    public const CONFIG_SIZE = 'size';

    public const CONFIG_SOURCE = 'source_ids';

    public const DEFAULT_ORDER_LIMIT = 800;

    /**
     * @var int
     */
    private $size = self::DEFAULT_ORDER_LIMIT;

    /**
     * Source Id filter used to select orders to drop
     * @var array
     */
    protected $sourceIdFilter = [];

    /**
     * Order Status filter used to select orders to drop
     * @var array
     */
    protected $orderStatusesFilter = [Order::STATUS_RECEIVED, Order::STATUS_ERROR];

    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $sourceIds = [];

    /**
     * @var OrderRawMapperService
     */
    protected $orderRawMapperService;

    /**
     * @var OrderRawConverterService
     */
    protected $orderRawConverterService;

    /**
     * AbstractOrderHandler constructor.
     *
     * @param array                    $config    Handler configuration
     * @param IOInterface          $ioAdapter IO Adapter
     * @param OrderRawMapperService    $orderDataMapperService
     * @param OrderRawConverterService $orderRawConverterService
     * @param Log                      $logger
     */
    public function __construct(
        IOInterface $ioAdapter,
        OrderRawMapperService $orderDataMapperService,
        OrderRawConverterService $orderRawConverterService,
        Log $logger,
        array $config = []
    ) {
        parent::__construct($ioAdapter, $logger);

        $this->processConfig($config);
        $this->orderRawMapperService = $orderDataMapperService;
        $this->orderRawConverterService = $orderRawConverterService;
    }

    /**
     * Process configuration from args
     *
     * @param $config
     *
     * @return void
     */
    private function processConfig(array $config)
    {
        if (isset($config[self::CONFIG_SIZE])) {
            $this->setSize($config[self::CONFIG_SIZE]);
        }

        if (isset($config[self::CONFIG_SOURCE])) {
            $this->sourceIds = $config[self::CONFIG_SOURCE];
        }
    }

    /**
     *
     * @return array
     * @throws \App\Exceptions\ConfigException
     */
    protected function getSourceIds()
    {
        if (empty($this->sourceIds)) {
            throw new ConfigException("No Source Ids found. Source Ids required to filter orders by");
        }

        return $this->sourceIds;
    }

    /**
     * getOrders
     *
     * Get all orders that are ready to drop to the corresponding warehouse
     *
     * @return Collection
     */
    protected function getOrders(): Collection
    {
        return $this->getOrderQuery()
            ->hasStatusIn($this->orderStatusesFilter)
            ->hasDroppableOrderItems($this->sourceIdFilter)
            ->limit($this->size)
            ->get();
    }


    /**
     * setSourceIdFilter
     * @param array $sourceIds
     */
    public function setSourceIdFilter(array $sourceIds)
    {
        $this->sourceIdFilter = $sourceIds;
    }

    /**
     * setOrderStatusesFilter
     * @param array $orderStatuses
     */
    public function setOrderStatusesFilter(array $orderStatuses)
    {
        $this->orderStatusesFilter = $orderStatuses;
    }

    /**
     * Send drop size
     *
     * @param $size
     * @return $this
     */
    public function setSize($size)
    {
        $this->size = $size;
        return $this;
    }

    /**
     * @param Order  $order
     * @param string $status
     * @return $this
     */
    protected function updateOrderStatusIfAllItemsMatch(
        Order $order,
        int $status
    ): self {
        $itemsWithDifferentStatuses = $order->orderItems()
            ->where('drop_status', '!=', $status)
            ->count();

        if ($itemsWithDifferentStatuses === 0) {
            $order->status = $status;
            $order->save();
            $this->recordProcessed($order);
        }

        return $this;
    }

    /**
     * @param Order       $order
     * @param int         $orderStatus
     * @param string|null $logMessage
     */
    protected function saveOrderLog(
        Order $order,
        int $orderStatus,
        ?string $logMessage
    ): void {
        $orderLog = new OrderLog();
        $orderLog->setRawAttributes([
            'parent_id' => $order->getAttribute('id'),
            'status' => $orderStatus,
            'message' => $logMessage
        ]);
        $orderLog->save();
        $this->recordProcessed($orderLog);
    }

    /**
     * @return Order|Builder
     */
    private function getOrderQuery()
    {
        return Order::query();
    }
}
