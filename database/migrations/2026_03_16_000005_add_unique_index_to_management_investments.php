<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('management_investments', function (Blueprint $table) {
            $table->unique(['item', 'mes', 'gestion'], 'management_investments_item_mes_gestion_unique');
        });
    }

    public function down(): void
    {
        Schema::table('management_investments', function (Blueprint $table) {
            $table->dropUnique('management_investments_item_mes_gestion_unique');
        });
    }
};
