<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserWalletSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->count(10)->create();
    }
}
