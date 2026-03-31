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
            // Estado de Titulación
            $table->boolean('has_graduation_procedure')->default(false)->after('has_monograph_received');
            $table->boolean('has_graduation_received')->default(false)->after('has_graduation_procedure');
            $table->boolean('has_documents_delivered')->default(false)->after('has_graduation_received');
            $table->boolean('has_diplomas_delivered')->default(false)->after('has_documents_delivered');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inscriptions', function (Blueprint $table) {
            $table->dropColumn([
                'has_graduation_procedure',
                'has_graduation_received',
                'has_documents_delivered',
                'has_diplomas_delivered',
            ]);
        });
    }
};
