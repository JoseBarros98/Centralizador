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
        $indexes = collect(\DB::select('SHOW INDEX FROM `program_allocations`'))->pluck('Key_name')->unique();

        Schema::table('program_allocations', function (Blueprint $table) use ($indexes) {
            if (!Schema::hasColumn('program_allocations', 'mes')) {
                $table->integer('mes')->nullable()->after('program_id');
            }
            if (!$indexes->contains('program_allocations_program_id_mes_unique')) {
                $table->unique(['program_id', 'mes']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $indexes = collect(\DB::select('SHOW INDEX FROM `program_allocations`'))->pluck('Key_name')->unique();

        Schema::table('program_allocations', function (Blueprint $table) use ($indexes) {
            if ($indexes->contains('program_allocations_program_id_mes_unique')) {
                $table->dropUnique(['program_id', 'mes']);
            }
            if (Schema::hasColumn('program_allocations', 'mes')) {
                $table->dropColumn('mes');
            }
        });
    }
};
