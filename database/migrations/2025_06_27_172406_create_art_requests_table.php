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
        Schema::create('art_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requester_id')->constrained('users')->onDelete('cascade');
            $table->date('request_date');
            $table->date('delivery_date');
            $table->foreignId('designer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('content_pillar_id')->nullable()->constrained('content_pillars')->onDelete('set null');
            $table->unsignedBigInteger('type_of_art_id');
            $table->text('description');
            $table->string('title');
            $table->text('content');
            $table->text('details')->nullable();
            $table->enum('status', [
                'COMPLETO', 
                'NO INICIADO', 
                'EN CURSO', 
                'RETRASADO', 
                'ESPERANDO APROBACION', 
                'ESPERANDO INFORMACION', 
                'CANCELADO', 
                'EN PAUSA'
            ])->default('NO INICIADO');
            $table->enum('priority', ['ALTA', 'MEDIA', 'BAJA'])->default('MEDIA');
            $table->text('observations')->nullable();
            $table->boolean('active')->default(true);
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
            
            // Foreign key para type_of_art (tabla singular)
            $table->foreign('type_of_art_id')->references('id')->on('type_of_art')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('art_requests');
    }
};
