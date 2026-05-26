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
            if (!Schema::hasColumn('inscriptions', 'external_accounting_registration')) {
                $table->string('external_accounting_registration')->nullable()->after('internal_accounting_plan_type');
            }
            if (!Schema::hasColumn('inscriptions', 'external_accounting_enrollment')) {
                $table->string('external_accounting_enrollment')->nullable()->after('external_accounting_registration');
            }
            if (!Schema::hasColumn('inscriptions', 'external_accounting_tuition')) {
                $table->string('external_accounting_tuition')->nullable()->after('external_accounting_enrollment');
            }
            if (!Schema::hasColumn('inscriptions', 'external_accounting_degrees')) {
                $table->string('external_accounting_degrees')->nullable()->after('external_accounting_tuition');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inscriptions', function (Blueprint $table) {
            $drop = array_filter([
                'external_accounting_registration', 'external_accounting_enrollment',
                'external_accounting_tuition', 'external_accounting_degrees',
            ], fn($c) => Schema::hasColumn('inscriptions', $c));
            if ($drop) {
                $table->dropColumn(array_values($drop));
            }
        });
    }
};
