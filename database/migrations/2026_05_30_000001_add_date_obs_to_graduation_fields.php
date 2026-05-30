<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inscriptions', function (Blueprint $table) {
            // Requisitos de Titulación — fecha y observaciones por cada ítem
            if (!Schema::hasColumn('inscriptions', 'legalized_degree_title_date'))
                $table->date('legalized_degree_title_date')->nullable()->after('has_legalized_degree_title');
            if (!Schema::hasColumn('inscriptions', 'legalized_degree_title_obs'))
                $table->string('legalized_degree_title_obs')->nullable()->after('legalized_degree_title_date');

            if (!Schema::hasColumn('inscriptions', 'legalized_academic_diploma_date'))
                $table->date('legalized_academic_diploma_date')->nullable()->after('has_legalized_academic_diploma');
            if (!Schema::hasColumn('inscriptions', 'legalized_academic_diploma_obs'))
                $table->string('legalized_academic_diploma_obs')->nullable()->after('legalized_academic_diploma_date');

            if (!Schema::hasColumn('inscriptions', 'identity_card_graduation_date'))
                $table->date('identity_card_graduation_date')->nullable()->after('has_identity_card_graduation');
            if (!Schema::hasColumn('inscriptions', 'identity_card_graduation_obs'))
                $table->string('identity_card_graduation_obs')->nullable()->after('identity_card_graduation_date');

            if (!Schema::hasColumn('inscriptions', 'birth_certificate_original_date'))
                $table->date('birth_certificate_original_date')->nullable()->after('has_birth_certificate_original');
            if (!Schema::hasColumn('inscriptions', 'birth_certificate_original_obs'))
                $table->string('birth_certificate_original_obs')->nullable()->after('birth_certificate_original_date');

            if (!Schema::hasColumn('inscriptions', 'photos_date'))
                $table->date('photos_date')->nullable()->after('has_photos');
            if (!Schema::hasColumn('inscriptions', 'photos_obs'))
                $table->string('photos_obs')->nullable()->after('photos_date');

            // Trabajo Final (Diplomado) — Monografía
            if (!Schema::hasColumn('inscriptions', 'monograph_elaboration_date'))
                $table->date('monograph_elaboration_date')->nullable()->after('has_monograph_elaboration');
            if (!Schema::hasColumn('inscriptions', 'monograph_elaboration_obs'))
                $table->string('monograph_elaboration_obs')->nullable()->after('monograph_elaboration_date');

            if (!Schema::hasColumn('inscriptions', 'monograph_received_date'))
                $table->date('monograph_received_date')->nullable()->after('has_monograph_received');
            if (!Schema::hasColumn('inscriptions', 'monograph_received_obs'))
                $table->string('monograph_received_obs')->nullable()->after('monograph_received_date');

            // Trabajo Final (Maestría) — Fase trabajo de grado
            if (!Schema::hasColumn('inscriptions', 'degree_work_presentation_date'))
                $table->date('degree_work_presentation_date')->nullable()->after('has_degree_work_presentation');
            if (!Schema::hasColumn('inscriptions', 'degree_work_presentation_obs'))
                $table->string('degree_work_presentation_obs')->nullable()->after('degree_work_presentation_date');

            if (!Schema::hasColumn('inscriptions', 'tutor_approval_report_date'))
                $table->date('tutor_approval_report_date')->nullable()->after('has_tutor_approval_report');
            if (!Schema::hasColumn('inscriptions', 'tutor_approval_report_obs'))
                $table->string('tutor_approval_report_obs')->nullable()->after('tutor_approval_report_date');

            if (!Schema::hasColumn('inscriptions', 'pre_defense_obs'))
                $table->string('pre_defense_obs')->nullable()->after('has_pre_defense');
            if (!Schema::hasColumn('inscriptions', 'defense_obs'))
                $table->string('defense_obs')->nullable()->after('has_defense');
            if (!Schema::hasColumn('inscriptions', 'defense_accounting_status_obs'))
                $table->string('defense_accounting_status_obs')->nullable()->after('has_defense_accounting_status');

            // Estado de Titulación — fecha y observaciones por cada ítem
            if (!Schema::hasColumn('inscriptions', 'graduation_procedure_date'))
                $table->date('graduation_procedure_date')->nullable()->after('has_graduation_procedure');
            if (!Schema::hasColumn('inscriptions', 'graduation_procedure_obs'))
                $table->string('graduation_procedure_obs')->nullable()->after('graduation_procedure_date');

            if (!Schema::hasColumn('inscriptions', 'graduation_received_date'))
                $table->date('graduation_received_date')->nullable()->after('has_graduation_received');
            if (!Schema::hasColumn('inscriptions', 'graduation_received_obs'))
                $table->string('graduation_received_obs')->nullable()->after('graduation_received_date');

            if (!Schema::hasColumn('inscriptions', 'documents_delivered_date'))
                $table->date('documents_delivered_date')->nullable()->after('has_documents_delivered');
            if (!Schema::hasColumn('inscriptions', 'documents_delivered_obs'))
                $table->string('documents_delivered_obs')->nullable()->after('documents_delivered_date');

            if (!Schema::hasColumn('inscriptions', 'diplomas_delivered_date'))
                $table->date('diplomas_delivered_date')->nullable()->after('has_diplomas_delivered');
            if (!Schema::hasColumn('inscriptions', 'diplomas_delivered_obs'))
                $table->string('diplomas_delivered_obs')->nullable()->after('diplomas_delivered_date');
        });
    }

    public function down(): void
    {
        Schema::table('inscriptions', function (Blueprint $table) {
            $cols = [
                'legalized_degree_title_date', 'legalized_degree_title_obs',
                'legalized_academic_diploma_date', 'legalized_academic_diploma_obs',
                'identity_card_graduation_date', 'identity_card_graduation_obs',
                'birth_certificate_original_date', 'birth_certificate_original_obs',
                'photos_date', 'photos_obs',
                'monograph_elaboration_date', 'monograph_elaboration_obs',
                'monograph_received_date', 'monograph_received_obs',
                'degree_work_presentation_date', 'degree_work_presentation_obs',
                'tutor_approval_report_date', 'tutor_approval_report_obs',
                'pre_defense_obs', 'defense_obs', 'defense_accounting_status_obs',
                'graduation_procedure_date', 'graduation_procedure_obs',
                'graduation_received_date', 'graduation_received_obs',
                'documents_delivered_date', 'documents_delivered_obs',
                'diplomas_delivered_date', 'diplomas_delivered_obs',
            ];
            $toDrop = array_filter($cols, fn($c) => Schema::hasColumn('inscriptions', $c));
            if ($toDrop) $table->dropColumn(array_values($toDrop));
        });
    }
};
