<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->unsignedBigInteger('id_session')
                ->nullable()
                ->unique()
                ->after('id_location');

            $table->foreign('id_session')
                ->references('id_session')
                ->on('charging_sessions')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropForeign(['id_session']);
            $table->dropUnique(['id_session']);
            $table->dropColumn('id_session');
        });
    }
};
