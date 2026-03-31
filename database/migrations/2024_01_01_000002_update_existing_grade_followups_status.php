<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Actualizar todos los seguimientos existentes a estado 'open'
        DB::table('grade_followups')
            ->whereNull('status')
            ->orWhere('status', '')
            ->update(['status' => 'open']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No necesitamos hacer nada aquí
    }
};
