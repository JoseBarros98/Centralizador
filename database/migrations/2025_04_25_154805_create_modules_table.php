<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->foreignId('teacher_id')->constrained('teachers');
            $table->foreignId('monitor_id')->constrained('users');
            $table->integer('class_count')->default(0);
            $table->boolean('active')->default(true);
            $table->date('recovery_start_date')->nullable();
            $table->date('recovery_end_date')->nullable();
            $table->text('recovery_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
