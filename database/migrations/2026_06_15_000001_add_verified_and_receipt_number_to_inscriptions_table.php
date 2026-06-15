<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inscriptions', function (Blueprint $table) {
            if (!Schema::hasColumn('inscriptions', 'is_verified')) {
                $table->string('is_verified')->nullable()->after('total_paid');
            }
            if (!Schema::hasColumn('inscriptions', 'receipt_number')) {
                $table->string('receipt_number')->nullable()->after('is_verified');
            }
        });
    }

    public function down(): void
    {
        Schema::table('inscriptions', function (Blueprint $table) {
            if (Schema::hasColumn('inscriptions', 'is_verified')) {
                $table->dropColumn('is_verified');
            }
            if (Schema::hasColumn('inscriptions', 'receipt_number')) {
                $table->dropColumn('receipt_number');
            }
        });
    }
};
