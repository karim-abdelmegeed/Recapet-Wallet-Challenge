<?php

namespace Database\Seeders;

use App\Models\Plateform;
use App\Models\User;
use App\Services\WalletService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class WalletDemoSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::query()->get();

        if ($users->count() < 2) {
            return;
        }

        $plateform = Plateform::query()->where('name', 'Main')->first();
        if (!$plateform) {
            return;
        }
        if (!$plateform->wallet()->exists()) {
            $plateform->wallet()->create(['balance' => 0]);
        }

        /** @var WalletService $service */
        $service = app(WalletService::class);

        foreach ($users as $user) {
            $service->deposit($user, random_int(10_00, 250_00), (string) Str::uuid());
        }

        for ($i = 0; $i < 20; $i++) {
            $sender = $users->random();
            $receiver = $users->where('id', '!=', $sender->id)->random();

            $amount = random_int(1_00, 50_00);

            try {
                $service->transfer($sender, $receiver, $amount, (string) Str::uuid());
            } catch (\Throwable $e) {
                continue;
            }
        }

        for ($i = 0; $i < 10; $i++) {
            $user = $users->random();
            $amount = random_int(1_00, 20_00);

            try {
                $service->withdraw($user, $amount, (string) Str::uuid());
            } catch (\Throwable $e) {
                continue;
            }
        }
    }
}
