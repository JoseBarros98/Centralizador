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
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained()->onDelete('cascade');
            $table->foreignId('inscription_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('last_name');
            $table->decimal('grade', 5, 2);
            $table->enum('approval_type', ['regular', 'recuperatorio', 'tutoria'])->default('regular');
            $table->boolean('approved')->default(false);
            $table->string('original_name')->nullable(); // Nombre original del archivo
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};
