<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketing_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('team_id')->nullable()->constrained('marketing_teams')->onDelete('set null');
            $table->tinyInteger('month')->unsigned();
            $table->smallInteger('year')->unsigned();
            $table->integer('monthly_goal')->default(0);
            $table->integer('debt_from_previous')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'month', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_goals');
    }
};
