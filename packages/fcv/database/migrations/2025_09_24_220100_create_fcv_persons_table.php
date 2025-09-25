<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('fcv_persons', function (Blueprint $table) {
            $table->id();
            $table->string('rut')->unique();
            $table->string('name');
            $table->string('photo_path')->nullable();
            $table->json('contact_info')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fcv_persons');
    }
};
