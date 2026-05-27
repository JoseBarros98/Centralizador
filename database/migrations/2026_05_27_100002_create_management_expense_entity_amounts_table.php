<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('management_expense_entity_amounts')) return;

        Schema::create('management_expense_entity_amounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entity_id')
                  ->constrained('management_expense_entities')
                  ->cascadeOnDelete();
            $table->string('item', 255);
            $table->unsignedTinyInteger('mes');
            $table->unsignedSmallInteger('gestion');
            $table->decimal('amount', 12, 2)->default(0);
            $table->timestamps();

            $table->unique(['entity_id', 'item', 'mes', 'gestion'], 'meea_entity_item_mes_gestion_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('management_expense_entity_amounts');
    }
};
