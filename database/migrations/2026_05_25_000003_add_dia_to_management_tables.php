<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Expenses ────────────────────────────────────────────────────────
        $expIndexes = collect(\DB::select('SHOW INDEX FROM `management_expenses`'))->pluck('Key_name')->unique();

        Schema::table('management_expenses', function (Blueprint $table) use ($expIndexes) {
            if (!Schema::hasColumn('management_expenses', 'dia')) {
                $table->tinyInteger('dia')->unsigned()->nullable()->after('gestion');
            }
        });

        \DB::table('management_expenses')->whereNull('dia')->update(['dia' => 1]);

        Schema::table('management_expenses', function (Blueprint $table) use ($expIndexes) {
            if ($expIndexes->contains('management_expenses_item_mes_gestion_unique')) {
                $table->dropUnique('management_expenses_item_mes_gestion_unique');
            }
            if (!$expIndexes->contains('mgmt_exp_item_mes_gestion_dia_unique')) {
                $table->unique(['item', 'mes', 'gestion', 'dia'], 'mgmt_exp_item_mes_gestion_dia_unique');
            }
        });

        // ── Investments ─────────────────────────────────────────────────────
        $invIndexes = collect(\DB::select('SHOW INDEX FROM `management_investments`'))->pluck('Key_name')->unique();

        Schema::table('management_investments', function (Blueprint $table) use ($invIndexes) {
            if (!Schema::hasColumn('management_investments', 'dia')) {
                $table->tinyInteger('dia')->unsigned()->nullable()->after('gestion');
            }
        });

        \DB::table('management_investments')->whereNull('dia')->update(['dia' => 1]);

        Schema::table('management_investments', function (Blueprint $table) use ($invIndexes) {
            if ($invIndexes->contains('management_investments_mes_gestion_index')) {
                $table->dropIndex('management_investments_mes_gestion_index');
            }
            if (!$invIndexes->contains('mgmt_inv_item_mes_gestion_dia_unique')) {
                $table->unique(['item', 'mes', 'gestion', 'dia'], 'mgmt_inv_item_mes_gestion_dia_unique');
            }
        });
    }

    public function down(): void
    {
        $expIndexes = collect(\DB::select('SHOW INDEX FROM `management_expenses`'))->pluck('Key_name')->unique();

        Schema::table('management_expenses', function (Blueprint $table) use ($expIndexes) {
            if ($expIndexes->contains('mgmt_exp_item_mes_gestion_dia_unique')) {
                $table->dropUnique('mgmt_exp_item_mes_gestion_dia_unique');
            }
            if (!$expIndexes->contains('management_expenses_item_mes_gestion_unique')) {
                $table->unique(['item', 'mes', 'gestion']);
            }
            if (Schema::hasColumn('management_expenses', 'dia')) {
                $table->dropColumn('dia');
            }
        });

        $invIndexes = collect(\DB::select('SHOW INDEX FROM `management_investments`'))->pluck('Key_name')->unique();

        Schema::table('management_investments', function (Blueprint $table) use ($invIndexes) {
            if ($invIndexes->contains('mgmt_inv_item_mes_gestion_dia_unique')) {
                $table->dropUnique('mgmt_inv_item_mes_gestion_dia_unique');
            }
            if (!$invIndexes->contains('management_investments_mes_gestion_index')) {
                $table->index(['mes', 'gestion']);
            }
            if (Schema::hasColumn('management_investments', 'dia')) {
                $table->dropColumn('dia');
            }
        });
    }
};
