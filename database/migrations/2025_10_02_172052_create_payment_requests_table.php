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
        Schema::create('payment_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained('modules')->onDelete('cascade');
            
            // Tipo de solicitud
            $table->enum('request_type', ['Modulo', 'Tutoria'])->default('Modulo');
            
            // Datos de la solicitud
            $table->string('payroll_number')->nullable();
            $table->date('request_date');
            $table->string('invoice_number')->nullable();
            $table->text('observations')->nullable();
            $table->decimal('total_amount', 10, 2);
            $table->decimal('retention_amount', 10, 2)->default(0);
            $table->decimal('net_amount', 10, 2);
            $table->integer('total_active_students')->default(0);
            
            // Campos específicos para tutorías (opcionales)
            $table->foreignId('tutoring_teacher_id')->nullable()->constrained('teachers')->onDelete('set null');
            $table->date('tutoring_start_date')->nullable();
            $table->date('tutoring_end_date')->nullable();
            $table->integer('tutoring_students_count')->nullable();
            
            // Estado de la solicitud
            $table->enum('status', ['Pendiente', 'Aprobado', 'Rechazado', 'Realizado'])->default('Pendiente');
            
            // Auditoría
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_requests');
    }
};
