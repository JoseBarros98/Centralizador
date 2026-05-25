<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketing_course_type_hidden_months', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_type_id')->constrained('marketing_course_types')->onDelete('cascade');
            $table->tinyInteger('month')->unsigned();
            $table->smallInteger('year')->unsigned();
            $table->timestamps();

            $table->unique(['course_type_id', 'month', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_course_type_hidden_months');
    }
};
