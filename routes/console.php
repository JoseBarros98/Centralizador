<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Programar sincronización automática de programas desde la BD externa
Schedule::command('sync:programs')
    ->everyTenMinutes()
    ->withoutOverlapping()
    ->runInBackground()
    ->onSuccess(function () {
        \Illuminate\Support\Facades\Log::info('Sincronización de programas completada exitosamente');
        \Illuminate\Support\Facades\Cache::put('last_auto_sync_programs', [
            'status' => 'success',
            'timestamp' => now(),
        ], now()->addDays(7));
    })
    ->onFailure(function () {
        \Illuminate\Support\Facades\Log::error('Error en la sincronización automática de programas');
        \Illuminate\Support\Facades\Cache::put('last_auto_sync_programs', [
            'status' => 'error',
            'timestamp' => now(),
        ], now()->addDays(7));
    });

// Programar sincronización automática de módulos desde la BD externa
Schedule::command('sync:modules')
    ->everyTenMinutes()
    ->withoutOverlapping()
    ->runInBackground()
    ->onSuccess(function () {
        \Illuminate\Support\Facades\Log::info('Sincronización de módulos completada exitosamente');
        \Illuminate\Support\Facades\Cache::put('last_auto_sync_modules', [
            'status' => 'success',
            'timestamp' => now(),
        ], now()->addDays(7));
    })
    ->onFailure(function () {
        \Illuminate\Support\Facades\Log::error('Error en la sincronización automática de módulos');
        \Illuminate\Support\Facades\Cache::put('last_auto_sync_modules', [
            'status' => 'error',
            'timestamp' => now(),
        ], now()->addDays(7));
    });

// Programar sincronización automática de inscripciones desde la BD externa
Schedule::command('sync:inscriptions')
    ->everyTenMinutes()
    ->withoutOverlapping()
    ->runInBackground()
    ->onSuccess(function () {
        \Illuminate\Support\Facades\Log::info('Sincronización de inscripciones completada exitosamente');
        \Illuminate\Support\Facades\Cache::put('last_auto_sync_inscriptions', [
            'status' => 'success',
            'timestamp' => now(),
        ], now()->addDays(7));
    })
    ->onFailure(function () {
        \Illuminate\Support\Facades\Log::error('Error en la sincronización automática de inscripciones');
        \Illuminate\Support\Facades\Cache::put('last_auto_sync_inscriptions', [
            'status' => 'error',
            'timestamp' => now(),
        ], now()->addDays(7));
    });

