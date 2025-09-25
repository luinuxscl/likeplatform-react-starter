<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('fcv_access_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('person_id')->nullable()->constrained('fcv_persons')->nullOnDelete();
            $table->unsignedBigInteger('vehicle_id')->nullable(); // reservado para futura relación
            $table->timestamp('occurred_at')->index();
            $table->string('direction'); // entrada | salida
            $table->string('status'); // permitido | denegado
            $table->string('reason')->nullable();
            $table->unsignedBigInteger('gatekeeper_id')->nullable(); // user_id del guardia en app host
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['person_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fcv_access_logs');
    }
};
