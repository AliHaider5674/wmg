<?php

namespace Database\Factories;

use App\Core\Enums\OrderItemStatus;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Class OrderItemFactory
 * @package Database\Factories
 */
class OrderItemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = OrderItem::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $taxRate = $this->faker->randomFloat(2, 0, 10);
        $price = $this->faker->randomFloat(2, 10, 100);
        $skuList = [
            "0010467410823","0030633337921","0601811170526","0014551493327",
            "0014551493426","0014551493525","0014551493624","0014551494423",
            "0014551495727","0014551496120","0014551496427","0014551496625",
            "0014551496724","0014551496823","0014551496922","0014551497127",
            "0014551497325","0014551497424","0014551497820","0014551497929",
            "0014551498025", "0014551498124","0014551920120","0014551920229",
            "0014551920311","0014998411427","0014998414022","0014998415425",
            "0014998415524","0014998416095","0014998416828","0014998417023",
            "0014998417825","0014998418327","0014998418525","0014998418624",
            "0014998419126","0016861743819"
        ];

        return [
            'order_line_id' => $this->faker->randomNumber(),
            'order_line_number' => $this->faker->randomNumber(),
            'sku' => $this->faker->randomElement($skuList),
            'name' => $this->faker->name,
            'source_id' => $this->faker->randomElement(['US', 'EU']),
            'net_amount' => number_format($price, 2),
            'gross_amount' => number_format($price * (1 + $taxRate/100), 2),
            'tax_amount' => number_format($taxRate/100 * $price, 2),
            'drop_id' => null,
            'drop_status' => OrderItemStatus::RECEIVED,
            'parent_id' => fn () => Order::factory()->create()->id,
            'tax_rate' => $taxRate,
            'item_type' => $this->faker->randomElement([
                OrderItem::PRODUCT_TYPE_PHYSICAL,
                OrderItem::PRODUCT_TYPE_DIGITAL
            ]),
            'currency' => $this->faker->currencyCode,
            'quantity' => $this->faker->randomFloat(0, 1, 10),
            'aggregated_line_id' => $this->faker->randomNumber(1)
        ];
    }
}
