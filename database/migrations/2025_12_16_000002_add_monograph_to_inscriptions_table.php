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
            if (!Schema::hasColumn('inscriptions', 'has_monograph_elaboration')) {
                $table->boolean('has_monograph_elaboration')->default(false)->after('graduation_procedure_type');
            }
            if (!Schema::hasColumn('inscriptions', 'has_monograph_received')) {
                $table->boolean('has_monograph_received')->default(false)->after('has_monograph_elaboration');
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
                ['has_monograph_elaboration', 'has_monograph_received'],
                fn($c) => Schema::hasColumn('inscriptions', $c)
            );
            if ($drop) {
                $table->dropColumn(array_values($drop));
            }
        });
    }
};
