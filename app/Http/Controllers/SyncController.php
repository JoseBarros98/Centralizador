<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;
use Carbon\Carbon;

class SyncController extends Controller
{
    /**
     * Mostrar el dashboard de sincronización
     */
    public function index()
    {
        // Obtener el último estado de sincronización desde cache
        $lastSyncPrograms = Cache::get('last_sync_programs', [
            'status' => 'nunca',
            'timestamp' => null,
            'stats' => null
        ]);
        
        $lastSyncModules = Cache::get('last_sync_modules', [
            'status' => 'nunca',
            'timestamp' => null,
            'stats' => null
        ]);
        
        $lastSyncInscriptions = Cache::get('last_sync_inscriptions', [
            'status' => 'nunca',
            'timestamp' => null,
            'stats' => null
        ]);

        $lastAutoSyncPrograms = Cache::get('last_auto_sync_programs', [
            'status' => 'nunca',
            'timestamp' => null,
        ]);

        $lastAutoSyncModules = Cache::get('last_auto_sync_modules', [
            'status' => 'nunca',
            'timestamp' => null,
        ]);

        $lastAutoSyncInscriptions = Cache::get('last_auto_sync_inscriptions', [
            'status' => 'nunca',
            'timestamp' => null,
        ]);

        // Verificar si hay sincronizaciones en ejecución
        $runningPrograms = Cache::has('sync:programs:running');
        $runningModules = Cache::has('sync:modules:running');
        $runningInscriptions = Cache::has('sync:inscriptions:running');

        // Calcular tiempo para la próxima sincronización
        $nextSync = $this->getNextScheduledTime();

        return view('admin.sync.index', compact(
            'lastSyncPrograms',
            'lastSyncModules',
            'lastSyncInscriptions',
            'lastAutoSyncPrograms',
            'lastAutoSyncModules',
            'lastAutoSyncInscriptions',
            'nextSync',
            'runningPrograms',
            'runningModules',
            'runningInscriptions'
        ));
    }

