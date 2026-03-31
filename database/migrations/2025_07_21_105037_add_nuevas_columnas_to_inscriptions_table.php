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
            //Accesos
            $table->boolean('was_added_to_the_group')->default(false)->after('document_observations');
            $table->boolean('accesses_were_sent')->default(false)->after('was_added_to_the_group');
            $table->boolean('mail_was_sent')->default(false)->after('accesses_were_sent');

            //Estado académico
            $table->enum('academic_status', ['Activo', 'Retirado', 'Congelado', 'Cambio', 'Devolucion', 'En Tramite', 'Titulado'])->after('mail_was_sent');
            $table->boolean('has_freezing_letter')->default(false)->after('academic_status');
            $table->string('freezing_letter_path')->nullable()->after('has_freezing_letter');
            $table->text('freezing_letter_observations')->nullable()->after('freezing_letter_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inscriptions', function (Blueprint $table) {
            $table->dropColumn([
                'was_added_to_the_group',
                'accesses_were_sent',
                'mail_was_sent',
                'academic_status',
                'has_freezing_letter',
                'freezing_letter_path',
                'freezing_letter_observations',
            ]);
        });
    }
};
