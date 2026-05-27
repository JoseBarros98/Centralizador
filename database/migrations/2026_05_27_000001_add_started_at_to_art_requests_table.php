<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('art_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('art_requests', 'started_at')) {
                $table->timestamp('started_at')->nullable()->after('actual_hours');
            }
        });
    }

    public function down(): void
    {
        Schema::table('art_requests', function (Blueprint $table) {
            if (Schema::hasColumn('art_requests', 'started_at')) {
                $table->dropColumn('started_at');
            }
        });
    }
};
