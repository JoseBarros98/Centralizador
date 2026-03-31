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
        Schema::table('program_allocations', function (Blueprint $table) {
            // Agregar campo mes (1-12)
            $table->integer('mes')->nullable()->after('program_id');
            
            // Agregar índice compuesto para programa + mes
            $table->unique(['program_id', 'mes'])->after('mes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('program_allocations', function (Blueprint $table) {
            $table->dropUnique(['program_id', 'mes']);
            $table->dropColumn('mes');
        });
    }
};
