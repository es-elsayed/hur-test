<?php

namespace Database\Factories;

use App\Models\Member;
use App\Models\Voucher;
use App\Models\VoucherRedeem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VoucherRedeem>
 */
class VoucherRedeemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = VoucherRedeem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $projectIds = fake()->randomElements(range(1, 10), fake()->numberBetween(1, 3));
        
        return [
            'voucher' => Voucher::factory(),
            'member' => Member::factory(),
            'redeem' => fake()->boolean(70),
            'projects' => implode(',', $projectIds),
        ];
    }

    /**
     * Indicate that the voucher has been redeemed.
     */
    public function redeemed(): static
    {
        return $this->state(fn (array $attributes) => [
            'redeem' => true,
        ]);
    }

    /**
     * Indicate that the voucher has not been redeemed.
     */
    public function notRedeemed(): static
    {
        return $this->state(fn (array $attributes) => [
            'redeem' => false,
        ]);
    }
}

