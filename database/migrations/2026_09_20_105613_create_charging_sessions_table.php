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
       Schema::create('charging_sessions', function (Blueprint $table) {
            $table->id('id_session');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('id_charger')->constrained('chargers', 'id_charger')->onDelete('cascade');
            $table->foreignId('id_reservation')->nullable()->constrained('reservations', 'id_reservation');
            $table->dateTime('waktu_mulai');
            $table->dateTime('waktu_selesai')->nullable();
            $table->decimal('energi_kwh', 8, 2)->default(0);
            $table->decimal('estimasi_biaya', 12, 2)->default(0);
            $table->enum('status', ['running', 'completed', 'interrupted', 'cancelled'])->default('running');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('charging_sessions');
    }
};
