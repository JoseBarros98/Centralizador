{{-- filepath: d:\VS_Code_Docs\centralizador\resources\views\teachers\show.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-white leading-tight">
                {{ __('Docente: ') . $teacher->academic_degree . ' ' . $teacher->full_name }}
            </h2>
            <div class="flex space-x-2">
                {{-- @can('teacher.edit') --}}
                <a href="{{ route('teachers.edit', $teacher) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    {{ __('Editar') }}
                </a>
                {{-- @endcan --}}

                <a href="{{ route('teachers.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 active:bg-gray-500 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    {{ __('Volver') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Badge de origen del docente -->
            @if($teacher->is_external)
                <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6 rounded-r-lg">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700">
                                <strong>Docente Sincronizado:</strong> Este docente fue importado automáticamente desde la base de datos externa.
                                @if($teacher->external_id)
                                    <span class="ml-2 text-xs bg-blue-100 px-2 py-1 rounded">ID Externo: {{ $teacher->external_id }}</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">{{ __('Información del Docente') }}</h3>
                            <p class="mt-2 text-sm text-gray-600">{{ __('Nombre Completo: ') . $teacher->full_name }}</p>
                            <p class="mt-2 text-sm text-gray-600">{{ __('CI: ') . $teacher->ci }}</p>
                            <p class="mt-2 text-sm text-gray-600">{{ __('Profesión: ') . $teacher->profession }}</p>
                            <p class="mt-2 text-sm text-gray-600">
                                {{ __('Fecha de Nacimiento: ') . ($teacher->birth_date ? \Carbon\Carbon::parse($teacher->birth_date)->format('d/m/Y') : 'N/A') }}
                            </p>
                        </div>
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">{{ __('Datos de Contacto') }}</h3>
                            <p class="mt-2 text-sm text-gray-600">{{ __('Teléfono: ') . ($teacher->phone ?? 'N/A') }}</p>
                            <p class="mt-2 text-sm text-gray-600">{{ __('Correo Electrónico: ') . ($teacher->email ?? 'N/A') }}</p>
                            <p class="mt-2 text-sm text-gray-600">{{ __('Dirección: ') . ($teacher->address ?? 'N/A') }}</p>
                        </div>
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">{{ __('Datos bancarios') }}</h3>
                            <p class="mt-2 text-sm text-gray-600">{{ __('Banco: ') . ($teacher->bank ?? 'N/A') }}</p>
                            <p class="mt-2 text-sm text-gray-600">{{ __('Número de cuenta: ') . ($teacher->account_number ?? 'N/A') }}</p>
                            <p class="mt-2 text-sm text-gray-600">{{ __('¿Emite Factura? ') . ($teacher->bill ?? 'N/A') }}</p>
                            <p class="mt-2 text-sm text-gray-600">{{ __('¿Es trabajador de ESAM? ') . ($teacher->esam_worker ?? 'N/A') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Formación Académica -->
            @php
                $academicInfoFiles = $teacher->files->whereNotNull('academic_info')->filter(function($file) {
                    return !empty($file->academic_info);
                });
                $allAcademicInfo = [];
                foreach ($academicInfoFiles as $file) {
                    if (is_array($file->academic_info)) {
                        foreach ($file->academic_info as $info) {
                            $allAcademicInfo[] = array_merge($info, ['source_file' => $file->file_name]);
                        }
                    }
                }
                
                // Agrupar por tipo y ordenar
                $groupedInfo = collect($allAcademicInfo)->groupBy('type');
                // Orden preferido de tipos
                $preferredOrder = ['Doctorado', 'Maestría', 'Maestria', 'Especialidad', 'Licenciatura', 'Diplomado', 'Curso', 'Taller', 'Seminario'];
                $orderedGrouped = collect();
                foreach ($preferredOrder as $type) {
                    if ($groupedInfo->has($type)) {
                        $orderedGrouped->put($type, $groupedInfo->get($type));
                    }
                }
            @endphp

            @if(!empty($allAcademicInfo))
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 overflow-hidden shadow-lg rounded-xl mb-6 border border-blue-100">
                    <div class="p-8 bg-white bg-opacity-80 backdrop-blur">
                        

                        <div class="grid grid-cols-1 gap-4">
                            @forelse($orderedGrouped as $type => $items)
                                @php
                                    $typeConfig = [
                                        'Doctorado' => ['bg' => 'from-purple-50 to-indigo-50', 'border' => 'border-purple-200', 'badge' => 'bg-purple-100 text-purple-800', 'title' => 'text-purple-900', 'icon' => '🎓'],
                                        'Maestría' => ['bg' => 'from-blue-50 to-cyan-50', 'border' => 'border-blue-200', 'badge' => 'bg-blue-100 text-blue-800', 'title' => 'text-blue-900', 'icon' => '📚'],
                                        'Maestria' => ['bg' => 'from-blue-50 to-cyan-50', 'border' => 'border-blue-200', 'badge' => 'bg-blue-100 text-blue-800', 'title' => 'text-blue-900', 'icon' => '📚'],
                                        'Especialidad' => ['bg' => 'from-orange-50 to-amber-50', 'border' => 'border-orange-200', 'badge' => 'bg-orange-100 text-orange-800', 'title' => 'text-orange-900', 'icon' => '⭐'],
                                        'Licenciatura' => ['bg' => 'from-yellow-50 to-amber-50', 'border' => 'border-yellow-200', 'badge' => 'bg-yellow-100 text-yellow-800', 'title' => 'text-yellow-900', 'icon' => '🏛️'],
                                        'Diplomado' => ['bg' => 'from-green-50 to-emerald-50', 'border' => 'border-green-200', 'badge' => 'bg-green-100 text-green-800', 'title' => 'text-green-900', 'icon' => '📜'],
                                        'Curso' => ['bg' => 'from-teal-50 to-cyan-50', 'border' => 'border-teal-200', 'badge' => 'bg-teal-100 text-teal-800', 'title' => 'text-teal-900', 'icon' => '📖'],
                                    ];
                                    $config = $typeConfig[$type] ?? ['bg' => 'from-gray-50 to-gray-100', 'border' => 'border-gray-200', 'badge' => 'bg-gray-100 text-gray-800', 'title' => 'text-gray-900', 'icon' => '📋'];
                                @endphp
                                
                                <div class="bg-gradient-to-r {{ $config['bg'] }} border {{ $config['border'] }} rounded-lg p-5 hover:shadow-md transition-shadow">
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="flex items-center gap-3">
                                            <span class="text-2xl">{{ $config['icon'] }}</span>
                                            <div>
                                                <h4 class="font-bold text-lg {{ $config['title'] }}">
                                                    {{ $type === 'Maestria' ? 'Maestría' : $type }}
                                                </h4>
                                                <p class="text-xs {{ str_replace('900', '600', $config['title']) }} font-medium">
                                                    {{ $items->count() }} {{ $items->count() === 1 ? 'título' : 'títulos' }}
                                                </p>
                                            </div>
                                        </div>
                                        <span class="inline-flex px-3 py-1.5 text-sm font-bold rounded-full {{ $config['badge'] }} shadow-sm">
                                            {{ $items->count() }}
                                        </span>
                                    </div>
                                    
                                    <div class="space-y-2 pl-11">
                                        @foreach($items as $info)
                                            <div class="bg-white rounded-md p-4 shadow-sm hover:shadow-md transition-shadow border-l-4" 
                                                 style="border-color: var(--{{ strtolower(str_replace(' ', '-', $type)) }}-color, #6366f1)">
                                                <div class="flex items-start justify-between gap-2">
                                                    <div class="flex-1 min-w-0">
                                                        <!-- Título principal -->
                                                        <h5 class="font-bold text-gray-900 text-sm leading-tight">
                                                            {{ $info['title'] ?? 'Información académica' }}
                                                        </h5>
                                                        
                                                        <!-- Institución si existe -->
                                                        @if(!empty($info['institution']))
                                                            <div class="flex items-center gap-2 mt-2 text-xs text-gray-600">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 flex-shrink-0 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                                                </svg>
                                                                <span class="font-medium truncate" title="{{ $info['institution'] }}">
                                                                    {{ Str::limit($info['institution'], 50) }}
                                                                </span>
                                                            </div>
                                                        @endif
                                                        
                                                        <!-- Año, Ubicación y Fuente -->
                                                        <div class="flex items-center gap-4 mt-2 text-xs text-gray-500 flex-wrap">
                                                            @if(!empty($info['year']))
                                                                <div class="flex items-center gap-1 bg-gray-100 px-2 py-1 rounded">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                                    </svg>
                                                                    <span class="font-medium">{{ $info['year'] }}</span>
                                                                </div>
                                                            @endif
                                                            
                                                            @if(!empty($info['location']))
                                                                <div class="flex items-center gap-1 bg-gray-100 px-2 py-1 rounded">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                                    </svg>
                                                                    <span class="truncate" title="{{ $info['location'] }}">
                                                                        {{ Str::limit($info['location'], 35) }}
                                                                    </span>
                                                                </div>
                                                            @endif
                                                        </div>

                                                        <!-- Información del archivo origen -->
                                                        @if(!empty($info['source_file']))
                                                            <div class="flex items-center gap-1 mt-2 text-xs text-gray-400">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                                </svg>
                                                                <span class="truncate">{{ Str::limit($info['source_file'], 25) }}</span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-8 text-gray-500">
                                    <p>No se encontró información académica</p>
                                </div>
                            @endforelse
                        </div>

                        <!-- Nota informativa mejorada -->
                        <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg flex items-start gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div>
                                <h5 class="text-sm font-semibold text-blue-900">Información extraída automáticamente</h5>
                                <p class="text-xs text-blue-700 mt-1">
                                    Esta formación académica fue extraída automáticamente de los archivos (CV, PDF) subidos por el docente. 
                                    La precisión depende de la calidad y estructura del documento. Se recomienda revisar la información para mayor exactitud.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Historial de Módulos -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-6">Historial de Módulos</h3>
                    
                    @if($teacher->modules->count() > 0)
                        <div class="space-y-6">
                            <!-- Módulos Pendientes -->
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                <h4 class="text-lg font-semibold text-yellow-700 mb-4 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Módulos Pendientes ({{ $teacher->modules->where('status', 'INSCRIPCION')->count() }})
                                </h4>
                                @if($teacher->modules->where('status', 'INSCRIPCION')->count() > 0)
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                        @foreach($teacher->modules->where('status', 'INSCRIPCION') as $module)
                                            <div class="bg-white border border-yellow-300 rounded-lg p-3 hover:shadow-sm transition-shadow">
                                                <div class="flex justify-between items-start mb-2">
                                                    <h5 class="font-medium text-yellow-900 text-sm">
                                                        <a href="{{ route('programs.modules.show', [$module->program, $module]) }}" 
                                                           class="hover:text-yellow-700 hover:underline transition-colors duration-150" 
                                                           title="Ver detalles del módulo">
                                                            {{ $module->name }}
                                                        </a>
                                                    </h5>
                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                        {{ $module->status }}
                                                    </span>
                                                </div>
                                                <div class="text-xs text-yellow-700 mb-2">
                                                    <strong>Programa:</strong> {{ $module->program->name ?? 'Sin programa' }}
                                                </div>
                                                <div class="text-xs text-yellow-600 space-y-1">
                                                    <div>
                                                        <strong>Inicio programado:</strong> {{ $module->start_date ? $module->start_date->format('d/m/Y') : 'Por definir' }}
                                                    </div>
                                                    @if($module->finalization_date)
                                                        <div>
                                                            <strong>Fin programado:</strong> {{ $module->finalization_date->format('d/m/Y') }}
                                                        </div>
                                                    @endif
                                                </div>
                                                @if($module->description)
                                                    <div class="text-xs text-yellow-600 mt-2">
                                                        <strong>Descripción:</strong> {{ Str::limit($module->description, 80) }}
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-4">
                                        <p class="text-yellow-600 text-sm">No hay módulos pendientes asignados</p>
                                    </div>
                                @endif
                            </div>
                            
                            <!-- Módulos Activos -->
                            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                <h4 class="text-lg font-semibold text-green-700 mb-4 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Módulos Activos ({{ $teacher->modules->where('status', 'EN DESARROLLO')->count() }})
                                </h4>
                                @if($teacher->modules->where('status', 'EN DESARROLLO')->count() > 0)
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                        @foreach($teacher->modules->where('status', 'EN DESARROLLO') as $module)
                                            <div class="bg-white border border-green-300 rounded-lg p-3 hover:shadow-sm transition-shadow">
                                                <div class="flex justify-between items-start mb-2">
                                                    <h5 class="font-medium text-green-900 text-sm">
                                                        <a href="{{ route('programs.modules.show', [$module->program, $module]) }}" 
                                                           class="hover:text-green-700 hover:underline transition-colors duration-150" 
                                                           title="Ver detalles del módulo">
                                                            {{ $module->name }}
                                                        </a>
                                                    </h5>
                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                        {{ $module->status }}
                                                    </span>
                                                </div>
                                                <div class="text-xs text-green-700 mb-2">
                                                    <strong>Programa:</strong> {{ $module->program->name ?? 'Sin programa' }}
                                                </div>
                                                <div class="text-xs text-green-600 space-y-1">
                                                    <div>
                                                        <strong>Inicio:</strong> {{ $module->start_date ? $module->start_date->format('d/m/Y') : 'No definido' }}
                                                    </div>
                                                    @if($module->finalization_date)
                                                        <div>
                                                            <strong>Fin programado:</strong> {{ $module->finalization_date->format('d/m/Y') }}
                                                        </div>
                                                    @endif
                                                </div>
                                                @if($module->description)
                                                    <div class="text-xs text-green-600 mt-2">
                                                        <strong>Descripción:</strong> {{ Str::limit($module->description, 80) }}
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-4">
                                        <p class="text-green-600 text-sm">No hay módulos activos en desarrollo</p>
                                    </div>
                                @endif
                            </div>
                            
                            <!-- Módulos Finalizados -->
                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                <h4 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v6a2 2 0 002 2h6a2 2 0 002-2V7a2 2 0 00-2-2H9z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 9h6v6H9V9z" />
                                    </svg>
                                    Módulos Finalizados ({{ $teacher->modules->where('status', 'CONCLUIDO')->count() }})
                                </h4>
                                @if($teacher->modules->where('status', 'CONCLUIDO')->count() > 0)
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                        @foreach($teacher->modules->where('status', 'CONCLUIDO')->sortByDesc('finalization_date') as $module)
                                            <div class="bg-white border border-gray-300 rounded-lg p-3 hover:shadow-sm transition-shadow">
                                                <div class="flex justify-between items-start mb-2">
                                                    <h5 class="font-medium text-gray-900 text-sm">
                                                        <a href="{{ route('programs.modules.show', [$module->program, $module]) }}" 
                                                           class="hover:text-gray-700 hover:underline transition-colors duration-150" 
                                                           title="Ver detalles del módulo">
                                                            {{ $module->name }}
                                                        </a>
                                                    </h5>
                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-600">
                                                        {{ $module->status }}
                                                    </span>
                                                </div>
                                                <div class="text-xs text-gray-700 mb-2">
                                                    <strong>Programa:</strong> {{ $module->program->name ?? 'Sin programa' }}
                                                </div>
                                                <div class="text-xs text-gray-600 space-y-1">
                                                    <div>
                                                        <strong>Inicio:</strong> {{ $module->start_date ? $module->start_date->format('d/m/Y') : 'No definido' }}
                                                    </div>
                                                    @if($module->finalization_date)
                                                        <div>
                                                            <strong>Finalizado:</strong> {{ $module->finalization_date->format('d/m/Y') }}
                                                        </div>
                                                    @endif
                                                </div>
                                                @if($module->description)
                                                    <div class="text-xs text-gray-600 mt-2">
                                                        <strong>Descripción:</strong> {{ Str::limit($module->description, 80) }}
                                                    </div>
                                                @endif
                                                
                                                <!-- Botones de Solicitar Pago -->
                                                <div class="mt-3 pt-2 border-t border-gray-200">
                                                    @php
                                                        // Obtener solicitudes por tipo (sin rechazadas)
                                                        $moduloRequest = $module->paymentRequest()
                                                            ->where('status', '!=', 'Rechazado')
                                                            ->where('request_type', 'Modulo')
                                                            ->latest()
                                                            ->first();
                                                        
                                                        $tutoriaRequest = $module->paymentRequest()
                                                            ->where('status', '!=', 'Rechazado')
                                                            ->where('request_type', 'Tutoria')
                                                            ->latest()
                                                            ->first();
                                                    @endphp
                                                    
                                                    <div class="space-y-2">
                                                        {{-- Solicitud de Módulo --}}
                                                        @if($moduloRequest)
                                                            @if($moduloRequest->status === 'Realizado')
                                                                <div class="inline-flex items-center justify-center w-full px-3 py-2 bg-purple-600 text-white text-xs font-semibold rounded-md cursor-default">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                    </svg>
                                                                    Pago Módulo Realizado
                                                                </div>
                                                            @else
                                                                <a href="{{ route('payment_requests.show', $moduloRequest->id) }}" 
                                                                   class="inline-flex items-center justify-center w-full px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-md transition-colors duration-150">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                                    </svg>
                                                                    Ver Solicitud Módulo
                                                                </a>
                                                            @endif
                                                        @else
                                                            <a href="{{ route('payment_requests.create', ['module_id' => $module->id, 'type' => 'Modulo']) }}" 
                                                               class="inline-flex items-center justify-center w-full px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-xs font-semibold rounded-md transition-colors duration-150">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                                                </svg>
                                                                Solicitar Pago Módulo
                                                            </a>
                                                        @endif
                                                        
                                                        {{-- Solicitud de Tutoría --}}
                                                        @if($tutoriaRequest)
                                                            @if($tutoriaRequest->status === 'Realizado')
                                                                <div class="inline-flex items-center justify-center w-full px-3 py-2 bg-purple-600 text-white text-xs font-semibold rounded-md cursor-default">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                                                                    </svg>
                                                                    Pago Tutoría Realizado
                                                                </div>
                                                            @else
                                                                <a href="{{ route('payment_requests.show', $tutoriaRequest->id) }}" 
                                                                   class="inline-flex items-center justify-center w-full px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-md transition-colors duration-150">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                                    </svg>
                                                                    Ver Solicitud Tutoría
                                                                </a>
                                                            @endif
                                                        @else
                                                            <a href="{{ route('payment_requests.create', ['module_id' => $module->id, 'type' => 'Tutoria']) }}" 
                                                               class="inline-flex items-center justify-center w-full px-3 py-2 bg-orange-600 hover:bg-orange-700 text-white text-xs font-semibold rounded-md transition-colors duration-150">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                                                </svg>
                                                                Solicitar Pago Tutoría
                                                            </a>
                                                        @endif
                                                    </div>
                                                </div>
                                                
                                                <!-- Sistema de Valoración -->
                                                <div class="mt-3 pt-2 border-t border-gray-200">
                                                    <div class="flex items-center justify-between">
                                                        <span class="text-xs font-semibold text-gray-700">Valoración del desempeño:</span>
                                                        <div class="flex items-center space-x-1" data-module-id="{{ $module->id }}">
                                                            @for($i = 1; $i <= 5; $i++)
                                                                <button 
                                                                    onclick="setRating({{ $module->id }}, {{ $i }})"
                                                                    class="star-btn text-lg focus:outline-none transition-colors duration-150 
                                                                           {{ ($module->teacher_rating && $i <= $module->teacher_rating) ? 'text-yellow-400' : 'text-gray-300 hover:text-yellow-300' }}"
                                                                    title="{{ $i }} estrella{{ $i > 1 ? 's' : '' }}"
                                                                >
                                                                    ★
                                                                </button>
                                                            @endfor
                                                            @if($module->teacher_rating)
                                                                <span class="ml-2 text-xs text-gray-600">({{ $module->teacher_rating }}/5)</span>
                                                            @else
                                                                <span class="ml-2 text-xs text-gray-500">Sin valorar</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-4">
                                        <p class="text-gray-600 text-sm">No hay módulos finalizados</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="text-center py-12">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            <h4 class="text-lg font-medium text-gray-900 mb-2">Sin módulos asignados</h4>
                            <p class="text-gray-500">Este docente no tiene módulos asignados aún.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Historial de Solicitudes de Pago -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex items-center mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900">Historial de Solicitudes de Pago</h3>
                    </div>
                    
                    @php
                        // Obtener solicitudes de pago del docente
                        // - Tipo Módulo: donde el docente sea el teacher_id del módulo
                        // - Tipo Tutoría: donde el docente sea el tutoring_teacher_id
                        $paymentRequests = \App\Models\PaymentRequest::where(function($query) use ($teacher) {
                            // Solicitudes tipo Módulo del docente
                            $query->where('request_type', 'Modulo')
                                  ->whereHas('module', function($q) use ($teacher) {
                                      $q->where('teacher_id', $teacher->id);
                                  });
                        })
                        ->orWhere(function($query) use ($teacher) {
                            // Solicitudes tipo Tutoría donde este docente es el tutor
                            $query->where('request_type', 'Tutoria')
                                  ->where('tutoring_teacher_id', $teacher->id);
                        })
                        ->with(['module.program', 'tutoringTeacher'])
                        ->orderBy('created_at', 'desc')
                        ->get();
                    @endphp
                    
                    @if($paymentRequests->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Tipo
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Módulo / Programa
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Fechas
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Monto
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Estado
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Fecha Solicitud
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Acciones
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($paymentRequests as $request)
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                    @if($request->request_type === 'Modulo') bg-blue-100 text-blue-800
                                                    @else bg-orange-100 text-orange-800 @endif">
                                                    @if($request->request_type === 'Modulo')
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                        </svg>
                                                        Módulo
                                                    @else
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                                        </svg>
                                                        Tutoría
                                                    @endif
                                                </span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $request->module->name }}
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    {{ $request->module->program->name ?? 'Sin programa' }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <div>{{ $request->start_date ? $request->start_date->format('d/m/Y') : 'N/A' }}</div>
                                                <div class="text-xs text-gray-400">
                                                    {{ $request->end_date ? $request->end_date->format('d/m/Y') : 'N/A' }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex flex-col">
                                                    <span class="font-semibold text-gray-700">Total: Bs. {{ number_format($request->total_amount, 2) }}</span>
                                                    @if($request->retention_amount > 0)
                                                        <span class="text-xs {{ $request->retention_percentage == 30 ? 'text-purple-600' : 'text-red-600' }}">
                                                            Retención ({{ $request->retention_percentage }}%): - Bs. {{ number_format($request->retention_amount, 2) }}
                                                        </span>
                                                        <span class="font-bold text-green-600">Neto: Bs. {{ number_format($request->net_amount, 2) }}</span>
                                                    @else
                                                        <span class="text-xs text-green-600">Sin retención (Factura)</span>
                                                        <span class="font-bold text-green-600">Neto: Bs. {{ number_format($request->net_amount, 2) }}</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                                    @if($request->status === 'Pendiente') bg-yellow-100 text-yellow-800
                                                    @elseif($request->status === 'Aprobado') bg-green-100 text-green-800
                                                    @elseif($request->status === 'Rechazado') bg-red-100 text-red-800
                                                    @else bg-purple-100 text-purple-800 @endif">
                                                    {{ $request->status }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $request->request_date ? $request->request_date->format('d/m/Y') : $request->created_at->format('d/m/Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                                <a href="{{ route('payment_requests.show', $request->id) }}" 
                                                   class="inline-flex items-center px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-md transition-colors duration-150">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                    Ver
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-12">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <h4 class="text-lg font-medium text-gray-900 mb-2">Sin solicitudes de pago</h4>
                            <p class="text-gray-500">Este docente no tiene solicitudes de pago registradas.</p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Archivos</h3>
                            <button id="upload-button" class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                </svg>
                                Subir archivo
                            </button>
                        </div>

                        @if($teacher->files->isEmpty())
                            <div class="text-center py-4">
                                <p class="text-gray-500">No hay archivos asociados al docente.</p>
                            </div>
                        @else
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Nombre del archivo
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Descripción
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Fecha de subida
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Acciones
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($teacher->files as $file)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        {{ $file->file_name }}
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <div class="text-sm text-gray-700 truncate max-w-xs">
                                                        {{ $file->description ?? 'Sin descripción' }}
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $file->created_at->format('d/m/Y H:i') }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                    <div class="flex space-x-2">
                                                        <a href="{{ route('teachers.files.serve', $file) }}" target="_blank" class="text-blue-600 hover:text-blue-900" title="Ver">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                            </svg>
                                                        </a>
                                                        <a href="{{ route('teachers.files.serve', $file) }}" download class="text-green-600 hover:text-green-900" title="Descargar">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                                            </svg>
                                                        </a>
                                                        <form action="{{ route('teachers.files.delete', $file) }}" method="POST" class="inline-block">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="text-red-600 hover:text-red-900" title="Eliminar" onclick="return confirm('¿Estás seguro de que deseas eliminar este archivo?')">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                                </svg>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif    
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal para subir archivos -->
        <div id="upload-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 hidden">
            <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Subir archivo</h3>
                    <button id="close-modal" class="text-gray-400 hover:text-gray-500">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form id="upload-form" action="{{ route('teachers.files.upload', $teacher) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-4">
                        <label for="file" class="block text-sm font-medium text-gray-700">Archivo</label>
                        <input type="file" name="file" id="file" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required>
                        <p class="text-xs text-gray-500 mt-1">Formatos permitidos: PDF, DOC, DOCX, JPG, JPEG, PNG. Tamaño máximo: 2MB.</p>
                    </div>

                    <div class="mb-4">
                        <label for="description" class="block text-sm font-medium text-gray-700">Descripción</label>
                        <textarea name="description" id="description" rows="2" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"></textarea>
                    </div>

                    <div class="flex justify-end">
                        <button type="button" id="cancel-upload" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-gray-700 bg-gray-200 hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 mr-2">
                            Cancelar
                        </button>
                        <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Subir
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const uploadButton = document.getElementById('upload-button');
                const uploadModal = document.getElementById('upload-modal');
                const closeModal = document.getElementById('close-modal');
                const cancelUpload = document.getElementById('cancel-upload');

                if (uploadButton) {
                    uploadButton.addEventListener('click', function(){
                        uploadModal.classList.remove('hidden');
                    });
                }

                if (closeModal) {
                    closeModal.addEventListener('click', function(){
                        uploadModal.classList.add('hidden');
                    });
                }

                if (cancelUpload) {
                    cancelUpload.addEventListener('click', function(){
                        uploadModal.classList.add('hidden');
                    });
                }

                // Cerrar el modal al hacer clic afuera de él
                window.addEventListener('click', function(event){
                    if (event.target === uploadModal) {
                        uploadModal.classList.add('hidden');
                    }
                });
            });

            // Función para manejar la valoración con estrellas
            function setRating(moduleId, rating) {
                // Actualizar visualmente las estrellas inmediatamente
                const moduleContainer = document.querySelector(`[data-module-id="${moduleId}"]`);
                const stars = moduleContainer.querySelectorAll('.star-btn');
                const ratingText = moduleContainer.querySelector('span:last-child');
                
                // Actualizar estrellas
                stars.forEach((star, index) => {
                    if (index < rating) {
                        star.classList.remove('text-gray-300', 'hover:text-yellow-300');
                        star.classList.add('text-yellow-400');
                    } else {
                        star.classList.remove('text-yellow-400');
                        star.classList.add('text-gray-300', 'hover:text-yellow-300');
                    }
                });
                
                // Actualizar texto de rating
                ratingText.textContent = `(${rating}/5)`;
                ratingText.classList.remove('text-gray-500');
                ratingText.classList.add('text-gray-600');
                
                // Enviar al servidor
                fetch(`{{ route('teachers.modules.rate', ['teacher' => $teacher->id, 'module' => '__MODULE_ID__']) }}`.replace('__MODULE_ID__', moduleId), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        rating: rating
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Mostrar mensaje de éxito temporal
                        const successMsg = document.createElement('div');
                        successMsg.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded shadow-lg z-50';
                        successMsg.textContent = 'Valoración guardada correctamente';
                        document.body.appendChild(successMsg);
                        
                        setTimeout(() => {
                            successMsg.remove();
                        }, 3000);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    // Revertir cambios visuales en caso de error
                    location.reload();
                });
            }
        </script>
    </div>
</x-app-layout>