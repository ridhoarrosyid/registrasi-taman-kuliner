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
            $table->foreignId('rent_id')->constrained('rents')->cascadeOnDelete();
            $table->integer('amount'); // Nominal pembayaran
            $table->string('payment_proof')->nullable(); // Path foto bukti transfer
            $table->enum('status', [
                'pending',
                'success',
                'failed'
            ])->default('pending');
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
