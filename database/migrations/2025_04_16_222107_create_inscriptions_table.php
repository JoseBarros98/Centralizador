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
        Schema::create('inscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); //Código generado en base al nombre y ci
            
            // Campos de estudiante - Sincronizados con DB externa
            $table->string('full_name'); // nombre_completo_estudiante (DB externa)
            $table->string('ci'); // nro_ci_estudiante (DB externa)
            $table->date('birth_date')->nullable(); // fecha_nacimiento_estudiante (DB externa)
            $table->string('phone')->nullable(); // telefono_estudiante (DB externa) - NULLABLE porque algunos vienen sin teléfono
            $table->string('email')->nullable(); // email_estudiante (DB externa) - SIN UNIQUE porque pueden haber duplicados en DB externa
            $table->foreignId('profession_id')->nullable()->constrained('professions'); // profesion_estudiante (DB externa)
            
            // Campos de inscripción - Sincronizados con DB externa
            $table->date('inscription_date'); // fecha_inscripcion (DB externa)
            $table->string('payment_plan')->nullable(); // plan_pago (DB externa) - STRING en lugar de ENUM para aceptar todos los valores
            
            // Campos de estado - DB externa
            $table->string('external_inscription_status')->nullable(); // estado_inscripcion_estudiante (DB externa)
            $table->string('external_academic_status')->nullable(); // estado_academico (DB externa)
            $table->string('external_degree_status')->nullable(); // estado_titulacion (DB externa)
            $table->boolean('external_university_enrolled')->default(false); // inscrito_universidad (DB externa)
            $table->date('external_preregistration_date')->nullable(); // fecha_registro_preinscrito (DB externa)
            
            // Campos de asesor - Sincronizados con DB externa
            $table->string('external_advisor_id')->nullable(); // idasesor (DB externa)
            $table->string('external_advisor_name')->nullable(); // nombre_completo_asesor (DB externa)
            $table->integer('external_program_id')->nullable(); // id_programa (DB externa) - Para vincular con el programa
            $table->foreignId('created_by')->constrained('users'); // Usuario del sistema (o usuario especial "Sistema Externo")
            
            // Campos adicionales del sistema local (no sincronizados)
            $table->enum('civil_status', ['Soltero', 'Casado', 'Divorciado', 'Viudo'])->nullable();
            $table->foreignId('university_id')->nullable()->constrained('universities');
            $table->string('program_id')->nullable()->constrained('');
            $table->enum('payment_method', ['QR', 'Efectivo', 'Deposito', 'Transferencia'])->nullable();
            $table->decimal('enrollment_fee', 10, 2)->nullable();
            $table->decimal('first_installment', 10, 2)->nullable();
            $table->decimal('total_paid', 10, 2)->nullable();
            $table->string('receipt_numbre')->nullable();
            $table->enum('status', ['Completo', 'Adelanto', 'Completando'])->nullable();
            $table->enum('gender', ['Masculino', 'Femenino'])->nullable();
            $table->string('residence')->nullable();
            $table->string('location')->nullable(); //Sede
            $table->text('notes')->nullable();
            $table->text('certification')->nullable();

            //Documentación
            $table->boolean('has_identity_card')->default(false);
            $table->boolean('has_degree_title')->default(false);
            $table->boolean('has_academic_diploma')->default(false);
            $table->boolean('has_birth_certificate')->default(false);
            $table->boolean('has_commitment_letter')->default(false);
            $table->string('commitment_letter_path')->nullable();
            $table->text('document_observations')->nullable();

            // Control de sincronización
            $table->boolean('is_synced')->default(false);
            $table->timestamp('last_synced_at')->nullable();
            $table->integer('external_id')->nullable()->unique(); // id_estudiante de la DB externa
            
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inscriptions');
    }
};
