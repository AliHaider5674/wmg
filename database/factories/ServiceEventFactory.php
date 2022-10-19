<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\ServiceEvent;

/**
 * Class ServiceEventFactory
 * @package Database\Factories
 */
class ServiceEventFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ServiceEvent::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'event' => $this->faker->randomElement(
                [
                    'service.events.test' ,
                    'service.events.item.shipped',
                    'service.events.source.update'
                ]
            ),
            'status' => $this->faker->randomElement([0,1]),
        ];
    }
}
