<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\External\ExternalProgram;

class TestExternalConnectionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:external-db';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prueba la conexión a la base de datos externa';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Probando conexión a la base de datos externa...');
        $this->newLine();

        try {
            // Test 1: Conexión básica
            $this->info('Test 1: Conexión básica');
            DB::connection('mysql_external')->getPdo();
            $this->info('✅ Conexión establecida correctamente');
            $this->newLine();

            // Test 2: Verificar vista de programas
            $this->info('Test 2: Verificar vista vista_programas_latam');
            $count = ExternalProgram::count();
            $this->info("✅ Vista encontrada. Total de programas: {$count}");
            $this->newLine();

            // Test 3: Obtener un programa de ejemplo
            if ($count > 0) {
                $this->info('Test 3: Datos de un programa de ejemplo');
                $program = ExternalProgram::first();
                
                $this->table(
                    ['Campo', 'Valor'],
                    [
                        ['id_programa', $program->id_programa ?? 'N/A'],
                        ['codigo_contable', $program->codigo_contable ?? 'N/A'],
                        ['nombre_programa', $program->nombre_programa ?? 'N/A'],
                        ['version_programa', $program->version_programa ?? 'N/A'],
                        ['grupo_programa', $program->grupo_programa ?? 'N/A'],
                        ['gestion_programa', $program->gestion_programa ?? 'N/A'],
                        ['fecha_inicio', $program->fecha_inicio ?? 'N/A'],
                        ['fecha_finalizacion', $program->fecha_finalizacion ?? 'N/A'],
                        ['fase_programa', $program->fase_programa ?? 'N/A'],
                    ]
                );
                
                $this->newLine();
                $this->info('Test 4: Mapeo a formato local');
                $localFormat = $program->toLocalFormat();
                $this->table(
                    ['Campo Local', 'Valor'],
                    collect($localFormat)->map(fn($value, $key) => [$key, $value ?? 'N/A'])->values()->toArray()
                );
            } else {
                $this->warn('⚠️  No hay programas en la base de datos externa para mostrar ejemplo');
            }

            $this->newLine();
            $this->info('🎉 Todas las pruebas completadas exitosamente!');
            
            return Command::SUCCESS;

        } catch (\PDOException $e) {
            $this->error('❌ Error de conexión PDO:');
            $this->error($e->getMessage());
            $this->newLine();
            $this->warn('Verifica las siguientes variables en tu archivo .env:');
            $this->warn('- DB_EXTERNAL_HOST');
            $this->warn('- DB_EXTERNAL_PORT');
            $this->warn('- DB_EXTERNAL_DATABASE');
            $this->warn('- DB_EXTERNAL_USERNAME');
            $this->warn('- DB_EXTERNAL_PASSWORD');
            
            return Command::FAILURE;

        } catch (\Exception $e) {
            $this->error('❌ Error general:');
            $this->error($e->getMessage());
            $this->newLine();
            $this->error('Stack trace:');
            $this->error($e->getTraceAsString());
            
            return Command::FAILURE;
        }
    }
}
