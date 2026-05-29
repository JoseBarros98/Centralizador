<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('participant_discounts')) return;

        Schema::create('participant_discounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inscription_id')->constrained('inscriptions')->onDelete('cascade');
            $table->foreignId('program_id')->constrained('programs')->onDelete('cascade');
            $table->enum('tipo', ['porcentaje', 'monto_fijo'])->default('monto_fijo');
            $table->decimal('valor', 12, 2)->default(0);
            $table->string('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->unique(['inscription_id', 'program_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('participant_discounts');
    }
};
