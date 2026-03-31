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
        Schema::table('inscriptions', function (Blueprint $table) {
            $table->string('name')->nullable()->after('full_name')->comment('Nombre del participante');
            $table->string('paternal_surname')->nullable()->after('name')->comment('Apellido paterno');
            $table->string('maternal_surname')->nullable()->after('paternal_surname')->comment('Apellido materno');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inscriptions', function (Blueprint $table) {
            $table->dropColumn(['name', 'paternal_surname', 'maternal_surname']);
        });
    }
};
