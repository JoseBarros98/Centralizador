<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-white leading-tight">
                {{ __('Historial de Seguimientos de Documentos') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('document_followups.show', ['program' => $program->id, 'inscription' => $inscription->id]) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:border-blue-800 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                    </svg>
                    Volver al Seguimiento
                </a>
                <a href="{{ route('programs.show', $program) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-800 focus:outline-none focus:border-gray-800 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                    </svg>
                    Volver al Programa
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Información del Participante -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Información del Participante</h3>
                            <div class="border-t border-gray-200 pt-4">
                                <dl class="grid grid-cols-1 gap-y-6">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Nombre</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $inscription->first_name }} 
                                                {{ $inscription->paternal_surname }} 
                                                {{ $inscription->maternal_surname }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Documento</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $inscription->ci }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Teléfono</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $inscription->phone }}</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Información del Programa</h3>
                            <div class="border-t border-gray-200 pt-4">
                                <dl class="grid grid-cols-1 gap-y-6">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Programa</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $program->name ?? 'Programa no especificado' }}</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Historial de Seguimientos -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium text-gray-900">Historial de Seguimientos</h3>
                        <div class="text-sm text-gray-500">
                            Total: {{ $followups->count() }} seguimiento(s)
                        </div>
                    </div>
                    
                    @if($followups->count() > 0)
                        <div class="space-y-6">
                            @foreach($followups as $followup)
                                <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                                    <div class="flex justify-between items-start mb-4">
                                        <div class="flex items-center space-x-3">
                                            <h4 class="text-lg font-medium text-gray-900">
                                                Seguimiento #{{ $loop->iteration }}
                                            </h4>
                                            @if($followup->isOpen())
                                                <span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-800 rounded-full">ABIERTO</span>
                                            @else
                                                <span class="px-2 py-1 text-xs font-semibold bg-red-100 text-red-800 rounded-full">CERRADO</span>
                                            @endif
                                        </div>
                                        <a href="{{ route('document_followups.show_followup', ['program' => $program->id, 'inscription' => $inscription->id, 'followup' => $followup->id]) }}" class="inline-flex items-center px-3 py-1 bg-blue-100 border border-transparent rounded-md font-semibold text-xs text-blue-800 uppercase tracking-widest hover:bg-blue-200 active:bg-blue-300 focus:outline-none focus:border-blue-300 focus:ring ring-blue-200 disabled:opacity-25 transition ease-in-out duration-150">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                                <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                            </svg>
                                            Ver Detalles
                                        </a>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-4">
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Fecha de Inicio</dt>
                                            <dd class="mt-1 text-sm text-gray-900">{{ $followup->created_at ? $followup->created_at->format('d/m/Y H:i') : 'Fecha no disponible' }}</dd>
                                        </div>
                                        @if($followup->isClosed())
                                            <div>
                                                <dt class="text-sm font-medium text-gray-500">Fecha de Cierre</dt>
                                                <dd class="mt-1 text-sm text-gray-900">{{ $followup->updated_at ? $followup->updated_at->format('d/m/Y H:i') : 'Fecha no disponible' }}</dd>
                                            </div>
                                        @endif
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Creado por</dt>
                                            <dd class="mt-1 text-sm text-gray-900">{{ $followup->creator ? $followup->creator->name : 'Usuario desconocido' }}</dd>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <dt class="text-sm font-medium text-gray-500">Observaciones</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ \Illuminate\Support\Str::limit($followup->observations ?? 'Sin observaciones', 200) }}</dd>
                                    </div>

                                    <!-- Resumen de Contactos -->
                                    <div class="border-t border-gray-200 pt-4">
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                            <div class="text-center">
                                                <div class="text-2xl font-bold text-blue-600">{{ $followup->contacts->count() }}</div>
                                                <div class="text-xs text-gray-500">Total Contactos</div>
                                            </div>
                                            <div class="text-center">
                                                <div class="text-2xl font-bold text-green-600">{{ $followup->contacts->where('contact_type', 'call')->count() }}</div>
                                                <div class="text-xs text-gray-500">Llamadas</div>
                                            </div>
                                            <div class="text-center">
                                                <div class="text-2xl font-bold text-purple-600">{{ $followup->contacts->where('contact_type', 'message')->count() }}</div>
                                                <div class="text-xs text-gray-500">Mensajes</div>
                                            </div>
                                        </div>
                                        
                                        @if($followup->contacts->count() > 0)
                                            <div class="mt-4">
                                                <div class="flex items-center justify-between text-sm">
                                                    <span class="text-gray-500">Respuestas obtenidas:</span>
                                                    <span class="font-medium text-gray-900">
                                                        {{ $followup->contacts->where('response_status', 'answered')->count() }} de {{ $followup->contacts->count() }}
                                                    </span>
                                                </div>
                                                <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                                                    @php
                                                        $percentage = $followup->contacts->count() > 0 ? ($followup->contacts->where('response_status', 'answered')->count() / $followup->contacts->count()) * 100 : 0;
                                                    @endphp
                                                    <div class="bg-green-600 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                <path d="M34 40h10v-4a6 6 0 00-10.712-3.714M34 40H14m20 0v-4a9.971 9.971 0 00-.712-3.714M14 40H4v-4a6 6 0 0110.713-3.714M14 40v-4c0-1.313.253-2.566.713-3.714m0 0A9.971 9.971 0 0124 24c4.265 0 7.764 2.676 9.287 6.286" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No hay seguimientos</h3>
                            <p class="mt-1 text-sm text-gray-500">Comience creando un nuevo seguimiento de documentos.</p>
                            <div class="mt-6">
                                <a href="{{ route('document_followups.create', ['program' => $program->id, 'inscription' => $inscription->id]) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                                    </svg>
                                    Crear Seguimiento
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
