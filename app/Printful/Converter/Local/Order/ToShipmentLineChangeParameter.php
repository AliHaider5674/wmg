<?php declare(strict_types=1);

namespace App\Printful\Converter\Local\Order;

use App\Models\Order;
use App\Models\Service\ModelBuilder\ShipmentLineChange\ItemParameter;
use App\Models\Service\ModelBuilder\ShipmentLineChangeParameter;
use Illuminate\Support\Collection;

/**
 * Class ToShipmentLineChangeParameter
 * @package App\Printful\Converter\Local\Order
 */
class ToShipmentLineChangeParameter
{
    /**
     * @param Order       $order
     * @param Collection  $orderItems
     * @param string|null $backorderReasonCode
     * @return ShipmentLineChangeParameter
     */
    public function convert(
        Order $order,
        Collection $orderItems,
        string $backorderReasonCode = null
    ): ShipmentLineChangeParameter {
        $parameter = new ShipmentLineChangeParameter();
        $parameter->orderId = $order->id;

        $orderItems->each(
            static function ($item) use ($parameter, $backorderReasonCode) {
                $itemParameter = new ItemParameter();
                $itemParameter->orderItemId = $item->id;
                $itemParameter->sku = $item->sku;
                $itemParameter->quantity = $item->quantity;
                $itemParameter->backOrderReasonCode = $backorderReasonCode;
                $itemParameter->returnedQuantity = $item->quantity_returned;
                $itemParameter->backorderQuantity = $item->quantity_backordered;

                $parameter->addItem($itemParameter);
            }
        );

        return $parameter;
    }
}
