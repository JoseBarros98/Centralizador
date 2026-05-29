<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('marketing_course_types')) return;

        Schema::create('marketing_course_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        \DB::table('marketing_course_types')->insert([
            ['name' => 'Diplomado', 'sort_order' => 1, 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Maestría',  'sort_order' => 2, 'active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_course_types');
    }
};
