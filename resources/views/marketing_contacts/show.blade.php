<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">{{ $marketingContact->nombre }}</h2>
    </x-slot>

    @php
        $isOwner = auth()->id() === (int) $marketingContact->created_by;
        $canEdit   = auth()->user() && (auth()->user()->can('marketing_contact.edit')   || (auth()->user()->can('marketing_contact.edit_own')   && $isOwner));
        $canDelete = auth()->user() && (auth()->user()->can('marketing_contact.delete') || (auth()->user()->can('marketing_contact.delete_own') && $isOwner));
        $canAddInteractions = auth()->user() && auth()->user()->can('marketing_contact.create');
    @endphp

    <div class="w-full sm:px-6 lg:px-8 space-y-4">

        <!-- Acciones -->
        <div class="flex justify-end gap-2">
            @if($canEdit)
            <a href="{{ route('marketing-contacts.edit', $marketingContact) }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 text-white text-sm font-semibold rounded-md hover:bg-yellow-700">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Editar
            </a>
            @endif
            @if($canDelete)
            <form method="POST" action="{{ route('marketing-contacts.destroy', $marketingContact) }}" onsubmit="return confirm('¿Eliminar este contacto?')">
                @csrf @method('DELETE')
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-semibold rounded-md hover:bg-red-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Eliminar
                </button>
            </form>
            @endif
            <a href="{{ route('marketing-contacts.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-700 text-sm font-semibold rounded-md hover:bg-gray-400">Volver</a>
        </div>

        <!-- Datos del contacto -->
        <div class="bg-white shadow-sm sm:rounded-lg p-6">
            <h3 class="text-base font-semibold text-gray-800 mb-4 pb-2 border-b">Información del Contacto</h3>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
                <div>
                    <p class="text-gray-500 font-medium">Nombre</p>
                    <p class="text-gray-900">{{ $marketingContact->nombre }}</p>
                </div>
                <div>
                    <p class="text-gray-500 font-medium">Profesión</p>
                    <p class="text-gray-900">{{ $marketingContact->profesion ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-gray-500 font-medium">Teléfono</p>
                    <p class="text-gray-900">{{ $marketingContact->telefono }}</p>
                </div>
                <div>
                    <p class="text-gray-500 font-medium">Programa</p>
                    <p class="text-gray-900">{{ $marketingContact->program?->name ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-gray-500 font-medium">Fecha</p>
                    <p class="text-gray-900">{{ $marketingContact->fecha->format('d/m/Y') }}</p>
                </div>
                <div>
                    <p class="text-gray-500 font-medium">Asesor</p>
                    <p class="text-gray-900">{{ $marketingContact->creator?->name ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-gray-500 font-medium">Referencia</p>
                    <p class="text-gray-900">{{ $marketingContact->referencia }}</p>
                </div>
                <div>
                    <p class="text-gray-500 font-medium">Origen</p>
                    <p class="text-gray-900">{{ $marketingContact->origen }}</p>
                </div>
                <div>
                    <p class="text-gray-500 font-medium">Estado de Pago</p>
                    <p class="text-gray-900">{{ $marketingContact->estado_pago ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-gray-500 font-medium">Conversión</p>
                    @if($marketingContact->conversion)
                        <span class="inline-flex items-center gap-1 text-green-700 font-medium">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Inscrito convertido
                        </span>
                    @else
                        <p class="text-gray-500">No convertido</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

            <!-- Respuestas -->
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-base font-semibold text-gray-800 mb-4 pb-2 border-b flex items-center justify-between">
                    Respuestas
                    <span class="text-xs bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full">{{ $marketingContact->responses->count() }}</span>
                </h3>

                <!-- Add response form -->
                @if($canAddInteractions)
                <div x-data="{ open: false }" class="mb-4">
                    <button @click="open = !open" type="button" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Agregar respuesta
                    </button>
                    <div x-show="open" x-transition class="mt-3 p-3 bg-gray-50 rounded-md">
                        <form method="POST" action="{{ route('marketing-contacts.responses.store', $marketingContact) }}">
                            @csrf
                            <div class="grid grid-cols-2 gap-3 mb-3">
                                <div class="col-span-2">
                                    <label class="text-xs font-medium text-gray-600">Respuesta *</label>
                                    <textarea name="respuesta" rows="2" required
                                        class="w-full mt-1 border-gray-300 rounded-md text-sm focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                                </div>
                                <div>
                                    <label class="text-xs font-medium text-gray-600">Fecha</label>
                                    <input type="date" name="fecha" value="{{ date('Y-m-d') }}"
                                        class="w-full mt-1 border-gray-300 rounded-md text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                            </div>
                            <div class="flex justify-end gap-2">
                                <button type="button" @click="open = false" class="px-3 py-1.5 text-xs bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">Cancelar</button>
                                <button type="submit" class="px-3 py-1.5 text-xs bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Guardar</button>
                            </div>
                        </form>
                    </div>
                </div>
                @endif

                <!-- List -->
                <div class="space-y-3 max-h-72 overflow-y-auto">
                    @forelse($marketingContact->responses as $response)
                    <div class="p-3 bg-gray-50 rounded-md text-sm">
                        <div class="flex justify-between items-start gap-2">
                            <p class="text-gray-800 flex-1">{{ $response->respuesta }}</p>
                            @if($canEdit)
                            <form method="POST" action="{{ route('marketing-contacts.responses.destroy', [$marketingContact, $response]) }}" onsubmit="return confirm('¿Eliminar esta respuesta?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-400 hover:text-red-600 shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </form>
                            @endif
                        </div>
                        <div class="mt-1 flex justify-between text-xs text-gray-400">
                            <span>{{ $response->creator?->name ?? '—' }}</span>
                            <span>{{ $response->fecha ? $response->fecha->format('d/m/Y') : $response->created_at->format('d/m/Y') }}</span>
                        </div>
                    </div>
                    @empty
                    <p class="text-sm text-gray-400 text-center py-4">Sin respuestas registradas.</p>
                    @endforelse
                </div>
            </div>

            <!-- Llamadas -->
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-base font-semibold text-gray-800 mb-4 pb-2 border-b flex items-center justify-between">
                    Respuestas a Llamadas
                    <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">{{ $marketingContact->calls->count() }}</span>
                </h3>

                <!-- Add call form -->
                @if($canAddInteractions)
                <div x-data="{ open: false }" class="mb-4">
                    <button @click="open = !open" type="button" class="text-sm text-green-600 hover:text-green-800 font-medium flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        Registrar llamada
                    </button>
                    <div x-show="open" x-transition class="mt-3 p-3 bg-gray-50 rounded-md">
                        <form method="POST" action="{{ route('marketing-contacts.calls.store', $marketingContact) }}">
                            @csrf
                            <div class="grid grid-cols-2 gap-3 mb-3">
                                <div class="col-span-2">
                                    <label class="text-xs font-medium text-gray-600">Respuesta de la llamada *</label>
                                    <textarea name="respuesta" rows="2" required
                                        class="w-full mt-1 border-gray-300 rounded-md text-sm focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                                </div>
                                <div class="col-span-2">
                                    <label class="text-xs font-medium text-gray-600">Fecha y hora de la llamada *</label>
                                    <input type="datetime-local" name="fecha_llamada" value="{{ date('Y-m-d\TH:i') }}" required
                                        class="w-full mt-1 border-gray-300 rounded-md text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                            </div>
                            <div class="flex justify-end gap-2">
                                <button type="button" @click="open = false" class="px-3 py-1.5 text-xs bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">Cancelar</button>
                                <button type="submit" class="px-3 py-1.5 text-xs bg-green-600 text-white rounded-md hover:bg-green-700">Guardar</button>
                            </div>
                        </form>
                    </div>
                </div>
                @endif

                <!-- List -->
                <div class="space-y-3 max-h-72 overflow-y-auto">
                    @forelse($marketingContact->calls as $call)
                    <div class="p-3 bg-gray-50 rounded-md text-sm">
                        <div class="flex justify-between items-start gap-2">
                            <p class="text-gray-800 flex-1">{{ $call->respuesta }}</p>
                            @if($canEdit)
                            <form method="POST" action="{{ route('marketing-contacts.calls.destroy', [$marketingContact, $call]) }}" onsubmit="return confirm('¿Eliminar esta llamada?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-400 hover:text-red-600 shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </form>
                            @endif
                        </div>
                        <div class="mt-1 flex justify-between text-xs text-gray-400">
                            <span>{{ $call->creator?->name ?? '—' }}</span>
                            <span>{{ $call->fecha_llamada->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                    @empty
                    <p class="text-sm text-gray-400 text-center py-4">Sin llamadas registradas.</p>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
