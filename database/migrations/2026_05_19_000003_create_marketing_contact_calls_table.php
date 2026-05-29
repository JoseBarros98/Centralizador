<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('marketing_contact_calls')) return;

        Schema::create('marketing_contact_calls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('marketing_contact_id')->constrained()->cascadeOnDelete();
            $table->text('respuesta');
            $table->dateTime('fecha_llamada');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_contact_calls');
    }
};
