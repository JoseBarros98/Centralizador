<?php

namespace App\Console\Commands;

use App\Services\ModuleSyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class SyncModulesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:modules 
                            {--program= : Sincronizar módulos de un programa específico por código}
                            {--stats : Mostrar solo estadísticas sin sincronizar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza módulos desde la base de datos externa usando stored procedure';

    protected ModuleSyncService $syncService;

    /**
     * Create a new command instance.
     */
    public function __construct(ModuleSyncService $syncService)
    {
        parent::__construct();
        $this->syncService = $syncService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            // Marcar como en ejecución
            Cache::put('sync:modules:running', true, now()->addMinutes(5));

            // Si solo se quieren estadísticas
            if ($this->option('stats')) {
                $this->displayStats();
                Cache::forget('sync:modules:running');
                return Command::SUCCESS;
            }

            // Si se especifica un programa específico
            if ($programCode = $this->option('program')) {
                return $this->syncSpecificProgram($programCode);
            }

            // Sincronización completa
            return $this->syncAllModules();

        } catch (\Exception $e) {
            $this->error("Error: {$e->getMessage()}");
            $this->error($e->getTraceAsString());

            // Guardar error en cache
            Cache::put('last_sync_modules', [
                'status' => 'error',
                'timestamp' => now(),
                'error' => $e->getMessage()
            ], now()->addDays(7));

            // Marcar como completado
            Cache::forget('sync:modules:running');

            return Command::FAILURE;
        }
    }

    /**
     * Sincroniza todos los módulos
     */
    protected function syncAllModules(): int
    {
        try {
            $startTime = microtime(true);
            
            $this->info('🔄 Iniciando sincronización de módulos...');
            $this->newLine();

            $stats = $this->syncService->syncAll();

            $executionTime = round(microtime(true) - $startTime, 2);

            $this->newLine();
            $this->info('✅ Sincronización completada');
            $this->newLine();

            $this->displaySyncResults($stats);

            // Guardar estado en cache
            Cache::put('last_sync_modules', [
                'status' => 'success',
                'timestamp' => now(),
                'execution_time' => $executionTime,
                'stats' => $stats,
                'source' => 'automatic'
            ], now()->addDays(7));

            // Marcar como completado
            Cache::forget('sync:modules:running');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            // Guardar error en cache
            Cache::put('last_sync_modules', [
                'status' => 'error',
                'timestamp' => now(),
                'error' => $e->getMessage()
            ], now()->addDays(7));

            // Marcar como completado
            Cache::forget('sync:modules:running');

            throw $e;
        }
    }

    /**
     * Sincroniza módulos de un programa específico
     */
    protected function syncSpecificProgram(string $programCode): int
    {
        $this->info("🔄 Sincronizando módulos del programa: {$programCode}");

        $success = $this->syncService->syncByProgramCode($programCode);

        if ($success) {
            $this->info("✅ Módulos sincronizados correctamente");
            return Command::SUCCESS;
        } else {
            $this->error("❌ No se pudieron sincronizar los módulos");
            return Command::FAILURE;
        }
    }

    /**
     * Muestra las estadísticas
     */
    protected function displayStats(): void
    {
        $stats = $this->syncService->getStats();

        $this->info('📊 Estadísticas de Módulos');
        $this->newLine();

        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Total Módulos en BD Local', $stats['local_modules_count']],
                ['Total Programas', $stats['local_programs_count']],
                ['Última Sincronización', $stats['last_sync'] ?? 'Nunca'],
            ]
        );

        if ($stats['modules_by_program']->isNotEmpty()) {
            $this->newLine();
            $this->info('📋 Módulos por Programa:');
            $this->newLine();

            $this->table(
                ['Código Programa', 'Nombre Programa', 'Cantidad Módulos'],
                $stats['modules_by_program']->map(function ($item) {
                    return [
                        $item['program_code'],
                        \Illuminate\Support\Str::limit($item['program_name'], 50),
                        $item['modules_count'],
                    ];
                })->toArray()
            );
        }
    }

    /**
     * Muestra los resultados de la sincronización
     */
    protected function displaySyncResults(array $stats): void
    {
        $this->table(
            ['Resultado', 'Cantidad'],
            [
                ['Programas Procesados', $stats['programs_processed']],
                ['Total Módulos Externos', $stats['total_modules']],
                ['Módulos Creados', $stats['modules_created']],
                ['Módulos Actualizados', $stats['modules_updated']],
                ['Módulos Eliminados', $stats['modules_deleted']],
                ['Errores', $stats['errors']],
            ]
        );

        if ($stats['errors'] > 0) {
            $this->warn("⚠️  Hubo {$stats['errors']} errores durante la sincronización. Revisa los logs para más detalles.");
        }
    }
}
