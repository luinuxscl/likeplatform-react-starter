<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 50);
            $table->nullableMorphs('auditable');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->json('metadata')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->string('url')->nullable();
            $table->timestamps();

            $table->index('action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
