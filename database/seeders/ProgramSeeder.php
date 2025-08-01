<?php

namespace Database\Seeders;

use App\Models\Program;
use Illuminate\Database\Seeder;

class ProgramSeeder extends Seeder
{
    public function run(): void
    {
        $programs = [
            [
                'name' => 'Maestría en Administración de Empresas',
                'description' => 'Programa de maestría enfocado en administración y gestión empresarial',
                'active' => true,
            ],
            [
                'name' => 'Diplomado en Marketing Digital',
                'description' => 'Programa especializado en estrategias de marketing digital',
                'active' => true,
            ],
            [
                'name' => 'Especialización en Finanzas',
                'description' => 'Programa de especialización en análisis financiero',
                'active' => true,
            ],
        ];

        foreach ($programs as $program) {
            Program::create($program);
        }
    }
}
