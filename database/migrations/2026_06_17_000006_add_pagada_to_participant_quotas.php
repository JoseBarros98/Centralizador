<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('participant_quotas', 'pagada')) {
            Schema::table('participant_quotas', function (Blueprint $table) {
                $table->boolean('pagada')->default(false)->after('fecha_vencimiento');
            });
        }
    }

    public function down(): void
    {
        Schema::table('participant_quotas', function (Blueprint $table) {
            $table->dropColumn('pagada');
        });
    }
};
