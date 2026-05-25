<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Expenses ────────────────────────────────────────────────────────
        Schema::table('management_expenses', function (Blueprint $table) {
            $table->tinyInteger('dia')->unsigned()->nullable()->after('gestion');
        });

        // Migrate existing monthly records → dia = 1
        \DB::table('management_expenses')->whereNull('dia')->update(['dia' => 1]);

        Schema::table('management_expenses', function (Blueprint $table) {
            // Drop old unique (item, mes, gestion)
            $table->dropUnique(['item', 'mes', 'gestion']);
            // New unique (item, mes, gestion, dia)
            $table->unique(['item', 'mes', 'gestion', 'dia'], 'mgmt_exp_item_mes_gestion_dia_unique');
        });

        // ── Investments ─────────────────────────────────────────────────────
        Schema::table('management_investments', function (Blueprint $table) {
            $table->tinyInteger('dia')->unsigned()->nullable()->after('gestion');
        });

        \DB::table('management_investments')->whereNull('dia')->update(['dia' => 1]);

        Schema::table('management_investments', function (Blueprint $table) {
            // Drop existing index (not unique — from migration 000005)
            $table->dropIndex(['mes', 'gestion']);
            $table->unique(['item', 'mes', 'gestion', 'dia'], 'mgmt_inv_item_mes_gestion_dia_unique');
        });
    }

    public function down(): void
    {
        Schema::table('management_expenses', function (Blueprint $table) {
            $table->dropUnique('mgmt_exp_item_mes_gestion_dia_unique');
            $table->unique(['item', 'mes', 'gestion']);
            $table->dropColumn('dia');
        });

        Schema::table('management_investments', function (Blueprint $table) {
            $table->dropUnique('mgmt_inv_item_mes_gestion_dia_unique');
            $table->index(['mes', 'gestion']);
            $table->dropColumn('dia');
        });
    }
};
