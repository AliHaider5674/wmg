<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Class ServiceFactory
 * @package Database\Factories
 */
class ServiceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Service::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'app_id' => $this->faker->name,
            'name' => $this->faker->name,
            'app_url' => 'http://localhost/' . $this->faker->domainName,
            'status' => $this->faker->randomElement([0,1]),
            'client' => $this->faker->randomElement(['m1', 'mom']),
            'event_rules' => '.*',
            'addition' => json_encode([
                'wsdl' => 'https://warnermusic.wmgecomstage.com/api/v2_soap?wsdl=1',
                'ht_username' => 'magneto',
                'ht_password' => 'experts1',
                'username' => '',
                'api_key' => ''
            ])
        ];
    }
}
