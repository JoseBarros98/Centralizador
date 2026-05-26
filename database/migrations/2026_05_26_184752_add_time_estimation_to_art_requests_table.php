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
        Schema::table('art_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('art_requests', 'estimated_hours')) {
                $table->decimal('estimated_hours', 5, 1)->nullable()->after('observations');
            }
            if (!Schema::hasColumn('art_requests', 'actual_hours')) {
                $table->decimal('actual_hours', 5, 1)->nullable()->after('estimated_hours');
            }
        });
    }

    public function down(): void
    {
        Schema::table('art_requests', function (Blueprint $table) {
            $table->dropColumn(['estimated_hours', 'actual_hours']);
        });
    }
};
