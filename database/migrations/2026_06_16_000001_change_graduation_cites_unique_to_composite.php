<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('graduation_cites', function (Blueprint $table) {
            // Eliminar el unique simple en cite_number
            $table->dropUnique(['cite_number']);
            // Agregar unique compuesto: mismo CITE puede existir con diferente concepto
            $table->unique(['cite_number', 'payment_type'], 'graduation_cites_cite_payment_unique');
        });
    }

    public function down(): void
    {
        Schema::table('graduation_cites', function (Blueprint $table) {
            $table->dropUnique('graduation_cites_cite_payment_unique');
            $table->unique('cite_number');
        });
    }
};
