<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('fcv_memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('person_id')->constrained('fcv_persons')->cascadeOnDelete();
            $table->foreignId('organization_id')->constrained('fcv_organizations')->cascadeOnDelete();
            $table->string('role')->index(); // alumno | funcionario | participante_externo
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();
            $table->unique(['person_id', 'organization_id', 'role']);
            $table->index(['start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fcv_memberships');
    }
};
