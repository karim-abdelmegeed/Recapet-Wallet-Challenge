<?php

namespace Database\Factories;

use App\Models\Ledger;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ledger>
 */
class LedgerFactory extends Factory
{
    protected $model = Ledger::class;

    public function definition(): array
    {
        return [
            'transaction_id' => Transaction::factory(),
            'wallet_id' => Wallet::factory(),
            'source_wallet_id' => Wallet::factory(),
            'destination_wallet_id' => Wallet::factory(),
            'amount' => $this->faker->numberBetween(1, 25_000),
            'entry_type' => $this->faker->randomElement(['debit', 'credit', 'fee']),
            'reference_type' => $this->faker->randomElement(['deposit', 'withdrawal', 'transfer']),
            'direction' => $this->faker->randomElement(['in', 'out']),
        ];
    }

    public function inbound(): static
    {
        return $this->state(fn () => ['direction' => 'in']);
    }

    public function outbound(): static
    {
        return $this->state(fn () => ['direction' => 'out']);
    }
}
