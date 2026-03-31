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
        Schema::table('type_of_art_files', function (Blueprint $table) {
            $table->unsignedBigInteger('file_size')->nullable()->after('file_type');
            $table->string('google_drive_id')->nullable()->after('file_size');
            $table->string('google_drive_link')->nullable()->after('google_drive_id');
            $table->boolean('stored_in_drive')->default(false)->after('google_drive_link');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('type_of_art_files', function (Blueprint $table) {
            $table->dropColumn(['file_size', 'google_drive_id', 'google_drive_link', 'stored_in_drive']);
        });
    }
};