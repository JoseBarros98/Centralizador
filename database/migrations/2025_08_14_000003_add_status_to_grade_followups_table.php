<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grade_followups', function (Blueprint $table) {
            if (!Schema::hasColumn('grade_followups', 'status')) {
                $table->string('status')->default('open')->after('grade_id');
                $table->index('status');
            }
            if (!Schema::hasColumn('grade_followups', 'creator_id')) {
                $table->unsignedBigInteger('creator_id')->nullable()->after('status');
                $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('grade_followups', function (Blueprint $table) {
            if (Schema::hasColumn('grade_followups', 'creator_id')) {
                $table->dropForeign(['creator_id']);
                $table->dropColumn('creator_id');
            }
            if (Schema::hasColumn('grade_followups', 'status')) {
                $table->dropIndex(['status']);
                $table->dropColumn('status');
            }
        });
    }
};
