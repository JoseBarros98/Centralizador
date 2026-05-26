<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $indexes = collect(\DB::select('SHOW INDEX FROM `management_expenses`'))->pluck('Key_name')->unique();

        if (!$indexes->contains('management_expenses_item_mes_gestion_unique')) {
            Schema::table('management_expenses', function (Blueprint $table) {
                $table->unique(['item', 'mes', 'gestion'], 'management_expenses_item_mes_gestion_unique');
            });
        }
    }

    public function down(): void
    {
        $indexes = collect(\DB::select('SHOW INDEX FROM `management_expenses`'))->pluck('Key_name')->unique();

        if ($indexes->contains('management_expenses_item_mes_gestion_unique')) {
            Schema::table('management_expenses', function (Blueprint $table) {
                $table->dropUnique('management_expenses_item_mes_gestion_unique');
            });
        }
    }
};
