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
            if (!Schema::hasColumn('inscriptions', 'participant_status')) {
                $table->string('participant_status')->nullable()->after('status');
            }
            if (!Schema::hasColumn('inscriptions', 'participant_justification')) {
                $table->text('participant_justification')->nullable()->after('participant_status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inscriptions', function (Blueprint $table) {
            $drop = array_filter(
                ['participant_status', 'participant_justification'],
                fn($c) => Schema::hasColumn('inscriptions', $c)
            );
            if ($drop) {
                $table->dropColumn(array_values($drop));
            }
        });
    }
};
