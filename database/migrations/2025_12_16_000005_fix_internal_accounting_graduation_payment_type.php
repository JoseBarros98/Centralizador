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
        if (Schema::hasColumn('inscriptions', 'internal_accounting_graduation_payment')) {
            Schema::table('inscriptions', function (Blueprint $table) {
                $table->string('internal_accounting_graduation_payment')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('inscriptions', 'internal_accounting_graduation_payment')) {
            Schema::table('inscriptions', function (Blueprint $table) {
                $table->boolean('internal_accounting_graduation_payment')->default(false)->change();
            });
        }
    }
};
