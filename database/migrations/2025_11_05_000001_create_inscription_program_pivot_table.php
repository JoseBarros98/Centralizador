<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Crear tabla pivot para relación muchos-a-muchos
        Schema::create('inscription_program', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inscription_id')->constrained('inscriptions')->onDelete('cascade');
            $table->foreignId('program_id')->constrained('programs')->onDelete('cascade');
            $table->timestamps();
            
            // Evitar duplicados
            $table->unique(['inscription_id', 'program_id']);
        });

        // Migrar datos existentes de inscriptions.program_id a la tabla pivot
        DB::statement('
            INSERT INTO inscription_program (inscription_id, program_id, created_at, updated_at)
            SELECT id, program_id, created_at, updated_at
            FROM inscriptions
            WHERE program_id IS NOT NULL
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inscription_program');
    }
};
