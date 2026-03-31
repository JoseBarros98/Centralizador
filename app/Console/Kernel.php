<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Cache;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Sincronización de Programas cada 10 minutos
        $schedule->command('sync:programs')
            ->everyTenMinutes()
            ->withoutOverlapping()
            ->before(function () {
                Cache::put('sync:programs:running', true, now()->addMinutes(5));
                \Illuminate\Support\Facades\Log::info('🔄 Iniciando sincronización de programas...');
            })
            ->after(function () {
                Cache::forget('sync:programs:running');
                \Illuminate\Support\Facades\Log::info('✅ Sincronización de programas completada');
            })
            ->onFailure(function () {
                Cache::forget('sync:programs:running');
                \Illuminate\Support\Facades\Log::error('❌ Error en sincronización de programas');
            });

        // Sincronización de Módulos cada 10 minutos (después de programas)
        $schedule->command('sync:modules')
            ->everyTenMinutes()
            ->withoutOverlapping()
            ->before(function () {
                Cache::put('sync:modules:running', true, now()->addMinutes(5));
                \Illuminate\Support\Facades\Log::info('🔄 Iniciando sincronización de módulos...');
            })
            ->after(function () {
                Cache::forget('sync:modules:running');
                \Illuminate\Support\Facades\Log::info('✅ Sincronización de módulos completada');
            })
            ->onFailure(function () {
                Cache::forget('sync:modules:running');
                \Illuminate\Support\Facades\Log::error('❌ Error en sincronización de módulos');
            });

        // Sincronización de Inscripciones cada 10 minutos (después de módulos)
        $schedule->command('sync:inscriptions')
            ->everyTenMinutes()
            ->withoutOverlapping()
            ->before(function () {
                Cache::put('sync:inscriptions:running', true, now()->addMinutes(5));
                \Illuminate\Support\Facades\Log::info('🔄 Iniciando sincronización de inscripciones...');
            })
            ->after(function () {
                Cache::forget('sync:inscriptions:running');
                \Illuminate\Support\Facades\Log::info('✅ Sincronización de inscripciones completada');
            })
            ->onFailure(function () {
                Cache::forget('sync:inscriptions:running');
                \Illuminate\Support\Facades\Log::error('❌ Error en sincronización de inscripciones');
            });
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
