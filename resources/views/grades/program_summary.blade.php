<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-white leading-tight">
                {{ __('Resumen de Calificaciones del Programa') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('programs.show', $program->id) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-800 focus:outline-none focus:border-gray-800 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                    </svg>
                    Volver
                </a>
            </div>
        </div>
    </x-slot>

    <div >
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">                     
                        <div class="grid grid-cols-1 md:grid-cols-1 gap-6">
                            <div>
                                <p><span class="font-semibold">Nombre:</span> {{ $program->name }}</p>
                                <p><span class="font-semibold">ID Programa:</span> 
                                    @if(str_starts_with($program->name, 'Diplomado'))
                                        D-{{ $program->code }}
                                    @elseif(str_starts_with($program->name, 'Maestría'))
                                        M-{{ $program->code }}
                                    @elseif(str_starts_with($program->name, 'Curso'))
                                        C-{{ $program->code }}
                                    @else
                                        {{ $program->code }}
                                    @endif
                                </p>
                                <p><span class="font-semibold">Área:</span> 
                                    @php
                                        $area = 'No disponible';
                                        try {
                                            if ($program->postgraduate_id) {
                                                $postgrad = \App\Models\External\ExternalPostgraduate::where('id_posgrado', $program->postgraduate_id)->first();
                                                if ($postgrad && $postgrad->area_posgrado) {
                                                    $area = $postgrad->area_posgrado;
                                                }
                                            }
                                        } catch (\Exception $e) {
                                            \Log::error("Error obteniendo área para programa {$program->code}: " . $e->getMessage());
                                        }
                                    @endphp
                                    @if($area === 'No disponible')
                                        <span class="text-gray-400">No especificada</span>
                                    @else
                                        {{ $area }}
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">                     
                    <div class="p-6 bg-white border-b border-gray-200">                     
                        <div class="grid grid-cols-1 md:grid-cols-1 gap-6">
                            <div>
                                <p><span class="font-semibold">Fecha de Inicio del Programa:</span> {{ \Carbon\Carbon::parse($program->start_date)->format('d/m/y') }}</p>
                                <p><span class="font-semibold">Fecha de Finalización del Programa:</span> {{ \Carbon\Carbon::parse($program->finalization_date)->format('d/m/y') }}</p>
                                <p><span class="font-semibold">Link Moodle:</span>  
                                    <input type="text" 
                                           id="moodle_link" 
                                           value="{{ $program->moodle_link ?? '' }}" 
                                           class="mt-2 w-full px-3 py-2 border border-gray-300 rounded-md text-sm" 
                                           placeholder="https://moodle.ejemplo.com"
                                           data-program-id="{{ $program->id }}">
                                    <button type="button" 
                                            id="save_moodle_link"
                                            class="mt-2 px-3 py-1 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700">
                                        Guardar Link
                                    </button>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white">
                        <div class="text-center">
                            <p class="text-gray-600 text-sm font-medium mb-2">Total Participantes</p>
                            <p class="text-3xl font-bold text-gray-900">{{ $totalParticipants ?? 0 }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white">
                        <div class="text-center">
                            <p class="text-gray-600 text-sm font-medium mb-2">Participantes Vigentes</p>
                            <p class="text-3xl font-bold text-gray-900">{{ $activeParticipants ?? 0 }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white">
                        <div class="text-center">
                            <p class="text-gray-600 text-sm font-medium mb-2">Requisitos Inscripción Completos</p>
                            <p class="text-3xl font-bold text-gray-900">{{ $inscriptionRequirementsMet ?? 0 }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white">
                        <div class="text-center">
                            <p class="text-gray-600 text-sm font-medium mb-2">Requisitos Titulación Completos</p>
                            <p class="text-3xl font-bold text-gray-900">{{ $graduationRequirementsMet ?? 0 }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Módulos del Programa</h3>
                        @if($availableModules->count() > 0)
                            <form action="{{ route('programs.addModule', $program->id) }}" method="POST" class="flex gap-2">
                                @csrf
                                <select name="module_id" class="px-3 py-2 border border-gray-300 rounded-md text-sm" required>
                                    <option value="">Seleccionar módulo para agregar...</option>
                                    @foreach($availableModules as $availModule)
                                        <option value="{{ $availModule->id }}">{{ $availModule->name }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md text-sm hover:bg-green-700">
                                    Agregar Módulo
                                </button>
                            </form>
                        @endif
                    </div>
                    
                    @if(count($modules ?? []) > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Módulo</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre Módulo/Asignatura</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Carga Horaria Presencial</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Carga Horaria No Presencial</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre Docente</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Profesión</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fechas (Inicio - Fin)</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hoja de Vida</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Curriculum Documentado</th>
                                        
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($modules as $module)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                                                @if(!$loop->first)
                                                    <form action="{{ route('programs.reorderModules', [$program->id, $module->id]) }}" method="POST" style="display:inline;">
                                                        @csrf
                                                        <input type="hidden" name="module_id" value="{{ $module->id }}">
                                                        <input type="hidden" name="direction" value="up">
                                                        <button type="submit" class="text-blue-600 hover:text-blue-900" title="Mover arriba">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                                <path fill-rule="evenodd" d="M3.293 9.707a1 1 0 010-1.414l6-6a1 1 0 011.414 0l6 6a1 1 0 01-1.414 1.414L11 5.414V15a1 1 0 11-2 0V5.414L4.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                                            </svg>
                                                        </button>
                                                    </form>
                                                @endif
                                                @if(!$loop->last)
                                                    <form action="{{ route('programs.reorderModules', [$program->id, $module->id]) }}" method="POST" style="display:inline;">
                                                        @csrf
                                                        <input type="hidden" name="module_id" value="{{ $module->id }}">
                                                        <input type="hidden" name="direction" value="down">
                                                        <button type="submit" class="text-blue-600 hover:text-blue-900" title="Mover abajo">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                                <path fill-rule="evenodd" d="M16.707 10.293a1 1 0 010 1.414l-6 6a1 1 0 01-1.414 0l-6-6a1 1 0 111.414-1.414L9 14.586V5a1 1 0 012 0v9.586l4.293-4.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                            </svg>
                                                        </button>
                                                    </form>
                                                @endif
                                                <form action="{{ route('programs.removeModule', [$program->id, $module->id]) }}" method="POST" style="display:inline;" onsubmit="return confirm('¿Estás seguro que deseas remover este módulo de la tabla?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900" title="Remover">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $loop->iteration }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $module->name ?? '-' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <div class="flex gap-1 items-center">
                                                    <input type="number" 
                                                           value="{{ $module->presential_hours ?? '' }}" 
                                                           class="w-16 px-2 py-1 border border-gray-300 rounded text-sm presential-hours" 
                                                           data-program-id="{{ $program->id }}"
                                                           data-module-id="{{ $module->id }}"
                                                           min="0" 
                                                           step="0.5"
                                                           placeholder="0">
                                                    <span class="text-gray-500">h</span>
                                                    <button type="button" 
                                                            class="px-2 py-1 bg-blue-500 text-white text-xs rounded hover:bg-blue-600 save-hours"
                                                            data-field="presential_hours">
                                                        ✓
                                                    </button>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <div class="flex gap-1 items-center">
                                                    <input type="number" 
                                                           value="{{ $module->non_presential_hours ?? '' }}" 
                                                           class="w-16 px-2 py-1 border border-gray-300 rounded text-sm non-presential-hours" 
                                                           data-program-id="{{ $program->id }}"
                                                           data-module-id="{{ $module->id }}"
                                                           min="0" 
                                                           step="0.5"
                                                           placeholder="0">
                                                    <span class="text-gray-500">h</span>
                                                    <button type="button" 
                                                            class="px-2 py-1 bg-blue-500 text-white text-xs rounded hover:bg-blue-600 save-hours"
                                                            data-field="non_presential_hours">
                                                        ✓
                                                    </button>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                @if($module->teacher)
                                                    {{ $module->teacher->name }} {{ $module->teacher->paternal_surname }} {{ $module->teacher->maternal_surname }}
                                                @else
                                                    <span class="text-gray-400">Sin asignar</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $module->teacher->profession ?? '-' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center"><b>Inicio:</b> {{ $module->start_date ? $module->start_date->format('d/m/Y') : 'No definida' }} <br> <b>Fin:</b> {{ $module->finalization_date ? $module->finalization_date->format('d/m/Y') : 'No definida' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                @if($module->teacher && $module->teacher->curriculum_vitae)
                                                    <a href="{{ Storage::url($module->teacher->curriculum_vitae) }}" target="_blank" class="text-indigo-600 hover:text-indigo-900">Descargar</a>
                                                @else
                                                    <span class="text-gray-400">-</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                @if($module->teacher && $module->teacher->documented_curriculum)
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Sí</span>
                                                @else
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">No</span>
                                                @endif
                                            </td>
                                            
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700">
                                        No hay módulos registrados para este programa.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Participantes del Programa</h3>
                        <div class="flex gap-2">
                            <button id="recalculateFinalGradesBtn" 
                                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Recalcular Notas Finales
                            </button>
                            <a href="{{ route('grades.programSummaryReport', $program->id) }}" 
                               class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md text-sm hover:bg-green-700 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                                Descargar Reporte XLSX
                            </a>
                        </div>
                    </div>
                    
                    @if(count($participants ?? []) > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" rowspan="2" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">N°</th>
                                        <th scope="col" rowspan="2" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Carnet</th>
                                        <th scope="col" rowspan="2" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Extensión</th>
                                        <th scope="col" rowspan="2" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                                        <th scope="col" rowspan="2" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ap. Paterno</th>
                                        <th scope="col" rowspan="2" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ap. Materno</th>
                                        <th scope="col" rowspan="2" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Celular</th>
                                        <th scope="col" rowspan="2" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Correo Electrónico</th>
                                        <th scope="col" rowspan="2" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Género</th>
                                        <th scope="col" rowspan="2" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Universidad de Titulación</th>
                                        <th scope="col" rowspan="2" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Profesión</th>
                                        <th scope="col" rowspan="2" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha de Nacimiento</th>
                                        <th scope="col" rowspan="2" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Edad</th>
                                        <th scope="col" rowspan="2" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ciudad de Residencia</th>
                                        <th scope="col" rowspan="2" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Asesor</th>
                                        <th scope="colgroup" colspan="{{ count($modules) + 2 }}" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-l border-gray-300">Notas</th>
                                        <th scope="colgroup" colspan="2" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-l border-gray-300">Estado Participantes</th>
                                        <th scope="colgroup" colspan="5" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-l border-gray-300">Requisitos de Inscripción</th>
                                        <th scope="colgroup" colspan="7" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-l border-gray-300">Requisitos de Titulación</th>
                                        @if(str_starts_with($program->name, 'Diplomado'))
                                            <th scope="colgroup" colspan="3" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-l border-gray-300">Trabajo Final</th>
                                        @elseif(str_starts_with($program->name, 'Maestría'))
                                            <th scope="colgroup" colspan="6" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-l border-gray-300">Fase de Trabajo de Grado</th>
                                        @endif
                                        <th scope="colgroup" colspan="5" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-l border-gray-300">Estado de Titulación</th>
                                        <th scope="colgroup" colspan="4" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-l border-gray-300">Contable Interno</th>
                                        <th scope="colgroup" colspan="4" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-l border-gray-300">Contable Externo</th>
                                    </tr>
                                    <tr class="border-t border-gray-200">
                                        @foreach($modules as $module)
                                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Mod. {{ $loop->iteration }}</th>
                                        @endforeach
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Notas Finales</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Nivelación</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Observaciones/Link Justificación</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Título Academico</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Título en Provisión Nacional</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Cedula de Identidad</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Certificado de Nacimiento</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Requisitos Completos</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Título Academico Legalizado</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Título en Provisión Nacional Legalizado</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Cedula de Identidad</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Certificado de Nacimiento Original</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Fotos</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Requisitos Completos</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo de Trámite</th>
                                        @if(str_starts_with($program->name, 'Diplomado'))
                                            
                                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Elaboración de Monografía</th>
                                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Monografía Recepcionada en Sede</th>
                                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Trabajo Final</th>
                                        @elseif(str_starts_with($program->name, 'Maestría'))
                                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Presentación Trabajo de Grado</th>
                                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Informe de Aprobación del Tutor</th>
                                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Predefensa</th>
                                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Defensa</th>
                                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Estado Contable de Defensa</th>
                                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Fase Trabajo de Grado</th>
                                        @endif
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Tramite</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Titulados/Recepcionados</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Entrega de Documentos</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Entrega de Diplomas</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Estado de Titulación</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo de Plan</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Estado de Cobranzas</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Saldo por Cobrar</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Pago Titulación</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Inscripción</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Matrícula</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Colegiatura</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Titulaciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($participants as $participant)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $loop->iteration }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $participant->ci ?? '-' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $participant->location ?? '-' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                <div class="flex gap-1 items-center">
                                                    <input type="text" 
                                                           value="{{ $participant->name ?? '' }}" 
                                                           class="px-2 py-1 border border-gray-300 rounded text-sm participant-name" 
                                                           data-program-id="{{ $program->id }}"
                                                           data-inscription-id="{{ $participant->id ?? '' }}"
                                                           placeholder="-">
                                                    <button type="button" 
                                                            class="px-2 py-1 bg-blue-500 text-white text-xs rounded hover:bg-blue-600 save-name-field"
                                                            title="Guardar nombre">
                                                        ✓
                                                    </button>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <div class="flex gap-1 items-center">
                                                    <input type="text" 
                                                           value="{{ $participant->paternal_surname ?? '' }}" 
                                                           class="px-2 py-1 border border-gray-300 rounded text-sm participant-paternal" 
                                                           data-program-id="{{ $program->id }}"
                                                           data-inscription-id="{{ $participant->id ?? '' }}"
                                                           placeholder="-">
                                                    <button type="button" 
                                                            class="px-2 py-1 bg-blue-500 text-white text-xs rounded hover:bg-blue-600 save-paternal-field"
                                                            title="Guardar apellido paterno">
                                                        ✓
                                                    </button>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <div class="flex gap-1 items-center">
                                                    <input type="text" 
                                                           value="{{ $participant->maternal_surname ?? '' }}" 
                                                           class="px-2 py-1 border border-gray-300 rounded text-sm participant-maternal" 
                                                           data-program-id="{{ $program->id }}"
                                                           data-inscription-id="{{ $participant->id ?? '' }}"
                                                           placeholder="-">
                                                    <button type="button" 
                                                            class="px-2 py-1 bg-blue-500 text-white text-xs rounded hover:bg-blue-600 save-maternal-field"
                                                            title="Guardar apellido materno">
                                                        ✓
                                                    </button>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $participant->phone ?? '-' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $participant->email ?? '-' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $participant->gender ?? '-' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $participant->university ?? '-' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ strtoupper($participant->profession ?? '-') }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $participant->birth_date ? $participant->birth_date->format('d/m/Y') : '-' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                @if($participant->birth_date)
                                                    {{ \Carbon\Carbon::parse($participant->birth_date)->age }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <div class="flex gap-1 items-center">
                                                    <input type="text" 
                                                           value="{{ $participant->residence ?? '' }}" 
                                                           class="px-2 py-1 border border-gray-300 rounded text-sm participant-residence" 
                                                           data-program-id="{{ $program->id }}"
                                                           data-inscription-id="{{ $participant->id ?? '' }}"
                                                           placeholder="-">
                                                    <button type="button" 
                                                            class="px-2 py-1 bg-blue-500 text-white text-xs rounded hover:bg-blue-600 save-residence"
                                                            title="Guardar residencia">
                                                        ✓
                                                    </button>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $participant->advisor ?? '-' }}</td>
                                            @foreach($modules as $module)
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                                    <input type="number" 
                                                           step="0.01"
                                                           min="0"
                                                           max="100"
                                                           class="module-grade"
                                                           data-program-id="{{ $program->id }}"
                                                           data-inscription-id="{{ $participant->id }}"
                                                           data-module-id="{{ $module->id }}"
                                                           value="{{ $participantGrades[$participant->id][$module->id] !== '-' ? $participantGrades[$participant->id][$module->id] : '' }}"
                                                           placeholder="-"
                                                           style="width: 70px; text-align: center;">
                                                </td>
                                            @endforeach
                                            @php
                                                $grades = [];
                                                foreach($modules as $module) {
                                                    $grade = $participantGrades[$participant->id][$module->id] ?? null;
                                                    if ($grade !== null && $grade !== '-' && is_numeric($grade)) {
                                                        $grades[] = (float)$grade;
                                                    }
                                                }
                                                $average = count($grades) > 0 ? array_sum($grades) / count($grades) : null;
                                            @endphp
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center font-medium
                                                @if($average !== null)
                                                    @if($average >= 60)
                                                        text-green-600
                                                    @else
                                                        text-red-600
                                                    @endif
                                                @else
                                                    text-gray-500
                                                @endif
                                            ">
                                                {{ $average !== null ? round($average, 2) : '-' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500">-</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                                <select class="w-32 px-2 py-1 border border-gray-300 rounded text-sm" 
                                                        data-program-id="{{ $program->id }}"
                                                        data-inscription-id="{{ $participant->id }}"
                                                        data-requirement="participant_status">
                                                    <option value="">Seleccionar</option>
                                                    <option value="VIGENTE" {{ $participant->participant_status === 'VIGENTE' ? 'selected' : '' }}>VIGENTE</option>
                                                    <option value="DEVOLUCIÓN" {{ $participant->participant_status === 'DEVOLUCIÓN' ? 'selected' : '' }}>DEVOLUCIÓN</option>
                                                    <option value="ABANDON" {{ $participant->participant_status === 'ABANDON' ? 'selected' : '' }}>ABANDONO</option>
                                                    <option value="INSCRIPCIÓN INCOMPLETA" {{ $participant->participant_status === 'INSCRIPCIÓN INCOMPLETA' ? 'selected' : '' }}>INSCRIPCIÓN INCOMPLETA</option>
                                                </select>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                                <input type="text" 
                                                       class="w-32 px-2 py-1 border border-gray-300 rounded text-sm"
                                                       data-program-id="{{ $program->id }}"
                                                       data-inscription-id="{{ $participant->id }}"
                                                       data-requirement="participant_justification"
                                                       value="{{ $participant->participant_justification ?? '' }}"
                                                       placeholder="Observaciones">
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                                <input type="checkbox" 
                                                       class="requirement-checkbox" 
                                                       data-program-id="{{ $program->id }}"
                                                       data-inscription-id="{{ $participant->id }}"
                                                       data-requirement="has_degree_title"
                                                       {{ $participant->has_degree_title ? 'checked' : '' }}>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                                <input type="checkbox" 
                                                       class="requirement-checkbox" 
                                                       data-program-id="{{ $program->id }}"
                                                       data-inscription-id="{{ $participant->id }}"
                                                       data-requirement="has_academic_diploma"
                                                       {{ $participant->has_academic_diploma ? 'checked' : '' }}>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                                <input type="checkbox" 
                                                       class="requirement-checkbox" 
                                                       data-program-id="{{ $program->id }}"
                                                       data-inscription-id="{{ $participant->id }}"
                                                       data-requirement="has_identity_card"
                                                       {{ $participant->has_identity_card ? 'checked' : '' }}>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                                <input type="checkbox" 
                                                       class="requirement-checkbox" 
                                                       data-program-id="{{ $program->id }}"
                                                       data-inscription-id="{{ $participant->id }}"
                                                       data-requirement="has_birth_certificate"
                                                       {{ $participant->has_birth_certificate ? 'checked' : '' }}>
                                            </td>
                                            @php
                                                $completedRequirements = 0;
                                                if ($participant->has_degree_title) $completedRequirements++;
                                                if ($participant->has_academic_diploma) $completedRequirements++;
                                                if ($participant->has_identity_card) $completedRequirements++;
                                                if ($participant->has_birth_certificate) $completedRequirements++;
                                                $requirementsPercentage = ($completedRequirements / 4) * 100;
                                            @endphp
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center font-semibold
                                                @if($requirementsPercentage == 100)
                                                    text-green-600
                                                @elseif($requirementsPercentage >= 50)
                                                    text-yellow-600
                                                @else
                                                    text-red-600
                                                @endif
                                            ">{{ intval($requirementsPercentage) }}%</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                                <input type="checkbox" 
                                                       class="requirement-checkbox" 
                                                       data-program-id="{{ $program->id }}"
                                                       data-inscription-id="{{ $participant->id }}"
                                                       data-requirement="has_legalized_degree_title"
                                                       {{ $participant->has_legalized_degree_title ? 'checked' : '' }}>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                                <input type="checkbox" 
                                                       class="requirement-checkbox" 
                                                       data-program-id="{{ $program->id }}"
                                                       data-inscription-id="{{ $participant->id }}"
                                                       data-requirement="has_legalized_academic_diploma"
                                                       {{ $participant->has_legalized_academic_diploma ? 'checked' : '' }}>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                                <input type="checkbox" 
                                                       class="requirement-checkbox" 
                                                       data-program-id="{{ $program->id }}"
                                                       data-inscription-id="{{ $participant->id }}"
                                                       data-requirement="has_identity_card_graduation"
                                                       {{ $participant->has_identity_card_graduation ? 'checked' : '' }}>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                                <input type="checkbox" 
                                                       class="requirement-checkbox" 
                                                       data-program-id="{{ $program->id }}"
                                                       data-inscription-id="{{ $participant->id }}"
                                                       data-requirement="has_birth_certificate_original"
                                                       {{ $participant->has_birth_certificate_original ? 'checked' : '' }}>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                                <input type="checkbox" 
                                                       class="requirement-checkbox" 
                                                       data-program-id="{{ $program->id }}"
                                                       data-inscription-id="{{ $participant->id }}"
                                                       data-requirement="has_photos"
                                                       {{ $participant->has_photos ? 'checked' : '' }}>
                                            </td>
                                            @php
                                                $completedGraduationRequirements = 0;
                                                if ($participant->has_legalized_degree_title) $completedGraduationRequirements++;
                                                if ($participant->has_legalized_academic_diploma) $completedGraduationRequirements++;
                                                if ($participant->has_identity_card_graduation) $completedGraduationRequirements++;
                                                if ($participant->has_birth_certificate_original) $completedGraduationRequirements++;
                                                if ($participant->has_photos) $completedGraduationRequirements++;
                                                $graduationRequirementsPercentage = ($completedGraduationRequirements / 5) * 100;
                                            @endphp
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center font-semibold
                                                @if($graduationRequirementsPercentage == 100)
                                                    text-green-600
                                                @elseif($graduationRequirementsPercentage >= 50)
                                                    text-yellow-600
                                                @else
                                                    text-red-600
                                                @endif
                                            ">{{ intval($graduationRequirementsPercentage) }}%</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                                <select class="px-2 py-1 border border-gray-300 rounded text-sm graduation-procedure-type"
                                                        data-program-id="{{ $program->id }}"
                                                        data-inscription-id="{{ $participant->id }}">
                                                    <option value="">Seleccionar</option>
                                                    <option value="Personal" {{ $participant->graduation_procedure_type === 'Personal' ? 'selected' : '' }}>Personal</option>
                                                    <option value="Convenio ESAM" {{ $participant->graduation_procedure_type === 'Convenio ESAM' ? 'selected' : '' }}>Convenio ESAM</option>
                                                </select>
                                            </td>
                                            @if(str_starts_with($program->name, 'Diplomado'))
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                                    <input type="checkbox" 
                                                           class="requirement-checkbox" 
                                                           data-program-id="{{ $program->id }}"
                                                           data-inscription-id="{{ $participant->id }}"
                                                           data-requirement="has_monograph_elaboration"
                                                           {{ $participant->has_monograph_elaboration ? 'checked' : '' }}>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                                    <input type="checkbox" 
                                                           class="requirement-checkbox" 
                                                           data-program-id="{{ $program->id }}"
                                                           data-inscription-id="{{ $participant->id }}"
                                                           data-requirement="has_monograph_received"
                                                           {{ $participant->has_monograph_received ? 'checked' : '' }}>
                                                </td>
                                                @php
                                                    $completedMonographRequirements = 0;
                                                    if ($participant->has_monograph_elaboration) $completedMonographRequirements++;
                                                    if ($participant->has_monograph_received) $completedMonographRequirements++;
                                                    $monographRequirementsPercentage = ($completedMonographRequirements / 2) * 100;
                                                @endphp
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center font-semibold
                                                    @if($monographRequirementsPercentage == 100)
                                                        text-green-600
                                                    @elseif($monographRequirementsPercentage >= 50)
                                                        text-yellow-600
                                                    @else
                                                        text-red-600
                                                    @endif
                                                ">{{ intval($monographRequirementsPercentage) }}%</td>
                                            @elseif(str_starts_with($program->name, 'Maestría'))
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                                    <input type="checkbox" 
                                                           class="requirement-checkbox" 
                                                           data-program-id="{{ $program->id }}"
                                                           data-inscription-id="{{ $participant->id }}"
                                                           data-requirement="has_degree_work_presentation"
                                                           {{ ($participant->has_degree_work_presentation ?? false) ? 'checked' : '' }}>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                                    <input type="checkbox" 
                                                           class="requirement-checkbox" 
                                                           data-program-id="{{ $program->id }}"
                                                           data-inscription-id="{{ $participant->id }}"
                                                           data-requirement="has_tutor_approval_report"
                                                           {{ ($participant->has_tutor_approval_report ?? false) ? 'checked' : '' }}>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                                    <input type="text" 
                                                           class="requirement-text-input w-full px-2 py-1 text-sm border border-gray-300 rounded" 
                                                           data-program-id="{{ $program->id }}"
                                                           data-inscription-id="{{ $participant->id }}"
                                                           data-requirement="has_pre_defense"
                                                           value="{{ $participant->has_pre_defense ?? '' }}">
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                                    <input type="text" 
                                                           class="requirement-text-input w-full px-2 py-1 text-sm border border-gray-300 rounded" 
                                                           data-program-id="{{ $program->id }}"
                                                           data-inscription-id="{{ $participant->id }}"
                                                           data-requirement="has_defense"
                                                           value="{{ $participant->has_defense ?? '' }}">
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                                    <input type="text" 
                                                           class="requirement-text-input w-full px-2 py-1 text-sm border border-gray-300 rounded" 
                                                           data-program-id="{{ $program->id }}"
                                                           data-inscription-id="{{ $participant->id }}"
                                                           data-requirement="has_defense_accounting_status"
                                                           value="{{ $participant->has_defense_accounting_status ?? '' }}">
                                                </td>
                                                @php
                                                    $completedMasterDegreeRequirements = 0;
                                                    if ($participant->has_degree_work_presentation ?? false) $completedMasterDegreeRequirements++;
                                                    if ($participant->has_tutor_approval_report ?? false) $completedMasterDegreeRequirements++;
                                                    $masterDegreePercentage = ($completedMasterDegreeRequirements / 2) * 100;
                                                @endphp
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center font-semibold
                                                    @if($masterDegreePercentage == 100)
                                                        text-green-600
                                                    @elseif($masterDegreePercentage >= 50)
                                                        text-yellow-600
                                                    @else
                                                        text-red-600
                                                    @endif
                                                ">{{ intval($masterDegreePercentage) }}%</td>
                                            @endif
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                                <input type="checkbox" 
                                                       class="requirement-checkbox" 
                                                       data-program-id="{{ $program->id }}"
                                                       data-inscription-id="{{ $participant->id }}"
                                                       data-requirement="has_graduation_procedure"
                                                       {{ $participant->has_graduation_procedure ? 'checked' : '' }}>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                                <input type="checkbox" 
                                                       class="requirement-checkbox" 
                                                       data-program-id="{{ $program->id }}"
                                                       data-inscription-id="{{ $participant->id }}"
                                                       data-requirement="has_graduation_received"
                                                       {{ $participant->has_graduation_received ? 'checked' : '' }}>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                                <input type="checkbox" 
                                                       class="requirement-checkbox" 
                                                       data-program-id="{{ $program->id }}"
                                                       data-inscription-id="{{ $participant->id }}"
                                                       data-requirement="has_documents_delivered"
                                                       {{ $participant->has_documents_delivered ? 'checked' : '' }}>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                                <input type="checkbox" 
                                                       class="requirement-checkbox" 
                                                       data-program-id="{{ $program->id }}"
                                                       data-inscription-id="{{ $participant->id }}"
                                                       data-requirement="has_diplomas_delivered"
                                                       {{ $participant->has_diplomas_delivered ? 'checked' : '' }}>
                                            </td>
                                            @php
                                                $completedEstadoCount = 0;
                                                if ($participant->has_graduation_procedure) $completedEstadoCount++;
                                                if ($participant->has_graduation_received) $completedEstadoCount++;
                                                if ($participant->has_documents_delivered) $completedEstadoCount++;
                                                if ($participant->has_diplomas_delivered) $completedEstadoCount++;
                                                $estadoPercentage = ($completedEstadoCount / 4) * 100;
                                            @endphp
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center font-semibold
                                                @if($estadoPercentage == 100)
                                                  text-green-600
                                                @elseif($estadoPercentage >= 50)
                                                  text-yellow-600
                                                @else
                                                  text-red-600
                                                @endif
                                            ">{{ intval($estadoPercentage) }}%</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                                {{ $participant->payment_plan ?? '-' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                                <input type="text" 
                                                       class="requirement-text w-24 px-2 py-1 border border-gray-300 rounded" 
                                                       data-program-id="{{ $program->id }}"
                                                       data-inscription-id="{{ $participant->id }}"
                                                       data-requirement="internal_accounting_billing_status"
                                                       value="{{ $participant->internal_accounting_billing_status ?? '' }}">
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                                <input type="number" 
                                                       step="0.01"
                                                       class="requirement-text w-24 px-2 py-1 border border-gray-300 rounded" 
                                                       data-program-id="{{ $program->id }}"
                                                       data-inscription-id="{{ $participant->id }}"
                                                       data-requirement="internal_accounting_amount_due"
                                                       value="{{ $participant->internal_accounting_amount_due ?? '' }}">
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                                <input type="text" 
                                                       class="requirement-text w-24 px-2 py-1 border border-gray-300 rounded" 
                                                       data-program-id="{{ $program->id }}"
                                                       data-inscription-id="{{ $participant->id }}"
                                                       data-requirement="internal_accounting_graduation_payment"
                                                       value="{{ $participant->internal_accounting_graduation_payment ?? '' }}">
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                                <input type="text" 
                                                       class="requirement-text w-24 px-2 py-1 border border-gray-300 rounded" 
                                                       data-program-id="{{ $program->id }}"
                                                       data-inscription-id="{{ $participant->id }}"
                                                       data-requirement="external_accounting_registration"
                                                       value="{{ $participant->external_accounting_registration ?? '' }}">
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                                <input type="text" 
                                                       class="requirement-text w-24 px-2 py-1 border border-gray-300 rounded" 
                                                       data-program-id="{{ $program->id }}"
                                                       data-inscription-id="{{ $participant->id }}"
                                                       data-requirement="external_accounting_enrollment"
                                                       value="{{ $participant->external_accounting_enrollment ?? '' }}">
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                                <input type="text" 
                                                       class="requirement-text w-24 px-2 py-1 border border-gray-300 rounded" 
                                                       data-program-id="{{ $program->id }}"
                                                       data-inscription-id="{{ $participant->id }}"
                                                       data-requirement="external_accounting_tuition"
                                                       value="{{ $participant->external_accounting_tuition ?? '' }}">
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                                <input type="text" 
                                                       class="requirement-text w-24 px-2 py-1 border border-gray-300 rounded" 
                                                       data-program-id="{{ $program->id }}"
                                                       data-inscription-id="{{ $participant->id }}"
                                                       data-requirement="external_accounting_degrees"
                                                       value="{{ $participant->external_accounting_degrees ?? '' }}">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700">
                                        No hay participantes registrados para este programa.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Manejo de botones para guardar cargas horarias
    const saveHoursButtons = document.querySelectorAll('.save-hours');
    
    saveHoursButtons.forEach(button => {
        button.addEventListener('click', function() {
            const field = this.dataset.field;
            const row = this.closest('tr');
            let input;
            
            if (field === 'presential_hours') {
                input = row.querySelector('.presential-hours');
            } else {
                input = row.querySelector('.non-presential-hours');
            }
            
            const programId = input.dataset.programId;
            const moduleId = input.dataset.moduleId;
            const value = input.value;
            
            if (value === '' || value === null) {
                alert('Por favor ingresa un valor');
                return;
            }
            
            fetch(`/programs/${programId}/modules/${moduleId}/update-hours`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                },
                body: JSON.stringify({
                    field: field,
                    value: value
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error al actualizar las horas');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    input.style.borderColor = '#10b981';
                    button.style.backgroundColor = '#10b981';
                    button.textContent = '✓';
                    setTimeout(() => {
                        input.style.borderColor = '#d1d5db';
                        button.style.backgroundColor = '#3b82f6';
                    }, 2000);
                } else {
                    alert('Error: ' + (data.message || 'No se pudo actualizar'));
                    input.style.borderColor = '#ef4444';
                    button.style.backgroundColor = '#ef4444';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al guardar: ' + error.message);
                input.style.borderColor = '#ef4444';
                button.style.backgroundColor = '#ef4444';
            });
        });
    });

    // Manejo del Link de Moodle
    const saveMoodleButton = document.getElementById('save_moodle_link');
    const moodleInput = document.getElementById('moodle_link');
    
    if (saveMoodleButton && moodleInput) {
        saveMoodleButton.addEventListener('click', function() {
            const programId = moodleInput.dataset.programId;
            const value = moodleInput.value;
            
            fetch(`/programs/${programId}/update-moodle-link`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                },
                body: JSON.stringify({
                    moodle_link: value
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error al actualizar el link');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    saveMoodleButton.style.backgroundColor = '#10b981';
                    saveMoodleButton.textContent = 'Guardado ✓';
                    setTimeout(() => {
                        saveMoodleButton.style.backgroundColor = '#2563eb';
                        saveMoodleButton.textContent = 'Guardar Link';
                    }, 2000);
                    moodleInput.style.borderColor = '#10b981';
                    setTimeout(() => {
                        moodleInput.style.borderColor = '#d1d5db';
                    }, 2000);
                } else {
                    alert('Error: ' + (data.message || 'No se pudo actualizar'));
                    saveMoodleButton.style.backgroundColor = '#ef4444';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al guardar: ' + error.message);
                saveMoodleButton.style.backgroundColor = '#ef4444';
            });
        });
    }

    // Manejo de botones para guardar residencia de participantes
    const saveResidenceButtons = document.querySelectorAll('.save-residence');
    
    saveResidenceButtons.forEach(button => {
        button.addEventListener('click', function() {
            const row = this.closest('tr');
            const input = row.querySelector('.participant-residence');
            const programId = input.dataset.programId;
            const inscriptionId = input.dataset.inscriptionId;
            const value = input.value;
            
            fetch(`/programs/${programId}/inscriptions/${inscriptionId}/update-residence`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                },
                body: JSON.stringify({
                    residence: value
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error al actualizar la residencia');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    input.style.borderColor = '#10b981';
                    button.style.backgroundColor = '#10b981';
                    button.textContent = '✓';
                    setTimeout(() => {
                        input.style.borderColor = '#d1d5db';
                        button.style.backgroundColor = '#3b82f6';
                    }, 2000);
                } else {
                    alert('Error: ' + (data.message || 'No se pudo actualizar'));
                    input.style.borderColor = '#ef4444';
                    button.style.backgroundColor = '#ef4444';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al guardar: ' + error.message);
                input.style.borderColor = '#ef4444';
                button.style.backgroundColor = '#ef4444';
            });
        });
    });

    // Manejo de botones para guardar nombre de participantes
    const saveNameButtons = document.querySelectorAll('.save-name-field');
    
    saveNameButtons.forEach(button => {
        button.addEventListener('click', function() {
            const row = this.closest('tr');
            const input = row.querySelector('.participant-name');
            const programId = input.dataset.programId;
            const inscriptionId = input.dataset.inscriptionId;
            const value = input.value.trim();
            
            if (!value) {
                alert('El nombre no puede estar vacío');
                return;
            }
            
            fetch(`/programs/${programId}/inscriptions/${inscriptionId}/update-name-field`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                },
                body: JSON.stringify({
                    field_type: 'name',
                    value: value
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error al actualizar el nombre');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    input.style.borderColor = '#10b981';
                    button.style.backgroundColor = '#10b981';
                    setTimeout(() => {
                        input.style.borderColor = '#d1d5db';
                        button.style.backgroundColor = '#3b82f6';
                    }, 2000);
                } else {
                    alert('Error: ' + (data.message || 'No se pudo actualizar'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al guardar: ' + error.message);
            });
        });
    });

    // Manejo de botones para guardar apellido paterno
    const savePaternalButtons = document.querySelectorAll('.save-paternal-field');
    
    savePaternalButtons.forEach(button => {
        button.addEventListener('click', function() {
            const row = this.closest('tr');
            const input = row.querySelector('.participant-paternal');
            const programId = input.dataset.programId;
            const inscriptionId = input.dataset.inscriptionId;
            const value = input.value.trim();
            
            
            fetch(`/programs/${programId}/inscriptions/${inscriptionId}/update-name-field`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                },
                body: JSON.stringify({
                    field_type: 'paternal_surname',
                    value: value
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error al actualizar el apellido');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    input.style.borderColor = '#10b981';
                    button.style.backgroundColor = '#10b981';
                    setTimeout(() => {
                        input.style.borderColor = '#d1d5db';
                        button.style.backgroundColor = '#3b82f6';
                    }, 2000);
                } else {
                    alert('Error: ' + (data.message || 'No se pudo actualizar'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al guardar: ' + error.message);
            });
        });
    });

    // Manejo de botones para guardar apellido materno
    const saveMaternalButtons = document.querySelectorAll('.save-maternal-field');
    
    saveMaternalButtons.forEach(button => {
        button.addEventListener('click', function() {
            const row = this.closest('tr');
            const input = row.querySelector('.participant-maternal');
            const programId = input.dataset.programId;
            const inscriptionId = input.dataset.inscriptionId;
            const value = input.value.trim();
            
            fetch(`/programs/${programId}/inscriptions/${inscriptionId}/update-name-field`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                },
                body: JSON.stringify({
                    field_type: 'maternal_surname',
                    value: value
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error al actualizar el apellido');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    input.style.borderColor = '#10b981';
                    button.style.backgroundColor = '#10b981';
                    setTimeout(() => {
                        input.style.borderColor = '#d1d5db';
                        button.style.backgroundColor = '#3b82f6';
                    }, 2000);
                } else {
                    alert('Error: ' + (data.message || 'No se pudo actualizar'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al guardar: ' + error.message);
            });
        });
    });

    // Manejo de checkboxes para requisitos de inscripción
    const requirementCheckboxes = document.querySelectorAll('.requirement-checkbox');
    
    requirementCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const programId = this.dataset.programId;
            const inscriptionId = this.dataset.inscriptionId;
            const requirement = this.dataset.requirement;
            const value = this.checked;
            
            fetch(`/programs/${programId}/inscriptions/${inscriptionId}/update-requirement`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                },
                body: JSON.stringify({
                    requirement: requirement,
                    value: value
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error al actualizar el requisito');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    this.style.borderColor = '#10b981';
                    this.style.accentColor = '#10b981';
                    setTimeout(() => {
                        this.style.borderColor = '';
                        this.style.accentColor = '';
                    }, 2000);
                    // Actualizar el porcentaje en la fila
                    updateRequirementsPercentage(this.closest('tr'));
                } else {
                    alert('Error: ' + (data.message || 'No se pudo actualizar'));
                    this.checked = !this.checked;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al guardar: ' + error.message);
                this.checked = !this.checked;
            });
        });
    });

    // Manejo de campos de texto para requisitos (Contable Interno)
    const requirementTextFields = document.querySelectorAll('.requirement-text');
    
    requirementTextFields.forEach(field => {
        // Evento de blur para guardar cambios
        field.addEventListener('blur', function() {
            const programId = this.dataset.programId;
            const inscriptionId = this.dataset.inscriptionId;
            const requirement = this.dataset.requirement;
            const value = this.value;
            
            if (!value || value.trim() === '') {
                return; // No guardar si está vacío
            }
            
            fetch(`/programs/${programId}/inscriptions/${inscriptionId}/update-requirement`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                },
                body: JSON.stringify({
                    requirement: requirement,
                    value: value
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error al actualizar el requisito');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    this.style.borderColor = '#10b981';
                    setTimeout(() => {
                        this.style.borderColor = '';
                    }, 2000);
                } else {
                    alert('Error: ' + (data.message || 'No se pudo actualizar'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al guardar: ' + error.message);
            });
        });
    });

    // Manejo de select para Estado Participantes (participant_status)
    const allParticipantStatusSelects = document.querySelectorAll('select[data-requirement="participant_status"]');
    allParticipantStatusSelects.forEach(select => {
        select.addEventListener('change', function() {
            const programId = this.dataset.programId;
            const inscriptionId = this.dataset.inscriptionId;
            const requirement = this.dataset.requirement;
            const value = this.value;
            
            fetch(`/programs/${programId}/inscriptions/${inscriptionId}/update-requirement`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                },
                body: JSON.stringify({
                    requirement: requirement,
                    value: value
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error al actualizar el estado');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    this.style.borderColor = '#10b981';
                    setTimeout(() => {
                        this.style.borderColor = '';
                    }, 2000);
                } else {
                    alert('Error: ' + (data.message || 'No se pudo actualizar'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al guardar: ' + error.message);
            });
        });
    });

    // Manejo de input para Observaciones/Justificación (participant_justification)
    const allParticipantJustificationInputs = document.querySelectorAll('input[data-requirement="participant_justification"]');
    allParticipantJustificationInputs.forEach(input => {
        input.addEventListener('blur', function() {
            const programId = this.dataset.programId;
            const inscriptionId = this.dataset.inscriptionId;
            const requirement = this.dataset.requirement;
            const value = this.value;
            
            fetch(`/programs/${programId}/inscriptions/${inscriptionId}/update-requirement`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                },
                body: JSON.stringify({
                    requirement: requirement,
                    value: value
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error al actualizar la observación');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    this.style.borderColor = '#10b981';
                    setTimeout(() => {
                        this.style.borderColor = '';
                    }, 2000);
                } else {
                    alert('Error: ' + (data.message || 'No se pudo actualizar'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al guardar: ' + error.message);
            });
        });
    });

    // Manejo de input para requisitos de Maestría (Predefensa, Defensa, Estado Contable)
    const requirementTextInputs = document.querySelectorAll('.requirement-text-input');
    
    requirementTextInputs.forEach(input => {
        input.addEventListener('blur', function() {
            const programId = this.dataset.programId;
            const inscriptionId = this.dataset.inscriptionId;
            const requirement = this.dataset.requirement;
            const value = this.value;
            
            fetch(`/programs/${programId}/inscriptions/${inscriptionId}/update-requirement`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                },
                body: JSON.stringify({
                    requirement: requirement,
                    value: value
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error al actualizar el requisito');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    this.style.borderColor = '#10b981';
                    setTimeout(() => {
                        this.style.borderColor = '';
                    }, 2000);
                } else {
                    alert('Error: ' + (data.message || 'No se pudo actualizar'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al guardar: ' + error.message);
            });
        });
    });


    function updateRequirementsPercentage(row) {
        const allCheckboxes = row.querySelectorAll('.requirement-checkbox');
        
        // Contar requisitos de cada grupo
        let completedInscriptionCount = 0;
        let completedGraduationCount = 0;
        let completedMonographCount = 0;
        let completedGraduationStatusCount = 0;
        
        allCheckboxes.forEach((cb, index) => {
            if (cb.checked) {
                if (index < 4) {
                    completedInscriptionCount++;
                } else if (index < 9) {
                    completedGraduationCount++;
                } else if (index < 11) {
                    completedMonographCount++;
                } else {
                    completedGraduationStatusCount++;
                }
            }
        });
        
        const inscriptionPercentage = (completedInscriptionCount / 4) * 100;
        const graduationPercentage = (completedGraduationCount / 5) * 100;
        const monographPercentage = (completedMonographCount / 2) * 100;
        const graduationStatusPercentage = (completedGraduationStatusCount / 4) * 100;
        
        // Encontrar celdas de porcentaje después de cada grupo
        const cells = Array.from(row.querySelectorAll('td'));
        
        // Encontrar índices de los checkboxes en la fila
        let checkboxIndices = [];
        cells.forEach((cell, index) => {
            const checkbox = cell.querySelector('.requirement-checkbox');
            if (checkbox) {
                checkboxIndices.push(index);
            }
        });
        
        // Actualizar porcentaje después del 4to checkbox (Requisitos de Inscripción)
        if (checkboxIndices.length >= 4) {
            let nextCell = cells[checkboxIndices[3]].nextElementSibling;
            while (nextCell && nextCell.textContent.trim() && !nextCell.textContent.includes('%')) {
                nextCell = nextCell.nextElementSibling;
            }
            if (nextCell && nextCell.textContent.includes('%')) {
                nextCell.textContent = Math.round(inscriptionPercentage) + '%';
                updatePercentageColor(nextCell, inscriptionPercentage);
            }
        }
        
        // Actualizar porcentaje después del 9no checkbox (Requisitos de Titulación)
        if (checkboxIndices.length >= 9) {
            let nextCell = cells[checkboxIndices[8]].nextElementSibling;
            while (nextCell && nextCell.textContent.trim() && !nextCell.textContent.includes('%')) {
                nextCell = nextCell.nextElementSibling;
            }
            if (nextCell && nextCell.textContent.includes('%')) {
                nextCell.textContent = Math.round(graduationPercentage) + '%';
                updatePercentageColor(nextCell, graduationPercentage);
            }
        }
        
        // Actualizar porcentaje después del 11vo checkbox (Trabajo Final)
        if (checkboxIndices.length >= 11) {
            let nextCell = cells[checkboxIndices[10]].nextElementSibling;
            while (nextCell && nextCell.textContent.trim() && !nextCell.textContent.includes('%')) {
                nextCell = nextCell.nextElementSibling;
            }
            if (nextCell && nextCell.textContent.includes('%')) {
                nextCell.textContent = Math.round(monographPercentage) + '%';
                updatePercentageColor(nextCell, monographPercentage);
            }
        }
        
        // Actualizar porcentaje después del 15to checkbox (Estado de Titulación)
        if (checkboxIndices.length >= 15) {
            let nextCell = cells[checkboxIndices[14]].nextElementSibling;
            while (nextCell && nextCell.textContent.trim() && !nextCell.textContent.includes('%')) {
                nextCell = nextCell.nextElementSibling;
            }
            if (nextCell && nextCell.textContent.includes('%')) {
                nextCell.textContent = Math.round(graduationStatusPercentage) + '%';
                updatePercentageColor(nextCell, graduationStatusPercentage);
            }
        }
    }
    
    function updatePercentageColor(cell, percentage) {
        if (percentage === 100) {
            cell.className = 'px-6 py-4 whitespace-nowrap text-sm text-center font-semibold text-green-600';
        } else if (percentage >= 50) {
            cell.className = 'px-6 py-4 whitespace-nowrap text-sm text-center font-semibold text-yellow-600';
        } else {
            cell.className = 'px-6 py-4 whitespace-nowrap text-sm text-center font-semibold text-red-600';
        }
    }

    // Los inputs de notas de módulos son editables y se guardan en la BD
    const moduleGradeInputs = document.querySelectorAll('.module-grade');
    
    moduleGradeInputs.forEach(input => {
        // Cambiar color mientras se edita
        input.addEventListener('focus', function() {
            this.style.backgroundColor = '#fffbeb';
        });

        // Guardar cuando se sale del campo
        input.addEventListener('blur', function() {
            this.style.backgroundColor = '';
            const programId = this.dataset.programId;
            const inscriptionId = this.dataset.inscriptionId;
            const moduleId = this.dataset.moduleId;
            const value = this.value;
            
            // Si el campo está vacío, no hacer nada
            if (!value || value.trim() === '') {
                return;
            }

            // Verificar que el valor es válido
            const gradeValue = parseFloat(value);
            if (isNaN(gradeValue) || gradeValue < 0 || gradeValue > 100) {
                alert('La nota debe estar entre 0 y 100');
                return;
            }

            fetch(`/programs/${programId}/inscriptions/${inscriptionId}/update-grade`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                },
                body: JSON.stringify({
                    module_id: moduleId,
                    grade: gradeValue
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Error ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    this.style.borderColor = '#10b981';
                    this.style.backgroundColor = '#f0fdf4';
                    setTimeout(() => {
                        this.style.borderColor = '';
                        this.style.backgroundColor = '';
                    }, 2000);
                } else {
                    alert('Error: ' + (data.message || 'No se pudo actualizar'));
                    this.style.borderColor = '#ef4444';
                    this.style.backgroundColor = '#fef2f2';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al guardar: ' + error.message);
                this.style.borderColor = '#ef4444';
                this.style.backgroundColor = '#fef2f2';
            });
        });
    });

    // Manejador para campo de Tipo de Trámite
    const procedureTypeFields = document.querySelectorAll('.graduation-procedure-type');
    
    procedureTypeFields.forEach(field => {
        field.addEventListener('change', function() {
            const programId = this.dataset.programId;
            const inscriptionId = this.dataset.inscriptionId;
            const value = this.value;
            
            fetch(`/programs/${programId}/inscriptions/${inscriptionId}/update-requirement`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                },
                body: JSON.stringify({
                    requirement: 'graduation_procedure_type',
                    value: value
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error al actualizar el tipo de trámite');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    this.style.borderColor = '#10b981';
                    setTimeout(() => {
                        this.style.borderColor = '';
                    }, 2000);
                } else {
                    alert('Error: ' + (data.message || 'No se pudo actualizar'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al guardar: ' + error.message);
                this.style.borderColor = '#ef4444';
            });
        });
    });

    // Handler para recalcular todas las notas finales
    const recalculateFinalGradesBtn = document.getElementById('recalculateFinalGradesBtn');
    
    if (recalculateFinalGradesBtn) {
        recalculateFinalGradesBtn.addEventListener('click', function() {
            console.log('Botón de recalcular presionado');
            
            // Obtener el número de módulos
            const moduleInputs = document.querySelectorAll('.module-grade');
            if (moduleInputs.length === 0) {
                alert('No hay módulos con notas para recalcular');
                return;
            }

            // Agrupar los inputs por inscripción
            const inscriptionMap = {};
            
            moduleInputs.forEach(input => {
                const inscriptionId = input.dataset.inscriptionId;
                if (!inscriptionMap[inscriptionId]) {
                    inscriptionMap[inscriptionId] = [];
                }
                
                const value = parseFloat(input.value);
                if (!isNaN(value) && value >= 0 && value <= 100) {
                    inscriptionMap[inscriptionId].push(value);
                }
            });

            console.log('Inscriptions map:', inscriptionMap);

            // Encontrar todas las filas de la tabla
            const allRows = document.querySelectorAll('table tbody tr');
            
            allRows.forEach(row => {
                // Encontrar todos los inputs de notas en esta fila
                const rowGradeInputs = row.querySelectorAll('.module-grade');
                
                if (rowGradeInputs.length === 0) return;

                const firstInput = rowGradeInputs[0];
                const inscriptionId = firstInput.dataset.inscriptionId;
                
                const grades = [];
                rowGradeInputs.forEach(input => {
                    const value = parseFloat(input.value);
                    if (!isNaN(value) && value >= 0 && value <= 100) {
                        grades.push(value);
                    }
                });

                console.log(`Row for inscription ${inscriptionId} has grades:`, grades);

                if (grades.length > 0) {
                    // Calcular el promedio
                    const average = grades.reduce((a, b) => a + b, 0) / grades.length;
                    const roundedAverage = Math.round(average * 100) / 100;
                    
                    // Buscar la celda de nota final
                    // Es la primera celda que no es un input después de todos los módulos
                    const cells = row.querySelectorAll('td');
                    let finalGradeCell = null;
                    
                    // Encontrar el índice de la última celda con input de grado
                    let lastGradeInputIndex = -1;
                    for (let i = 0; i < cells.length; i++) {
                        if (cells[i].querySelector('.module-grade')) {
                            lastGradeInputIndex = i;
                        }
                    }
                    
                    // La celda de nota final es la que sigue
                    if (lastGradeInputIndex >= 0 && lastGradeInputIndex < cells.length - 1) {
                        finalGradeCell = cells[lastGradeInputIndex + 1];
                    }
                    
                    if (finalGradeCell) {
                        console.log(`Updating final grade cell for inscription ${inscriptionId} with value ${roundedAverage}`);
                        finalGradeCell.textContent = roundedAverage;
                        
                        // Actualizar el color según la nota
                        finalGradeCell.className = 'px-6 py-4 whitespace-nowrap text-sm text-center font-medium';
                        if (roundedAverage >= 60) {
                            finalGradeCell.classList.add('text-green-600');
                        } else {
                            finalGradeCell.classList.add('text-red-600');
                        }
                    } else {
                        console.warn(`Could not find final grade cell for inscription ${inscriptionId}`);
                    }
                }
            });

            // Feedback visual
            this.style.backgroundColor = '#10b981';
            this.style.boxShadow = '0 0 0 3px rgba(16, 185, 129, 0.3)';
            
            setTimeout(() => {
                this.style.backgroundColor = '';
                this.style.boxShadow = '';
            }, 1500);

            alert('Notas finales recalculadas correctamente');
        });
    }
});
</script>
