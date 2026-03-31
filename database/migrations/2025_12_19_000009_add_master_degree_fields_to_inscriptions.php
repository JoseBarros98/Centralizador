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
            // Campos para Maestría - Fase de trabajo de grado
            $table->boolean('has_degree_work_presentation')->default(false)->after('has_monograph_received');
            $table->boolean('has_tutor_approval_report')->default(false)->after('has_degree_work_presentation');
            $table->boolean('has_pre_defense')->default(false)->after('has_tutor_approval_report');
            $table->boolean('has_defense')->default(false)->after('has_pre_defense');
            $table->boolean('has_defense_accounting_status')->default(false)->after('has_defense');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inscriptions', function (Blueprint $table) {
            $table->dropColumn([
                'has_degree_work_presentation',
                'has_tutor_approval_report',
                'has_pre_defense',
                'has_defense',
                'has_defense_accounting_status',
            ]);
        });
    }
};
