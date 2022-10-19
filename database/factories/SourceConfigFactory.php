<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\SourceConfig;

/**
 * Class SourceConfigFactory
 * @package Database\Factories
 */
class SourceConfigFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SourceConfig::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'distribution_id' => 'US',
            'source_id'       => 'US'
        ];
    }
}
