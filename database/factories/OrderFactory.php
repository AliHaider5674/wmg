<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Order;

/**
 * Class OrderFactory
 * @package Database\Factories
 */
class OrderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $shippingNetAmount = $this->faker->randomFloat(2, 5, 12);
        $shippingTaxRate = $this->faker->randomFloat(2, 6, 15);
        $shippingTaxAmount = $shippingNetAmount * (1.00 + ($shippingTaxRate / 100));
        $shippingGrossAmount = $shippingNetAmount + $shippingTaxAmount;

        return [
            'status' => Order::STATUS_RECEIVED,
            'sales_channel' => 'brunomars',
            'request_id' => $this->faker->unique()->randomNumber,
            'order_id' => $this->faker->randomNumber(),
            "shipping_net_amount" => number_format($shippingNetAmount, 2),
            "shipping_gross_amount" => number_format($shippingGrossAmount),
            "shipping_tax_amount" => number_format($shippingTaxAmount),
            "shipping_tax_rate" => number_format($shippingTaxRate),
        ];
    }
}
