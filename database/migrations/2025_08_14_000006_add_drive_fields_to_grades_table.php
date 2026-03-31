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
        Schema::table('grades', function (Blueprint $table) {
            if (!Schema::hasColumn('grades', 'drive_file_id')) {
                $table->string('drive_file_id')->nullable()->after('updated_at');
            }
            if (!Schema::hasColumn('grades', 'drive_folder_id')) {
                $table->string('drive_folder_id')->nullable()->after('drive_file_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            if (Schema::hasColumn('grades', 'drive_file_id')) {
                $table->dropColumn('drive_file_id');
            }
            if (Schema::hasColumn('grades', 'drive_folder_id')) {
                $table->dropColumn('drive_folder_id');
            }
        });
    }
};