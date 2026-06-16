<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('graduation_cites', function (Blueprint $table) {
            $table->dropColumn('status_color');
        });
    }

    public function down(): void
    {
        Schema::table('graduation_cites', function (Blueprint $table) {
            $table->string('status_color')->default('yellow')->after('payment_status');
        });
    }
};
