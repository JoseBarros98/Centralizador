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
            // Campo para rastrear si hay múltiples inscripciones del mismo estudiante en diferentes meses
            $table->string('payment_group_id')->nullable()->after('local_payment_status')
                  ->comment('ID de grupo para inscripciones del mismo CI en meses diferentes');
            
            // Campo para rastrear el estado consolidado (para reportes anuales)
            $table->enum('consolidated_status', ['Pendiente', 'Adelanto', 'Completando', 'Completo'])->nullable()->after('payment_group_id')
                  ->comment('Estado consolidado para reportes (ignora si hay Adelanto + Completando en meses diferentes)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inscriptions', function (Blueprint $table) {
            $table->dropColumn(['payment_group_id', 'consolidated_status']);
        });
    }
};
