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
        Schema::table('inscriptions', function (Blueprint $table) {
            // Cambiar campos de defensa de booleano a string (texto)
            $table->string('has_pre_defense')->nullable()->change();
            $table->string('has_defense')->nullable()->change();
            $table->string('has_defense_accounting_status')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inscriptions', function (Blueprint $table) {
            // Revertir a booleano si es necesario
            $table->boolean('has_pre_defense')->default(false)->change();
            $table->boolean('has_defense')->default(false)->change();
            $table->boolean('has_defense_accounting_status')->default(false)->change();
        });
    }
};
