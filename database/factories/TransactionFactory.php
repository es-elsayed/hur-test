<?php

namespace Database\Factories;

use App\Models\Member;
use App\Models\Project;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Transaction::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'transaction' => fake()->unique()->bothify('TXN-########-????'),
            'project' => Project::factory(),
            'client' => Member::factory()->client(),
            'amount' => fake()->randomFloat(2, 100, 10000),
            'data' => [
                'payment_method' => fake()->randomElement(['credit_card', 'paypal', 'bank_transfer']),
                'payment_status' => fake()->randomElement(['pending', 'completed', 'failed']),
                'payment_date' => fake()->dateTimeThisYear()->format('Y-m-d H:i:s'),
                'gateway_response' => fake()->sentence(),
            ],
        ];
    }

    /**
     * Indicate that the transaction is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'data' => array_merge($attributes['data'] ?? [], [
                'payment_status' => 'completed',
            ]),
        ]);
    }

    /**
     * Indicate that the transaction is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'data' => array_merge($attributes['data'] ?? [], [
                'payment_status' => 'pending',
            ]),
        ]);
    }
}

