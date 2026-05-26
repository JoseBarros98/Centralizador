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
            if (!Schema::hasColumn('inscriptions', 'payment_group_id')) {
                $table->string('payment_group_id')->nullable()->after('local_payment_status')
                      ->comment('ID de grupo para inscripciones del mismo CI en meses diferentes');
            }
            if (!Schema::hasColumn('inscriptions', 'consolidated_status')) {
                $table->enum('consolidated_status', ['Pendiente', 'Adelanto', 'Completando', 'Completo'])->nullable()->after('payment_group_id')
                      ->comment('Estado consolidado para reportes (ignora si hay Adelanto + Completando en meses diferentes)');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inscriptions', function (Blueprint $table) {
            $drop = array_filter(
                ['payment_group_id', 'consolidated_status'],
                fn($c) => Schema::hasColumn('inscriptions', $c)
            );
            if ($drop) {
                $table->dropColumn(array_values($drop));
            }
        });
    }
};
