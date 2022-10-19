<?php

namespace App\IMMuleSoft\Handler;

use App\Core\Handlers\AbstractOrderHandler;
use App\Core\Services\Converters\OrderRawConverterService;
use App\Core\Services\OrderRawMapperService;
use App\Exceptions\ConfigException;
use App\Exceptions\MethodNotImplementedException;
use App\Exceptions\NoRecordException;
use App\IMMuleSoft\Handler\Order\OrderDrop;
use App\IMMuleSoft\Handler\IO\Order;
use Illuminate\Support\Facades\Log;

/**
 * Class OrderHandler
 * @package App\IMMuleSoft\Handler
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2022
 * @link     http://www.wmg.com
 */
class OrderHandler extends AbstractOrderHandler
{
    private OrderDrop $orderDrop;

    /**
     * Method to use as callback for send method, if it is set using.
     *
     * @var callable
     */
    protected $callback;


    /**
     * @inheritDoc
     */
    public function __construct(
        Order                    $ioAdapter,
        OrderRawMapperService    $orderDataMapperService,
        OrderRawConverterService $orderRawConverterService,
        Log                      $logger,
        OrderDrop                $orderDrop,
        array                    $config = []
    ) {
        parent::__construct(
            $ioAdapter,
            $orderDataMapperService,
            $orderRawConverterService,
            $logger,
            $config
        );

        $this->orderDrop = $orderDrop;
    }

    /**
     * Handle order drop
     *
     * @return void
     * @throws ConfigException|NoRecordException
     */
    public function handle()
    {
        //get warehouse Id to filter orders by
        $orderBySourceIds = $this->getSourceIds();
        //apply warehouse filter
        $this->setSourceIdFilter($orderBySourceIds);

        //get orders that are ready to drop to the warehouse
        $orders = $this->getOrders();

        //return if no orders to drop
        if (!$orders->count()) {
            throw new NoRecordException('No orders are ready to be dropped.');
        }

        //Drop orders to the warehouse
        $this->orderDrop->handle($orders, $this->sourceIdFilter);
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        return true;
    }

    /**
     * @inheritDoc
     * @throws MethodNotImplementedException
     */
    protected function rollbackItem($item, ...$args): void
    {
        throw new MethodNotImplementedException(
            "The %s method is not implemented in this IO class"
        );
    }
}
