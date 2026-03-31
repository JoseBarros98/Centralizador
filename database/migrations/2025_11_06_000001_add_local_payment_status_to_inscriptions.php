<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('inscriptions', function (Blueprint $table) {
            // Agregar nuevo campo para estados de pago locales
            $table->enum('local_payment_status', ['Pendiente', 'Adelanto', 'Completando', 'Completo'])
                  ->default('Pendiente')
                  ->after('status')
                  ->comment('Estado de pago local (independiente del estado académico externo)');
        });

        // Migrar datos existentes: copiar status actual a local_payment_status
        DB::statement("
            UPDATE inscriptions 
            SET local_payment_status = COALESCE(status, 'Pendiente')
            WHERE is_synced = true
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inscriptions', function (Blueprint $table) {
            $table->dropColumn('local_payment_status');
        });
    }
};
