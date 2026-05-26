<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            if (!Schema::hasColumn('modules', 'shared_google_meet_link')) {
                $table->string('shared_google_meet_link')->nullable()->after('order');
            }
            if (!Schema::hasColumn('modules', 'shared_google_meet_space_name')) {
                $table->string('shared_google_meet_space_name')->nullable()->after('shared_google_meet_link');
            }
            if (!Schema::hasColumn('modules', 'shared_google_meet_meeting_code')) {
                $table->string('shared_google_meet_meeting_code')->nullable()->after('shared_google_meet_space_name');
            }
            if (!Schema::hasColumn('modules', 'shared_google_meet_co_organizers')) {
                $table->json('shared_google_meet_co_organizers')->nullable()->after('shared_google_meet_meeting_code');
            }
            if (!Schema::hasColumn('modules', 'shared_google_meet_synced_at')) {
                $table->timestamp('shared_google_meet_synced_at')->nullable()->after('shared_google_meet_co_organizers');
            }
            if (!Schema::hasColumn('modules', 'shared_google_meet_sync_error')) {
                $table->text('shared_google_meet_sync_error')->nullable()->after('shared_google_meet_synced_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            $drop = array_filter([
                'shared_google_meet_link', 'shared_google_meet_space_name', 'shared_google_meet_meeting_code',
                'shared_google_meet_co_organizers', 'shared_google_meet_synced_at', 'shared_google_meet_sync_error',
            ], fn($c) => Schema::hasColumn('modules', $c));
            if ($drop) {
                $table->dropColumn(array_values($drop));
            }
        });
    }
};
