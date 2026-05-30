<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monthly_assignments', function (Blueprint $table) {
            if (!Schema::hasColumn('monthly_assignments', 'responsable_id')) {
                $table->foreignId('responsable_id')
                    ->nullable()
                    ->after('generado_por')
                    ->constrained('users')
                    ->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('monthly_assignments', function (Blueprint $table) {
            if (Schema::hasColumn('monthly_assignments', 'responsable_id')) {
                $table->dropForeign(['responsable_id']);
                $table->dropColumn('responsable_id');
            }
        });
    }
};
