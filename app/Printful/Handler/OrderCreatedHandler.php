<?php declare(strict_types=1);

namespace App\Printful\Handler;

use App\Core\Handlers\IO\IOInterface;
use App\Core\Handlers\SingleOrderHandler;
use App\Core\Repositories\OrderLogRepository;
use App\Core\Services\Converters\OrderRawConverterService;
use App\Models\Order;
use App\Models\OrderDrop as OrderDropModel;
use App\Models\OrderItem;
use App\Printful\Handler\IO\PrintfulOrderCreated;
use App\Printful\Service\OrderAckService;
use App\Printful\Service\PrintfulOrderMapper;
use App\Repositories\OrderDropRepository;
use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Exception;
use Printful\Structures\Order\Order as PrintfulOrder;
use App\Core\Enums\OrderStatus;
use Printful\Exceptions\PrintfulApiException;
use Throwable;

/**
 * Class OrderCreatedHandler
 * @package App\Printful\Handler
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OrderCreatedHandler extends SingleOrderHandler
{
    /**
     * @var string[]
     */
    protected $sourceIds = ['PF'];

    /**
     * @var OrderAckService
     */
    protected $ackHandler;

    /**
     * @var OrderDropRepository
     */
    protected $orderDropRepository;

    /**
     * OrderCreatedHandler constructor.
     *
     * @param PrintfulOrderCreated $ioAdapter
     * @param PrintfulOrderMapper      $orderDataMapperService
     * @param OrderRawConverterService $orderRawConverterService
     * @param Log                      $logger
     * @param OrderAckService          $ackHandler
     * @param OrderDropRepository      $orderDropRepository
     * @param array                    $config
     */
    public function __construct(
        PrintfulOrderCreated $ioAdapter,
        PrintfulOrderMapper $orderDataMapperService,
        OrderRawConverterService $orderRawConverterService,
        Log $logger,
        OrderAckService $ackHandler,
        OrderDropRepository $orderDropRepository,
        OrderLogRepository $orderLogRepository,
        array $config = []
    ) {
        parent::__construct(
            $ioAdapter,
            $orderDataMapperService,
            $orderRawConverterService,
            $orderLogRepository,
            $logger,
            $config
        );

        $this->sendCallback = Closure::fromCallable([$this, 'createOrderDrop']);
        $this->ackHandler = $ackHandler;
        $this->orderDropRepository = $orderDropRepository;
    }

    /**
     * Will be called after each order drop
     * @param $order Order
     * @param $orderItems Collection
     * @param $status int
     */
    protected function finishOrderDrop(Order $order, Collection $orderItems, int $status)
    {
        parent::finishOrderDrop($order, $orderItems, $status);
        if ($status === OrderStatus::DROPPED) {
            $this->ackHandler->acknowledgeOrder($order, $orderItems);
            return;
        }
        if ($status === OrderStatus::SOFT_ERROR) {
            $this->ackHandler->errorProcessingOrder($order, $orderItems);
        }
    }


    /**
     * Process order with error
     * @param Order       $order
     * @param Collection  $orderItems
     * @param Throwable   $exception
     * @return int        new status
     */
    protected function processOrderWithError(
        Order $order,
        Collection $orderItems,
        Throwable $exception
    ):int {
        if ($exception instanceof PrintfulApiException) {
            if ($exception->getCode()>= 400 && $exception->getCode()< 500) {
                //Soft error
                $this->processOrder(
                    $order,
                    $orderItems,
                    OrderStatus::SOFT_ERROR,
                    $exception->getMessage()
                );
            }
            return OrderStatus::SOFT_ERROR;
        }
        $this->processOrder(
            $order,
            $orderItems,
            OrderStatus::ERROR,
            $exception->getMessage()
        );
        return OrderStatus::ERROR;
    }


    /**
     * @param array $data
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function createOrderDrop(array $data): void
    {
        /** @var PrintfulOrder $warehouseOrder */
        $warehouseOrder = $data[IOInterface::DATA_FIELD_WAREHOUSE_ORDER];

        /** @var Order $order */
        $order = $data[IOInterface::DATA_FIELD_ORDER];

        /** @var Collection $drop */
        $orderItems = $data[IOInterface::DATA_FIELD_ORDER_ITEMS];

        $drop = $this->orderDropRepository->create([
            'content' => $warehouseOrder->id,
            'status' => OrderDropModel::STATUS_DONE,
        ]);

        $order->drop_id = $drop->id;
        $order->save();

        $orderItems->each(static function (OrderItem $item) use ($drop) {
            $item->drop_id = $drop->id;
            $item->save();
        });
    }
}
