<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Safe add: runs whether 000002 was the old or new version
        Schema::table('marketing_goal_weeks', function (Blueprint $table) {
            if (!Schema::hasColumn('marketing_goal_weeks', 'week_start')) {
                $table->date('week_start')->nullable()->after('weekly_goal');
            }
            if (!Schema::hasColumn('marketing_goal_weeks', 'week_end')) {
                $table->date('week_end')->nullable()->after('week_start');
            }
        });
    }

    public function down(): void
    {
        Schema::table('marketing_goal_weeks', function (Blueprint $table) {
            $table->dropColumnIfExists('week_start');
            $table->dropColumnIfExists('week_end');
        });
    }
};
