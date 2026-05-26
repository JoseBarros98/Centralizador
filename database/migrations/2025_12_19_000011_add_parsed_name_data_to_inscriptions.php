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
            if (!Schema::hasColumn('inscriptions', 'name')) {
                $table->string('name')->nullable()->after('full_name')->comment('Nombre del participante');
            }
            if (!Schema::hasColumn('inscriptions', 'paternal_surname')) {
                $table->string('paternal_surname')->nullable()->after('name')->comment('Apellido paterno');
            }
            if (!Schema::hasColumn('inscriptions', 'maternal_surname')) {
                $table->string('maternal_surname')->nullable()->after('paternal_surname')->comment('Apellido materno');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inscriptions', function (Blueprint $table) {
            $drop = array_filter(
                ['name', 'paternal_surname', 'maternal_surname'],
                fn($c) => Schema::hasColumn('inscriptions', $c)
            );
            if ($drop) {
                $table->dropColumn(array_values($drop));
            }
        });
    }
};
