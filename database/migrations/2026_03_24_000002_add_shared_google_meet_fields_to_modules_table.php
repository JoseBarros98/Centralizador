<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->string('shared_google_meet_link')->nullable()->after('order');
            $table->string('shared_google_meet_space_name')->nullable()->after('shared_google_meet_link');
            $table->string('shared_google_meet_meeting_code')->nullable()->after('shared_google_meet_space_name');
            $table->json('shared_google_meet_co_organizers')->nullable()->after('shared_google_meet_meeting_code');
            $table->timestamp('shared_google_meet_synced_at')->nullable()->after('shared_google_meet_co_organizers');
            $table->text('shared_google_meet_sync_error')->nullable()->after('shared_google_meet_synced_at');
        });
    }

    public function down(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->dropColumn([
                'shared_google_meet_link',
                'shared_google_meet_space_name',
                'shared_google_meet_meeting_code',
                'shared_google_meet_co_organizers',
                'shared_google_meet_synced_at',
                'shared_google_meet_sync_error',
            ]);
        });
    }
};