<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chargers', function (Blueprint $table) {
            $table->id('id_charger');
            $table->foreignId('location_id')->constrained('locations', 'id_location')->cascadeOnDelete();
            $table->string('charger_type');
            $table->decimal('power_kw', 8, 2);
            // available = siap dipakai, reserved = sedang ditahan reservasi,
            // occupied = sedang charging, faulted = rusak, unavailable = dimatikan
            $table->enum('status', ['available', 'reserved', 'occupied', 'faulted', 'unavailable'])
                ->default('available');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chargers');
    }
};
