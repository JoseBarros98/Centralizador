<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-white leading-tight">
                {{ $artRequest->title }}
            </h2>
            <div class="flex space-x-2">
                @role(['admin', 'marketing', 'academic'])
                <a href="{{ route('art_requests.edit', $artRequest) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:border-blue-800 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Editar
                </a>
                @endrole
                <a href="{{ route('art_requests.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 active:bg-gray-500 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Volver
                </a>
            </div>
        </div>
    </x-slot>

    <div >
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Información Principal -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Información Principal</h3>
                        </div>
                        <div class="p-6 bg-white space-y-6">
                            <!-- Descripción -->
                            <div>
                                <h4 class="text-sm font-medium text-gray-700 mb-2">Descripción</h4>
                                <div class="prose prose-sm max-w-none">
                                    <p class="text-gray-900 whitespace-pre-wrap">{{ $artRequest->description }}</p>
                                </div>
                            </div>

                            <!-- Contenido -->
                            <div>
                                <h4 class="text-sm font-medium text-gray-700 mb-2">Contenido</h4>
                                <div class="prose prose-sm max-w-none">
                                    <p class="text-gray-900 whitespace-pre-wrap">{{ $artRequest->content }}</p>
                                </div>
                            </div>

                            @if($artRequest->details)
                            <!-- Detalles -->
                            <div>
                                <h4 class="text-sm font-medium text-gray-700 mb-2">Detalles Adicionales</h4>
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <p class="text-gray-900 whitespace-pre-wrap">{{ $artRequest->details }}</p>
                                </div>
                            </div>
                            @endif

                            <!-- Tipo de Arte -->
                            <div>
                                <h4 class="text-sm font-medium text-gray-700 mb-2">Tipo de Arte</h4>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                    {{ $artRequest->typeOfArt->name }}
                                </span>
                            </div>

                            <!-- Pilar de Contenido -->
                            @if($artRequest->contentPillar)
                            <div>
                                <h4 class="text-sm font-medium text-gray-700 mb-2">Pilar de Contenido</h4>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                                    {{ $artRequest->contentPillar->name }}
                                </span>
                            </div>
                            @endif

                            <!-- Fechas -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <h4 class="text-sm font-medium text-gray-700 mb-2">Fecha de Solicitud</h4>
                                    <div class="flex items-center">
                                        <svg class="h-5 w-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <span class="text-gray-900">{{ $artRequest->request_date->format('d/m/Y') }}</span>
                                    </div>
                                </div>

                                @if($artRequest->delivery_date)
                                <div>
                                    <h4 class="text-sm font-medium text-gray-700 mb-2">Fecha de Entrega</h4>
                                    <div class="flex items-center">
                                        <svg class="h-5 w-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <span class="text-gray-900">{{ $artRequest->delivery_date->format('d/m/Y') }}</span>
                                        @if($artRequest->delivery_date->isPast() && $artRequest->status !== 'COMPLETO')
                                            <span class="ml-2 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                Vencida
                                            </span>
                                        @elseif($artRequest->delivery_date->isToday())
                                            <span class="ml-2 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                Hoy
                                            </span>
                                        @elseif($artRequest->delivery_date->diffInDays() <= 3)
                                            <span class="ml-2 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                                Próxima
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                @endif
                            </div>

                            @if($artRequest->observations)
                            <!-- Observaciones -->
                            <div>
                                <h4 class="text-sm font-medium text-gray-700 mb-2">Observaciones</h4>
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <p class="text-gray-900 whitespace-pre-wrap">{{ $artRequest->observations }}</p>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Archivos -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Archivos</h3>
                            <p class="mt-1 text-sm text-gray-500">{{ $artRequest->files->count() }} archivo(s)</p>
                        </div>
                        
                        @if($artRequest->files->count() > 0)
                        <div class="p-6 bg-white">
                            <div class="space-y-3">
                                @foreach($artRequest->files as $file)
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div class="flex items-center min-w-0 flex-1">
                                        <svg class="h-5 w-5 text-gray-400 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-medium text-gray-900 truncate">{{ $file->file_name }}</p>
                                            @if($file->description)
                                                <p class="text-xs text-gray-500">{{ $file->description }}</p>
                                            @endif
                                            <p class="text-xs text-gray-500">{{ $file->file_category }} • Subido {{ $file->created_at }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2 ml-3">
                                        <a href="{{ route('art_requests.files.serve', $file) }}" target="_blank" class="text-indigo-600 hover:text-indigo-900">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>
                                        <a href="{{ route('art_requests.files.download', $file) }}" class="text-indigo-600 hover:text-indigo-900">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                        </a>
                                        <form action="{{ route('art_requests.files.destroy', $file) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" onclick="return confirm('¿Estás seguro de eliminar este archivo?')" class="text-red-600 hover:text-red-900">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @else
                        <div class="p-6 bg-white text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="mt-2 text-sm text-gray-500">No hay archivos adjuntos</p>
                        </div>
                        @endif

                        <!-- Agregar Archivo -->
                        <div class="p-6 bg-gray-50 border-t border-gray-200">
                            <form action="{{ route('art_requests.files.add', $artRequest) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                                @csrf
                                <div>
                                    <x-label for="file" :value="__('Agregar Archivo')" />
                                    <input type="file" name="file" id="file" required
                                           accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.mp4,.avi,.mov,.zip,.rar"
                                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                    <p class="mt-1 text-xs text-gray-500">Formatos permitidos: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, JPG, JPEG, PNG, GIF, MP4, AVI, MOV, ZIP, RAR (máx. 50MB)</p>
                                </div>
                                <div>
                                    <x-label for="file_description" :value="__('Descripción del Archivo')" />
                                    <x-input id="file_description" class="block mt-1 w-full" 
                                             type="text" name="file_description" />
                                </div>
                                <div class="flex justify-end">
                                    <x-button>
                                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                        </svg>
                                        Agregar Archivo
                                    </x-button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Cambiar Estado -->
                    @role(['design', 'admin'])
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Cambiar Estado</h3>
                        </div>
                        <div class="p-6 bg-white">
                            <form action="{{ route('art_requests.update', $artRequest) }}" method="POST">
                                @csrf
                                @method('PUT')
                                
                                <!-- Campos ocultos para mantener los datos actuales -->
                                <input type="hidden" name="title" value="{{ $artRequest->title }}">
                                <input type="hidden" name="description" value="{{ $artRequest->description }}">
                                <input type="hidden" name="content" value="{{ $artRequest->content }}">
                                <input type="hidden" name="details" value="{{ $artRequest->details }}">
                                <input type="hidden" name="type_of_art_id" value="{{ $artRequest->type_of_art_id }}">
                                <input type="hidden" name="content_pillar_id" value="{{ $artRequest->content_pillar_id }}">
                                <input type="hidden" name="request_date" value="{{ $artRequest->request_date->format('Y-m-d') }}">
                                <input type="hidden" name="delivery_date" value="{{ $artRequest->delivery_date->format('Y-m-d') }}">
                                <input type="hidden" name="designer_id" value="{{ $artRequest->designer_id }}">
                                <input type="hidden" name="priority" value="{{ $artRequest->priority }}">
                                <input type="hidden" name="observations" value="{{ $artRequest->observations }}">
                                
                                <div class="space-y-4">
                                    <div>
                                        <x-label for="status_quick" :value="__('Estado')" />
                                        <select name="status" id="status_quick" required
                                                class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">
                                            <option value="NO INICIADO" {{ $artRequest->status == 'NO INICIADO' ? 'selected' : '' }}>No Iniciado</option>
                                            <option value="EN CURSO" {{ $artRequest->status == 'EN CURSO' ? 'selected' : '' }}>En Curso</option>
                                            <option value="ESPERANDO APROBACIÓN" {{ $artRequest->status == 'ESPERANDO APROBACIÓN' ? 'selected' : '' }}>Esperando Aprobación</option>
                                            <option value="ESPERANDO INFORMACIÓN" {{ $artRequest->status == 'ESPERANDO INFORMACIÓN' ? 'selected' : '' }}>Esperando Información</option>
                                            <option value="EN PAUSA" {{ $artRequest->status == 'EN PAUSA' ? 'selected' : '' }}>En Pausa</option>
                                            <option value="COMPLETO" {{ $artRequest->status == 'COMPLETO' ? 'selected' : '' }}>Completo</option>
                                            <option value="CANCELADO" {{ $artRequest->status == 'CANCELADO' ? 'selected' : '' }}>Cancelado</option>
                                            <option value="RETRASADO" {{ $artRequest->status == 'RETRASADO' ? 'selected' : '' }}>Retrasado</option>
                                        </select>
                                    </div>
                                    <x-button class="w-full justify-center">
                                        Actualizar Estado
                                    </x-button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @endrole

                    <!-- Estado y Prioridad Actual -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Estado Actual</h3>
                        </div>
                        <div class="p-6 bg-white space-y-4">
                            <!-- Estado -->
                            <div>
                                <h4 class="text-sm font-medium text-gray-700 mb-2">Estado</h4>
                                @php
                                    $statusColors = [
                                        'NO INICIADO' => 'bg-gray-100 text-gray-800',
                                        'EN CURSO' => 'bg-blue-100 text-blue-800',
                                        'ESPERANDO APROBACIÓN' => 'bg-yellow-100 text-yellow-800',
                                        'ESPERANDO INFORMACIÓN' => 'bg-orange-100 text-orange-800',
                                        'EN PAUSA' => 'bg-purple-100 text-purple-800',
                                        'COMPLETO' => 'bg-green-100 text-green-800',
                                        'CANCELADO' => 'bg-red-100 text-red-800',
                                        'RETRASADO' => 'bg-red-100 text-red-800'
                                    ];
                                @endphp
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $statusColors[$artRequest->status] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ $artRequest->status }}
                                </span>
                            </div>

                            <!-- Prioridad -->
                            <div>
                                <h4 class="text-sm font-medium text-gray-700 mb-2">Prioridad</h4>
                                @php
                                    $priorityColors = [
                                        'BAJA' => 'bg-gray-100 text-gray-800',
                                        'MEDIA' => 'bg-blue-100 text-blue-800',
                                        'ALTA' => 'bg-orange-100 text-orange-800'
                                    ];
                                @endphp
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $priorityColors[$artRequest->priority] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ $artRequest->priority }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Información del Solicitante -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Información del Solicitante</h3>
                        </div>
                        <div class="p-6 bg-white space-y-4">
                            <!-- Solicitante -->
                            <div>
                                <h4 class="text-sm font-medium text-gray-700 mb-1">Solicitante</h4>
                                <div class="flex items-center">
                                    <div class="h-8 w-8 rounded-full bg-indigo-600 flex items-center justify-center text-white text-sm font-medium mr-3">
                                        {{ substr($artRequest->requester->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $artRequest->requester->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $artRequest->requester->email }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Diseñador Asignado -->
                            @if($artRequest->designer)
                            <div>
                                <h4 class="text-sm font-medium text-gray-700 mb-1">Diseñador Asignado</h4>
                                <div class="flex items-center">
                                    <div class="h-8 w-8 rounded-full bg-green-600 flex items-center justify-center text-white text-sm font-medium mr-3">
                                        {{ substr($artRequest->designer->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $artRequest->designer->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $artRequest->designer->email }}</p>
                                    </div>
                                </div>
                            </div>
                            @else
                            <div>
                                <h4 class="text-sm font-medium text-gray-700 mb-1">Diseñador Asignado</h4>
                                <p class="text-sm text-gray-500">Sin asignar</p>
                            </div>
                            @endif

                            <!-- Fechas -->
                            @role('admin')
                            <div>
                                <h4 class="text-sm font-medium text-gray-700 mb-2">Fechas</h4>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Creado:</span>
                                        <span class="text-gray-900">{{ $artRequest->created_at->format('d/m/Y H:i') }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Actualizado:</span>
                                        <span class="text-gray-900">{{ $artRequest->updated_at->format('d/m/Y H:i') }}</span>
                                    </div>
                                </div>
                            </div>
                            @endrole
                        </div>
                    </div>

                    <!-- Acciones -->
                    @role(['admin', 'marketing', 'academic'])
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white">
                            <div class="flex flex-col space-y-3">
                                <a href="{{ route('art_requests.edit', $artRequest) }}" class="w-full inline-flex justify-center items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:border-indigo-800 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    Editar Solicitud
                                </a>
                                <form action="{{ route('art_requests.destroy', $artRequest) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar esta solicitud?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-800 focus:outline-none focus:border-red-800 focus:ring ring-red-300 disabled:opacity-25 transition ease-in-out duration-150">
                                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Eliminar Solicitud
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endrole
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
