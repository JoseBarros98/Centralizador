<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\External\ExternalModule;
use App\Models\Program;

class TestModuleSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:modules-sync {program_code?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prueba la conexión y obtención de módulos desde la BD externa';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Probando sincronización de módulos desde la BD externa...');
        $this->newLine();

        try {
            $programCode = $this->argument('program_code');

            if ($programCode) {
                return $this->testSpecificProgram($programCode);
            } else {
                return $this->testFirstProgram();
            }

        } catch (\Exception $e) {
            $this->error("Error: {$e->getMessage()}");
            $this->error($e->getTraceAsString());
            return Command::FAILURE;
        }
    }

    /**
     * Prueba con el primer programa disponible
     */
    protected function testFirstProgram(): int
    {
        $program = Program::first();

        if (!$program) {
            $this->error('❌ No hay programas en la base de datos local');
            return Command::FAILURE;
        }

        $this->info("Usando el primer programa disponible: {$program->code} - {$program->name}");
        $this->newLine();

        return $this->testSpecificProgram($program->code);
    }

    /**
     * Prueba con un programa específico
     */
    protected function testSpecificProgram(string $programCode): int
    {
        $this->info("Test 1: Obtener programa local");
        $program = Program::where('code', $programCode)->first();

        if (!$program) {
            $this->error("❌ Programa no encontrado: {$programCode}");
            return Command::FAILURE;
        }

        $this->info("✅ Programa encontrado: {$program->name}");
        $this->newLine();

        $this->info("Test 2: Ejecutar stored procedure sp_obtener_modulos_programa");
        $externalModules = ExternalModule::getModulesByProgram($programCode);

        $this->info("✅ Stored procedure ejecutado correctamente");
        $this->info("📊 Módulos encontrados: {$externalModules->count()}");
        $this->newLine();

        if ($externalModules->isEmpty()) {
            $this->warn("⚠️  No hay módulos para este programa en la BD externa");
            return Command::SUCCESS;
        }

        $this->info("Test 3: Datos del primer módulo");
        $firstModule = $externalModules->first();

        $this->table(
            ['Campo Externo', 'Valor'],
            [
                ['id_programa', $firstModule->id_programa ?? 'N/A'],
                ['nombre_modulo', $firstModule->nombre_modulo ?? 'N/A'],
                ['fecha_inicio', $firstModule->fecha_inicio ?? 'N/A'],
                ['fecha_fin', $firstModule->fecha_fin ?? 'N/A'],
                ['estado_modulo', $firstModule->estado_modulo ?? 'N/A'],
                ['docente', $firstModule->docente ?? 'N/A'],
                ['cantidad_inscritos', $firstModule->cantidad_inscritos ?? 'N/A'],
            ]
        );

        $this->newLine();
        $this->info("Test 4: Mapeo a formato local");
        $localFormat = ExternalModule::toLocalFormat($firstModule, $program->id);

        $this->table(
            ['Campo Local', 'Valor'],
            collect($localFormat)->map(fn($value, $key) => [$key, $value ?? 'NULL'])->values()->toArray()
        );

        $this->newLine();

        if ($externalModules->count() > 1) {
            $this->info("📋 Lista de todos los módulos:");
            $this->table(
                ['#', 'Nombre', 'Estado', 'Docente', 'Fecha Inicio'],
                $externalModules->map(function ($module, $index) {
                    return [
                        $index + 1,
                        \Illuminate\Support\Str::limit($module->nombre_modulo ?? 'N/A', 40),
                        $module->estado_modulo ?? 'N/A',
                        \Illuminate\Support\Str::limit($module->docente ?? 'N/A', 30),
                        $module->fecha_inicio ?? 'N/A',
                    ];
                })->toArray()
            );
        }

        $this->newLine();
        $this->info('🎉 Todas las pruebas completadas exitosamente!');
        $this->newLine();
        $this->info('💡 Para sincronizar estos módulos, ejecuta:');
        $this->info("   php artisan sync:modules --program={$programCode}");
        
        return Command::SUCCESS;
    }
}
