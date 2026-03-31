<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-white leading-tight">
                {{ __('Detalles del Módulo') }}
            </h2>
            <div class="flex space-x-2">
                @can('program.edit')
                <a href="{{ route('programs.modules.edit', ['program' => $program->id, 'module' => $module->id]) }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 active:bg-yellow-800 focus:outline-none focus:border-yellow-800 focus:ring ring-yellow-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                    </svg>
                    Editar
                </a>
                @endcan
                <a href="{{ route('programs.show', $program->id) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-800 focus:outline-none focus:border-gray-800 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                    </svg>
                    Volver al Programa
                </a>
            </div>
        </div>
    </x-slot>

    <div >
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Información del Módulo</h3>
                            <div class="border-t border-gray-200 pt-4">
                                <dl class="grid grid-cols-1 gap-y-6">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Nombre</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $module->name }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Programa</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $program->name }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Docente</dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            @if($module->teacher)
                                                <div class="flex items-center space-x-2">
                                                    <a href="{{ route('teachers.show', $module->teacher) }}" 
                                                       class="text-blue-600 hover:text-blue-800 hover:underline transition-colors duration-150 font-medium" 
                                                       title="Ver perfil del docente">
                                                        {{ $module->teacher->full_name }}
                                                    </a>
                                                    @if($module->teacher->is_external)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800" title="Docente sincronizado desde BD externa">
                                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd" />
                                                            </svg>
                                                            Sincronizado
                                                        </span>
                                                    @endif
                                                </div>
                                            @elseif($module->teacher_name)
                                                <div class="flex items-center space-x-2">
                                                    <span class="text-gray-700">{{ $module->teacher_name }}</span>
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800" title="Docente de BD externa sin asignar localmente">
                                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                        </svg>
                                                        Pendiente
                                                    </span>
                                                </div>
                                            @else
                                                <span class="text-gray-400">No asignado</span>
                                            @endif
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Encargado de Monitoreo</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $module->monitor->name ?? 'No asignado' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Estado</dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                @if($module->status == 'Desarrollo') bg-green-100 text-green-800
                                                @elseif($module->status == 'Pendiente') bg-yellow-100 text-yellow-800
                                                @else bg-gray-100 text-gray-600 @endif">
                                                {{ $module->status }}
                                            </span>
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Fechas</h3>
                            <div class="border-t border-gray-200 pt-4">
                                <dl class="grid grid-cols-1 gap-y-6">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Fecha de Inicio</dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{ $module->start_date ? $module->start_date->format('d/m/Y') : 'No definida' }}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Fecha de Finalización</dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{ $module->finalization_date ? $module->finalization_date->format('d/m/Y') : 'No definida' }}
                                        </dd>
                                    </div>
                                </dl>
                            </div>

                            
                            @role(['admin', 'academic'])
                            <h3 class="text-lg font-medium text-gray-900 mt-8 mb-4">Recuperatorio</h3>
                            <div class="border-t border-gray-200 pt-4">
                                @if($module->hasRecoveryScheduled())
                                    <div class="bg-blue-50 p-4 rounded-lg">
                                        <div class="flex">
                                            <div class="flex-shrink-0">
                                                <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2h-1V9z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                            <div class="ml-3">
                                                <h3 class="text-sm font-medium text-blue-800">Recuperatorio Programado</h3>
                                                <div class="mt-2 text-sm text-blue-700">
                                                    <p>Fecha de inicio: {{ $module->recovery_start_date->format('d/m/Y') }}</p>
                                                    <p>Fecha de fin: {{ $module->recovery_end_date->format('d/m/Y') }}</p>
                                                    @if($module->recovery_notes)
                                                        <p class="mt-2"><strong>Notas:</strong> {{ $module->recovery_notes }}</p>
                                                    @endif
                                                </div>
                                                <div class="mt-4">
                                                    <a href="{{ route('modules.recovery.edit', ['program' => $program->id, 'module' => $module->id]) }}" class="text-sm font-medium text-blue-600 hover:text-blue-500">
                                                        Editar configuración <span aria-hidden="true">&rarr;</span>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <p class="text-sm text-gray-500">No hay recuperatorio programado para este módulo.</p>
                                    <div class="mt-4">
                                        <a href="{{ route('modules.recovery.edit', ['program' => $program->id, 'module' => $module->id]) }}" class="text-sm font-medium text-blue-600 hover:text-blue-500">
                                            Configurar recuperatorio <span aria-hidden="true">&rarr;</span>
                                        </a>
                                    </div>
                                @endif
                            </div>
                            @endrole
                        </div>
                    </div>
                </div>
            </div>

            <!-- Acciones rápidas -->
            @role(['admin', 'academic'])
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Acciones Rápidas</h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <a href="{{ route('programs.modules.classes.create', ['program' => $program->id, 'module' => $module->id]) }}" class="bg-blue-50 hover:bg-blue-100 p-4 rounded-lg flex items-center transition-colors">
                            <div class="bg-blue-100 p-3 rounded-full mr-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-medium text-blue-900">Añadir Clase</h4>
                                <p class="text-sm text-blue-700">Crear una nueva clase para este módulo</p>
                            </div>
                        </a>
                        <a href="{{ route('grades.upload', ['program' => $program->id, 'module' => $module->id]) }}" class="bg-green-50 hover:bg-green-100 p-4 rounded-lg flex items-center transition-colors">
                            <div class="bg-green-100 p-3 rounded-full mr-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-medium text-green-900">Subir Calificaciones</h4>
                                <p class="text-sm text-green-700">Importar calificaciones de los participantes</p>
                            </div>
                        </a>
                        <a href="{{ route('grades.summary', ['program' => $program->id, 'module' => $module->id]) }}" class="bg-amber-50 hover:bg-amber-100 p-4 rounded-lg flex items-center transition-colors">
                            <div class="bg-amber-100 p-3 rounded-full mr-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-medium text-amber-900">Ver Calificaciones</h4>
                                <p class="text-sm text-amber-700">Resumen de calificaciones del módulo</p>
                            </div>
                        </a>
                        <a href="{{ route('attendances.summary', ['program' => $program->id, 'module' => $module->id]) }}" class="bg-purple-50 hover:bg-purple-100 p-4 rounded-lg flex items-center transition-colors">
                            <div class="bg-purple-100 p-3 rounded-full mr-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-medium text-purple-900">Resumen de Asistencia</h4>
                                <p class="text-sm text-purple-700">Ver asistencia general del módulo</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
            @endrole

            <!-- Clases -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    @php
                        $sortedClasses = $module->classes->sortBy('class_date')->values();
                        $weekdayLabels = [
                            0 => 'Domingo',
                            1 => 'Lunes',
                            2 => 'Martes',
                            3 => 'Miercoles',
                            4 => 'Jueves',
                            5 => 'Viernes',
                            6 => 'Sabado',
                        ];
                        $classPatternDays = $sortedClasses
                            ->map(static fn ($moduleClass) => $moduleClass->class_date->dayOfWeek)
                            ->unique()
                            ->sort()
                            ->values()
                            ->map(static fn ($weekday) => $weekdayLabels[$weekday] ?? null)
                            ->filter()
                            ->implode(', ');
                        $firstScheduledClass = $sortedClasses->first();
                        $lastScheduledClass = $sortedClasses->last();
                    @endphp

                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Clases</h3>
                        
                    </div>

                    @if($sortedClasses->isNotEmpty())
                        <div class="mb-6 rounded-lg border border-indigo-200 bg-indigo-50 p-4">
                            <h4 class="text-sm font-semibold text-indigo-900">Resumen del patron</h4>
                            <div class="mt-2 grid grid-cols-1 gap-3 text-sm text-indigo-900 md:grid-cols-3">
                                <div>
                                    <p class="text-xs font-medium uppercase tracking-wide text-indigo-700">Dias</p>
                                    <p class="mt-1">{{ $classPatternDays ?: 'No definido' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-medium uppercase tracking-wide text-indigo-700">Rango</p>
                                    <p class="mt-1">
                                        {{ $firstScheduledClass?->class_date?->format('d/m/Y') ?? 'No definido' }}
                                        a
                                        {{ $lastScheduledClass?->class_date?->format('d/m/Y') ?? 'No definido' }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs font-medium uppercase tracking-wide text-indigo-700">Sesiones</p>
                                    <p class="mt-1">{{ $sortedClasses->count() }} clases programadas</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Enlace</th>
                                    @role(['academic', 'admin'])
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Asistencia</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                    @endrole
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($module->classes as $class)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $class->class_date->format('d/m/Y') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            @php($classMeetingLink = $module->shared_google_meet_link ?: ($class->google_meet_link ?: $class->class_link))
                                            @if($classMeetingLink)
                                                <a href="{{ $classMeetingLink }}" target="_blank" class="text-blue-600 hover:text-blue-900">{{ __('Abrir enlace') }}</a>
                                            @else
                                                <span class="text-gray-500">{{ __('No disponible') }}</span>
                                            @endif
                                        </td>
                                        @role(['academic', 'admin'])
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            @if($class->attendances->count() > 0)
                                                <a href="{{ route('attendances.show', [$program->id, $module->id, $class->id]) }}" class="text-green-600 hover:text-green-900 flex items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    {{ $class->attendances->count() }} {{ __('registros') }}
                                                </a>
                                            @else
                                                <a href="{{ route('attendances.upload', [$program->id, $module->id, $class->id]) }}" class="text-yellow-600 hover:text-yellow-900 flex items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                                    </svg>
                                                    {{ __('Subir XLSX') }}
                                                </a>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <a href="{{ route('attendances.show', ['program' => $program->id, 'module' => $module->id, 'class' => $class->id]) }}" class="text-indigo-600 hover:text-indigo-900">
                                                    <x-action-icons action="view" />
                                                </a>
                                                <a href="{{ route('programs.modules.classes.edit', ['program' => $program->id, 'module' => $module->id, 'class' => $class->id]) }}" class="text-yellow-600 hover:text-yellow-900">
                                                    <x-action-icons action="edit" />
                                                </a>
                                                <form method="POST" action="{{ route('programs.modules.classes.destroy', ['program' => $program->id, 'module' => $module->id, 'class' => $class->id]) }}" class="inline" onsubmit="return confirm('¿Estás seguro de eliminar esta clase?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900">
                                                        <x-action-icons action="delete" />
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                        @endrole
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No hay clases para mostrar</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
