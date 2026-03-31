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
        Schema::create('inscription_payment_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inscription_id')->constrained('inscriptions')->onDelete('cascade');
            $table->string('ci', 20);
            $table->enum('old_status', ['Pendiente', 'Adelanto', 'Completando', 'Completo'])->nullable();
            $table->enum('new_status', ['Pendiente', 'Adelanto', 'Completando', 'Completo'])->default('Pendiente');
            $table->decimal('amount_paid', 8, 2)->nullable();
            $table->date('status_date')->comment('Fecha en que se registró el cambio de estado');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('changed_by')->nullable()->comment('user_id que hizo el cambio');
            $table->timestamps();
            
            // Índices para búsquedas rápidas
            $table->index('inscription_id');
            $table->index('ci');
            $table->index('status_date');
            $table->index('new_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inscription_payment_history');
    }
};
