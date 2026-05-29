<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('participant_quotas')) return;

        Schema::create('participant_quotas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inscription_id')->constrained('inscriptions')->onDelete('cascade');
            $table->foreignId('program_id')->constrained('programs')->onDelete('cascade');
            $table->foreignId('payment_plan_id')->nullable()->constrained('payment_plans')->onDelete('set null');
            $table->unsignedSmallInteger('numero_cuota');
            $table->decimal('importe_base', 12, 2)->default(0);
            $table->decimal('descuento_aplicado', 12, 2)->default(0);
            $table->decimal('importe_final', 12, 2)->default(0);
            $table->date('fecha_vencimiento');
            $table->timestamps();

            $table->unique(['inscription_id', 'program_id', 'numero_cuota'], 'uq_participant_quota');
            $table->index(['program_id', 'fecha_vencimiento']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('participant_quotas');
    }
};
