<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => Hash::make('password'), // password fijo para tests
            'phone' => $this->faker->optional()->phoneNumber(),
            'status' => 1, // active
            'google_id' => null,
            'avatar' => null,
            'email_verified_at' => now(),
        ];
    }
}
