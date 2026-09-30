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
        Schema::create('chargers', function (Blueprint $table) {
            // 1. Mengacu ke id_location milik tabel locations
            $table->foreignId('location_id')->constrained('locations', 'id_location')->onDelete('cascade');
            
            // 2. Primary key charger
            $table->id('id_charger'); // Atau $table->id(); jika menggunakan 'id'
            
            $table->string('type');
            $table->integer('power_kw');
            $table->enum('status', ['available', 'occupied', 'maintenance'])->default('available');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chargers');
    }
};
