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
        Schema::table('attendances', function (Blueprint $table) {
            if (!Schema::hasColumn('attendances', 'has_license')) {
                $table->boolean('has_license')->default(false)->after('status');
            }
            if (!Schema::hasColumn('attendances', 'license_type')) {
                $table->enum('license_type', ['permiso', 'licencia_medica', 'licencia_laboral', 'emergencia_familiar', 'otro'])->nullable()->after('has_license');
            }
            if (!Schema::hasColumn('attendances', 'license_notes')) {
                $table->text('license_notes')->nullable()->after('license_type');
            }
            if (!Schema::hasColumn('attendances', 'license_granted_by')) {
                $table->string('license_granted_by')->nullable()->after('license_notes');
            }
            if (!Schema::hasColumn('attendances', 'license_granted_at')) {
                $table->timestamp('license_granted_at')->nullable()->after('license_granted_by');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $columns = ['has_license', 'license_type', 'license_notes', 'license_granted_by', 'license_granted_at'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('attendances', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};