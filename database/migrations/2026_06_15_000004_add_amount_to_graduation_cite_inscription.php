<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('graduation_cite_inscription', function (Blueprint $table) {
            if (!Schema::hasColumn('graduation_cite_inscription', 'amount')) {
                $table->decimal('amount', 10, 2)->default(0)->after('participant_program');
            }
        });
    }

    public function down(): void
    {
        Schema::table('graduation_cite_inscription', function (Blueprint $table) {
            if (Schema::hasColumn('graduation_cite_inscription', 'amount')) {
                $table->dropColumn('amount');
            }
        });
    }
};
