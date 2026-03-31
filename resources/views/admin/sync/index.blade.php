<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Sincronización de Datos') }}
        </h2>
    </x-slot>

    <div >
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Próxima Sincronización -->
            <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-lg shadow-lg p-6 mb-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="p-3 bg-white bg-opacity-20 rounded-full">
                            <svg class="w-8 h-8 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-black text-lg font-semibold">Próxima Sincronización Automática</h3>
                            <p class="text-indigo-800 text-sm">Las sincronizaciones se ejecutan cada 10 minutos</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-black text-3xl font-bold" id="countdown">{{ $nextSync['minutes'] }} min</p>
                        <p class="text-indigo-800 text-sm">{{ $nextSync['human'] }}</p>
                    </div>
                </div>
            </div>

            <!-- Última Ejecución Automática -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Estado de Sincronización Automática</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="rounded-lg border border-blue-100 bg-blue-50 p-4">
                        <p class="text-sm font-semibold text-blue-800 mb-2">Programas</p>
                        @if($lastAutoSyncPrograms['status'] === 'nunca' || empty($lastAutoSyncPrograms['timestamp']))
                            <p class="text-sm text-gray-600">Aún no hay registro automático</p>
                        @else
                            <p class="text-sm {{ $lastAutoSyncPrograms['status'] === 'success' ? 'text-green-700' : 'text-red-700' }} font-semibold">
                                {{ $lastAutoSyncPrograms['status'] === 'success' ? 'Automática exitosa' : 'Automática con error' }}
                            </p>
                            <p class="text-sm text-gray-600 mt-1">
                                Hace {{ \Carbon\Carbon::parse($lastAutoSyncPrograms['timestamp'])->diffForHumans(now(), true) }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ \Carbon\Carbon::parse($lastAutoSyncPrograms['timestamp'])->format('d/m/Y H:i:s') }}
                            </p>
                        @endif
                    </div>

                    <div class="rounded-lg border border-green-100 bg-green-50 p-4">
                        <p class="text-sm font-semibold text-green-800 mb-2">Módulos</p>
                        @if($lastAutoSyncModules['status'] === 'nunca' || empty($lastAutoSyncModules['timestamp']))
                            <p class="text-sm text-gray-600">Aún no hay registro automático</p>
                        @else
                            <p class="text-sm {{ $lastAutoSyncModules['status'] === 'success' ? 'text-green-700' : 'text-red-700' }} font-semibold">
                                {{ $lastAutoSyncModules['status'] === 'success' ? 'Automática exitosa' : 'Automática con error' }}
                            </p>
                            <p class="text-sm text-gray-600 mt-1">
                                Hace {{ \Carbon\Carbon::parse($lastAutoSyncModules['timestamp'])->diffForHumans(now(), true) }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ \Carbon\Carbon::parse($lastAutoSyncModules['timestamp'])->format('d/m/Y H:i:s') }}
                            </p>
                        @endif
                    </div>

                    <div class="rounded-lg border border-purple-100 bg-purple-50 p-4">
                        <p class="text-sm font-semibold text-purple-800 mb-2">Inscripciones</p>
                        @if($lastAutoSyncInscriptions['status'] === 'nunca' || empty($lastAutoSyncInscriptions['timestamp']))
                            <p class="text-sm text-gray-600">Aún no hay registro automático</p>
                        @else
                            <p class="text-sm {{ $lastAutoSyncInscriptions['status'] === 'success' ? 'text-green-700' : 'text-red-700' }} font-semibold">
                                {{ $lastAutoSyncInscriptions['status'] === 'success' ? 'Automática exitosa' : 'Automática con error' }}
                            </p>
                            <p class="text-sm text-gray-600 mt-1">
                                Hace {{ \Carbon\Carbon::parse($lastAutoSyncInscriptions['timestamp'])->diffForHumans(now(), true) }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ \Carbon\Carbon::parse($lastAutoSyncInscriptions['timestamp'])->format('d/m/Y H:i:s') }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Botones de Sincronización Manual -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Sincronización Manual</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <form action="{{ route('admin.sync.programs') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition duration-150 ease-in-out flex items-center justify-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            <span>Programas</span>
                        </button>
                    </form>

                    <form action="{{ route('admin.sync.modules') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-4 rounded-lg transition duration-150 ease-in-out flex items-center justify-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            <span>Módulos</span>
                        </button>
                    </form>

                    <form action="{{ route('admin.sync.inscriptions') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-3 px-4 rounded-lg transition duration-150 ease-in-out flex items-center justify-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            <span>Inscripciones</span>
                        </button>
                    </form>

                    <form action="{{ route('admin.sync.all') }}" method="POST" onsubmit="return confirm('Esto ejecutará todas las sincronizaciones. ¿Desea continuar?');">
                        @csrf
                        <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-4 rounded-lg transition duration-150 ease-in-out flex items-center justify-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            <span>Sincronizar Todo</span>
                        </button>
                    </form>
                </div>
                <p class="mt-4 text-sm text-gray-600">
                    <strong>Nota:</strong> Se recomienda ejecutar las sincronizaciones en orden: primero Programas, luego Módulos y finalmente Inscripciones.
                </p>
            </div>

            <!-- Estado de Sincronizaciones -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <!-- Programas -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden {{ $runningPrograms ? 'ring-2 ring-yellow-400' : '' }}">
                    <div class="bg-blue-600 px-6 py-4 flex items-center justify-between">
                        <h3 class="text-white font-semibold flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Programas
                        </h3>
                        @if($runningPrograms)
                            <div class="flex items-center space-x-2 bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm">
                                <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                <span>En ejecución</span>
                            </div>
                        @endif
                    </div>
                    <div class="p-6">
                        @if($lastSyncPrograms['status'] === 'nunca')
                            <div class="text-center py-4">
                                <svg class="w-12 h-12 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="text-gray-600">Sin sincronización</p>
                                <p class="text-sm text-gray-500 mt-1">Ejecute la primera sincronización</p>
                            </div>
                        @elseif($lastSyncPrograms['status'] === 'success')
                            <div class="flex items-center text-green-600 mb-3">
                                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="font-semibold">Exitosa</span>
                            </div>
                            <p class="text-sm text-gray-600">
                                <strong>Última vez:</strong><br>
                                {{ $lastSyncPrograms['timestamp']->format('d/m/Y H:i:s') }}<br>
                                <span class="text-gray-500">{{ $lastSyncPrograms['timestamp']->diffForHumans() }}</span>
                            </p>
                            @if(isset($lastSyncPrograms['execution_time']))
                                <p class="text-sm text-gray-600 mt-2">
                                    <strong>Tiempo:</strong> {{ $lastSyncPrograms['execution_time'] }}s
                                </p>
                            @endif
                        @else
                            <div class="flex items-center text-red-600 mb-3">
                                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="font-semibold">Error</span>
                            </div>
                            <p class="text-sm text-gray-600 mb-2">
                                {{ $lastSyncPrograms['timestamp']->diffForHumans() }}
                            </p>
                            @if(isset($lastSyncPrograms['error']))
                                <p class="text-xs text-red-600 bg-red-50 p-2 rounded">
                                    {{ $lastSyncPrograms['error'] }}
                                </p>
                            @endif
                        @endif
                    </div>
                </div>

                <!-- Módulos -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden {{ $runningModules ? 'ring-2 ring-yellow-400' : '' }}">
                    <div class="bg-green-600 px-6 py-4 flex items-center justify-between">
                        <h3 class="text-white font-semibold flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                            Módulos
                        </h3>
                        @if($runningModules)
                            <div class="flex items-center space-x-2 bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm">
                                <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                <span>En ejecución</span>
                            </div>
                        @endif
                    </div>
                    <div class="p-6">
                        @if($lastSyncModules['status'] === 'nunca')
                            <div class="text-center py-4">
                                <svg class="w-12 h-12 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="text-gray-600">Sin sincronización</p>
                                <p class="text-sm text-gray-500 mt-1">Ejecute la primera sincronización</p>
                            </div>
                        @elseif($lastSyncModules['status'] === 'success')
                            <div class="flex items-center text-green-600 mb-3">
                                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="font-semibold">Exitosa</span>
                            </div>
                            <p class="text-sm text-gray-600">
                                <strong>Última vez:</strong><br>
                                {{ $lastSyncModules['timestamp']->format('d/m/Y H:i:s') }}<br>
                                <span class="text-gray-500">{{ $lastSyncModules['timestamp']->diffForHumans() }}</span>
                            </p>
                            @if(isset($lastSyncModules['execution_time']))
                                <p class="text-sm text-gray-600 mt-2">
                                    <strong>Tiempo:</strong> {{ $lastSyncModules['execution_time'] }}s
                                </p>
                            @endif
                        @else
                            <div class="flex items-center text-red-600 mb-3">
                                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="font-semibold">Error</span>
                            </div>
                            <p class="text-sm text-gray-600 mb-2">
                                {{ $lastSyncModules['timestamp']->diffForHumans() }}
                            </p>
                            @if(isset($lastSyncModules['error']))
                                <p class="text-xs text-red-600 bg-red-50 p-2 rounded">
                                    {{ $lastSyncModules['error'] }}
                                </p>
                            @endif
                        @endif
                    </div>
                </div>

                <!-- Inscripciones -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden {{ $runningInscriptions ? 'ring-2 ring-yellow-400' : '' }}">
                    <div class="bg-purple-600 px-6 py-4 flex items-center justify-between">
                        <h3 class="text-white font-semibold flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            Inscripciones
                        </h3>
                        @if($runningInscriptions)
                            <div class="flex items-center space-x-2 bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm">
                                <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                <span>En ejecución</span>
                            </div>
                        @endif
                    </div>
                    <div class="p-6">
                        @if($lastSyncInscriptions['status'] === 'nunca')
                            <div class="text-center py-4">
                                <svg class="w-12 h-12 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="text-gray-600">Sin sincronización</p>
                                <p class="text-sm text-gray-500 mt-1">Ejecute la primera sincronización</p>
                            </div>
                        @elseif($lastSyncInscriptions['status'] === 'success')
                            <div class="flex items-center text-green-600 mb-3">
                                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="font-semibold">Exitosa</span>
                            </div>
                            <p class="text-sm text-gray-600">
                                <strong>Última vez:</strong><br>
                                {{ $lastSyncInscriptions['timestamp']->format('d/m/Y H:i:s') }}<br>
                                <span class="text-gray-500">{{ $lastSyncInscriptions['timestamp']->diffForHumans() }}</span>
                            </p>
                            @if(isset($lastSyncInscriptions['execution_time']))
                                <p class="text-sm text-gray-600 mt-2">
                                    <strong>Tiempo:</strong> {{ $lastSyncInscriptions['execution_time'] }}s
                                </p>
                            @endif
                        @else
                            <div class="flex items-center text-red-600 mb-3">
                                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="font-semibold">Error</span>
                            </div>
                            <p class="text-sm text-gray-600 mb-2">
                                {{ $lastSyncInscriptions['timestamp']->diffForHumans() }}
                            </p>
                            @if(isset($lastSyncInscriptions['error']))
                                <p class="text-xs text-red-600 bg-red-50 p-2 rounded">
                                    {{ $lastSyncInscriptions['error'] }}
                                </p>
                            @endif
                        @endif
                    </div>
                </div>
            </div>

            <!-- Información Adicional -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Información Importante
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="font-semibold text-gray-700 mb-2">Sincronización Automática</h4>
                        <ul class="text-sm text-gray-600 space-y-2">
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Se ejecuta cada 10 minutos automáticamente
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                No se superponen ejecuciones (si una está en curso, espera)
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Se ejecutan en segundo plano sin afectar el sistema
                            </li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-700 mb-2">Orden Recomendado</h4>
                        <ol class="text-sm text-gray-600 space-y-2">
                            <li class="flex items-start">
                                <span class="bg-blue-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs mr-2 flex-shrink-0">1</span>
                                <span><strong>Programas:</strong> Primero sincroniza los programas académicos</span>
                            </li>
                            <li class="flex items-start">
                                <span class="bg-green-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs mr-2 flex-shrink-0">2</span>
                                <span><strong>Módulos:</strong> Luego los módulos (dependen de programas)</span>
                            </li>
                            <li class="flex items-start">
                                <span class="bg-purple-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs mr-2 flex-shrink-0">3</span>
                                <span><strong>Inscripciones:</strong> Finalmente las inscripciones</span>
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Actualizar countdown cada segundo
        const nextSyncMinutes = {{ $nextSync['minutes'] }};
        const countdownElement = document.getElementById('countdown');
        let remainingSeconds = Math.floor(nextSyncMinutes * 60);

        setInterval(() => {
            remainingSeconds--;
            if (remainingSeconds <= 0) {
                location.reload();
            }
            const minutes = Math.floor(remainingSeconds / 60);
            const seconds = Math.floor(remainingSeconds % 60);
            countdownElement.textContent = `${minutes}:${seconds.toString().padStart(2, '0')} min`;
        }, 1000);

        // Auto-refresh mientras hay sincronizaciones en ejecución
        const isRunning = {{ json_encode($runningPrograms || $runningModules || $runningInscriptions) }};
        if (isRunning) {
            // Refresh cada 5 segundos si hay algo en ejecución
            setInterval(() => {
                location.reload();
            }, 5000);
        }

        // Auto-refresh cuando se ejecuta una sincronización manual
        const forms = document.querySelectorAll('form[action*="sync"]');
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                const button = this.querySelector('button[type="submit"]');
                button.disabled = true;
                button.innerHTML = `
                    <svg class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Procesando...</span>
                `;
                
                // Comenzar a refrescar cada 5 segundos después de enviar
                setTimeout(() => {
                    const refreshInterval = setInterval(() => {
                        fetch(window.location.href)
                            .then(r => r.text())
                            .then(html => {
                                // Detener refresh si ya no hay indicador de "En ejecución"
                                if (!html.includes('En ejecución') && !html.includes('Procesando')) {
                                    clearInterval(refreshInterval);
                                    location.reload();
                                }
                            });
                    }, 5000);
                }, 500);
            });
        });
    </script>
    @endpush
</x-app-layout>
