<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('content_pillar_files', function (Blueprint $table) {
            if (!Schema::hasColumn('content_pillar_files', 'google_drive_id')) {
                $table->string('google_drive_id')->nullable()->after('file_type');
            }
            if (!Schema::hasColumn('content_pillar_files', 'google_drive_link')) {
                $table->text('google_drive_link')->nullable()->after('google_drive_id');
            }
            if (!Schema::hasColumn('content_pillar_files', 'file_size')) {
                $table->bigInteger('file_size')->nullable()->after('google_drive_link');
            }
            if (!Schema::hasColumn('content_pillar_files', 'stored_in_drive')) {
                $table->boolean('stored_in_drive')->default(false)->after('file_size');
            }
        });
    }

    public function down(): void
    {
        Schema::table('content_pillar_files', function (Blueprint $table) {
            $drop = array_filter(
                ['google_drive_id', 'google_drive_link', 'file_size', 'stored_in_drive'],
                fn($c) => Schema::hasColumn('content_pillar_files', $c)
            );
            if ($drop) {
                $table->dropColumn(array_values($drop));
            }
        });
    }
};
