<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            Solicitudes de Arte — Kanban
        </h2>
    </x-slot>

    {{-- Barra superior: filtros + botones --}}
    <div class="w-full sm:px-6 lg:px-8 mb-4">
        <div class="bg-white rounded-lg shadow-sm p-4">
            <form method="GET" action="{{ route('art_requests.kanban') }}"
                  class="flex flex-wrap items-end gap-3">

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Desde</label>
                    <input type="date" name="date_from" value="{{ $dateFrom->format('Y-m-d') }}"
                           class="rounded-md border-gray-300 shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Hasta</label>
                    <input type="date" name="date_to" value="{{ $dateTo->format('Y-m-d') }}"
                           class="rounded-md border-gray-300 shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Diseñador</label>
                    <select name="designer_id"
                            class="rounded-md border-gray-300 shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Todos</option>
                        @foreach($designers as $designer)
                            <option value="{{ $designer->id }}"
                                {{ request('designer_id') == $designer->id ? 'selected' : '' }}>
                                {{ $designer->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-medium hover:bg-indigo-700">
                    Filtrar
                </button>
                <a href="{{ route('art_requests.kanban') }}"
                   class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md text-sm font-medium hover:bg-gray-200">
                    Limpiar
                </a>

                {{-- Separador --}}
                <div class="flex-1"></div>

                {{-- Toggle vistas --}}
                <div class="flex rounded-lg border border-gray-200 overflow-hidden text-sm">
                    <a href="{{ route('art_requests.index', request()->only('date_from','date_to','designer_id')) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-2 bg-white text-gray-600 hover:bg-gray-50">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                        </svg>
                        Lista
                    </a>
                    <span class="inline-flex items-center gap-1.5 px-3 py-2 bg-indigo-600 text-white border-l border-gray-200 font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                        </svg>
                        Kanban
                    </span>
                    <a href="{{ route('art_requests.calendar', request()->only('designer_id')) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-2 bg-white text-gray-600 hover:bg-gray-50 border-l border-gray-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Calendario
                    </a>
                </div>

                @can('content.create')
                <a href="{{ route('art_requests.create') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-medium hover:bg-indigo-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nueva Solicitud
                </a>
                @endcan
            </form>
        </div>
    </div>

    {{-- Tablero Kanban --}}
    <div class="w-full sm:px-6 lg:px-8 pb-8">
        <div class="flex gap-4 overflow-x-auto pb-4" style="min-height: 70vh;">

            @foreach($statuses as $statusKey => $meta)
            @php $cards = $grouped[$statusKey]; @endphp
            <div class="flex-shrink-0 w-64 flex flex-col rounded-lg {{ $meta['bg'] }} border-t-4 {{ $meta['border'] }} shadow-sm">

                {{-- Cabecera de columna --}}
                <div class="flex items-center justify-between px-3 py-2.5">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full {{ $meta['dot'] }}"></span>
                        <span class="text-sm font-semibold text-gray-700">{{ $meta['label'] }}</span>
                    </div>
                    <span class="kanban-count text-xs font-bold bg-white/80 text-gray-600 rounded-full px-2 py-0.5 min-w-[22px] text-center">
                        {{ $cards->count() }}
                    </span>
                </div>

                {{-- Zona de tarjetas (droppable) --}}
                <div class="kanban-column flex-1 flex flex-col gap-2 p-2 overflow-y-auto"
                     data-status="{{ $statusKey }}"
                     style="min-height: 120px; max-height: calc(70vh - 60px);">

                    @foreach($cards as $card)
                    @php
                        $isOverdue = $card->delivery_date && $card->delivery_date->lt(now()) && $card->status !== 'COMPLETO' && $card->status !== 'CANCELADO';
                        $priorityCfg = [
                            'ALTA'  => ['dot' => 'bg-red-500',    'text' => 'text-red-700',   'label' => 'Alta'],
                            'MEDIA' => ['dot' => 'bg-amber-400',  'text' => 'text-amber-700', 'label' => 'Media'],
                            'BAJA'  => ['dot' => 'bg-green-500',  'text' => 'text-green-700', 'label' => 'Baja'],
                        ];
                        $pc = $priorityCfg[$card->priority] ?? $priorityCfg['MEDIA'];
                    @endphp

                    <div class="kanban-card bg-white rounded-lg shadow-sm border border-gray-200 p-3 cursor-grab active:cursor-grabbing
                                hover:shadow-md transition-shadow select-none {{ $isOverdue ? 'border-l-4 border-l-red-400' : '' }}"
                         data-id="{{ $card->id }}"
                         draggable="true">

                        {{-- Prioridad + Tipo de arte --}}
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full {{ $pc['dot'] }}"></span>
                                <span class="text-xs font-medium {{ $pc['text'] }}">{{ $pc['label'] }}</span>
                            </div>
                            <span class="text-xs text-gray-400 truncate max-w-[100px]">{{ $card->typeOfArt->name }}</span>
                        </div>

                        {{-- Título --}}
                        <a href="{{ route('art_requests.show', $card) }}"
                           class="block text-sm font-semibold text-gray-800 leading-snug hover:text-indigo-600 mb-2 line-clamp-2"
                           onclick="event.stopPropagation()">
                            {{ $card->title }}
                        </a>

                        {{-- Solicitante / Diseñador --}}
                        <div class="space-y-0.5 mb-2">
                            <div class="flex items-center gap-1 text-xs text-gray-500">
                                <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                <span class="truncate">{{ $card->requester->name }}</span>
                            </div>
                            <div class="flex items-center gap-1 text-xs text-gray-500">
                                <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                </svg>
                                <span class="truncate">{{ $card->designer->name ?? 'Sin asignar' }}</span>
                            </div>
                        </div>

                        {{-- Fecha de entrega --}}
                        <div class="flex items-center gap-1 text-xs {{ $isOverdue ? 'text-red-600 font-semibold' : 'text-gray-400' }}">
                            <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            {{ $card->delivery_date ? $card->delivery_date->format('d/m/Y') : '—' }}
                            @if($isOverdue)
                                <span class="ml-1">· Vencida</span>
                            @endif
                        </div>

                        {{-- Estimación de tiempo --}}
                        @if($card->estimated_hours)
                        @php
                            $kanbanEst = (float) $card->estimated_hours;
                            $kanbanAct = (float) $card->actual_hours;
                            $kanbanEff = ($kanbanEst > 0 && $kanbanAct > 0) ? round(($kanbanEst / $kanbanAct) * 100) : null;
                        @endphp
                        <div class="flex items-center justify-between mt-1.5 pt-1.5 border-t border-gray-100 text-xs text-gray-400">
                            <span class="flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ number_format($kanbanEst, 1) }}h est.
                                @if($kanbanAct) / {{ number_format($kanbanAct, 1) }}h real @endif
                            </span>
                            @if($kanbanEff !== null)
                            <span class="font-medium {{ $kanbanEff >= 90 ? 'text-green-600' : ($kanbanEff >= 70 ? 'text-yellow-600' : 'text-red-500') }}">
                                {{ $kanbanEff }}%
                            </span>
                            @endif
                        </div>
                        @endif
                    </div>
                    @endforeach

                    {{-- Placeholder visible cuando la columna está vacía --}}
                    <div class="kanban-empty text-center text-xs text-gray-400 py-6 {{ $cards->count() ? 'hidden' : '' }}">
                        Sin solicitudes
                    </div>
                </div>
            </div>
            @endforeach

        </div>
    </div>

    {{-- Toast de confirmación --}}
    <div id="kanban-toast"
         class="fixed bottom-6 right-6 z-50 flex items-center gap-3 px-4 py-3 rounded-lg shadow-lg text-sm font-medium
                transition-all duration-300 translate-y-4 opacity-0 pointer-events-none"
         role="alert">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        <span id="kanban-toast-msg"></span>
    </div>

    @push('styles')
    <style>
        .outline-indigo { outline: 2px solid #818cf8; outline-offset: 1px; }
        .kanban-dragging { transform: rotate(1deg); box-shadow: 0 10px 25px -3px rgb(0 0 0 / .15); }
    </style>
    @endpush

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>
    <script>
    (function () {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
        const toast     = document.getElementById('kanban-toast');
        const toastMsg  = document.getElementById('kanban-toast-msg');
        let toastTimer  = null;

        function showToast(message, type = 'success') {
            toastMsg.textContent = message;
            toast.className = toast.className
                .replace(/bg-\S+/, '')
                .replace(/text-\S+(?=\s|$)/, '');
            if (type === 'success') {
                toast.classList.add('bg-green-600', 'text-white');
            } else {
                toast.classList.add('bg-red-600', 'text-white');
            }
            toast.classList.remove('translate-y-4', 'opacity-0', 'pointer-events-none');
            clearTimeout(toastTimer);
            toastTimer = setTimeout(() => {
                toast.classList.add('translate-y-4', 'opacity-0', 'pointer-events-none');
            }, 3000);
        }

        function updateColumnCount(column) {
            const header  = column.closest('.flex-col');
            const counter = header.querySelector('.kanban-count');
            const empty   = column.querySelector('.kanban-empty');
            const cards   = column.querySelectorAll('.kanban-card');
            if (counter) counter.textContent = cards.length;
            if (empty)   empty.classList.toggle('hidden', cards.length > 0);
        }

        function patchStatus(cardId, newStatus, card, originColumn) {
            fetch(`/art-requests/${cardId}/update-status`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ status: newStatus }),
            })
            .then(res => {
                if (!res.ok) throw new Error('Error del servidor');
                return res.json();
            })
            .then(() => {
                showToast('Estado actualizado: ' + newStatus);
            })
            .catch(() => {
                // Revertir: mover la tarjeta de vuelta a la columna original
                originColumn.prepend(card);
                updateColumnCount(originColumn);
                showToast('No se pudo actualizar el estado', 'error');
            });
        }

        document.querySelectorAll('.kanban-column').forEach(column => {
            Sortable.create(column, {
                group: 'kanban',
                animation: 150,
                ghostClass: 'opacity-40',
                chosenClass: 'outline-indigo',
                dragClass: 'kanban-dragging',
                onEnd(evt) {
                    if (evt.from === evt.to) return; // sin cambio de columna

                    const card         = evt.item;
                    const newStatus    = evt.to.dataset.status;
                    const originColumn = evt.from;
                    const cardId       = card.dataset.id;

                    updateColumnCount(evt.from);
                    updateColumnCount(evt.to);

                    patchStatus(cardId, newStatus, card, originColumn);
                },
            });
        });
    })();
    </script>
    @endpush
</x-app-layout>
