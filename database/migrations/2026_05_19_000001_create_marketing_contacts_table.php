<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('marketing_contacts')) return;

        Schema::create('marketing_contacts', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('profesion')->nullable();
            $table->foreignId('program_id')->nullable()->constrained('programs')->nullOnDelete();
            $table->string('telefono');
            $table->enum('referencia', ['No interesado', 'Interesado', 'Maestro', 'Sin título en provisión nacional', 'Sin respuesta'])->default('Sin respuesta');
            $table->enum('origen', ['Facebook corporativo', 'Facebook propio', 'Base propia', 'Base derivada']);
            $table->date('fecha');
            $table->enum('estado_pago', ['Esperando pago', 'Adelanto realizado', 'Pago completo'])->nullable();
            $table->boolean('conversion')->default(false);
            $table->foreignId('inscription_id')->nullable()->constrained('inscriptions')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_contacts');
    }
};
