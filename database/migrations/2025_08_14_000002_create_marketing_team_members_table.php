<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('marketing_team_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('marketing_teams')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamp('left_at')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
            
            $table->unique(['team_id', 'user_id', 'active']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('marketing_team_members');
    }
};
