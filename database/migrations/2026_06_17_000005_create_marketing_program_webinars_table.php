<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('marketing_program_webinars')) {
            return;
        }

        Schema::create('marketing_program_webinars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('marketing_program_id')->constrained('marketing_programs')->cascadeOnDelete();
            $table->string('label')->nullable();
            $table->date('date')->nullable();
            $table->string('link')->nullable();
            $table->string('drive_file_id')->nullable();
            $table->string('drive_file_link')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_program_webinars');
    }
};