    /**
     * Ejecutar sincronización de programas
     */
    public function syncPrograms(Request $request)
    {
        try {
            $startTime = microtime(true);
            
            Artisan::call('sync:programs');
            $output = Artisan::output();
            
            $executionTime = round(microtime(true) - $startTime, 2);
            
            // Guardar estado en cache
            Cache::put('last_sync_programs', [
                'status' => 'success',
                'timestamp' => now(),
                'execution_time' => $executionTime,
                'output' => $output,
                'source' => 'manual'
            ], now()->addDays(7));

            Log::info('Sincronización manual de programas completada', [
                'user_id' => Auth::id(),
                'execution_time' => $executionTime
            ]);

            $redirectUrl = $request->input('redirect_to');
            return redirect($redirectUrl ?: route('admin.sync.index'))
                ->with('success', 'Sincronización de programas completada exitosamente en ' . $executionTime . ' segundos.');
                
        } catch (\Exception $e) {
            Cache::put('last_sync_programs', [
                'status' => 'error',
                'timestamp' => now(),
                'error' => $e->getMessage(),
                'source' => 'manual'
            ], now()->addDays(7));

            Log::error('Error en sincronización manual de programas', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            $redirectUrl = $request->input('redirect_to');
            return redirect($redirectUrl ?: route('admin.sync.index'))
                ->with('error', 'Error al sincronizar programas: ' . $e->getMessage());
        }
    }

    /**
     * Ejecutar sincronización de módulos
     */
    public function syncModules(Request $request)
    {
        try {
            $startTime = microtime(true);
            
            Artisan::call('sync:modules');
            $output = Artisan::output();
            
            $executionTime = round(microtime(true) - $startTime, 2);
            
            Cache::put('last_sync_modules', [
                'status' => 'success',
                'timestamp' => now(),
                'execution_time' => $executionTime,
                'output' => $output
            ], now()->addDays(7));

            Log::info('Sincronización manual de módulos completada', [
                'user_id' => Auth::id(),
                'execution_time' => $executionTime
            ]);

            $redirectUrl = $request->input('redirect_to');
            return redirect($redirectUrl ?: route('admin.sync.index'))
                ->with('success', 'Sincronización de módulos completada exitosamente en ' . $executionTime . ' segundos.');
                
        } catch (\Exception $e) {
            Cache::put('last_sync_modules', [
                'status' => 'error',
                'timestamp' => now(),
                'error' => $e->getMessage(),
                'source' => 'manual'
            ], now()->addDays(7));

            Log::error('Error en sincronización manual de módulos', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            $redirectUrl = $request->input('redirect_to');
            return redirect($redirectUrl ?: route('admin.sync.index'))
                ->with('error', 'Error al sincronizar módulos: ' . $e->getMessage());
        }
    }

    /**
     * Ejecutar sincronización de inscripciones
     */
    public function syncInscriptions()
    {
        try {
            $startTime = microtime(true);
            
            Artisan::call('sync:inscriptions');
            $output = Artisan::output();
            
            $executionTime = round(microtime(true) - $startTime, 2);
            
            Cache::put('last_sync_inscriptions', [
                'status' => 'success',
                'timestamp' => now(),
                'execution_time' => $executionTime,
                'output' => $output
            ], now()->addDays(7));

            Log::info('Sincronización manual de inscripciones completada', [
                'user_id' => Auth::id(),
                'execution_time' => $executionTime
            ]);

            return redirect()->route('admin.sync.index')
                ->with('success', 'Sincronización de inscripciones completada exitosamente en ' . $executionTime . ' segundos.');
                
        } catch (\Exception $e) {
            Cache::put('last_sync_inscriptions', [
                'status' => 'error',
                'timestamp' => now(),
                'error' => $e->getMessage(),
                'source' => 'manual'
            ], now()->addDays(7));

            Log::error('Error en sincronización manual de inscripciones', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return redirect()->route('admin.sync.index')
                ->with('error', 'Error al sincronizar inscripciones: ' . $e->getMessage());
        }
    }

    /**
     * Ejecutar todas las sincronizaciones
     */
    public function syncAll()
    {
        try {
            $startTime = microtime(true);
            $results = [];

            // Programas
            Artisan::call('sync:programs');
            $results['programs'] = Artisan::output();

            // Módulos
            Artisan::call('sync:modules');
            $results['modules'] = Artisan::output();

            // Inscripciones
            Artisan::call('sync:inscriptions');
            $results['inscriptions'] = Artisan::output();

            $executionTime = round(microtime(true) - $startTime, 2);

            Log::info('Sincronización manual completa ejecutada', [
                'user_id' => Auth::id(),
                'execution_time' => $executionTime
            ]);

            return redirect()->route('admin.sync.index')
                ->with('success', 'Todas las sincronizaciones completadas exitosamente en ' . $executionTime . ' segundos.');
                
        } catch (\Exception $e) {
            Log::error('Error en sincronización manual completa', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return redirect()->route('admin.sync.index')
                ->with('error', 'Error al ejecutar las sincronizaciones: ' . $e->getMessage());
        }
    }

    /**
     * Obtener el tiempo para la próxima sincronización programada
     */
    private function getNextScheduledTime()
    {
        $now = Carbon::now();
        $currentMinute = $now->minute;

        // Las sincronizaciones se ejecutan cada 10 minutos.
        $nextMinute = (int) (ceil(($currentMinute + 1) / 10) * 10);
        $next = $now->copy()->second(0);

        if ($nextMinute >= 60) {
            $next->addHour()->minute(0);
        } else {
            $next->minute($nextMinute);
        }

        if ($next->isPast()) {
            $next->addMinutes(10);
        }

        return [
            'datetime' => $next,
            'human' => $next->diffForHumans(),
            'minutes' => $now->diffInMinutes($next)
        ];
    }

    /**
     * Obtener el estado del scheduler
     */
    public function schedulerStatus()
    {
        try {
            // Verificar si el scheduler está corriendo
            $schedules = app()->make(\Illuminate\Console\Scheduling\Schedule::class);
            $events = $schedules->events();
            
            $scheduledTasks = [];
            foreach ($events as $event) {
                $scheduledTasks[] = [
                    'command' => $event->command ?? $event->description,
                    'expression' => $event->expression,
                    'next_run' => $event->nextRunDate() ? $event->nextRunDate()->format('Y-m-d H:i:s') : 'N/A',
                ];
            }

            return response()->json([
                'status' => 'running',
                'tasks' => $scheduledTasks
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
