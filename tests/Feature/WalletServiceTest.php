<?php

use App\Models\Plateform;
use App\Models\User;
use App\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('deposits into a user wallet and returns a transaction', function () {
    $user = User::factory()->create();
    $user->wallet()->create(['balance' => 0]);

    /** @var WalletService $service */
    $service = app(WalletService::class);

    $tx = $service->deposit($user, 1000, 'dep-1');

    expect($tx)->not->toBeNull();
    expect($tx->type)->toBe('deposit');
    expect((int) $tx->amount)->toBe(1000);

    $user->refresh();
    $user->wallet->refresh();
    expect((int) $user->wallet->balance)->toBe(1000);
});

it('deposit is idempotent for the same idempotency key', function () {
    $user = User::factory()->create();
    $user->wallet()->create(['balance' => 0]);

    /** @var WalletService $service */
    $service = app(WalletService::class);

    $tx1 = $service->deposit($user, 1000, 'dep-idem');
    $tx2 = $service->deposit($user, 1000, 'dep-idem');

    expect($tx2->id)->toBe($tx1->id);

    $user->refresh();
    $user->wallet->refresh();
    expect((int) $user->wallet->balance)->toBe(1000);
});

it('withdraws from a user wallet and returns a transaction', function () {
    $user = User::factory()->create();
    $user->wallet()->update(['balance' => 5000]);

    /** @var WalletService $service */
    $service = app(WalletService::class);
    $tx = $service->withdraw($user, 1200, 'wd-1');
    expect($tx)->not->toBeNull();
    expect($tx->type)->toBe('withdrawal');
    expect((int) $tx->amount)->toBe(1200);

    $user->refresh();
    $user->wallet->refresh();
    expect((int) $user->wallet->balance)->toBe(3800);
});

it('withdraw is idempotent for the same idempotency key', function () {
    $user = User::factory()->create();
    $user->wallet()->update(['balance' => 5000]);

    /** @var WalletService $service */
    $service = app(WalletService::class);

    $tx1 = $service->withdraw($user, 1200, 'wd-idem');
    $tx2 = $service->withdraw($user, 1200, 'wd-idem');

    expect($tx2->id)->toBe($tx1->id);

    $user->refresh();
    $user->wallet->refresh();
    expect((int) $user->wallet->balance)->toBe(3800);
});

it('throws when withdrawing more than the wallet balance', function () {
    $user = User::factory()->create();
    $user->wallet()->create(['balance' => 100]);

    /** @var WalletService $service */
    $service = app(WalletService::class);

    $service->withdraw($user, 200, 'wd-over');
})->throws(InvalidArgumentException::class);

it('transfers money between two user wallets without fee (amount < 2500)', function () {
    $sender = User::factory()->create();
    $receiver = User::factory()->create();

    $sender->wallet()->update(['balance' => 5000]);
    $receiver->wallet()->update(['balance' => 1000]);

    /** @var WalletService $service */
    $service = app(WalletService::class);

    $tx = $service->transfer($sender, $receiver, 2000, 'tr-1');

    expect($tx->type)->toBe('transfer');
    expect((int) $tx->amount)->toBe(2000);

    $sender->wallet->refresh();
    $receiver->wallet->refresh();

    expect((int) $sender->wallet->balance)->toBe(3000);
    expect((int) $receiver->wallet->balance)->toBe(3000);
});

it('transfers money and charges fee to the plateform wallet (amount >= 2500)', function () {
    $plateform = Plateform::create(['name' => 'Main']);
    $plateform->wallet()->create(['balance' => 0]);

    $sender = User::factory()->create();
    $receiver = User::factory()->create();

    $sender->wallet()->update(['balance' => 10000]);
    $receiver->wallet()->update(['balance' => 500]);

    /** @var WalletService $service */
    $service = app(WalletService::class);

    $amount = 2500;
    $expectedFee = (int) (($amount * 0.1) + (2.5 * 100));

    $tx = $service->transfer($sender, $receiver, $amount, 'tr-fee-1');

    expect($tx->type)->toBe('transfer');

    $sender->wallet->refresh();
    $receiver->wallet->refresh();
    $plateform->wallet->refresh();

    expect((int) $sender->wallet->balance)->toBe(10000 - ($amount + $expectedFee));
    expect((int) $receiver->wallet->balance)->toBe(500 + $amount);
    expect((int) $plateform->wallet->balance)->toBe($expectedFee);
});
