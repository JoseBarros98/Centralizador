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
        if (!Schema::hasTable('inscription_program')) {
            Schema::create('inscription_program', function (Blueprint $table) {
                $table->id();
                $table->foreignId('inscription_id')->constrained('inscriptions')->onDelete('cascade');
                $table->foreignId('program_id')->constrained('programs')->onDelete('cascade');
                $table->timestamps();

                $table->unique(['inscription_id', 'program_id']);
            });
        }

        // Migrar datos existentes; INSERT IGNORE evita duplicados si ya existen
        DB::statement('
            INSERT IGNORE INTO inscription_program (inscription_id, program_id, created_at, updated_at)
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
