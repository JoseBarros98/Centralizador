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
        Schema::table('content_pillar_files', function (Blueprint $table) {
            $table->string('google_drive_id')->nullable()->after('file_type');
            $table->text('google_drive_link')->nullable()->after('google_drive_id');
            $table->bigInteger('file_size')->nullable()->after('google_drive_link');
            $table->boolean('stored_in_drive')->default(false)->after('file_size');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('content_pillar_files', function (Blueprint $table) {
            $table->dropColumn(['google_drive_id', 'google_drive_link', 'file_size', 'stored_in_drive']);
        });
    }
};
