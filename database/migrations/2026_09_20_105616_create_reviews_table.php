<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id('id_review');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('id_location')->constrained('locations', 'id_location')->cascadeOnDelete();
            $table->foreignId('id_session')->nullable()->unique()
                ->constrained('charging_sessions', 'id_session')->nullOnDelete();
            // Rating & ulasan alat/mesin (1-5)
            $table->unsignedTinyInteger('rating_alat');
            $table->text('ulasan_alat')->nullable();
            // Rating & ulasan tempat/fasilitas penunjang (1-5)
            $table->unsignedTinyInteger('rating_tempat');
            $table->text('ulasan_tempat')->nullable();
            $table->string('foto_lokasi')->nullable();
            $table->text('balasan_operator')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
