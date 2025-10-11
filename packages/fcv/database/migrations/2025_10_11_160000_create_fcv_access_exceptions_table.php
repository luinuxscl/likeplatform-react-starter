<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fcv_access_exceptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('person_id')->constrained('fcv_persons')->cascadeOnDelete();
            $table->enum('reason', ['medical', 'special_event', 'maintenance', 'administrative', 'other'])->default('other');
            $table->text('description')->nullable();
            $table->dateTime('valid_from');
            $table->dateTime('valid_until');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index(['person_id', 'status']);
            $table->index(['valid_from', 'valid_until']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fcv_access_exceptions');
    }
};
