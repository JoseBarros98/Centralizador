<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add new columns first (table must have dia before we can add the new unique on it)
        Schema::table('management_expense_entity_amounts', function (Blueprint $table) {
            if (!Schema::hasColumn('management_expense_entity_amounts', 'dia')) {
                $table->unsignedTinyInteger('dia')->after('gestion')->default(1);
            }
            if (!Schema::hasColumn('management_expense_entity_amounts', 'observation')) {
                $table->text('observation')->nullable()->after('amount');
            }
        });

        // 2. Add the new unique constraint (entity_id is first → MySQL FK index satisfied)
        Schema::table('management_expense_entity_amounts', function (Blueprint $table) {
            if (!Schema::hasIndex('management_expense_entity_amounts', 'meea_entity_item_mes_gestion_dia_unique')) {
                $table->unique(
                    ['entity_id', 'item', 'mes', 'gestion', 'dia'],
                    'meea_entity_item_mes_gestion_dia_unique'
                );
            }
        });

        // 3. Now safe to drop the old monthly unique (FK index is covered by the new one)
        Schema::table('management_expense_entity_amounts', function (Blueprint $table) {
            if (Schema::hasIndex('management_expense_entity_amounts', 'meea_entity_item_mes_gestion_unique')) {
                $table->dropUnique('meea_entity_item_mes_gestion_unique');
            }
        });
    }

    public function down(): void
    {
        Schema::table('management_expense_entity_amounts', function (Blueprint $table) {
            $table->dropUnique('meea_entity_item_mes_gestion_dia_unique');
            $table->dropColumn(['dia', 'observation']);
            $table->unique(
                ['entity_id', 'item', 'mes', 'gestion'],
                'meea_entity_item_mes_gestion_unique'
            );
        });
    }
};
