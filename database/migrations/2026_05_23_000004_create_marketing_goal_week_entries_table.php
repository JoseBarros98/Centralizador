<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('marketing_goal_week_entries')) return;

        Schema::create('marketing_goal_week_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('week_id')->constrained('marketing_goal_weeks')->onDelete('cascade');
            $table->foreignId('course_type_id')->constrained('marketing_course_types')->onDelete('cascade');
            $table->integer('completo')->default(0);
            $table->integer('adelanto')->default(0);
            $table->integer('completando')->default(0);
            $table->timestamps();

            $table->unique(['week_id', 'course_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_goal_week_entries');
    }
};
