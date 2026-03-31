<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('module_classes', function (Blueprint $table) {
            $table->string('google_calendar_event_id')->nullable()->after('class_link');
            $table->string('google_calendar_event_link')->nullable()->after('google_calendar_event_id');
            $table->string('google_meet_link')->nullable()->after('google_calendar_event_link');
            $table->string('google_meet_conference_id')->nullable()->after('google_meet_link');
            $table->json('google_meet_co_organizers')->nullable()->after('google_meet_conference_id');
            $table->timestamp('google_synced_at')->nullable()->after('google_meet_co_organizers');
            $table->text('google_sync_error')->nullable()->after('google_synced_at');
        });
    }

    public function down(): void
    {
        Schema::table('module_classes', function (Blueprint $table) {
            $table->dropColumn([
                'google_calendar_event_id',
                'google_calendar_event_link',
                'google_meet_link',
                'google_meet_conference_id',
                'google_meet_co_organizers',
                'google_synced_at',
                'google_sync_error',
            ]);
        });
    }
};
