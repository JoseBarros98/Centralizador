<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_plans', function (Blueprint $table) {
            $table->decimal('matricula', 12, 2)->default(0)->after('importe_base_cuota');
            $table->decimal('certificacion', 12, 2)->default(0)->after('matricula');
        });

        Schema::table('assignment_details', function (Blueprint $table) {
            $table->decimal('matricula_importe', 12, 2)->default(0)->after('adelanto_importe');
            $table->decimal('matricula_cobrado', 12, 2)->default(0)->after('matricula_importe');
            $table->decimal('certificacion_importe', 12, 2)->default(0)->after('matricula_cobrado');
            $table->decimal('certificacion_cobrado', 12, 2)->default(0)->after('certificacion_importe');
        });
    }

    public function down(): void
    {
        Schema::table('payment_plans', function (Blueprint $table) {
            $table->dropColumn(['matricula', 'certificacion']);
        });

        Schema::table('assignment_details', function (Blueprint $table) {
            $table->dropColumn(['matricula_importe', 'matricula_cobrado', 'certificacion_importe', 'certificacion_cobrado']);
        });
    }
};
