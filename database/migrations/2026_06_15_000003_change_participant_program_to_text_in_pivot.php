<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('graduation_cite_inscription', function (Blueprint $table) {
            $table->text('participant_program')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('graduation_cite_inscription', function (Blueprint $table) {
            $table->string('participant_program')->nullable()->change();
        });
    }
};
