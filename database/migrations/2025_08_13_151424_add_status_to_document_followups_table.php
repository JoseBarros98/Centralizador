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
        Schema::table('document_followups', function (Blueprint $table) {
            if (!Schema::hasColumn('document_followups', 'status')) {
                $table->string('status', 20)->default('open'); // open, closed
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_followups', function (Blueprint $table) {
            if (Schema::hasColumn('document_followups', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
