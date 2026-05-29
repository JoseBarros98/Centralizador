<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('monthly_assignments')) return;

        Schema::create('monthly_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('programs')->onDelete('cascade');
            $table->unsignedTinyInteger('mes');
            $table->unsignedSmallInteger('gestion');
            $table->foreignId('generado_por')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('estado', ['borrador', 'finalizado'])->default('borrador');
            $table->timestamps();

            $table->unique(['program_id', 'mes', 'gestion']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_assignments');
    }
};
