<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('fcv_organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('type')->index(); // interna | convenio | externa
            $table->string('access_rule_preset')->index(); // horario_estricto | horario_flexible | acceso_total
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fcv_organizations');
    }
};
