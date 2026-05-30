<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_plans', function (Blueprint $table) {
            if (!Schema::hasColumn('payment_plans', 'matricula')) {
                $table->decimal('matricula', 12, 2)->default(0)->after('importe_base_cuota');
            }
            if (!Schema::hasColumn('payment_plans', 'certificacion')) {
                $table->decimal('certificacion', 12, 2)->default(0)->after('matricula');
            }
        });

        Schema::table('assignment_details', function (Blueprint $table) {
            if (!Schema::hasColumn('assignment_details', 'matricula_importe')) {
                $table->decimal('matricula_importe', 12, 2)->default(0)->after('adelanto_importe');
            }
            if (!Schema::hasColumn('assignment_details', 'matricula_cobrado')) {
                $table->decimal('matricula_cobrado', 12, 2)->default(0)->after('matricula_importe');
            }
            if (!Schema::hasColumn('assignment_details', 'certificacion_importe')) {
                $table->decimal('certificacion_importe', 12, 2)->default(0)->after('matricula_cobrado');
            }
            if (!Schema::hasColumn('assignment_details', 'certificacion_cobrado')) {
                $table->decimal('certificacion_cobrado', 12, 2)->default(0)->after('certificacion_importe');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payment_plans', function (Blueprint $table) {
            $cols = array_filter(['matricula', 'certificacion'], fn($c) => Schema::hasColumn('payment_plans', $c));
            if ($cols) $table->dropColumn(array_values($cols));
        });

        Schema::table('assignment_details', function (Blueprint $table) {
            $cols = array_filter(
                ['matricula_importe', 'matricula_cobrado', 'certificacion_importe', 'certificacion_cobrado'],
                fn($c) => Schema::hasColumn('assignment_details', $c)
            );
            if ($cols) $table->dropColumn(array_values($cols));
        });
    }
};
