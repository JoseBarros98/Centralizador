<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-white leading-tight">
                {{ __('Detalles del Programa') }}
            </h2>
            <div class="flex space-x-2">
                
                <a href="{{ route('programs.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-800 focus:outline-none focus:border-gray-800 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
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
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Columna 1: Información Básica -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Información General
                            </h3>
                            <div class="border-t border-gray-200 pt-4">
                                <dl class="space-y-4">
                                    <div>
                                        <dt class="text-xs font-medium text-gray-500 uppercase">Código</dt>
                                        <dd class="mt-1 text-sm font-mono text-gray-900">{{ $program->code }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs font-medium text-gray-500 uppercase">Nombre</dt>
                                        <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ $program->name }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs font-medium text-gray-500 uppercase">Versión / Grupo / Gestión</dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            V{{ $program->version }} / G{{ $program->group }} / {{ $program->year }}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs font-medium text-gray-500 uppercase">Código Contable</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $program->accounting_code ?? 'N/A' }}</dd>
                                    </div>
                                    @if($program->postgraduate_id)
                                    <div>
                                        <dt class="text-xs font-medium text-gray-500 uppercase">ID Posgrado</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $program->postgraduate_id }}</dd>
                                    </div>
                                    @endif
                                </dl>
                            </div>
                        </div>

                        <!-- Columna 2: Estado y Fechas -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Estado y Fechas
                            </h3>
                            <div class="border-t border-gray-200 pt-4">
                                <dl class="space-y-4">
                                    <div>
                                        <dt class="text-xs font-medium text-gray-500 uppercase">Estado/Fase</dt>
                                        <dd class="mt-1">
                                            <span class="px-3 py-1 inline-flex text-xs leading-tight font-semibold rounded
                                                {{ in_array($program->status, ['INSCRIPCION']) ? 'bg-blue-100 text-blue-800' : '' }}
                                                {{ $program->status == 'DESARROLLO' ? 'bg-green-100 text-green-800' : '' }}
                                                {{ $program->status == 'NO APROBADO' ? 'bg-red-100 text-red-800' : '' }}
                                                {{ $program->status == 'CONCLUIDO' ? 'bg-gray-100 text-gray-800' : '' }}
                                                {{ $program->status == 'ARMADO CARPETAS' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                                {{ $program->status == 'RECEPCION DE REQUISITOS DE TITULACION' ? 'bg-cyan-100 text-cyan-800' : '' }}
                                                {{ $program->status == 'ELABORACION DE TRABAJOS FINALES' ? 'bg-pink-100 text-pink-800' : '' }}
                                                {{ $program->status == 'PROCESO DE TITULACION' ? 'bg-indigo-100 text-indigo-800' : '' }}
                                                {{ $program->status == 'NO EJECUTADO' ? 'bg-orange-100 text-orange-800' : '' }}">
                                                {{ $program->status ?? 'Sin estado' }}
                                            </span>
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs font-medium text-gray-500 uppercase">Modalidad</dt>
                                        <dd class="mt-1">
                                            @if($program->modality)
                                                <span class="px-3 py-1 inline-flex text-xs leading-tight font-semibold rounded
                                                    {{ $program->modality == 'V' ? 'bg-purple-100 text-purple-800' : '' }}
                                                    {{ $program->modality == 'P' ? 'bg-green-100 text-green-800' : '' }}
                                                    {{ $program->modality == 'S' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                                    {{ $program->modality == 'H' ? 'bg-blue-100 text-blue-800' : '' }}">
                                                    {{ $program->modality_name }}
                                                </span>
                                            @else
                                                <span class="text-gray-400 text-sm">No especificada</span>
                                            @endif
                                        </dd>
                                    </div>
                                    @if($program->registration_date)
                                    <div>
                                        <dt class="text-xs font-medium text-gray-500 uppercase flex items-center">
                                            <svg class="w-4 h-4 mr-1 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                            </svg>
                                            Fecha de Matriculación
                                        </dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $program->registration_date->format('d/m/Y') }}</dd>
                                    </div>
                                    @endif
                                    @if($program->start_date)
                                    <div>
                                        <dt class="text-xs font-medium text-gray-500 uppercase">Fecha de Inicio</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $program->start_date->format('d/m/Y') }}</dd>
                                    </div>
                                    @endif
                                    @if($program->finalization_date)
                                    <div>
                                        <dt class="text-xs font-medium text-gray-500 uppercase">Fecha de Finalización</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $program->finalization_date->format('d/m/Y') }}</dd>
                                    </div>
                                    @endif
                                    @if($program->passing_grade)
                                    <div>
                                        <dt class="text-xs font-medium text-gray-500 uppercase">Nota Mínima de Aprobación</dt>
                                        <dd class="mt-1">
                                            <span class="text-lg font-bold text-indigo-600">{{ $program->passing_grade }}</span>
                                        </dd>
                                    </div>
                                    @endif
                                </dl>
                            </div>
                        </div>

                        <!-- Columna 3: Estadísticas en Tiempo Real -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                Estadísticas - Total Registros: {{ $program->total_records }}
                            </h3>
                            <div class="border-t border-gray-200 pt-4">
                                <dl class="space-y-4">
                                    <div class="bg-blue-50 rounded-lg p-3">
                                        <dt class="text-xs font-medium text-blue-700 uppercase">Inscritos</dt>
                                        <dd class="mt-1 text-2xl font-bold text-blue-900">{{ $program->registered_count }}</dd>
                                    </div>
                                    <div class="bg-yellow-50 rounded-lg p-3">
                                        <dt class="text-xs font-medium text-yellow-700 uppercase">Preinscritos</dt>
                                        <dd class="mt-1 text-2xl font-bold text-yellow-900">{{ $program->preregistered_count }}</dd>
                                    </div>
                                    @if($program->withdrawn_count > 0)
                                    <div class="bg-red-50 rounded-lg p-3">
                                        <dt class="text-xs font-medium text-red-700 uppercase">Retirados</dt>
                                        <dd class="mt-1 text-2xl font-bold text-red-900">{{ $program->withdrawn_count }}</dd>
                                    </div>
                                    @endif
                                    
                                    <div class="border-t border-gray-200 pt-3 mt-3">
                                        <dt class="text-xs font-medium text-gray-500 uppercase">Total de Módulos</dt>
                                        <dd class="mt-1 text-lg font-semibold text-gray-900">{{ $program->modules()->count() }}</dd>
                                    </div>
                                </dl>
                            </div>
                            
                            @role('admin')
                            <div class="mt-6 pt-4 border-t border-gray-200">
                                <dl class="space-y-2">
                                    <div>
                                        <dt class="text-xs font-medium text-gray-500">Sincronizado</dt>
                                        <dd class="mt-1 text-xs text-gray-600">{{ $program->updated_at->format('d/m/Y H:i') }}</dd>
                                    </div>
                                </dl>
                            </div>
                            @endrole
                        </div>
                    </div>
                </div>
            </div>

            <!-- Acciones rápidas Academicos, Admin-->
            @role(['admin', 'academic'])
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Acciones Rápidas</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        
                        <a href="{{ route('programs.inscriptions', $program) }}" class="bg-green-50 hover:bg-green-100 p-4 rounded-lg flex items-center transition-colors">
                            <div class="bg-green-100 p-3 rounded-full mr-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-medium text-green-900">Ver Inscritos</h4>
                                <p class="text-sm text-green-700">Gestionar documentos y seguimiento</p>
                            </div>
                        </a>
                        <a href="{{ route('grades.programSummary', $program->id) }}" class="bg-amber-50 hover:bg-amber-100 p-4 rounded-lg flex items-center transition-colors">
                            <div class="bg-amber-100 p-3 rounded-full mr-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-medium text-amber-900">Resumen de Calificaciones</h4>
                                <p class="text-sm text-amber-700">Ver calificaciones por módulo</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
            @endrole

            <!-- Acciones rápidas Marketing-->
            @role('marketing')
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4 text-center">Acciones Rápidas</h3> {{-- Añadido text-center aquí --}}
                        <div class="grid grid-cols-1 md:grid-cols-1 gap-4">
                            <a href="{{ route('programs.inscriptions', $program) }}"
                            class="bg-green-50 hover:bg-green-100 p-4 rounded-lg flex items-center justify-center transition-colors"> {{-- AÑADIDO: justify-center --}}
                                <div class="bg-green-100 p-3 rounded-full mr-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-medium text-green-900">Ver Inscritos</h4>
                                    <p class="text-sm text-green-700">Gestionar documentos y seguimiento</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            @endrole

            <!-- Módulos -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Módulos</h3>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Docente</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Monitor</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Fechas</th>
                                    
                                    
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($program->modules as $module)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex items-center space-x-2">
{{--                                                 
                                                @can('program.edit')
                                                <a href="{{ route('programs.modules.edit', ['program' => $program->id, 'module' => $module->id]) }}" ><x-action-icons action="edit" /></a>
                                                @endcan --}}
                                                
                                                @if($module->status === 'CONCLUIDO')
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
                                                    
                                                    <div class="flex items-center gap-1">
                                                        {{-- Solicitud de Módulo --}}
                                                        @if($moduloRequest)
                                                            @if($moduloRequest->status === 'Realizado')
                                                                <span class="inline-flex items-center justify-center w-8 h-8 text-purple-600" title="Pago Módulo Realizado">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                    </svg>
                                                                </span>
                                                            @else
                                                                <a href="{{ route('payment_requests.show', $moduloRequest->id) }}" 
                                                                   class="inline-flex items-center justify-center w-8 h-8 text-blue-600 hover:text-blue-700 transition-colors"
                                                                   title="Ver Solicitud de Pago Módulo">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                                    </svg>
                                                                </a>
                                                            @endif
                                                        @else
                                                            <a href="{{ route('payment_requests.create', ['module_id' => $module->id, 'type' => 'Modulo']) }}" 
                                                               class="inline-flex items-center justify-center w-8 h-8 text-green-600 hover:text-green-700 transition-colors"
                                                               title="Solicitar Pago Módulo">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                                                </svg>
                                                            </a>
                                                        @endif
                                                        
                                                        {{-- Solicitud de Tutoría --}}
                                                        @if($tutoriaRequest)
                                                            @if($tutoriaRequest->status === 'Realizado')
                                                                <span class="inline-flex items-center justify-center w-8 h-8 text-purple-600" title="Pago Tutoría Realizado">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                                                                    </svg>
                                                                </span>
                                                            @else
                                                                <a href="{{ route('payment_requests.show', $tutoriaRequest->id) }}" 
                                                                   class="inline-flex items-center justify-center w-8 h-8 text-blue-600 hover:text-blue-700 transition-colors"
                                                                   title="Ver Solicitud de Pago Tutoría">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                                    </svg>
                                                                </a>
                                                            @endif
                                                        @else
                                                            <a href="{{ route('payment_requests.create', ['module_id' => $module->id, 'type' => 'Tutoria']) }}" 
                                                               class="inline-flex items-center justify-center w-8 h-8 text-orange-600 hover:text-orange-700 transition-colors"
                                                               title="Solicitar Pago Tutoría">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                                                </svg>
                                                            </a>
                                                        @endif
                                                    </div>
                                                @endif
                                                
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 inline-flex text-xs leading-tight font-semibold rounded
                                                {{ in_array($module->status, ['INSCRIPCION']) ? 'bg-blue-100 text-blue-800' : '' }}
                                                {{ $module->status == 'EN DESARROLLO' ? 'bg-green-100 text-green-800' : '' }}
                                                {{ $module->status == 'POSTERGADO' ? 'bg-red-100 text-red-800' : '' }}
                                                {{ $module->status == 'CONCLUIDO' ? 'bg-gray-100 text-gray-800' : '' }}
                                                {{ $module->status == 'PROGRAMADO' ? 'bg-yellow-100 text-yellow-800' : '' }}">
                                                {{ Str::limit($module->status ?? 'N/A', 20) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('programs.modules.show', ['program' => $program->id, 'module' => $module->id]) }}" class="text-indigo-600 hover:text-indigo-900 hover:underline">
                                                {{ $module->name }}
                                            </a>
                                        </td>
                                        
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            @if($module->teacher)
                                                <div class="flex items-center space-x-2">
                                                    <a href="{{ route('teachers.show', $module->teacher) }}" class="text-indigo-600 hover:text-indigo-900 hover:underline">
                                                        {{ $module->teacher->full_name }}
                                                    </a>
                                                    @if($module->teacher->is_external)
                                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800" title="Sincronizado automáticamente">
                                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd" />
                                                            </svg>
                                                        </span>
                                                    @endif
                                                </div>
                                            @elseif($module->teacher_name)
                                                <div class="flex items-center space-x-2">
                                                    <span class="text-gray-700">{{ $module->teacher_name }}</span>
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800" title="Pendiente de sincronización">No asignado</span>
                                                </div>
                                            @else
                                                <span class="text-gray-400">No asignado</span>
                                            @endif
                                        </td>
                                        
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $module->monitor->name ?? 'No asignado' }}</td>

                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center"><b>Inicio:</b> {{ $module->start_date ? $module->start_date->format('d/m/Y') : 'No definida' }} <br> <b>Fin:</b> {{ $module->finalization_date ? $module->finalization_date->format('d/m/Y') : 'No definida' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No hay módulos para mostrar</td>
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
