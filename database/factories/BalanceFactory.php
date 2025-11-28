<?php

namespace Database\Factories;

use App\Models\Balance;
use App\Models\Member;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Balance>
 */
class BalanceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Balance::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'member' => Member::factory(),
            'process' => fake()->randomElement(['income', 'outcome']),
            'amount' => fake()->randomFloat(2, 50, 5000),
            'project' => Project::factory(),
            'action' => fake()->randomElement(['complete', 'un-complete']),
        ];
    }

    /**
     * Indicate that the balance is an income.
     */
    public function income(): static
    {
        return $this->state(fn (array $attributes) => [
            'process' => 'income',
        ]);
    }

    /**
     * Indicate that the balance is an outcome.
     */
    public function outcome(): static
    {
        return $this->state(fn (array $attributes) => [
            'process' => 'outcome',
        ]);
    }

    /**
     * Indicate that the balance action is complete.
     */
    public function complete(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'complete',
        ]);
    }

    /**
     * Indicate that the balance action is un-complete.
     */
    public function unComplete(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'un-complete',
        ]);
    }
}

