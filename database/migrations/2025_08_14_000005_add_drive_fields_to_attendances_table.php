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
            if (!Schema::hasColumn('attendances', 'drive_file_id')) {
                $table->string('drive_file_id')->nullable()->after('updated_at');
            }
            if (!Schema::hasColumn('attendances', 'drive_folder_id')) {
                $table->string('drive_folder_id')->nullable()->after('drive_file_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            if (Schema::hasColumn('attendances', 'drive_file_id')) {
                $table->dropColumn('drive_file_id');
            }
            if (Schema::hasColumn('attendances', 'drive_folder_id')) {
                $table->dropColumn('drive_folder_id');
            }
        });
    }
};