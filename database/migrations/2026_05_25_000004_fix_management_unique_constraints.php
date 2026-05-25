<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'management_investments' => 'mgmt_inv',
            'management_expenses'    => 'mgmt_exp',
        ];

        foreach ($tables as $table => $prefix) {
            $indexes = collect(\DB::select("SHOW INDEX FROM `{$table}`"))
                ->pluck('Key_name')
                ->unique()
                ->values();

            // Drop old 3-column unique (item, mes, gestion) if it still exists
            $oldUnique = "{$table}_item_mes_gestion_unique";
            if ($indexes->contains($oldUnique)) {
                \DB::statement("ALTER TABLE `{$table}` DROP INDEX `{$oldUnique}`");
            }

            // Drop stale (mes, gestion) index if it exists
            $oldIndex = "{$table}_mes_gestion_index";
            if ($indexes->contains($oldIndex)) {
                \DB::statement("ALTER TABLE `{$table}` DROP INDEX `{$oldIndex}`");
            }

            // Add dia column if somehow missing
            if (!Schema::hasColumn($table, 'dia')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->tinyInteger('dia')->unsigned()->nullable()->after('gestion');
                });
                \DB::table($table)->whereNull('dia')->update(['dia' => 1]);
            }

            // Add new 4-column unique (item, mes, gestion, dia) if not yet present
            $newUnique = "{$prefix}_item_mes_gestion_dia_unique";
            if (!$indexes->contains($newUnique)) {
                Schema::table($table, function (Blueprint $t) use ($newUnique) {
                    $t->unique(['item', 'mes', 'gestion', 'dia'], $newUnique);
                });
            }
        }
    }

    public function down(): void {}
};
