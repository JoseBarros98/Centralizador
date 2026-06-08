<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('management_income_entity_amounts')) return;

        Schema::create('management_income_entity_amounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entity_id')
                  ->constrained('management_income_entities')
                  ->cascadeOnDelete();
            $table->string('item', 255);
            $table->unsignedTinyInteger('mes');
            $table->unsignedTinyInteger('dia');
            $table->unsignedSmallInteger('gestion');
            $table->decimal('amount', 12, 2)->default(0);
            $table->text('observation')->nullable();
            $table->timestamps();

            $table->unique(['entity_id', 'item', 'mes', 'dia', 'gestion'], 'miea_entity_item_mes_dia_gestion_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('management_income_entity_amounts');
    }
};
