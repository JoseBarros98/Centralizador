<?php

namespace App\Console\Commands;

use App\Services\InscriptionSyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class SyncInscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:inscriptions 
                            {--test : Solo probar la conexión}
                            {--program= : ID del programa a sincronizar (sincroniza todos si no se especifica)}
                            {--force : Forzar sincronización de todos los registros}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincronizar inscripciones desde la base de datos externa';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $startTime = microtime(true);
            
            // Marcar como en ejecución
            Cache::put('sync:inscriptions:running', true, now()->addMinutes(5));

            $syncService = new InscriptionSyncService();

            // Si es solo una prueba de conexión
            if ($this->option('test')) {
                $this->info('Probando conexión con base de datos externa...');
                Cache::forget('sync:inscriptions:running');
                
                if ($syncService->testConnection()) {
                    $this->info('✓ Conexión exitosa con la base de datos externa');
                    return 0;
                } else {
                    $this->error('✗ No se pudo conectar con la base de datos externa');
                    return 1;
                }
            }

            $this->info('Iniciando sincronización de inscripciones...');
            $this->newLine();

            // Verificar si se especificó un programa
            $programId = $this->option('program');
            
            if ($programId) {
                $this->info("Sincronizando solo inscripciones del programa ID: {$programId}");
            } else {
                $this->info("Sincronizando inscripciones de todos los programas...");
            }
            $this->newLine();

            // Ejecutar sincronización
            $result = $syncService->syncAll($programId);

            $executionTime = round(microtime(true) - $startTime, 2);

            if ($result['success']) {
                $this->info("✓ Sincronización completada:");
                $this->line("  - Total: {$result['total']} registros");
                $this->line("  - Sincronizados: {$result['synced']}");
                
                if ($result['errors'] > 0) {
                    $this->warn("  - Errores: {$result['errors']}");
                    $this->newLine();
                    $this->warn('Revisa los logs para más detalles sobre los errores.');
                }

                // Guardar estado en cache
                Cache::put('last_sync_inscriptions', [
                    'status' => 'success',
                    'timestamp' => now(),
                    'execution_time' => $executionTime,
                    'stats' => $result,
                    'source' => 'automatic'
                ], now()->addDays(7));

                // Marcar como completado
                Cache::forget('sync:inscriptions:running');
                
                return 0;
            } else {
                $this->error('✗ Error en la sincronización: ' . $result['message']);

                // Guardar error en cache
                Cache::put('last_sync_inscriptions', [
                    'status' => 'error',
                    'timestamp' => now(),
                    'error' => $result['message'],
                    'execution_time' => $executionTime,
                    'source' => 'automatic'
                ], now()->addDays(7));

                // Marcar como completado
                Cache::forget('sync:inscriptions:running');

                return 1;
            }
        } catch (\Exception $e) {
            $this->error('✗ Error en la sincronización: ' . $e->getMessage());

            $executionTime = round(microtime(true) - $startTime, 2);

            // Guardar error en cache
            Cache::put('last_sync_inscriptions', [
                'status' => 'error',
                'timestamp' => now(),
                'error' => $e->getMessage(),
                'execution_time' => $executionTime,
                'source' => 'automatic'
            ], now()->addDays(7));

            // Marcar como completado
            Cache::forget('sync:inscriptions:running');

            return 1;
        }
    }
}
