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
        // Actualizar todos los registros existentes que tengan status null a 'open'
        DB::table('document_followups')
            ->whereNull('status')
            ->update(['status' => 'open']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir los cambios estableciendo status a null
        DB::table('document_followups')
            ->where('status', 'open')
            ->update(['status' => null]);
    }
};
