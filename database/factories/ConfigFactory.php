<?php

namespace Database\Factories;

use Configuration;
use Illuminate\Database\Eloquent\Factories\Factory;

//phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter

/**
 * Class ConfigFactory
 * @package Database\Factories
 */
class ConfigFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     * @todo This class doesn't exist
     */
    protected $model = \App\Configuration::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'path' => 'file_drop_frequency',
            'value' => '3600',
        ];
    }
}
