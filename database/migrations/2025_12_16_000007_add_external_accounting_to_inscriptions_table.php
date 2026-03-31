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
            $table->string('external_accounting_registration')->nullable()->after('internal_accounting_plan_type');
            $table->string('external_accounting_enrollment')->nullable()->after('external_accounting_registration');
            $table->string('external_accounting_tuition')->nullable()->after('external_accounting_enrollment');
            $table->string('external_accounting_degrees')->nullable()->after('external_accounting_tuition');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inscriptions', function (Blueprint $table) {
            $table->dropColumn([
                'external_accounting_registration',
                'external_accounting_enrollment',
                'external_accounting_tuition',
                'external_accounting_degrees',
            ]);
        });
    }
};
