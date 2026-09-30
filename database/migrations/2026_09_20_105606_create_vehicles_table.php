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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id('id_vehicle');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('merek');
            $table->string('model');
            $table->string('nomor_polisi');
            $table->string('tipe_konektor'); // Type 2, CCS2, CHAdeMO
            $table->decimal('kapasitas_baterai_kwh', 8, 2); //
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
