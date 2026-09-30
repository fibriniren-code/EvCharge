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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id('id_review');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('id_location')->constrained('locations', 'id_location')->onDelete('cascade');
            // Rating & Ulasan Alat/Mesin
            $table->unsignedTinyInteger('rating_alat'); // 1-5[cite: 3]
            $table->text('ulasan_alat')->nullable();
            // Rating & Ulasan Tempat/Fasilitas Penunjang
            $table->unsignedTinyInteger('rating_tempat'); // 1-5[cite: 3]
            $table->text('ulasan_tempat')->nullable();
            $table->string('foto_lokasi')->nullable(); // Upload foto fasilitas[cite: 3]
            $table->text('balasan_operator')->nullable(); // Fitur merespons keluhan[cite: 3]
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
