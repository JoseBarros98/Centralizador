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
            if (Schema::hasColumn('inscriptions', 'has_pre_defense')) {
                $table->string('has_pre_defense')->nullable()->change();
            }
            if (Schema::hasColumn('inscriptions', 'has_defense')) {
                $table->string('has_defense')->nullable()->change();
            }
            if (Schema::hasColumn('inscriptions', 'has_defense_accounting_status')) {
                $table->string('has_defense_accounting_status')->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inscriptions', function (Blueprint $table) {
            if (Schema::hasColumn('inscriptions', 'has_pre_defense')) {
                $table->boolean('has_pre_defense')->default(false)->change();
            }
            if (Schema::hasColumn('inscriptions', 'has_defense')) {
                $table->boolean('has_defense')->default(false)->change();
            }
            if (Schema::hasColumn('inscriptions', 'has_defense_accounting_status')) {
                $table->boolean('has_defense_accounting_status')->default(false)->change();
            }
        });
    }
};
