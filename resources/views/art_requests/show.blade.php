<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ $artRequest->title }}
        </h2>
    </x-slot>

    @php
        $isArtRequestOwner = auth()->id() === (int) $artRequest->created_by;
        $canEditArtRequest   = auth()->user() && (auth()->user()->can('content.edit')   || (auth()->user()->can('content.edit_own')   && $isArtRequestOwner));
        $canDeleteArtRequest = auth()->user() && (auth()->user()->can('content.delete') || (auth()->user()->can('content.delete_own') && $isArtRequestOwner));
    @endphp
    <div >
        <div class="w-full sm:px-6 lg:px-8">
            <div class="flex items-center justify-end gap-2 mb-4">
                @if($canEditArtRequest)
                <a href="{{ route('art_requests.edit', $artRequest) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:border-blue-800 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Editar
                </a>
                @endif
                <a href="{{ route('art_requests.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 active:bg-gray-500 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Volver
                </a>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Información Principal -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Información Principal</h3>
                        </div>
                        <div class="p-6 bg-white space-y-6">
                            @php
                                $statusColors = [
                                    'NO INICIADO' => 'bg-gray-100 text-gray-800',
                                    'EN CURSO' => 'bg-blue-100 text-blue-800',
                                    'ESPERANDO APROBACION' => 'bg-yellow-100 text-yellow-800',
                                    'ESPERANDO INFORMACION' => 'bg-orange-100 text-orange-800',
                                    'EN PAUSA' => 'bg-purple-100 text-purple-800',
                                    'COMPLETO' => 'bg-green-100 text-green-800',
                                    'CANCELADO' => 'bg-red-100 text-red-800',
                                    'RETRASADO' => 'bg-red-100 text-red-800'
                                ];
                                $priorityColors = [
                                    'BAJA' => 'bg-gray-100 text-gray-800',
                                    'MEDIA' => 'bg-blue-100 text-blue-800',
                                    'ALTA' => 'bg-orange-100 text-orange-800'
                                ];
                                $hasObservations = filled(trim((string) $artRequest->observations));
                            @endphp

                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                                <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Estado</p>
                                    <div class="mt-3">
                                        <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium {{ $statusColors[$artRequest->status] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ $artRequest->status }}
                                        </span>
                                    </div>
                                </div>

                                <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Prioridad</p>
                                    <div class="mt-3">
                                        <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium {{ $priorityColors[$artRequest->priority] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ $artRequest->priority }}
                                        </span>
                                    </div>
                                </div>

                                <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Diseñador Asignado</p>
                                    <p class="mt-3 text-sm font-medium text-gray-900">{{ $artRequest->designer->name ?? 'Sin asignar' }}</p>
                                </div>

                                <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Entrega</p>
                                    @if($artRequest->delivery_date)
                                        <div class="mt-3 flex flex-wrap items-center gap-2">
                                            <span class="text-sm font-medium text-gray-900">{{ $artRequest->delivery_date->format('d/m/Y') }}</span>
                                            @if($artRequest->delivery_date->isPast() && $artRequest->status !== 'COMPLETO')
                                                <span class="inline-flex items-center rounded-full bg-red-100 px-2 py-1 text-xs font-medium text-red-800">Vencida</span>
                                            @elseif($artRequest->delivery_date->isToday())
                                                <span class="inline-flex items-center rounded-full bg-yellow-100 px-2 py-1 text-xs font-medium text-yellow-800">Hoy</span>
                                            @elseif($artRequest->delivery_date->diffInDays() <= 3)
                                                <span class="inline-flex items-center rounded-full bg-orange-100 px-2 py-1 text-xs font-medium text-orange-800">Próxima</span>
                                            @endif
                                        </div>
                                    @else
                                        <p class="mt-3 text-sm text-gray-500">Sin fecha definida</p>
                                    @endif
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-6 xl:grid-cols-5">
                                <div class="xl:col-span-2 rounded-xl border border-gray-200 p-5">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Descripción</p>
                                    <p class="mt-3 text-sm leading-7 text-gray-900 whitespace-pre-wrap">{{ $artRequest->description }}</p>
                                </div>

                                <div class="xl:col-span-3 rounded-xl border border-indigo-100 bg-indigo-50/50 p-5">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600">Contenido</p>
                                    <p class="mt-3 text-sm leading-7 text-gray-900 whitespace-pre-wrap">{{ $artRequest->content }}</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div class="rounded-xl border border-gray-200 p-4">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Tipo de Arte</p>
                                    <div class="mt-3">
                                        <span class="inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-sm font-medium text-blue-800">
                                            {{ $artRequest->typeOfArt->name }}
                                        </span>
                                    </div>
                                </div>

                                <div class="rounded-xl border border-gray-200 p-4">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Pilar de Contenido</p>
                                    <div class="mt-3">
                                        @if($artRequest->contentPillar)
                                            <span class="inline-flex items-center rounded-full bg-purple-100 px-3 py-1 text-sm font-medium text-purple-800">
                                                {{ $artRequest->contentPillar->name }}
                                            </span>
                                        @else
                                            <p class="text-sm text-gray-500">Sin pilar asignado</p>
                                        @endif
                                    </div>
                                </div>

                                <div class="rounded-xl border border-gray-200 p-4">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Fecha de Solicitud</p>
                                    <div class="mt-3 flex items-center">
                                        <svg class="mr-2 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <span class="text-sm font-medium text-gray-900">{{ $artRequest->request_date->format('d/m/Y') }}</span>
                                    </div>
                                </div>

                                <div class="rounded-xl border border-gray-200 p-4">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Fecha de Entrega</p>
                                    <div class="mt-3 flex items-center">
                                        <svg class="mr-2 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <span class="text-sm font-medium text-gray-900">
                                            {{ $artRequest->delivery_date ? $artRequest->delivery_date->format('d/m/Y') : 'Sin fecha definida' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Comentarios -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg" id="comments-section">
                        <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                            <h3 class="text-base font-semibold text-gray-900">Comentarios</h3>
                            <span class="text-xs text-gray-400 bg-gray-100 px-2.5 py-1 rounded-full" id="comments-count">
                                {{ $artRequest->comments->count() }}
                            </span>
                        </div>

                        {{-- Área de chat --}}
                        <div class="h-96 overflow-y-auto bg-gray-50 p-5 space-y-3" id="comments-list">
                            @forelse($artRequest->comments as $comment)
                            @php $isOwn = auth()->id() === $comment->user_id; @endphp
                            <div class="flex {{ $isOwn ? 'justify-end' : 'justify-start' }} items-end gap-2"
                                 id="comment-{{ $comment->id }}">
                                @if(!$isOwn)
                                <div class="flex-shrink-0 h-7 w-7 rounded-full bg-indigo-400 flex items-center justify-center text-white text-xs font-semibold mb-4">
                                    {{ strtoupper(substr($comment->user->name, 0, 1)) }}
                                </div>
                                @endif
                                <div class="max-w-[75%] flex flex-col {{ $isOwn ? 'items-end' : 'items-start' }} gap-1">
                                    @if(!$isOwn)
                                    <span class="text-xs font-medium text-gray-500 ml-1">{{ $comment->user->name }}</span>
                                    @endif
                                    <div class="relative group">
                                        <div class="px-4 py-2.5 text-sm leading-relaxed
                                            {{ $isOwn
                                                ? 'bg-indigo-600 text-white rounded-2xl rounded-br-none'
                                                : 'bg-white border border-gray-200 text-gray-800 rounded-2xl rounded-bl-none shadow-sm' }}
                                            {{ $comment->is_internal ? 'ring-2 ring-amber-400' : '' }}">
                                            @if($comment->is_internal)
                                            <p class="flex items-center gap-1 text-xs font-semibold mb-1.5 {{ $isOwn ? 'text-amber-300' : 'text-amber-500' }}">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                                </svg>
                                                Interno
                                            </p>
                                            @endif
                                            <p class="whitespace-pre-line">{{ $comment->body }}</p>
                                            @if($comment->attachment_name)
                                            <a href="{{ route('art-requests.comments.attachment', [$artRequest, $comment]) }}"
                                               target="_blank"
                                               class="mt-2 pt-2 flex items-center gap-2 text-xs border-t {{ $isOwn ? 'border-indigo-500 hover:text-indigo-200' : 'border-gray-200 hover:text-indigo-600' }} transition-colors">
                                                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                                </svg>
                                                <span class="truncate max-w-[180px]">{{ $comment->attachment_name }}</span>
                                            </a>
                                            @endif
                                        </div>
                                        @if(auth()->id() === $comment->user_id || auth()->user()->can('content.edit'))
                                        <button onclick="deleteComment({{ $artRequest->id }}, {{ $comment->id }})"
                                                class="absolute -top-1.5 {{ $isOwn ? '-left-1.5' : '-right-1.5' }} opacity-0 group-hover:opacity-100 transition-opacity h-5 w-5 rounded-full bg-red-500 text-white flex items-center justify-center shadow"
                                                title="Eliminar">
                                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                        @endif
                                    </div>
                                    <span class="text-xs text-gray-400 {{ $isOwn ? 'mr-1' : 'ml-1' }}">{{ $comment->created_at->format('d/m/Y H:i') }}</span>
                                </div>
                                @if($isOwn)
                                <div class="flex-shrink-0 h-7 w-7 rounded-full bg-indigo-600 flex items-center justify-center text-white text-xs font-semibold mb-4">
                                    {{ strtoupper(substr($comment->user->name, 0, 1)) }}
                                </div>
                                @endif
                            </div>
                            @empty
                            <div class="h-full flex flex-col items-center justify-center text-center py-16" id="no-comments-msg">
                                <svg class="w-10 h-10 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                                <p class="text-sm text-gray-400">Sin comentarios aún.</p>
                                <p class="text-xs text-gray-300 mt-1">Sé el primero en comentar.</p>
                            </div>
                            @endforelse
                        </div>

                        {{-- Input --}}
                        <div class="px-4 py-3 bg-white border-t border-gray-200">
                            <form id="comment-form">
                                @csrf
                                @can('content.view')
                                <label class="flex items-center gap-1.5 text-xs text-gray-500 cursor-pointer select-none mb-2">
                                    <input type="checkbox" id="comment-internal" name="is_internal"
                                           class="rounded border-gray-300 text-amber-500 focus:ring-amber-400 h-3.5 w-3.5">
                                    <svg class="w-3.5 h-3.5 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                    </svg>
                                    Solo equipo interno
                                </label>
                                @endcan
                                {{-- Chip de archivo seleccionado --}}
                                <div id="attachment-preview" class="hidden mb-2 flex items-center gap-2 px-3 py-1.5 bg-indigo-50 border border-indigo-200 rounded-full text-xs text-indigo-700 w-fit max-w-full">
                                    <svg class="w-3.5 h-3.5 text-indigo-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                    </svg>
                                    <span id="attachment-filename" class="truncate max-w-[200px]"></span>
                                    <button type="button" id="attachment-clear" class="flex-shrink-0 text-indigo-400 hover:text-indigo-700 ml-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                                <div class="flex items-end gap-2">
                                    <textarea id="comment-body" name="body" rows="1"
                                              placeholder="Escribe un comentario..."
                                              class="flex-1 rounded-2xl border-gray-300 shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500 resize-none py-2.5 px-4"
                                              oninput="this.style.height='auto';this.style.height=Math.min(this.scrollHeight,120)+'px'"></textarea>
                                    <input type="file" id="comment-attachment" class="hidden"
                                           accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.mp4,.avi,.mov,.zip,.rar">
                                    <button type="button" id="attachment-btn"
                                            class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-100 text-gray-500 flex items-center justify-center hover:bg-gray-200 transition-colors"
                                            title="Adjuntar archivo">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                        </svg>
                                    </button>
                                    <button type="submit"
                                            class="flex-shrink-0 h-10 w-10 rounded-full bg-indigo-600 text-white flex items-center justify-center hover:bg-indigo-700 disabled:opacity-50 transition-colors">
                                        <svg class="w-4 h-4 translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                        </svg>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Cambiar Estado -->
                    @can('content.toggle_active')
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Cambiar Estado</h3>
                        </div>
                        <div class="p-6 bg-white">
                            <form action="{{ route('art_requests.updateStatus', $artRequest) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <div class="space-y-4">
                                    <div>
                                        <x-label for="status_quick" :value="__('Estado')" />
                                        <select name="status" id="status_quick" required
                                                class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">
                                            <option value="NO INICIADO" {{ $artRequest->status == 'NO INICIADO' ? 'selected' : '' }}>No Iniciado</option>
                                            <option value="EN CURSO" {{ $artRequest->status == 'EN CURSO' ? 'selected' : '' }}>En Curso</option>
                                            <option value="ESPERANDO APROBACION" {{ $artRequest->status == 'ESPERANDO APROBACION' ? 'selected' : '' }}>Esperando Aprobación</option>
                                            <option value="ESPERANDO INFORMACION" {{ $artRequest->status == 'ESPERANDO INFORMACION' ? 'selected' : '' }}>Esperando Información</option>
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
                    @endcan


                    <!-- Estimación de Tiempo -->
                    @if($artRequest->started_at || $artRequest->actual_hours)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-5 border-b border-gray-200">
                            <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Tiempo de Ejecución
                            </h3>
                        </div>
                        <div class="p-5 space-y-3">
                            @if($artRequest->started_at)
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-500">Inicio</span>
                                <span class="font-medium text-gray-800">{{ $artRequest->started_at->format('d/m/Y H:i') }}</span>
                            </div>
                            @endif
                            @if($artRequest->actual_hours)
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-500">Tiempo total</span>
                                <span class="font-semibold text-gray-800">{{ number_format($artRequest->actual_hours, 1) }} hrs</span>
                            </div>
                            @elseif($artRequest->started_at && $artRequest->status === 'EN CURSO')
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-500">Transcurrido</span>
                                <span class="font-semibold text-blue-600">{{ round($artRequest->started_at->diffInMinutes(now()) / 60, 1) }} hrs</span>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

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
                            @can('system.view_logs')
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
                            @endcan
                        </div>
                    </div>

                    <!-- Archivos -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-5 bg-white border-b border-gray-200 flex items-center justify-between">
                            <div>
                                <h3 class="text-base font-medium text-gray-900">Archivos</h3>
                                <p class="mt-0.5 text-xs text-gray-500">{{ $artRequest->files->count() }} archivo(s)</p>
                            </div>
                        </div>

                        @if($artRequest->files->count() > 0)
                        <div class="p-4">
                            <div class="space-y-2">
                                @foreach($artRequest->files as $file)
                                <div class="flex items-center justify-between p-2.5 bg-gray-50 rounded-lg">
                                    <div class="flex items-center min-w-0 flex-1">
                                        <svg class="h-4 w-4 text-gray-400 mr-2.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-xs font-medium text-gray-900 truncate">{{ $file->file_name }}</p>
                                            <p class="text-xs text-gray-400">{{ $file->file_category }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-1.5 ml-2">
                                        <a href="{{ route('art_requests.files.serve', $file) }}" target="_blank" class="text-indigo-500 hover:text-indigo-700">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>
                                        <a href="{{ route('art_requests.files.download', $file) }}" class="text-indigo-500 hover:text-indigo-700">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                        </a>
                                        @can('content.manage_files')
                                        <form action="{{ route('art_requests.files.destroy', $file) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" onclick="return confirm('¿Eliminar este archivo?')" class="text-red-400 hover:text-red-600">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                        @endcan
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @else
                        <div class="p-5 text-center">
                            <svg class="mx-auto h-8 w-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="mt-1.5 text-xs text-gray-400">Sin archivos adjuntos</p>
                        </div>
                        @endif

                        @can('content.manage_files')
                        <div class="p-4 bg-gray-50 border-t border-gray-200">
                            <form action="{{ route('art_requests.files.add', $artRequest) }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                                @csrf
                                <div>
                                    <x-label for="file_sidebar" :value="__('Agregar Archivo')" />
                                    <input type="file" name="file" id="file_sidebar"
                                           accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.mp4,.avi,.mov,.zip,.rar"
                                           class="block w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                </div>
                                <div>
                                    <x-input id="file_description_sidebar" class="block w-full text-sm" type="text" name="file_description" placeholder="Descripción (opcional)" />
                                </div>
                                <x-button class="w-full justify-center text-xs">Subir Archivo</x-button>
                            </form>
                        </div>
                        @endcan
                    </div>

                    <!-- Historial de Modificaciones -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-5 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-gray-200 flex items-center justify-between">
                            <div>
                                <h3 class="text-base font-medium text-gray-900">Historial de Modificaciones</h3>
                            </div>
                            <button onclick="openRecordModificationModal()" class="inline-flex items-center px-3 py-1.5 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Registrar
                            </button>
                        </div>
                        <x-art-request-modifications-history :artRequest="$artRequest" />
                    </div>

                    <!-- Historial de Estado -->
                    @can('system.view_logs')
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-5 border-b border-gray-200 flex items-center justify-between">
                            <div>
                                <h3 class="text-base font-medium text-gray-900">Historial de Estado</h3>
                                <p class="text-xs text-gray-500 mt-0.5">Registro de cambios de estado</p>
                            </div>
                            <span class="text-xs text-gray-400">{{ $artRequest->statusHistory->count() }} {{ $artRequest->statusHistory->count() === 1 ? 'cambio' : 'cambios' }}</span>
                        </div>

                        @if($artRequest->statusHistory->count() > 0)
                        @php
                            $statusBadgeColors = [
                                'NO INICIADO'           => 'bg-gray-100 text-gray-600',
                                'EN CURSO'              => 'bg-blue-100 text-blue-700',
                                'ESPERANDO APROBACION'  => 'bg-yellow-100 text-yellow-700',
                                'ESPERANDO INFORMACION' => 'bg-orange-100 text-orange-700',
                                'EN PAUSA'              => 'bg-purple-100 text-purple-700',
                                'RETRASADO'             => 'bg-red-100 text-red-700',
                                'COMPLETO'              => 'bg-green-100 text-green-700',
                                'CANCELADO'             => 'bg-slate-100 text-slate-600',
                            ];
                        @endphp
                        <div class="p-5">
                            <ol class="relative border-l border-gray-200 ml-3 space-y-4">
                                @foreach($artRequest->statusHistory as $entry)
                                <li class="ml-6">
                                    <span class="absolute -left-2.5 flex h-5 w-5 items-center justify-center rounded-full bg-white border-2 border-blue-400 ring-4 ring-white">
                                        <svg class="h-2.5 w-2.5 text-blue-500" fill="currentColor" viewBox="0 0 8 8">
                                            <circle cx="4" cy="4" r="3"/>
                                        </svg>
                                    </span>
                                    <div class="flex flex-wrap items-center gap-1">
                                        @if($entry->from_status)
                                            <span class="text-xs px-2.5 py-0.5 rounded-full {{ $statusBadgeColors[$entry->from_status] ?? 'bg-gray-100 text-gray-600' }}">
                                                {{ $entry->from_status }}
                                            </span>
                                            <svg class="w-3 h-3 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                        @else
                                            <span class="text-xs text-gray-400 italic">Creado con</span>
                                        @endif
                                        <span class="text-xs px-2.5 py-0.5 rounded-full font-medium {{ $statusBadgeColors[$entry->to_status] ?? 'bg-gray-100 text-gray-600' }}">
                                            {{ $entry->to_status }}
                                        </span>
                                    </div>
                                    <p class="mt-0.5 text-xs text-gray-400">
                                        {{ $entry->user->name }} &middot; {{ $entry->created_at->format('d/m/Y H:i') }}
                                    </p>
                                </li>
                                @endforeach
                            </ol>
                        </div>
                        @else
                        <div class="p-6 text-center text-gray-400 text-xs">
                            Sin cambios de estado registrados.
                        </div>
                        @endif
                    </div>
                    @endcan

                    <!-- Acciones -->
                    @if($canEditArtRequest || $canDeleteArtRequest)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white">
                            <div class="flex flex-col space-y-3">
                                @if($canEditArtRequest)
                                <a href="{{ route('art_requests.edit', $artRequest) }}" class="w-full inline-flex justify-center items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:border-indigo-800 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    Editar Solicitud
                                </a>
                                @endif
                                @if($canDeleteArtRequest)
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
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

        <!-- Modal para registrar modificación -->
        <x-art-request-record-modification-modal :artRequest="$artRequest" />

        </div>
    </div>

@push('scripts')
<script>
(function () {
    const artRequestId = {{ $artRequest->id }};
    const storeUrl     = `/art-requests/${artRequestId}/comments`;
    const csrf         = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const form         = document.getElementById('comment-form');
    const list         = document.getElementById('comments-list');
    const countEl      = document.getElementById('comments-count');
    const noMsg        = document.getElementById('no-comments-msg');

    // Scroll al último mensaje al cargar
    if (list) list.scrollTop = list.scrollHeight;

    // Paperclip
    const attachmentInput   = document.getElementById('comment-attachment');
    const attachmentBtn     = document.getElementById('attachment-btn');
    const attachmentPreview = document.getElementById('attachment-preview');
    const attachmentFilename= document.getElementById('attachment-filename');
    const attachmentClear   = document.getElementById('attachment-clear');

    if (attachmentBtn) attachmentBtn.addEventListener('click', () => attachmentInput.click());

    if (attachmentInput) {
        attachmentInput.addEventListener('change', function () {
            if (this.files[0]) {
                attachmentFilename.textContent = this.files[0].name;
                attachmentPreview.classList.remove('hidden');
            }
        });
    }

    if (attachmentClear) {
        attachmentClear.addEventListener('click', function () {
            attachmentInput.value = '';
            attachmentPreview.classList.add('hidden');
            attachmentFilename.textContent = '';
        });
    }

    function updateCount() {
        const n = list.querySelectorAll('[id^="comment-"]').length;
        if (countEl) countEl.textContent = n;
        if (noMsg) noMsg.classList.toggle('hidden', n > 0);
    }

    function buildCommentHTML(c) {
        const internalBadge = c.is_internal
            ? `<p class="flex items-center gap-1 text-xs font-semibold mb-1.5 text-amber-300">
                   <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                       <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                   </svg>
                   Interno
               </p>`
            : '';
        const attachmentHtml = c.attachment_url
            ? `<a href="${c.attachment_url}" target="_blank"
                  class="mt-2 pt-2 flex items-center gap-2 text-xs border-t border-indigo-500 hover:text-indigo-200 transition-colors">
                   <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                       <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                   </svg>
                   <span class="truncate max-w-[180px]">${c.attachment_name}</span>
               </a>`
            : '';
        const ringClass = c.is_internal ? ' ring-2 ring-amber-400' : '';
        const deleteBtn = `
            <button onclick="deleteComment(${artRequestId}, ${c.id})"
                    class="absolute -top-1.5 -left-1.5 opacity-0 group-hover:opacity-100 transition-opacity h-5 w-5 rounded-full bg-red-500 text-white flex items-center justify-center shadow"
                    title="Eliminar">
                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>`;

        return `
        <div class="flex justify-end items-end gap-2" id="comment-${c.id}">
            <div class="max-w-[75%] flex flex-col items-end gap-1">
                <div class="relative group">
                    <div class="px-4 py-2.5 text-sm leading-relaxed bg-indigo-600 text-white rounded-2xl rounded-br-none${ringClass}">
                        ${internalBadge}
                        <p class="whitespace-pre-line">${c.body}</p>
                        ${attachmentHtml}
                    </div>
                    ${deleteBtn}
                </div>
                <span class="text-xs text-gray-400 mr-1">${c.created_at}</span>
            </div>
            <div class="flex-shrink-0 h-7 w-7 rounded-full bg-indigo-600 flex items-center justify-center text-white text-xs font-semibold mb-4">
                ${c.avatar}
            </div>
        </div>`;
    }

    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const bodyEl     = document.getElementById('comment-body');
            const body       = bodyEl.value.trim();
            const isInternal = document.getElementById('comment-internal')?.checked ?? false;
            const attachFile = attachmentInput?.files[0] ?? null;
            const btn        = form.querySelector('button[type="submit"]');

            if (!body) return;
            btn.disabled = true;

            const fd = new FormData();
            fd.append('body', body);
            fd.append('is_internal', isInternal ? '1' : '0');
            if (attachFile) fd.append('attachment', attachFile);

            fetch(storeUrl, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body: fd,
            })
            .then(r => r.ok ? r.json() : Promise.reject(r))
            .then(comment => {
                const div = document.createElement('div');
                div.innerHTML = buildCommentHTML(comment).trim();
                list.appendChild(div.firstChild);
                bodyEl.value = '';
                bodyEl.style.height = 'auto';
                if (attachmentInput) {
                    attachmentInput.value = '';
                    attachmentPreview.classList.add('hidden');
                    attachmentFilename.textContent = '';
                }
                const internalCb = document.getElementById('comment-internal');
                if (internalCb) internalCb.checked = false;
                list.scrollTop = list.scrollHeight;
                updateCount();
            })
            .catch(() => alert('No se pudo agregar el comentario.'))
            .finally(() => { btn.disabled = false; });
        });
    }

    window.deleteComment = function (requestId, commentId) {
        if (!confirm('¿Eliminar este comentario?')) return;

        fetch(`/art-requests/${requestId}/comments/${commentId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
            },
        })
        .then(r => r.ok ? r.json() : Promise.reject(r))
        .then(() => {
            const el = document.getElementById(`comment-${commentId}`);
            if (el) el.remove();
            updateCount();
        })
        .catch(() => alert('No se pudo eliminar el comentario.'));
    };
})();
</script>
@endpush
</x-app-layout>
