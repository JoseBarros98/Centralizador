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
        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->comment('id_programa externo');
            $table->string('accounting_code')->nullable()->comment('codigo_contable externo');
            $table->string('name')->comment('nombre_programa externo');
            $table->integer('version')->default(1)->comment('version_programa externo');
            $table->integer('group')->default(1)->comment('grupo_programa externo');
            $table->string('year')->nullable()->comment('gestion_programa externo');
            $table->date('start_date')->nullable()->comment('fecha_inicio externo');
            $table->date('finalization_date')->nullable()->comment('fecha_finalizacion externo');
            $table->string('status')->nullable()->comment('fase_programa externo');
            
            // Campos estáticos adicionales de la BD externa
            $table->string('postgraduate_id')->nullable()->comment('id_posgrado externo');
            $table->date('registration_date')->nullable()->comment('fecha_matriculacion externo');
            $table->string('modality', 10)->nullable()->comment('modalidad externo (V=Virtual, P=Presencial, etc)');
            $table->decimal('passing_grade', 5, 2)->nullable()->comment('nota_de_aprobacion externo');
            
            $table->string('academic_code')->nullable()->comment('Futuro uso');
            $table->string('area')->nullable()->comment('Futuro uso');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('programs');
    }
};
