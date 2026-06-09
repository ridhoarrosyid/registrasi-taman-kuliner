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
        Schema::create('layout_maps', function (Blueprint $table) {
            $table->id();
            $table->string('image_path'); // Lokasi gambar peta
            $table->boolean('is_active')->default(true); // Status apakah peta ini yang dipakai
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('layout_maps');
    }
};
