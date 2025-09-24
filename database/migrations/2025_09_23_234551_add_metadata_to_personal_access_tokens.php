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
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            if (! Schema::hasColumn('personal_access_tokens', 'description')) {
                $table->text('description')->nullable()->after('name');
            }

            if (! Schema::hasColumn('personal_access_tokens', 'last_used_ip')) {
                $table->string('last_used_ip', 45)->nullable()->after('last_used_at');
            }

            if (! Schema::hasColumn('personal_access_tokens', 'last_used_user_agent')) {
                $table->string('last_used_user_agent')->nullable()->after('last_used_ip');
            }

            if (! Schema::hasColumn('personal_access_tokens', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('last_used_user_agent');
            }

            if (! Schema::hasColumn('personal_access_tokens', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('expires_at')->constrained('users')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            if (Schema::hasColumn('personal_access_tokens', 'created_by')) {
                $table->dropConstrainedForeignId('created_by');
            }

            $columns = array_filter([
                Schema::hasColumn('personal_access_tokens', 'description') ? 'description' : null,
                Schema::hasColumn('personal_access_tokens', 'last_used_ip') ? 'last_used_ip' : null,
                Schema::hasColumn('personal_access_tokens', 'last_used_user_agent') ? 'last_used_user_agent' : null,
                Schema::hasColumn('personal_access_tokens', 'expires_at') ? 'expires_at' : null,
            ]);

            if (! empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
