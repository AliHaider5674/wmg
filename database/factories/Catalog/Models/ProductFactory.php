<?php

namespace Database\Factories\Catalog\Models;

use App\Catalog\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Class ProductFactory
 * @package Database\factories
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2022
 * @link     http://www.wmg.com
 */
class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $artistsName = [
            "Echosmith", "Drake", "Mastodon", "Pink Floyd", "Florence and the Machine",
            "Ed Sheeran", "Paramore", "New Order"
        ];

        $productName = ['Black Hoodie', 'Pump it up', 'Shape of you'];

        return [
            'sku' => uniqid('', true),
            'name' => $this->faker->randomElement($productName),
            'artist_name' => $this->faker->randomElement($artistsName),
        ];
    }
}
