<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('marketing_goal_weeks')) return;

        Schema::create('marketing_goal_weeks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goal_id')->constrained('marketing_goals')->onDelete('cascade');
            $table->tinyInteger('week_number')->unsigned();
            $table->integer('weekly_goal')->default(0);
            $table->date('week_start')->nullable();
            $table->date('week_end')->nullable();
            $table->date('meeting_date')->nullable();
            $table->timestamps();

            $table->unique(['goal_id', 'week_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_goal_weeks');
    }
};
