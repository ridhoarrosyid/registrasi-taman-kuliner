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
        Schema::create('rents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('slot_id')->constrained('slots')->cascadeOnDelete();

            $table->string('business_name');
            $table->enum('status', [
                'pending_payment',
                'payment_failed',
                'pending_verification',
                'renewal_pending_verification',
                'active',
                'expired'
            ])->default('pending_payment');

            $table->dateTime('reserved_until');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('qr_code')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rents');
    }
};
