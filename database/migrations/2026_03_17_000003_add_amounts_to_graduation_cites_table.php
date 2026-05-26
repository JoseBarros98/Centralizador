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
        Schema::table('graduation_cites', function (Blueprint $table) {
            if (!Schema::hasColumn('graduation_cites', 'amount_per_participant')) {
                $table->decimal('amount_per_participant', 10, 2)->default(0)->after('payment_type');
            }
            if (!Schema::hasColumn('graduation_cites', 'total_amount')) {
                $table->decimal('total_amount', 10, 2)->default(0)->after('amount_per_participant');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('graduation_cites', function (Blueprint $table) {
            $drop = array_filter(
                ['amount_per_participant', 'total_amount'],
                fn($c) => Schema::hasColumn('graduation_cites', $c)
            );
            if ($drop) {
                $table->dropColumn(array_values($drop));
            }
        });
    }
};
