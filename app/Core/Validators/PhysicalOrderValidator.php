<?php
namespace App\Core\Validators;

use App\Models\OrderAddress;
use App\Models\Order;
use Illuminate\Support\Facades\Validator;

/**
 * class PhysicalOrderValidator
 */
class PhysicalOrderValidator
{
    private Validator $validator;
    private const RULES = [
        'order.sales_channel' => ['required'],
        'order.request_id' => ['required'],
        'order.order_id' => ['required'],
        'order_items.*.order_line_id' => ['required'],
        'shipping_address.first_name' => ['required'],
        'shipping_address.last_name' => ['required'],
    ];
    public function __construct(Validator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param \App\Models\Order        $order
     * @param array                    $items
     * @param \App\Models\OrderAddress $shippingAddress
     * @param \App\Models\OrderAddress $billingAddress
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validate(
        Order $order,
        array $items,
        OrderAddress $shippingAddress,
        OrderAddress $billingAddress
    ):\Illuminate\Contracts\Validation\Validator {
        $itemsData = [];
        foreach ($items as $item) {
            $itemsData[] = $item->toArray();
        }
        return $this->validator::make(
            [
                'order' => $order->toArray(),
                'order_items' => $itemsData,
                'shipping_address' => $shippingAddress->toArray(),
                'billing_address' => $billingAddress->toArray()
            ],
            self::RULES
        );
    }
}
