<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('schedule_id')
                ->nullable()
                ->constrained('schedules')
                ->onDelete('set null');
            $table->dateTime('session_start_at')->nullable();
            $table->dateTime('session_end_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['schedule_id']);
            $table->dropColumn(['schedule_id', 'session_start_at', 'session_end_at']);
        });
    }
};
