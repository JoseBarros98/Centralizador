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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_class_id')->constrained('module_classes')->onDelete('cascade');
            $table->string('name');
            $table->string('email')->nullable();
            $table->dateTime('join_time')->nullable();
            $table->dateTime('leave_time')->nullable();
            $table->foreignId('inscription_id')->nullable();
            $table->boolean('is_registered_inscription')->default(false);
            $table->integer('duration')->default(0); // Duration in minutes
            $table->integer('attendance_percentage')->default(0); // Percentage of attendance
            $table->enum('status', ['present', 'late', 'absent', 'partial'])->default('absent');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
