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
            if (!Schema::hasColumn('inscriptions', 'has_graduation_procedure')) {
                $table->boolean('has_graduation_procedure')->default(false)->after('has_monograph_received');
            }
            if (!Schema::hasColumn('inscriptions', 'has_graduation_received')) {
                $table->boolean('has_graduation_received')->default(false)->after('has_graduation_procedure');
            }
            if (!Schema::hasColumn('inscriptions', 'has_documents_delivered')) {
                $table->boolean('has_documents_delivered')->default(false)->after('has_graduation_received');
            }
            if (!Schema::hasColumn('inscriptions', 'has_diplomas_delivered')) {
                $table->boolean('has_diplomas_delivered')->default(false)->after('has_documents_delivered');
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
                'has_graduation_procedure', 'has_graduation_received',
                'has_documents_delivered', 'has_diplomas_delivered',
            ], fn($c) => Schema::hasColumn('inscriptions', $c));
            if ($drop) {
                $table->dropColumn(array_values($drop));
            }
        });
    }
};
