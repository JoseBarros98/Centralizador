<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-white leading-tight">
                {{ __('Historial de Seguimientos de Calificación') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('grade_followups.show', ['program' => $program->id, 'module' => $module->id, 'grade' => $grade->id]) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-800 focus:outline-none focus:border-gray-800 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                    </svg>
                    Volver al Seguimiento
                </a>
                <a href="{{ route('grades.summary', ['program' => $program->id, 'module' => $module->id]) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-800 focus:outline-none focus:border-gray-800 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L9 5.414V17a1 1 0 102 0V5.414l5.293 5.293a1 1 0 001.414-1.414l-7-7z" clip-rule="evenodd" />
                    </svg>
                    Ver Calificaciones
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Información del Estudiante -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Información del Estudiante</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Estudiante</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                @if($grade->inscription)
                                    {{ $grade->inscription->first_name }} {{ $grade->inscription->paternal_surname }} {{ $grade->inscription->maternal_surname }}
                                @elseif($grade->participant)
                                    {{ $grade->participant->first_name }} {{ $grade->participant->paternal_surname ?? '' }} {{ $grade->participant->maternal_surname ?? '' }}
                                @else
                                    {{ $grade->name }} {{ $grade->last_name }}
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Programa</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $program->name ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Módulo</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $module->name ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Calificación</dt>
                            <dd class="mt-1">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $grade->grade >= 71 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $grade->grade }}
                                </span>
                                @if($grade->grade >= 71)
                                    <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Aprobado
                                    </span>
                                @else
                                    <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        Reprobado
                                    </span>
                                @endif
                            </dd>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Historial de Seguimientos -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Historial de Seguimientos</h3>
                    
                    @if($followups->count() > 0)
                        <div class="space-y-6">
                            @foreach($followups as $followup)
                                <div class="border border-gray-200 rounded-lg p-6">
                                    <div class="flex justify-between items-start mb-4">
                                        <div class="flex items-center space-x-3">
                                            <h4 class="text-md font-medium text-gray-900">
                                                Seguimiento #{{ $followup->id }}
                                            </h4>
                                            @if($followup->isClosed())
                                                <span class="px-2 py-1 text-xs font-semibold bg-red-100 text-red-800 rounded-full">CERRADO</span>
                                            @else
                                                <span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-800 rounded-full">ABIERTO</span>
                                            @endif
                                        </div>
                                        <a href="{{ route('grade_followups.show_followup', ['program' => $program->id, 'module' => $module->id, 'grade' => $grade->id, 'followup' => $followup->id]) }}" 
                                           class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                            Ver Detalles →
                                        </a>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Fecha de Inicio</dt>
                                            <dd class="mt-1 text-sm text-gray-900">{{ $followup->created_at->format('d/m/Y H:i') }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Creado por</dt>
                                            <dd class="mt-1 text-sm text-gray-900">{{ $followup->creator->name ?? 'Usuario eliminado' }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Total de Contactos</dt>
                                            <dd class="mt-1 text-sm text-gray-900">{{ $followup->contacts->count() }}</dd>
                                        </div>
                                    </div>
                                    
                                    @if($followup->observations)
                                        <div class="mb-4">
                                            <dt class="text-sm font-medium text-gray-500">Observaciones</dt>
                                            <dd class="mt-1 text-sm text-gray-900">{{ $followup->observations }}</dd>
                                        </div>
                                    @endif

                                    <!-- Estadísticas de Contactos -->
                                    @if($followup->contacts->count() > 0)
                                        <div class="bg-gray-50 rounded-lg p-4">
                                            <h5 class="text-sm font-medium text-gray-900 mb-3">Estadísticas de Contactos</h5>
                                            
                                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                                <div class="text-center">
                                                    <div class="text-2xl font-bold text-green-600">{{ $followup->contacts->where('type', 'call')->count() }}</div>
                                                    <div class="text-xs text-gray-500">Llamadas</div>
                                                </div>
                                                <div class="text-center">
                                                    <div class="text-2xl font-bold text-purple-600">{{ $followup->contacts->where('type', 'message')->count() }}</div>
                                                    <div class="text-xs text-gray-500">Mensajes</div>
                                                </div>
                                                <div class="text-center">
                                                    <div class="text-2xl font-bold text-blue-600">{{ $followup->contacts->where('got_response', 'true')->count() }}</div>
                                                    <div class="text-xs text-gray-500">Con Respuesta</div>
                                                </div>
                                                <div class="text-center">
                                                    <div class="text-2xl font-bold text-gray-600">{{ $followup->contacts->where('got_response', 'false')->count() }}</div>
                                                    <div class="text-xs text-gray-500">Sin Respuesta</div>
                                                </div>
                                            </div>
                                            
                                            @if($followup->contacts->count() > 0)
                                                <div class="mt-4">
                                                    <div class="flex items-center justify-between text-sm">
                                                        <span class="text-gray-500">Respuestas obtenidas:</span>
                                                        <span class="font-medium text-gray-900">
                                                            {{ $followup->contacts->where('got_response', 'true')->count() }} de {{ $followup->contacts->count() }}
                                                        </span>
                                                    </div>
                                                    <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                                                        @php
                                                            $responseRate = $followup->contacts->count() > 0 ? ($followup->contacts->where('got_response', 'true')->count() / $followup->contacts->count()) * 100 : 0;
                                                        @endphp
                                                        <div class="bg-green-600 h-2 rounded-full" style="width: {{ $responseRate }}%"></div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @endif

                                    <!-- Información de Recuperatorio -->
                                    @if($followup->has_recovery)
                                        <div class="mt-4 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                            <h5 class="text-sm font-medium text-yellow-800 mb-2">Información de Recuperatorio</h5>
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                                <div>
                                                    <span class="text-yellow-700">Fecha de Inicio:</span>
                                                    <span class="font-medium text-yellow-900">
                                                        {{ $followup->recovery_start_date ? $followup->recovery_start_date->format('d/m/Y') : 'No definida' }}
                                                    </span>
                                                </div>
                                                <div>
                                                    <span class="text-yellow-700">Fecha de Fin:</span>
                                                    <span class="font-medium text-yellow-900">
                                                        {{ $followup->recovery_end_date ? $followup->recovery_end_date->format('d/m/Y') : 'No definida' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4 inline-block">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-yellow-800">Sin Seguimientos Registrados</h3>
                                        <div class="mt-2 text-sm text-yellow-700">
                                            <p>No se han registrado seguimientos para esta calificación.</p>
                                        </div>
                                        <div class="mt-4">
                                            <a href="{{ route('grade_followups.create', ['program' => $program->id, 'module' => $module->id, 'grade' => $grade->id]) }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 active:bg-yellow-800 focus:outline-none focus:border-yellow-800 focus:ring ring-yellow-300 disabled:opacity-25 transition ease-in-out duration-150">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                                                </svg>
                                                Crear Seguimiento
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
