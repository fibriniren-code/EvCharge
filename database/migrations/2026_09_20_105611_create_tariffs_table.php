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
       Schema::create('tariffs', function (Blueprint $table) {
            $table->id('id_tariff');
            $table->foreignId('id_location')->constrained('locations', 'id_location')->onDelete('cascade');
            $table->decimal('harga_per_kwh', 12, 2);
            $table->decimal('biaya_layanan', 12, 2)->default(0);
            $table->decimal('biaya_minimum', 12, 2)->default(0);
            $table->decimal('overstay_fee_per_menit', 12, 2)->default(0); // Penalti keterlambatan[cite: 3]
            $table->dateTime('periode_berlaku');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tariffs');
    }
};
