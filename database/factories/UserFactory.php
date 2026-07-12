<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'role_id' => 4, // Default to client role
            'username' => fake()->unique()->userName(),
            'name' => fake()->name(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),
            'bio' => fake()->sentence(),
            'facebook_link' => 'https://facebook.com/' . fake()->userName(),
            'twitter_link' => 'https://twitter.com/' . fake()->userName(),
            'linkedin_link' => 'https://linkedin.com/in/' . fake()->userName(),
            'instagram_link' => 'https://instagram.com/' . fake()->userName(),
        ];
    }

}
