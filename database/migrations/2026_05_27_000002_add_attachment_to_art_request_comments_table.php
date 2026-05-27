<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('art_request_comments', function (Blueprint $table) {
            if (!Schema::hasColumn('art_request_comments', 'attachment_name')) {
                $table->string('attachment_name')->nullable()->after('is_internal');
                $table->string('attachment_drive_id')->nullable()->after('attachment_name');
                $table->string('attachment_type')->nullable()->after('attachment_drive_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('art_request_comments', function (Blueprint $table) {
            $cols = array_filter(['attachment_name', 'attachment_drive_id', 'attachment_type'], fn($c) => Schema::hasColumn('art_request_comments', $c));
            if ($cols) $table->dropColumn(array_values($cols));
        });
    }
};
