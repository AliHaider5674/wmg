<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\OrderAddress;

/**
 * Class OrderAddressFactory
 * @package Database\Factories
 */
class OrderAddressFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = OrderAddress::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'address1'  => $this->faker->streetAddress,
            'address2' => $this->faker->secondaryAddress,
            'city' => $this->faker->city,
            'state' => $this->faker->state,
            'zip' => $this->faker->postcode,
            'country_code' => 'US',
            'customer_address_type' => OrderAddress::CUSTOMER_ADDRESS_TYPE_SHIPPING,
            "phone" => $this->faker->phoneNumber,
            "email" => $this->faker->email,
        ];
    }
}
