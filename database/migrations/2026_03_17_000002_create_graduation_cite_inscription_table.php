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
        Schema::create('graduation_cite_inscription', function (Blueprint $table) {
            $table->id();
            $table->foreignId('graduation_cite_id')->constrained('graduation_cites')->cascadeOnDelete();
            $table->foreignId('inscription_id')->constrained('inscriptions')->cascadeOnDelete();
            $table->string('participant_full_name')->nullable();
            $table->string('participant_ci')->nullable();
            $table->string('participant_program')->nullable();
            $table->timestamps();

            $table->unique(['graduation_cite_id', 'inscription_id'], 'graduation_cite_inscription_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('graduation_cite_inscription');
    }
};