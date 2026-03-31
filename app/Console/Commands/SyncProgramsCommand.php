<?php

namespace App\Console\Commands;

use App\Services\ProgramSyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class SyncProgramsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:programs 
                            {--code= : Sincronizar un programa específico por código}
                            {--stats : Mostrar solo estadísticas sin sincronizar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza programas desde la base de datos externa';

    protected ProgramSyncService $syncService;

    /**
     * Create a new command instance.
     */
    public function __construct(ProgramSyncService $syncService)
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
            Cache::put('sync:programs:running', true, now()->addMinutes(5));

            // Si solo se quieren estadísticas
            if ($this->option('stats')) {
                $this->displayStats();
                Cache::forget('sync:programs:running');
                return Command::SUCCESS;
            }

            // Si se especifica un código específico
            if ($code = $this->option('code')) {
                Cache::forget('sync:programs:running');
                return $this->syncSpecificProgram($code);
            }

            // Sincronización completa
            return $this->syncAllPrograms();

        } catch (\Exception $e) {
            $this->error("Error: {$e->getMessage()}");
            $this->error($e->getTraceAsString());

            // Guardar error en cache
            Cache::put('last_sync_programs', [
                'status' => 'error',
                'timestamp' => now(),
                'error' => $e->getMessage(),
                'source' => 'automatic'
            ], now()->addDays(7));

            // Marcar como completado
            Cache::forget('sync:programs:running');

            return Command::FAILURE;
        }
    }

    /**
     * Sincroniza todos los programas
     */
    protected function syncAllPrograms(): int
    {
        $startTime = microtime(true);
        
        $this->info('🔄 Iniciando sincronización de programas...');
        $this->newLine();

        $stats = $this->syncService->syncAll();

        $executionTime = round(microtime(true) - $startTime, 2);

        $this->newLine();
        $this->info('✅ Sincronización completada');
        $this->newLine();

        $this->displaySyncResults($stats);

        // Guardar estado en cache
        Cache::put('last_sync_programs', [
            'status' => 'success',
            'timestamp' => now(),
            'execution_time' => $executionTime,
            'stats' => $stats,
            'source' => 'automatic'
        ], now()->addDays(7));

        // Marcar como completado
        Cache::forget('sync:programs:running');

        return Command::SUCCESS;
    }

    /**
     * Sincroniza un programa específico
     */
    protected function syncSpecificProgram(string $code): int
    {
        $this->info("🔄 Sincronizando programa: {$code}");

        $success = $this->syncService->syncByCode($code);

        if ($success) {
            $this->info("✅ Programa sincronizado correctamente");
            return Command::SUCCESS;
        } else {
            $this->error("❌ No se pudo sincronizar el programa");
            return Command::FAILURE;
        }
    }

    /**
     * Muestra las estadísticas
     */
    protected function displayStats(): void
    {
        $stats = $this->syncService->getStats();

        $this->info('📊 Estadísticas de Programas');
        $this->newLine();

        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Programas en BD Local', $stats['local_count']],
                ['Programas en BD Externa', $stats['external_count']],
                ['Última Sincronización', $stats['last_sync'] ?? 'Nunca'],
            ]
        );
    }

    /**
     * Muestra los resultados de la sincronización
     */
    protected function displaySyncResults(array $stats): void
    {
        $this->table(
            ['Resultado', 'Cantidad'],
            [
                ['Total Procesados', $stats['total']],
                ['Creados', $stats['created']],
                ['Actualizados', $stats['updated']],
                ['Errores', $stats['errors']],
            ]
        );

        if ($stats['errors'] > 0) {
            $this->warn("⚠️  Hubo {$stats['errors']} errores durante la sincronización. Revisa los logs para más detalles.");
        }
    }
}
