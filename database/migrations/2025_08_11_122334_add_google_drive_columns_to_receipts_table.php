<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('receipts', function (Blueprint $table) {
            if (!Schema::hasColumn('receipts', 'file_name')) {
                $table->string('file_name')->nullable()->after('file_path');
            }
            if (!Schema::hasColumn('receipts', 'file_type')) {
                $table->string('file_type')->nullable()->after('file_name');
            }
            if (!Schema::hasColumn('receipts', 'file_size')) {
                $table->unsignedBigInteger('file_size')->nullable()->after('file_type');
            }
            if (!Schema::hasColumn('receipts', 'google_drive_id')) {
                $table->string('google_drive_id')->nullable()->after('file_size');
            }
            if (!Schema::hasColumn('receipts', 'google_drive_link')) {
                $table->string('google_drive_link')->nullable()->after('google_drive_id');
            }
            if (!Schema::hasColumn('receipts', 'stored_in_drive')) {
                $table->boolean('stored_in_drive')->default(false)->after('google_drive_link');
            }
        });
    }

    public function down(): void
    {
        Schema::table('receipts', function (Blueprint $table) {
            $drop = array_filter(
                ['file_name', 'file_type', 'file_size', 'google_drive_id', 'google_drive_link', 'stored_in_drive'],
                fn($c) => Schema::hasColumn('receipts', $c)
            );
            if ($drop) {
                $table->dropColumn(array_values($drop));
            }
        });
    }
};
