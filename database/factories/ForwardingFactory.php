<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ForwardingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'forwarding_name' => $this->faker->company(),
            'company_name' => $this->faker->companySuffix() . ' ' . $this->faker->company(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
        ];
    }
}
