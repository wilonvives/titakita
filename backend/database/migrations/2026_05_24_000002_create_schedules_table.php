<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')
                ->constrained('events')
                ->onDelete('cascade');
            $table->integer('session_duration_minutes');
            $table->jsonb('start_times');
            $table->integer('capacity_per_session')->nullable();
            $table->string('scope_type');
            $table->jsonb('weekdays')->nullable();
            $table->date('range_start_date')->nullable();
            $table->date('range_end_date')->nullable();
            $table->jsonb('specific_dates')->nullable();
            $table->timestamps();

            $table->index('event_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
