<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Plateform;
use Database\Seeders\UserWalletSeeder;
use Database\Seeders\WalletDemoSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $platform = Plateform::query()->firstOrCreate([
            'name' => 'Main'
        ]);
        if (!$platform->wallet()->exists()) {
            $platform->wallet()->create([
                'balance' => 0
            ]);
        }
        //testing user
        $user = User::create([
            'name' => 'test',
            'email' => 'test@gmail.com',
            'password' => Hash::make('123456'),
        ]);
        $this->call([
            UserWalletSeeder::class,
            WalletDemoSeeder::class,
            BalanceSnapshotSeeder::class,
        ]);
    }
}
