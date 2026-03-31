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
            // Requisitos de Titulación
            $table->boolean('has_legalized_degree_title')->default(false)->after('document_observations');
            $table->boolean('has_legalized_academic_diploma')->default(false)->after('has_legalized_degree_title');
            $table->boolean('has_identity_card_graduation')->default(false)->after('has_legalized_academic_diploma');
            $table->boolean('has_birth_certificate_original')->default(false)->after('has_identity_card_graduation');
            $table->boolean('has_photos')->default(false)->after('has_birth_certificate_original');
            $table->string('graduation_procedure_type')->nullable()->after('has_photos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inscriptions', function (Blueprint $table) {
            $table->dropColumn([
                'has_legalized_degree_title',
                'has_legalized_academic_diploma',
                'has_identity_card_graduation',
                'has_birth_certificate_original',
                'has_photos',
                'graduation_procedure_type',
            ]);
        });
    }
};
