<?php

namespace Database\Factories;

use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Member>
 */
class MemberFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Member::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'type' => fake()->randomElement(['client', 'freelancer']),
        ];
    }

    /**
     * Indicate that the member is a client.
     */
    public function client(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'client',
        ]);
    }

    /**
     * Indicate that the member is a freelancer.
     */
    public function freelancer(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'freelancer',
        ]);
    }
}

