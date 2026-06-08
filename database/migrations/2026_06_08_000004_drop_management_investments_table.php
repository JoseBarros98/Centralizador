<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('management_investments');
    }

    public function down(): void
    {
        // Restore table only if needed — recreate the structure from original migration
        Schema::create('management_investments', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->id();
            $table->string('item');
            $table->decimal('investment_amount', 12, 2);
            $table->unsignedTinyInteger('mes');
            $table->unsignedTinyInteger('dia')->default(1);
            $table->unsignedSmallInteger('gestion');
            $table->text('observation')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->unique(['item', 'mes', 'dia', 'gestion'], 'mgmt_inv_item_mes_gestion_dia_unique');
            $table->index(['mes', 'gestion']);
        });
    }
};
