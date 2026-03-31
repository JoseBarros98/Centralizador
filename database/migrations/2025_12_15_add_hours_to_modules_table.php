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
        Schema::table('modules', function (Blueprint $table) {
            $table->decimal('presential_hours', 8, 2)->nullable()->after('name');
            $table->decimal('non_presential_hours', 8, 2)->nullable()->after('presential_hours');
        });

        Schema::table('programs', function (Blueprint $table) {
            if (!Schema::hasColumn('programs', 'moodle_link')) {
                $table->string('moodle_link')->nullable()->after('finalization_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->dropColumn('presential_hours');
            $table->dropColumn('non_presential_hours');
        });

        Schema::table('programs', function (Blueprint $table) {
            if (Schema::hasColumn('programs', 'moodle_link')) {
                $table->dropColumn('moodle_link');
            }
        });
    }
};
