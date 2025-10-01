<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('fcv_courses', function (Blueprint $table) {
            if (! Schema::hasColumn('fcv_courses', 'valid_from')) {
                $table->date('valid_from')->nullable()->after('description');
            }

            if (! Schema::hasColumn('fcv_courses', 'valid_until')) {
                $table->date('valid_until')->nullable()->after('valid_from');
            }

            if (! Schema::hasColumn('fcv_courses', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        Schema::table('fcv_courses', function (Blueprint $table) {
            if (Schema::hasColumn('fcv_courses', 'valid_from')) {
                $table->dropColumn('valid_from');
            }

            if (Schema::hasColumn('fcv_courses', 'valid_until')) {
                $table->dropColumn('valid_until');
            }

            if (Schema::hasColumn('fcv_courses', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};
