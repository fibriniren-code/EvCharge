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
            $table->foreignId('location_id')->constrained('locations', 'id_location')->onDelete('cascade');
            $table->string('type');
            $table->integer('power_kw');
            $table->string('status')->default('available');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chargers');
    }
};