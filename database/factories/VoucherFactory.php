<?php

namespace Database\Factories;

use App\Models\Voucher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Voucher>
 */
class VoucherFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Voucher::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->bothify('????####')),
            'discount_type' => fake()->randomElement(['percent', 'fixed']),
            'discount_value' => fake()->randomFloat(2, 5, 100),
            'max_uses' => fake()->numberBetween(1, 100),
            'expires_at' => fake()->dateTimeBetween('now', '+1 year'),
        ];
    }

    /**
     * Indicate that the voucher is a percentage discount.
     */
    public function percent(): static
    {
        return $this->state(fn (array $attributes) => [
            'discount_type' => 'percent',
            'discount_value' => fake()->randomFloat(2, 5, 50),
        ]);
    }

    /**
     * Indicate that the voucher is a fixed discount.
     */
    public function fixed(): static
    {
        return $this->state(fn (array $attributes) => [
            'discount_type' => 'fixed',
            'discount_value' => fake()->randomFloat(2, 10, 500),
        ]);
    }

    /**
     * Indicate that the voucher is expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => fake()->dateTimeBetween('-1 year', '-1 day'),
        ]);
    }
}

