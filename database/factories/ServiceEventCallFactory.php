<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\ServiceEventCall;

/**
 * Class ServiceEventCallFactory
 * @package Database\Factories
 */
class ServiceEventCallFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ServiceEventCall::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'data' => [],
            'status' => $this->faker->randomElement([0,1]),
        ];
    }
}
