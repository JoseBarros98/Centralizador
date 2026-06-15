<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inscriptions', function (Blueprint $table) {
            if (!Schema::hasColumn('inscriptions', 'verified_color')) {
                $table->string('verified_color')->nullable()->after('is_verified');
            }
        });
    }

    public function down(): void
    {
        Schema::table('inscriptions', function (Blueprint $table) {
            if (Schema::hasColumn('inscriptions', 'verified_color')) {
                $table->dropColumn('verified_color');
            }
        });
    }
};
