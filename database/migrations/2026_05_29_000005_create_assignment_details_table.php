<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('assignment_details')) return;

        Schema::create('assignment_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monthly_assignment_id')->constrained('monthly_assignments')->onDelete('cascade');
            $table->foreignId('inscription_id')->constrained('inscriptions')->onDelete('cascade');

            // Datos del participante (snapshot)
            $table->string('nombre_completo');
            $table->string('plan_pago_nombre')->nullable();
            $table->string('telefono')->nullable();

            // Cuotas retrasadas (acumulación de todas las cuotas pendientes de meses anteriores)
            $table->json('cuotas_retrasadas_numeros')->nullable();
            $table->decimal('cuotas_retrasadas_importe', 12, 2)->default(0);
            $table->decimal('cuotas_retrasadas_cobrado', 12, 2)->default(0);

            // Cuota vigente (mes actual)
            $table->unsignedSmallInteger('cuota_vigente_numero')->nullable();
            $table->decimal('cuota_vigente_importe', 12, 2)->default(0);
            $table->decimal('cuota_vigente_cobrado', 12, 2)->default(0);

            // Adelanto (cuota futura prepagada)
            $table->unsignedSmallInteger('adelanto_numero_cuota')->nullable();
            $table->decimal('adelanto_importe', 12, 2)->default(0);

            // Total
            $table->decimal('total_asignacion', 12, 2)->default(0);

            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->unique(['monthly_assignment_id', 'inscription_id']);
            $table->index('inscription_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignment_details');
    }
};
