<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id('id_payment');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            // Nullable: top-up saldo tidak terikat ke sesi charging tertentu.
            $table->foreignId('id_session')->nullable()
                ->constrained('charging_sessions', 'id_session')->nullOnDelete();
            $table->string('metode'); // Saldo, E-Wallet, VA, Kartu Kredit, QRIS
            $table->decimal('jumlah', 12, 2);
            $table->enum('status', ['pending', 'success', 'failed', 'refunded'])->default('pending');
            $table->dateTime('waktu_pembayaran')->nullable();
            $table->string('referensi_gateway')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
