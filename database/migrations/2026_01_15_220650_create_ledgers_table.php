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
        Schema::create('ledgers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('transactions');
            $table->foreignId('wallet_id')->constrained('wallets');
            $table->foreignId('source_wallet_id')->nullable()->constrained('wallets');
            $table->foreignId('destination_wallet_id')->nullable()->constrained('wallets');
            $table->bigInteger('amount');
            $table->string(column: 'entry_type')->comment('debit, credit, fee');
            $table->string(column: 'reference_type')->comment('deposit, withdrawal, transfer');
            $table->enum('direction', ['in', 'out']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ledgers');
    }
};
