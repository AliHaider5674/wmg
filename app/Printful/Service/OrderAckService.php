<?php declare(strict_types=1);

namespace App\Printful\Service;

use App\Core\Constants\BackorderStatusReasonCodes;
use App\Models\Order;
use App\Printful\Converter\Local\Order\ToShipmentLineChangeParameter;
use App\Printful\Handler\OrderAckHandler;
use Exception;
use Illuminate\Support\Collection;

/**
 * Class OrderAckService
 *
 * Since the Printful Ack happens right when an order is dropped as opposed to a
 * webhook or a handler, we will use this service to process orders and send
 * them to the OrderAckHandler.
 *
 * @package App\Printful\Service
 */
class OrderAckService
{
    /**
     * @var OrderAckHandler
     */
    private $ackHandler;

    /**
     * @var ToShipmentLineChangeParameter
     */
    private $orderConverter;

    /**
     * OrderAckService constructor.
     * @param OrderAckHandler               $ackHandler
     * @param ToShipmentLineChangeParameter $orderConverter
     */
    public function __construct(
        OrderAckHandler $ackHandler,
        ToShipmentLineChangeParameter $orderConverter
    ) {
        $this->orderConverter = $orderConverter;
        $this->ackHandler = $ackHandler;
    }

    /**
     * Send acknowledgement that the order was received from the warehouse
     *
     * @param Order      $order
     * @param Collection $orderItems
     * @return $this
     * @throws Exception
     */
    public function acknowledgeOrder(Order $order, Collection $orderItems): self
    {
        $this->ackHandler->processAckParameter(
            $this->orderConverter->convert($order, $orderItems)
        );

        return $this;
    }

    /**
     * Send message to Magento that there was an error sending the order to the
     * warehouse
     *
     * @param Order      $order
     * @param Collection $orderItems
     * @return $this
     * @throws Exception
     */
    public function errorProcessingOrder(
        Order $order,
        Collection $orderItems
    ): self {
        $this->ackHandler->processAckParameter(
            $this->orderConverter->convert(
                $order,
                $orderItems,
                BackorderStatusReasonCodes::ERROR
            )
        );

        return $this;
    }
}
