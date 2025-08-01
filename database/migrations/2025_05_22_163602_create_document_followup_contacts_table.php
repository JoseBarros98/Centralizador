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
        Schema::create('document_followup_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_followup_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['call', 'message']);
            $table->date('contact_date');
            $table->enum('response_status', ['answered', 'not_answered']);
            $table->date('response_date')->nullable();
            $table->text('notes');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_followup_contacts');
    }
};
