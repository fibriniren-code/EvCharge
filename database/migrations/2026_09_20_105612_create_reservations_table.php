<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id('id_reservation');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('charger_id')->constrained('chargers', 'id_charger')->cascadeOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles', 'id_vehicle')->nullOnDelete();
            $table->dateTime('waktu_mulai');
            $table->dateTime('waktu_selesai');
            $table->dateTime('expires_at')->nullable();
            $table->enum('status', ['pending', 'confirmed', 'active', 'completed', 'cancelled', 'expired'])
                ->default('pending');
            $table->timestamps();

            $table->index(['charger_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
