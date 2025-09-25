<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('fcv_schedules', function (Blueprint $table) {
            $table->id();
            $table->morphs('scheduleable'); // scheduleable_type & _id (e.g., course, event)
            $table->unsignedTinyInteger('day_of_week'); // 0=Sun..6=Sat
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamps();
            $table->index(['scheduleable_type', 'scheduleable_id', 'day_of_week']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fcv_schedules');
    }
};
