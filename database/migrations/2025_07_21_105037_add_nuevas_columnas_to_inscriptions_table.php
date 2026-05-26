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
            if (!Schema::hasColumn('inscriptions', 'was_added_to_the_group')) {
                $table->boolean('was_added_to_the_group')->default(false)->after('document_observations');
            }
            if (!Schema::hasColumn('inscriptions', 'accesses_were_sent')) {
                $table->boolean('accesses_were_sent')->default(false)->after('was_added_to_the_group');
            }
            if (!Schema::hasColumn('inscriptions', 'mail_was_sent')) {
                $table->boolean('mail_was_sent')->default(false)->after('accesses_were_sent');
            }
            if (!Schema::hasColumn('inscriptions', 'academic_status')) {
                $table->enum('academic_status', ['Activo', 'Retirado', 'Congelado', 'Cambio', 'Devolucion', 'En Tramite', 'Titulado'])->after('mail_was_sent');
            }
            if (!Schema::hasColumn('inscriptions', 'has_freezing_letter')) {
                $table->boolean('has_freezing_letter')->default(false)->after('academic_status');
            }
            if (!Schema::hasColumn('inscriptions', 'freezing_letter_path')) {
                $table->string('freezing_letter_path')->nullable()->after('has_freezing_letter');
            }
            if (!Schema::hasColumn('inscriptions', 'freezing_letter_observations')) {
                $table->text('freezing_letter_observations')->nullable()->after('freezing_letter_path');
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
                'was_added_to_the_group', 'accesses_were_sent', 'mail_was_sent',
                'academic_status', 'has_freezing_letter', 'freezing_letter_path', 'freezing_letter_observations',
            ], fn($c) => Schema::hasColumn('inscriptions', $c));
            if ($drop) {
                $table->dropColumn(array_values($drop));
            }
        });
    }
};
