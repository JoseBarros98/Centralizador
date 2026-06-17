<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('marketing_programs')) {
            return;
        }

        Schema::create('marketing_programs', function (Blueprint $table) {
            $table->id();
            $table->string('status', 30)->default('en_oferta');
            $table->foreignId('program_id')->nullable()->constrained('programs')->nullOnDelete();
            $table->string('name');
            $table->string('certification')->nullable();
            $table->foreignId('marketing_team_id')->nullable()->constrained('marketing_teams')->nullOnDelete();
            $table->json('class_days')->nullable();
            $table->boolean('has_teacher_panel')->default(false);
            $table->string('teacher_panel_drive_id')->nullable();
            $table->string('teacher_panel_drive_link')->nullable();
            $table->date('campaign_launch_date')->nullable();
            $table->date('inauguration_date')->nullable();
            $table->date('module_0_date')->nullable();
            $table->date('module_1_date')->nullable();
            $table->unsignedSmallInteger('gestion')->default(date('Y'));
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_programs');
    }
};
