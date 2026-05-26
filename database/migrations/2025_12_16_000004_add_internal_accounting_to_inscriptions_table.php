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
            if (!Schema::hasColumn('inscriptions', 'internal_accounting_plan_type')) {
                $table->string('internal_accounting_plan_type')->nullable()->after('has_diplomas_delivered');
            }
            if (!Schema::hasColumn('inscriptions', 'internal_accounting_billing_status')) {
                $table->string('internal_accounting_billing_status')->nullable()->after('internal_accounting_plan_type');
            }
            if (!Schema::hasColumn('inscriptions', 'internal_accounting_amount_due')) {
                $table->decimal('internal_accounting_amount_due', 10, 2)->nullable()->after('internal_accounting_billing_status');
            }
            if (!Schema::hasColumn('inscriptions', 'internal_accounting_graduation_payment')) {
                $table->string('internal_accounting_graduation_payment')->nullable()->after('internal_accounting_amount_due');
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
                'internal_accounting_plan_type', 'internal_accounting_billing_status',
                'internal_accounting_amount_due', 'internal_accounting_graduation_payment',
            ], fn($c) => Schema::hasColumn('inscriptions', $c));
            if ($drop) {
                $table->dropColumn(array_values($drop));
            }
        });
    }
};
