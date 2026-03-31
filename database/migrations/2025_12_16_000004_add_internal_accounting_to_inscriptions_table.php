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
            $table->string('internal_accounting_plan_type')->nullable()->after('has_diplomas_delivered');
            $table->string('internal_accounting_billing_status')->nullable()->after('internal_accounting_plan_type');
            $table->decimal('internal_accounting_amount_due', 10, 2)->nullable()->after('internal_accounting_billing_status');
            $table->string('internal_accounting_graduation_payment')->nullable()->after('internal_accounting_amount_due');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inscriptions', function (Blueprint $table) {
            $table->dropColumn([
                'internal_accounting_plan_type',
                'internal_accounting_billing_status',
                'internal_accounting_amount_due',
                'internal_accounting_graduation_payment',
            ]);
        });
    }
};
