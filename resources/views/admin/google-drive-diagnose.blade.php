@extends('layouts.app')

@section('content')
<style>
.status-badge {
    @apply px-3 py-1 rounded-full text-sm font-medium;
}
.status-success {
    @apply bg-green-100 text-green-800;
}
.status-error {
    @apply bg-red-100 text-red-800;
}
.status-neutral {
    @apply bg-gray-100 text-gray-800;
}
</style>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="bg-blue-600 text-white px-6 py-4">
                <h1 class="text-2xl font-bold flex items-center">
                    <svg class="w-8 h-8 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Diagnóstico de Google Drive
                </h1>
                <p class="text-blue-100 mt-2">Información detallada sobre la configuración de Google Drive API</p>
            </div>

            <div class="p-6">
                <!-- Configuración básica -->
                <div class="mb-8">
                    <h2 class="text-xl font-semibold mb-4 text-gray-800">Configuración Básica</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($diagnostics['config'] as $key => $value)
                            <div class="flex items-center justify-between p-4 border rounded-lg">
                                <span class="font-medium text-gray-700">{{ strtoupper(str_replace('_', ' ', $key)) }}:</span>
                                <span class="status-badge
                                    @php
                                        if(in_array($value, ['Configurado', 'Disponible', 'Exitoso'])) {
                                            echo 'status-success';
                                        } elseif(in_array($value, ['No configurado', 'No disponible'])) {
                                            echo 'status-error';
                                        } else {
                                            echo 'status-neutral';
                                        }
                                    @endphp">
                                    {{ $value }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Estado del cliente Google -->
                <div class="mb-8">
                    <h2 class="text-xl font-semibold mb-4 text-gray-800">Estado del Cliente Google</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex items-center justify-between p-4 border rounded-lg">
                            <span class="font-medium text-gray-700">GOOGLE CLIENT CLASS:</span>
                            <span class="status-badge {{ $diagnostics['google_client'] === 'Disponible' ? 'status-success' : 'status-error' }}">
                                {{ $diagnostics['google_client'] }}
                            </span>
                        </div>
                        
                        <div class="flex items-center justify-between p-4 border rounded-lg">
                            <span class="font-medium text-gray-700">CLIENT CREATION:</span>
                            <span class="status-badge {{ $diagnostics['client_creation'] === 'Exitoso' ? 'status-success' : 'status-error' }}">
                                {{ $diagnostics['client_creation'] }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- URLs de autorización -->
                @if(isset($diagnostics['auth_url']))
                <div class="mb-8">
                    <h2 class="text-xl font-semibold mb-4 text-gray-800">URL de Autorización</h2>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-4 border rounded-lg">
                            <span class="font-medium text-gray-700">AUTH URL GENERATION:</span>
                            <span class="status-badge {{ $diagnostics['auth_url'] === 'Disponible' ? 'status-success' : 'status-error' }}">
                                {{ $diagnostics['auth_url'] }}
                            </span>
                        </div>
                        
                        @if(isset($diagnostics['auth_url_value']) && $diagnostics['auth_url'] === 'Disponible')
                        <div class="p-4 bg-gray-50 border rounded-lg">
                            <p class="text-sm text-gray-600 mb-2">URL de autorización generada:</p>
                            <a href="{{ $diagnostics['auth_url_value'] }}" 
                               class="text-blue-600 hover:text-blue-800 text-sm break-all" 
                               target="_blank">
                                {{ Str::limit($diagnostics['auth_url_value'], 100) }}...
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Estado del servicio -->
                <div class="mb-8">
                    <h2 class="text-xl font-semibold mb-4 text-gray-800">GoogleDriveService</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex items-center justify-between p-4 border rounded-lg">
                            <span class="font-medium text-gray-700">SERVICE CREATION:</span>
                            <span class="status-badge {{ $diagnostics['service_creation'] === 'Exitoso' ? 'status-success' : 'status-error' }}">
                                {{ $diagnostics['service_creation'] }}
                            </span>
                        </div>
                        
                        @if(isset($diagnostics['service_auth_url']))
                        <div class="flex items-center justify-between p-4 border rounded-lg">
                            <span class="font-medium text-gray-700">SERVICE AUTH URL:</span>
                            <span class="status-badge {{ $diagnostics['service_auth_url'] === 'Disponible' ? 'status-success' : 'status-error' }}">
                                {{ $diagnostics['service_auth_url'] }}
                            </span>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Errores detallados -->
                <div class="mb-8">
                    <h2 class="text-xl font-semibold mb-4 text-gray-800">Errores Detallados</h2>
                    <div class="space-y-4">
                        @foreach($diagnostics as $key => $value)
                            @if(is_string($value) && Str::contains($value, 'Error:'))
                                <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
                                    <h3 class="font-medium text-red-800">{{ strtoupper(str_replace('_', ' ', $key)) }}</h3>
                                    <p class="text-red-700 text-sm mt-1">{{ $value }}</p>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>

                <!-- Acciones -->
                <div class="border-t pt-6">
                    <h2 class="text-xl font-semibold mb-4 text-gray-800">Acciones Recomendadas</h2>
                    
                    <div class="space-y-3">
                        @if($diagnostics['google_client'] !== 'Disponible')
                            <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                                <p class="text-yellow-800"><strong>⚠️ Google Client no disponible:</strong> Verifica que el paquete google/apiclient esté instalado correctamente.</p>
                            </div>
                        @endif
                        
                        @if($diagnostics['config']['client_id'] === 'No configurado' || $diagnostics['config']['client_secret'] === 'No configurado')
                            <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                                <p class="text-yellow-800"><strong>⚠️ Credenciales incompletas:</strong> Configura GOOGLE_CLIENT_ID y GOOGLE_CLIENT_SECRET en tu archivo .env</p>
                            </div>
                        @endif
                        
                        @if(isset($diagnostics['client_creation']) && Str::contains($diagnostics['client_creation'], 'Error:'))
                            <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
                                <p class="text-red-800"><strong>🚫 Error en creación del cliente:</strong> Verifica tus credenciales de Google Cloud Console.</p>
                            </div>
                        @endif
                    </div>
                    
                    <div class="flex space-x-4 mt-6">
                        <a href="{{ route('admin.google-drive.setup') }}" 
                           class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Ir a Configuración
                        </a>
                        
                        <button onclick="location.reload()" 
                                class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Actualizar Diagnóstico
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
