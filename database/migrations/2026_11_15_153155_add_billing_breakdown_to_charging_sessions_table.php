<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('charging_sessions', function (Blueprint $table) {
            $table->decimal('harga_per_kwh', 12, 2)->nullable()->after('estimasi_biaya');
            $table->decimal('biaya_energi', 12, 2)->nullable()->after('harga_per_kwh');
            $table->decimal('biaya_layanan', 12, 2)->nullable()->after('biaya_energi');
            $table->decimal('biaya_overstay', 12, 2)->nullable()->after('biaya_layanan');
        });
    }

    public function down(): void
    {
        Schema::table('charging_sessions', function (Blueprint $table) {
            $table->dropColumn(['harga_per_kwh', 'biaya_energi', 'biaya_layanan', 'biaya_overstay']);
        });
    }
};