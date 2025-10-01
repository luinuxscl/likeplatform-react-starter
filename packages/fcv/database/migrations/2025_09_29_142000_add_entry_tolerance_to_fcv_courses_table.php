<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('fcv_courses', function (Blueprint $table) {
            if (! Schema::hasColumn('fcv_courses', 'entry_tolerance_mode')) {
                $table->string('entry_tolerance_mode', 20)->default('20')->after('valid_until');
            }

            if (! Schema::hasColumn('fcv_courses', 'entry_tolerance_minutes')) {
                $table->unsignedSmallInteger('entry_tolerance_minutes')->nullable()->default(20)->after('entry_tolerance_mode');
            }
        });
    }

    public function down(): void
    {
        Schema::table('fcv_courses', function (Blueprint $table) {
            if (Schema::hasColumn('fcv_courses', 'entry_tolerance_mode')) {
                $table->dropColumn('entry_tolerance_mode');
            }

            if (Schema::hasColumn('fcv_courses', 'entry_tolerance_minutes')) {
                $table->dropColumn('entry_tolerance_minutes');
            }
        });
    }
};
