<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('management_income_entities')) return;

        Schema::create('management_income_entities', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('gestion');
            $table->string('name', 100);
            $table->unsignedTinyInteger('display_order')->default(0);
            $table->timestamps();

            $table->unique(['gestion', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('management_income_entities');
    }
};
