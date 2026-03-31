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
        Schema::create('program_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('payment_request_id')->nullable()->constrained('payment_requests')->onDelete('cascade');
            $table->foreignId('program_id')->constrained('programs')->onDelete('cascade');
            $table->foreignId('module_id')->nullable()->constrained('modules')->onDelete('cascade');
            
            // Datos básicos
            $table->string('gestion')->nullable();
            $table->string('categoria')->nullable();
            $table->string('etapa')->nullable();
            
            // Campos editables
            $table->decimal('cobro_titulacion', 12, 2)->nullable();
            $table->decimal('asignacion_programa', 12, 2)->nullable();
            $table->string('responsable_cartera')->nullable();
            $table->date('fecha_pago')->nullable();
            
            // Montos y porcentajes
            $table->decimal('monto_al_5', 12, 2)->nullable();
            $table->decimal('porcentaje_al_5', 5, 2)->nullable();
            
            $table->decimal('monto_al_10', 12, 2)->nullable();
            $table->decimal('porcentaje_al_10', 5, 2)->nullable();
            
            $table->decimal('monto_al_15', 12, 2)->nullable();
            $table->decimal('porcentaje_al_15', 5, 2)->nullable();
            
            $table->decimal('monto_al_20', 12, 2)->nullable();
            $table->decimal('porcentaje_al_20', 5, 2)->nullable();
            
            $table->decimal('monto_al_25', 12, 2)->nullable();
            $table->decimal('porcentaje_al_25', 5, 2)->nullable();
            
            $table->decimal('monto_al_30', 12, 2)->nullable();
            $table->decimal('porcentaje_al_30', 5, 2)->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_allocations');
    }
};
