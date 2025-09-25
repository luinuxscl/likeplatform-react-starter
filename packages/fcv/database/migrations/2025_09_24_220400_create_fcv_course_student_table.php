<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('fcv_course_student', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('fcv_courses')->cascadeOnDelete();
            $table->foreignId('person_id')->constrained('fcv_persons')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['course_id', 'person_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fcv_course_student');
    }
};
