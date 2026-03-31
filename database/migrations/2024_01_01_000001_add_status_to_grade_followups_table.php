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
        Schema::table('grade_followups', function (Blueprint $table) {
            $table->string('status')->default('open')->after('grade_id');
            $table->unsignedBigInteger('creator_id')->nullable()->after('status');
            
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grade_followups', function (Blueprint $table) {
            $table->dropForeign(['creator_id']);
            $table->dropIndex(['status']);
            $table->dropColumn(['status', 'creator_id']);
        });
    }
};
