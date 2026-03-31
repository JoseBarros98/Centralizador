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
            // Trabajo Final / Monografía
            $table->boolean('has_monograph_elaboration')->default(false)->after('graduation_procedure_type');
            $table->boolean('has_monograph_received')->default(false)->after('has_monograph_elaboration');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inscriptions', function (Blueprint $table) {
            $table->dropColumn([
                'has_monograph_elaboration',
                'has_monograph_received',
            ]);
        });
    }
};
