<?php

namespace Database\Factories;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        return [
            'type' => $this->faker->randomElement(['deposit', 'withdrawal', 'transfer', 'fee']),
            'status' => 'completed',
            'idempotency_key' => (string) Str::uuid(),
            'initiator_user_id' => User::factory(),
            'amount' => $this->faker->numberBetween(1, 25_000),
            'source_wallet_id' => Wallet::factory(),
            'destination_wallet_id' => Wallet::factory(),
        ];
    }

    public function deposit(): static
    {
        return $this->state(fn () => [
            'type' => 'deposit',
            'source_wallet_id' => null,
        ]);
    }

    public function withdrawal(): static
    {
        return $this->state(fn () => [
            'type' => 'withdrawal',
            'destination_wallet_id' => null,
        ]);
    }

    public function transfer(): static
    {
        return $this->state(fn () => [
            'type' => 'transfer',
        ]);
    }

    public function fee(): static
    {
        return $this->state(fn () => [
            'type' => 'fee',
        ]);
    }
}
