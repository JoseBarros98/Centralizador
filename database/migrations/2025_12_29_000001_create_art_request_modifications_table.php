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
        Schema::create('art_request_modifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('art_request_id')->constrained('art_requests')->cascadeOnDelete();
            $table->enum('modification_type', ['COLOR', 'TAMAÑO', 'TEXTO', 'CONTENIDO', 'POSICIÓN', 'ESTILO', 'FUENTE', 'IMAGEN', 'OTRO'])->default('OTRO');
            $table->text('description');
            $table->json('old_value')->nullable();
            $table->json('new_value')->nullable();
            $table->json('details')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            // Índices
            $table->index('art_request_id');
            $table->index('modification_type');
            $table->index('created_by');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('art_request_modifications');
    }
};
