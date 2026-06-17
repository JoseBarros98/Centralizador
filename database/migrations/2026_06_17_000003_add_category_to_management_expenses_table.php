<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('management_expenses', 'category')) {
            Schema::table('management_expenses', function (Blueprint $table) {
                $table->string('category', 30)->default('operativo')->after('item');
            });
        }

        if (!Schema::hasColumn('management_expense_entity_amounts', 'category')) {
            Schema::table('management_expense_entity_amounts', function (Blueprint $table) {
                $table->string('category', 30)->default('operativo')->after('item');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('management_expenses', 'category')) {
            Schema::table('management_expenses', function (Blueprint $table) {
                $table->dropColumn('category');
            });
        }

        if (Schema::hasColumn('management_expense_entity_amounts', 'category')) {
            Schema::table('management_expense_entity_amounts', function (Blueprint $table) {
                $table->dropColumn('category');
            });
        }
    }
};
