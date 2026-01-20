<?php

namespace Database\Factories;

use App\Models\Plateform;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Wallet>
 */
class WalletFactory extends Factory
{
    protected $model = Wallet::class;

    public function definition(): array
    {
        return [
            'owner_id' => Plateform::factory(),
            'owner_type' => Plateform::class,
            'balance' => $this->faker->numberBetween(0, 100_000),
        ];
    }

    public function forUser(?User $user = null): static
    {
        return $this->state(function () use ($user) {
            return [
                'owner_id' => $user?->id ?? User::factory(),
                'owner_type' => User::class,
            ];
        });
    }

    public function forPlateform(?Plateform $plateform = null): static
    {
        return $this->state(function () use ($plateform) {
            return [
                'owner_id' => $plateform?->id ?? Plateform::factory(),
                'owner_type' => Plateform::class,
            ];
        });
    }
}
