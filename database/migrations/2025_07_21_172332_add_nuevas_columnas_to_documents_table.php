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
        Schema::table('documents', function (Blueprint $table) {
            $table->enum('document_type', [
                'ci',
                'titulo',
                'diploma',
                'nacimiento',
                'documentacion_completa',
                'compromiso',
                'congelamiento',
                'recibo',
                'factura',
                'comprobante_pago'
            ])->after('inscription_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            //
        });
    }
};
