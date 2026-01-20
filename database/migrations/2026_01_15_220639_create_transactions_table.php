<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['deposit', 'withdrawal', 'transfer', 'fee']);
            $table->enum('status', ['pending', 'completed', 'failed', 'reversed']);
            $table->string('idempotency_key')->unique();
            $table->foreignId('initiator_user_id')->nullable()->constrained('users');
            $table->bigInteger(column: 'amount');
            $table->foreignId('source_wallet_id')->nullable()->constrained('wallets');
            $table->foreignId('destination_wallet_id')->nullable()->constrained('wallets');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
