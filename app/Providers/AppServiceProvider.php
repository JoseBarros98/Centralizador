<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use App\Models\ArtRequest;
use App\Observers\ArtRequestObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Establecer la longitud predeterminada de las cadenas en las migraciones
        Schema::defaultStringLength(191);
        
        // Forzar HTTPS solo cuando se especifique explícitamente
        if (env('FORCE_HTTPS', false)) {
            URL::forceScheme('https');
        }

        // Registrar observers
        ArtRequest::observe(ArtRequestObserver::class);
    }
}
