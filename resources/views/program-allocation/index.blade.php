@extends('layouts.app')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

<style>
    #program-results {
        list-style: none;
    }
    
    #program-results li {
        padding: 8px 14px;
        font-size: 14px;
        line-height: 1.5;
        color: #374151;
        cursor: pointer;
        border-bottom: 1px solid #f3f4f6;
    }
    
    #program-results li:hover {
        background-color: #f3f4f6;
    }
    
    #program-results li:last-child {
        border-bottom: none;
    }
</style>

<div class="py-12">
    <div class="max-w-full mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Asignación por Programa</h1>
            <p class="mt-2 text-sm text-gray-600">Gestión y seguimiento de asignaciones por programa</p>
        </div>

        <!-- Estadísticas -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
            <!-- Card Asignación Total -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white">
                    <div class="flex items-center">
                        <div class="flex-1">
                            <p class="text-gray-600 text-sm font-medium mb-2">Asignación Total</p>
                            <p class="text-3xl font-bold text-gray-900">{{ number_format($totalAsignacion, 2) }}</p>
                        </div>
                        <div class="ml-4">
                            <svg class="h-12 w-12 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card Total Cobrado -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white">
                    <div class="flex items-center">
                        <div class="flex-1">
                            <p class="text-gray-600 text-sm font-medium mb-2">Total Cobrado</p>
                            <p class="text-3xl font-bold text-green-600">{{ number_format($totalCobrado, 2) }}</p>
                        </div>
                        <div class="ml-4">
                            <svg class="h-12 w-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card Porcentaje Total Alcanzado -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white">
                    <div class="flex items-center">
                        <div class="flex-1">
                            <p class="text-gray-600 text-sm font-medium mb-2">Porcentaje Total Alcanzado</p>
                            <p class="text-3xl font-bold text-purple-600">{{ number_format($porcentajeTotalAlcanzado, 2) }}%</p>
                        </div>
                        <div class="ml-4">
                            <svg class="h-12 w-12 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Filtros</h3>
            <form method="GET">
                <!-- Fila 1 de Filtros -->
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-4">
                    <!-- Búsqueda Programa -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Buscar Programa</label>
                        <input type="text" name="programa_search" placeholder="Nombre o ID" value="{{ request('programa_search') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <!-- Filtro Categoría -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Categoría</label>
                        <select name="categoria" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Todas</option>
                            @foreach($categorias as $key => $categoria)
                                <option value="{{ $key }}" @if(request('categoria') == $key) selected @endif>{{ $categoria }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filtro Etapa -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Etapa</label>
                        <select name="etapa" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Todas</option>
                            @foreach($etapas as $etapa)
                                <option value="{{ $etapa }}" @if(request('etapa') == $etapa) selected @endif>{{ ucfirst($etapa) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filtro Responsable Cartera -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Responsable Cartera</label>
                        <select name="responsable_cartera" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Todos</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" @if(request('responsable_cartera') == $user->id) selected @endif>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filtro Mes -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Mes</label>
                        <select name="mes" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Todos</option>
                            <option value="1" @if(request('mes', date('n')) == '1') selected @endif>Enero</option>
                            <option value="2" @if(request('mes', date('n')) == '2') selected @endif>Febrero</option>
                            <option value="3" @if(request('mes', date('n')) == '3') selected @endif>Marzo</option>
                            <option value="4" @if(request('mes', date('n')) == '4') selected @endif>Abril</option>
                            <option value="5" @if(request('mes', date('n')) == '5') selected @endif>Mayo</option>
                            <option value="6" @if(request('mes', date('n')) == '6') selected @endif>Junio</option>
                            <option value="7" @if(request('mes', date('n')) == '7') selected @endif>Julio</option>
                            <option value="8" @if(request('mes', date('n')) == '8') selected @endif>Agosto</option>
                            <option value="9" @if(request('mes', date('n')) == '9') selected @endif>Septiembre</option>
                            <option value="10" @if(request('mes', date('n')) == '10') selected @endif>Octubre</option>
                            <option value="11" @if(request('mes', date('n')) == '11') selected @endif>Noviembre</option>
                            <option value="12" @if(request('mes', date('n')) == '12') selected @endif>Diciembre</option>
                        </select>
                    </div>
                </div>

                <!-- Fila 2 de Filtros -->
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-4">
                    <!-- Filtro Gestión (Año de Asignación) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Año de Asignación</label>
                        <select name="gestion" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Todos</option>
                            @foreach($gestiones as $gestion)
                                <option value="{{ $gestion }}" @if(request('gestion', date('Y')) == $gestion) selected @endif>{{ $gestion }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Fila de Botones -->
                <div class="flex gap-2">
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700 font-medium">
                        Filtrar
                    </button>
                    <a href="{{ route('program-allocation.index') }}" class="px-6 py-2 bg-gray-300 text-gray-700 rounded-md text-sm hover:bg-gray-400 text-center font-medium">
                        Limpiar
                    </a>
                </div>
            </form>

            <script>
                // Aplicar filtros por defecto (mes y año actual) si es la primera carga
                document.addEventListener('DOMContentLoaded', function() {
                    const urlParams = new URLSearchParams(window.location.search);
                    const hasMesParam = urlParams.has('mes');
                    const hasGestionParam = urlParams.has('gestion');
                    
                    // Si no hay parámetros de filtro, aplicar mes y año actual
                    if (!hasMesParam && !hasGestionParam) {
                        const currentMonth = new Date().getMonth() + 1;
                        const currentYear = new Date().getFullYear();
                        
                        // Crear nueva URL con parámetros por defecto
                        const newUrl = new URL(window.location);
                        newUrl.searchParams.set('mes', currentMonth);
                        newUrl.searchParams.set('gestion', currentYear);
                        
                        window.location.href = newUrl.toString();
                    }
                });
            </script>
        </div>

        <!-- Botón de Importar del Mes Anterior -->
        @if(auth()->user()->can('program_allocation.create'))
            <div class="mb-6 bg-blue-50 rounded-lg shadow-sm border border-blue-200 p-6">
                <h3 class="text-lg font-semibold text-blue-900 mb-4">Importar Asignaciones</h3>
                <button type="button" id="import-btn" class="px-6 py-2 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700 font-medium">
                    <svg class="inline-block h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    Importar del Mes Anterior
                </button>
                <p class="mt-2 text-sm text-blue-700">Copiar asignaciones del mes anterior con los mismos programas y responsables</p>
            </div>
        @endif

        <!-- Selector de Programas -->
        @if($availablePrograms->count() > 0 && auth()->user()->can('program_allocation.create'))
            <div class="mb-6 bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <form id="add-allocation-form" class="w-full">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                        <div class="relative md:col-span-2">
                            <label for="program-search" class="block text-sm font-semibold text-gray-700 mb-2">Selecciona un Programa:</label>
                            <input type="text" id="program-search" placeholder="Buscar programa..." 
                                   class="block w-full rounded-md shadow-sm border border-gray-300 focus:border-indigo-500 focus:ring focus:ring-indigo-200 p-2 text-sm"
                                   autocomplete="off">
                            <input type="hidden" id="program" name="program_id" required>
                            <div id="program-results" class="absolute z-10 w-full bg-white shadow-lg rounded-md mt-1 max-h-60 overflow-auto hidden border border-gray-200"></div>
                        </div>
                        <div>
                            <label for="mes" class="block text-sm font-semibold text-gray-700 mb-2">Mes:</label>
                            <select id="mes" name="mes" required class="block w-full rounded-md shadow-sm border border-gray-300 focus:border-indigo-500 focus:ring focus:ring-indigo-200 p-2 text-sm">
                                <option value="">Seleccionar mes...</option>
                                <option value="1">Enero</option>
                                <option value="2">Febrero</option>
                                <option value="3">Marzo</option>
                                <option value="4">Abril</option>
                                <option value="5">Mayo</option>
                                <option value="6">Junio</option>
                                <option value="7">Julio</option>
                                <option value="8">Agosto</option>
                                <option value="9">Septiembre</option>
                                <option value="10">Octubre</option>
                                <option value="11">Noviembre</option>
                                <option value="12">Diciembre</option>
                            </select>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="w-full md:w-auto px-6 py-2 bg-indigo-600 text-white font-medium rounded-md text-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                                <svg class="inline-block h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Agregar Asignación
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        @endif

        <!-- Tabla -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Acciones</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">N°</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Gestión</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Mes</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Categoría</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Programa</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Área</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Etapa</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Fecha Inicio</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Fecha Fin</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Cobro Titulación</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Asignación Programa</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Responsable Cartera</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Fecha Pago</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Monto al 5</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">% Cobro</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Monto al 10</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">% Cobro</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Monto al 15</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">% Cobro</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Monto al 20</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">% Cobro</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Monto al 25</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">% Cobro</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Monto al 30</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">% Cobro</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($allocations as $index => $allocation)
                            <tr class="hover:bg-gray-50">
                                <!-- Acciones -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm flex justify-center">
                                    @if(auth()->user()->can('program_allocation.delete'))
                                        <button type="button" class="delete-allocation-btn text-red-600 hover:text-red-900" data-id="{{ $allocation->id }}" data-program="{{ $allocation->program->name }}">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $loop->iteration }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $allocation->program->year ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    @php
                                        $meses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
                                        $mesNombre = isset($meses[$allocation->mes]) ? $meses[$allocation->mes] : '-';
                                    @endphp
                                    {{ $mesNombre }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    @php
                                        $programName = trim($allocation->program->name ?? '');
                                        $programNameLower = strtolower($programName);
                                        
                                        $categoria = 'Otro';
                                        
                                        // Usar strpos - más robusto para búsqueda al inicio
                                        // Buscar solo el inicio de la palabra, sin tildes problemáticas
                                        if (strpos($programNameLower, 'diplomado') === 0) {
                                            $categoria = 'Diplomado';
                                        } elseif (strpos($programNameLower, 'maestr') === 0 || strpos($programNameLower, 'master') === 0) {
                                            // Captura: maestría, maestria, maestr, master, etc.
                                            $categoria = 'Maestría';
                                        } elseif (strpos($programNameLower, 'doctorado') === 0 || strpos($programNameLower, 'phd') === 0) {
                                            $categoria = 'Doctorado';
                                        } elseif (strpos($programNameLower, 'especialidad') === 0 || strpos($programNameLower, 'especializac') === 0) {
                                            // Captura: especialidad, especialización, especializacion
                                            $categoria = 'Especialidad';
                                        } elseif (strpos($programNameLower, 'curso') === 0) {
                                            $categoria = 'Curso';
                                        }
                                    @endphp
                                    {{ $categoria }}
                                </td>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    @php
                                        $programName = trim($allocation->program->name ?? '');
                                        $programNameLower = strtolower($programName);
                                        $prefix = '';
                                        
                                        if (strpos($programNameLower, 'diplomado') === 0) {
                                            $prefix = 'D-';
                                        } elseif (strpos($programNameLower, 'maestr') === 0 || strpos($programNameLower, 'master') === 0) {
                                            // Captura: maestría, maestria, maestr, master, etc.
                                            $prefix = 'M-';
                                        } elseif (strpos($programNameLower, 'doctorado') === 0 || strpos($programNameLower, 'phd') === 0) {
                                            $prefix = 'DOC-';
                                        } elseif (strpos($programNameLower, 'especialidad') === 0 || strpos($programNameLower, 'especializac') === 0) {
                                            // Captura: especialidad, especialización, especializacion
                                            $prefix = 'E-';
                                        } elseif (strpos($programNameLower, 'curso') === 0) {
                                            $prefix = 'C-';
                                        }
                                    @endphp
                                    {{ $prefix }}{{ $allocation->program->code ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $allocation->program->name ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    @php
                                        $area = 'No disponible';
                                        try {
                                            if ($allocation->program && $allocation->program->postgraduate_id) {
                                                $postgrad = \App\Models\External\ExternalPostgraduate::where('id_posgrado', $allocation->program->postgraduate_id)->first();
                                                if ($postgrad && $postgrad->area_posgrado) {
                                                    $area = $postgrad->area_posgrado;
                                                }
                                            }
                                        } catch (\Exception $e) {
                                            \Log::error("Error obteniendo área para programa {$allocation->program->code}: " . $e->getMessage());
                                        }
                                    @endphp
                                    @if($area === 'No disponible')
                                        <span class="text-gray-400">-</span>
                                    @else
                                        {{ $area }}
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $allocation->program->status ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $allocation->program && $allocation->program->start_date ? $allocation->program->start_date->format('d/m/Y') : '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $allocation->program && $allocation->program->finalization_date ? $allocation->program->finalization_date->format('d/m/Y') : '-' }}</td>
                                <!-- Cobro Titulación -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <span class="@if(auth()->user()->can('program_allocation.edit'))editable-cell @endif" data-id="{{ $allocation->id }}" data-field="cobro_titulacion" data-type="number">{{ $allocation->cobro_titulacion ?? '-' }}</span>
                                </td>
                                <!-- Asignación Programa -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <span class="@if(auth()->user()->can('program_allocation.edit'))editable-cell @endif" data-id="{{ $allocation->id }}" data-field="asignacion_programa" data-type="number">{{ $allocation->asignacion_programa ?? '-' }}</span>
                                </td>
                                <!-- Responsable Cartera -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    @php
                                        $responsableNombre = '-';
                                        if ($allocation->responsable_cartera) {
                                            // El campo almacena el ID del usuario
                                            $responsableUser = $users->firstWhere('id', (int)$allocation->responsable_cartera);
                                            if ($responsableUser) {
                                                $responsableNombre = $responsableUser->name;
                                            }
                                        }
                                    @endphp
                                    <span class="@if(auth()->user()->can('program_allocation.edit'))editable-cell @endif" data-id="{{ $allocation->id }}" data-field="responsable_cartera" data-type="select" data-value="{{ $allocation->responsable_cartera }}">{{ $responsableNombre }}</span>
                                </td>
                                <!-- Fecha Pago -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <span class="@if(auth()->user()->can('program_allocation.edit'))editable-cell @endif" data-id="{{ $allocation->id }}" data-field="fecha_pago" data-type="date">{{ $allocation->fecha_pago ? \Carbon\Carbon::parse($allocation->fecha_pago)->format('d/m/Y') : '-' }}</span>
                                </td>
                                <!-- Monto al 5 -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <span class="@if(auth()->user()->can('program_allocation.edit'))editable-cell @endif" data-id="{{ $allocation->id }}" data-field="monto_al_5" data-type="number">{{ $allocation->monto_al_5 ?? '-' }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><span data-id="{{ $allocation->id }}" data-field="porcentaje_al_5">{{ $allocation->porcentaje_al_5 ? number_format($allocation->porcentaje_al_5, 2) . '%' : '-' }}</span></td>
                                <!-- Monto al 10 -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <span class="@if(auth()->user()->can('program_allocation.edit'))editable-cell @endif" data-id="{{ $allocation->id }}" data-field="monto_al_10" data-type="number">{{ $allocation->monto_al_10 ?? '-' }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><span data-id="{{ $allocation->id }}" data-field="porcentaje_al_10">{{ $allocation->porcentaje_al_10 ? number_format($allocation->porcentaje_al_10, 2) . '%' : '-' }}</span></td>
                                <!-- Monto al 15 -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <span class="@if(auth()->user()->can('program_allocation.edit'))editable-cell @endif" data-id="{{ $allocation->id }}" data-field="monto_al_15" data-type="number">{{ $allocation->monto_al_15 ?? '-' }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><span data-id="{{ $allocation->id }}" data-field="porcentaje_al_15">{{ $allocation->porcentaje_al_15 ? number_format($allocation->porcentaje_al_15, 2) . '%' : '-' }}</span></td>
                                <!-- Monto al 20 -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <span class="@if(auth()->user()->can('program_allocation.edit'))editable-cell @endif" data-id="{{ $allocation->id }}" data-field="monto_al_20" data-type="number">{{ $allocation->monto_al_20 ?? '-' }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><span data-id="{{ $allocation->id }}" data-field="porcentaje_al_20">{{ $allocation->porcentaje_al_20 ? number_format($allocation->porcentaje_al_20, 2) . '%' : '-' }}</span></td>
                                <!-- Monto al 25 -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <span class="@if(auth()->user()->can('program_allocation.edit'))editable-cell @endif" data-id="{{ $allocation->id }}" data-field="monto_al_25" data-type="number">{{ $allocation->monto_al_25 ?? '-' }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><span data-id="{{ $allocation->id }}" data-field="porcentaje_al_25">{{ $allocation->porcentaje_al_25 ? number_format($allocation->porcentaje_al_25, 2) . '%' : '-' }}</span></td>
                                <!-- Monto al 30 -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <span class="@if(auth()->user()->can('program_allocation.edit'))editable-cell @endif" data-id="{{ $allocation->id }}" data-field="monto_al_30" data-type="number">{{ $allocation->monto_al_30 ?? '-' }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><span data-id="{{ $allocation->id }}" data-field="porcentaje_al_30">{{ $allocation->porcentaje_al_30 ? number_format($allocation->porcentaje_al_30, 2) . '%' : '-' }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="25" class="px-6 py-4 text-center text-sm text-gray-500">
                                    No hay datos disponibles
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Paginación -->
        @if($allocations->hasPages())
            <div class="mt-6">
                {{ $allocations->links() }}
            </div>
        @endif
    </div>
</div>

<style>
    .editable-cell {
        cursor: pointer;
        padding: 6px 8px;
        border: 1px solid #d1d5db;
        border-radius: 4px;
        transition: all 0.2s ease;
        background-color: #fafafa;
        display: inline-block;
        min-width: 60px;
        text-align: left;
    }
    
    .editable-cell:hover {
        background-color: #f0f9ff;
        border-color: #3b82f6;
        color: #1e40af;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
    }
    
    /* Estilos para celdas no editables */
    span:not(.editable-cell) {
        cursor: default;
    }
    
    .editable-cell-input {
        width: 100%;
        padding: 4px 8px;
        border: 1px solid #3b82f6;
        border-radius: 4px;
        font-size: 14px;
    }
    
    .editable-cell-input:focus {
        outline: none;
        border-color: #1e40af;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Establecer el mes actual por defecto en el formulario de agregar asignación
    const mesSelect = document.getElementById('mes');
    if (mesSelect) {
        const currentMonth = new Date().getMonth() + 1; // getMonth() devuelve 0-11
        mesSelect.value = currentMonth.toString();
    }
    
    // Usuarios disponibles para responsable_cartera
    const usuariosResponsable = @json($users->map(fn($user) => ['id' => $user->id, 'name' => $user->name])->toArray());

    function getResponsableNameById(id) {
        const usuario = usuariosResponsable.find(u => String(u.id) === String(id));
        return usuario ? usuario.name : id;
    }
    
    // TODOS los programas disponibles para búsqueda (no solo los sin asignación)
    const availablePrograms = @json($allPrograms->map(fn($p) => ['id' => $p->id, 'name' => $p->name])->toArray());
    
    console.log('Programas disponibles:', availablePrograms);
    
    // Configurar búsqueda de programas
    setupProgramSearch();
    
    function setupProgramSearch() {
        const searchInput = document.getElementById('program-search');
        const hiddenInput = document.getElementById('program');
        const resultsContainer = document.getElementById('program-results');
        
        console.log('setupProgramSearch - searchInput:', searchInput);
        console.log('setupProgramSearch - hiddenInput:', hiddenInput);
        console.log('setupProgramSearch - resultsContainer:', resultsContainer);
        
        if (!searchInput || !hiddenInput || !resultsContainer) {
            console.error('No se encontraron los elementos necesarios');
            return;
        }

        let searchTimeout;
        searchInput.addEventListener('input', function() {
            console.log('Input event - valor:', this.value);
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            
            if (query.length === 0) {
                resultsContainer.classList.add('hidden');
                return;
            }

            searchTimeout = setTimeout(() => {
                console.log('Buscando programas con query:', query);
                
                // Filtrar programas localmente
                const filtered = availablePrograms.filter(program => 
                    program.name.toLowerCase().includes(query.toLowerCase())
                );
                
                console.log('Resultados filtrados:', filtered);

                if (filtered.length === 0) {
                    resultsContainer.innerHTML = '<div class="p-2 text-gray-500 text-sm">No se encontraron programas</div>';
                    resultsContainer.classList.remove('hidden');
                    return;
                }

                let html = '<ul class="divide-y divide-gray-200">';
                filtered.forEach(item => {
                    html += `<li class="p-2 hover:bg-indigo-50 cursor-pointer text-sm" data-id="${item.id}">${item.name}</li>`;
                });
                html += '</ul>';

                resultsContainer.innerHTML = html;
                resultsContainer.classList.remove('hidden');

                resultsContainer.querySelectorAll('li').forEach(li => {
                    li.addEventListener('click', function() {
                        hiddenInput.value = this.getAttribute('data-id');
                        searchInput.value = this.textContent.trim();
                        resultsContainer.classList.add('hidden');
                    });
                });
            }, 300);
        });
        
        // Ocultar resultados al hacer clic fuera
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !resultsContainer.contains(e.target)) {
                resultsContainer.classList.add('hidden');
            }
        });
    }
    
    // Manejar adición de nueva asignación
    const addAllocationForm = document.getElementById('add-allocation-form');
    const importBtn = document.getElementById('import-btn');

    // Manejar importación desde mes anterior
    if (importBtn) {
        importBtn.addEventListener('click', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const mesActual = urlParams.get('mes') || new Date().getMonth() + 1;
            const mesAnterior = mesActual == 1 ? 12 : mesActual - 1;

            if (!confirm(`¿Deseas importar las asignaciones del mes anterior (mes ${mesAnterior}) al mes ${mesActual}?`)) {
                return;
            }

            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            fetch('{{ route("program-allocation.import-previous-month") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    mes_actual: parseInt(mesActual),
                    mes_anterior: parseInt(mesAnterior)
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(`✓ ${data.message}`);
                    window.location.reload();
                } else {
                    alert(`✗ Error: ${data.message}`);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al importar asignaciones');
            });
        });
    }

    if (addAllocationForm) {
        addAllocationForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const programId = document.getElementById('program').value;
            const mes = document.getElementById('mes').value;
            
            if (!programId) {
                alert('Por favor selecciona un programa');
                return;
            }
            
            if (!mes) {
                alert('Por favor selecciona un mes');
                return;
            }
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            fetch('{{ route("program-allocation.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    program_id: programId,
                    mes: mes
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Recargar la página para mostrar la nueva asignación
                    window.location.reload();
                } else {
                    alert('Error al crear asignación: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al crear asignación');
            });
        });
    }

    const editableCells = document.querySelectorAll('.editable-cell');
    
    editableCells.forEach(cell => {
        cell.addEventListener('click', function() {
            if (this.querySelector('input') || this.querySelector('select')) return; // Ya está siendo editado
            
            const value = this.textContent === '-' ? '' : this.textContent;
            const fieldType = this.dataset.type;
            const fieldName = this.dataset.field;
            const allocationId = this.dataset.id; // Capturar ID aquí
            
            console.log('Celda clickeada:', {allocationId, fieldName, fieldType, value});
            
            let input;
            
            if (fieldType === 'select' && fieldName === 'responsable_cartera') {
                // Crear select para responsable_cartera con usuarios
                input = document.createElement('select');
                input.className = 'editable-cell-input';
                
                // Opción vacía
                const emptyOption = document.createElement('option');
                emptyOption.value = '';
                emptyOption.textContent = '- Seleccionar -';
                input.appendChild(emptyOption);
                
                // Agregar usuarios
                usuariosResponsable.forEach(usuario => {
                    const option = document.createElement('option');
                    option.value = usuario.id;  // Guardar ID
                    option.textContent = usuario.name;
                    input.appendChild(option);
                });
                
                input.value = value;
            } else {
                // Crear input normal
                const inputType = fieldType === 'date' ? 'date' : (fieldType === 'number' ? 'number' : 'text');
                input = document.createElement('input');
                input.type = inputType;
                input.className = 'editable-cell-input';
                input.value = value;
                
                if (fieldType === 'date' && value) {
                    // Convertir formato d/m/Y a YYYY-MM-DD
                    const parts = value.split('/');
                    if (parts.length === 3) {
                        input.value = `${parts[2]}-${parts[1]}-${parts[0]}`;
                    }
                }
            }
            
            this.textContent = '';
            this.appendChild(input);
            input.focus();
            if (input.type === 'text' || input.type === 'number' || input.type === 'date') {
                input.select();
            }
            
            const saveCell = (newValue) => {
                if (newValue === '') {
                    this.textContent = '-';
                } else {
                    if (fieldType === 'date') {
                        const date = new Date(newValue);
                        this.textContent = date.toLocaleDateString('es-ES');
                    } else if (fieldType === 'select' && fieldName === 'responsable_cartera') {
                        this.textContent = getResponsableNameById(newValue);
                    } else {
                        this.textContent = newValue;
                    }
                }
                
                saveCellData(allocationId, fieldName, newValue === '' ? null : newValue, fieldType);
            };
            
            const handleBlur = () => {
                const newValue = input.value;
                saveCell(newValue);
            };
            
            input.addEventListener('blur', handleBlur);
            
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    const newValue = input.value;
                    saveCell(newValue);
                } else if (e.key === 'Escape') {
                    if (fieldType === 'select' && fieldName === 'responsable_cartera') {
                        this.textContent = value === '' ? '-' : getResponsableNameById(value);
                    } else {
                        this.textContent = value === '' ? '-' : value;
                    }
                }
            });
            
            if (fieldType === 'select') {
                input.addEventListener('change', (e) => {
                    e.stopPropagation();
                    e.preventDefault();
                    handleBlur();
                });
            }
        });
    });
    
    function saveCellData(id, field, value, fieldType) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        console.log('saveCellData llamado con:', {id, field, value, fieldType});
        const url = `/program-allocation/${id}/update-field`;
        console.log('URL construida:', url);
        console.log('Tipo de id:', typeof id, 'Valor id:', id);
        
        const requestBody = {
            field: field,
            value: value,
            type: fieldType
        };
        console.log('Body:', requestBody);
        console.log('ANTES DE FETCH - URL que se va a enviar:', url);
        console.log('ANTES DE FETCH - método:', 'PATCH');
        
        fetch(url, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(requestBody)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Campo actualizado exitosamente');
                
                // Actualizar porcentajes si se actualizó un monto o asignación_programa
                const montoFields = ['monto_al_5', 'monto_al_10', 'monto_al_15', 'monto_al_20', 'monto_al_25', 'monto_al_30'];
                
                if (montoFields.includes(field) || field === 'asignacion_programa') {
                    // Actualizar los porcentajes en la tabla
                    const allocationData = data.data;
                    montoFields.forEach(montoField => {
                        const porcentajeField = montoField.replace('monto', 'porcentaje');
                        const porcentajeCell = document.querySelector(`[data-id="${id}"][data-field="${porcentajeField}"]`);
                        
                        if (porcentajeCell && allocationData[porcentajeField]) {
                            porcentajeCell.textContent = (Math.round(allocationData[porcentajeField] * 100) / 100).toFixed(2) + '%';
                        }
                    });
                }
                
                // Actualizar las cards de totales
                if (data.totals) {
                    updateCards(data.totals);
                }
            } else {
                alert('Error al actualizar: ' + data.message);
                console.error('Error al actualizar:', data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    }
    
    function updateCards(totals) {
        // Obtener los elementos de las cards
        const cardElements = document.querySelectorAll('.grid.grid-cols-1.sm\\:grid-cols-2.lg\\:grid-cols-3 p.text-3xl');
        
        if (cardElements.length >= 3) {
            // Actualizar Total Asignación
            cardElements[0].textContent = new Intl.NumberFormat('es-ES', { 
                minimumFractionDigits: 2, 
                maximumFractionDigits: 2 
            }).format(totals.totalAsignacion || 0);
            
            // Actualizar Total Cobrado
            cardElements[1].textContent = new Intl.NumberFormat('es-ES', { 
                minimumFractionDigits: 2, 
                maximumFractionDigits: 2 
            }).format(totals.totalCobrado || 0);
            
            // Actualizar Porcentaje Total Alcanzado
            cardElements[2].textContent = (Math.round(totals.porcentajeTotalAlcanzado * 100) / 100).toFixed(2) + '%';
        }
    }

    // Manejar eliminación de asignaciones
    const deleteButtons = document.querySelectorAll('.delete-allocation-btn');
    deleteButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const program = this.getAttribute('data-program');
            
            if (!confirm(`¿Estás seguro de que deseas eliminar la asignación para ${program}?`)) {
                return;
            }

            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            fetch(`/program-allocation/${id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Recargar la página para actualizar la lista
                    window.location.reload();
                } else {
                    alert('Error al eliminar: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al eliminar la asignación');
            });
        });
    });
});
</script>
@endsection
