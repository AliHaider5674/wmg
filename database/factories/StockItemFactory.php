<?php

namespace Database\Factories;

use App\Models\StockItem;
use Illuminate\Database\Eloquent\Factories\Factory;

//phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter

/**
 * Class StockItemFactory
 * @package Database\Factories
 */
class StockItemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = StockItem::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'sku' => uniqid('', true),
            'qty' => $this->faker->randomNumber(),
            'source_id' => $this->faker->randomElement(['US', 'GNAR'])
        ];
    }
}
