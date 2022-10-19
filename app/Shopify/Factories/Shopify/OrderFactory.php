<?php
namespace App\Shopify\Factories\Shopify;

use App\Shopify\Enums\ShopifyOrderStatus;
use App\Shopify\Models\ShopifyOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @class FulfillmentOrderFactory
 * @package App\Shopify
 */
class OrderFactory extends Factory
{
    protected $model = ShopifyOrder::class;
    public function definition()
    {
        return [
            'id' => $this->faker->randomNumber(),
            'status' => $this->faker->randomElement([
                ShopifyOrderStatus::FETCHED,
                ShopifyOrderStatus::ERROR,
                ShopifyOrderStatus::EXPANDED]),
            'order_id' => $this->faker->randomNumber(),
            'ordered_at' => $this->faker->dateTimeBetween()->format('Y-m-d H:i:s')
        ];
    }
}
