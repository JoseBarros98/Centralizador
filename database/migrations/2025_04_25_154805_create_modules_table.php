<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->onDelete('cascade')->comment('id_programa externo');
            $table->string('name')->comment('nombre_modulo externo');
            $table->date('start_date')->nullable()->comment('fecha_inicio externo');
            $table->date('finalization_date')->nullable()->comment('fecha_fin externo');
            $table->string('status')->nullable()->comment('estado_modulo externo');
            $table->string('teacher_name')->nullable()->comment('docente externo (temporal, usar teacher_id cuando esté disponible)');
            $table->foreignId('teacher_id')->nullable()->constrained('teachers')->comment('ID del docente local');
            $table->foreignId('monitor_id')->nullable()->constrained('users')->comment('Campo editable local');
            $table->date('recovery_start_date')->nullable()->comment('Campo editable local');
            $table->date('recovery_end_date')->nullable()->comment('Campo editable local');
            $table->text('recovery_notes')->nullable()->comment('Campo editable local');
            $table->integer('teacher_rating')->nullable()->comment('Campo editable local');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
