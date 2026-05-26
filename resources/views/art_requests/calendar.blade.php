<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            Solicitudes de Arte — Calendario
        </h2>
    </x-slot>

    {{-- Barra superior: filtros + botones --}}
    <div class="w-full sm:px-6 lg:px-8 mb-4">
        <div class="bg-white rounded-lg shadow-sm p-4">
            <form method="GET" action="{{ route('art_requests.calendar') }}"
                  class="flex flex-wrap items-end gap-3">

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Año</label>
                    <select name="year"
                            class="rounded-md border-gray-300 shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        @for($y = now()->year - 2; $y <= now()->year + 1; $y++)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
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

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Estado</label>
                    <select name="status"
                            class="rounded-md border-gray-300 shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Todos</option>
                        @foreach($statuses as $s)
                            <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-medium hover:bg-indigo-700">
                    Filtrar
                </button>
                <a href="{{ route('art_requests.calendar') }}"
                   class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md text-sm font-medium hover:bg-gray-200">
                    Limpiar
                </a>

                <div class="flex-1"></div>

                {{-- Toggle vistas --}}
                <div class="flex rounded-lg border border-gray-200 overflow-hidden text-sm">
                    <a href="{{ route('art_requests.index', request()->only('designer_id','status')) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-2 bg-white text-gray-600 hover:bg-gray-50">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                        </svg>
                        Lista
                    </a>
                    <a href="{{ route('art_requests.kanban', request()->only('designer_id')) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-2 bg-white text-gray-600 hover:bg-gray-50 border-l border-gray-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                        </svg>
                        Kanban
                    </a>
                    <span class="inline-flex items-center gap-1.5 px-3 py-2 bg-indigo-600 text-white border-l border-gray-200 font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Calendario
                    </span>
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

    {{-- Calendario --}}
    <div class="w-full sm:px-6 lg:px-8 pb-8">
        <div class="bg-white rounded-lg shadow-sm p-4 md:p-6">
            <div id="calendar-container"></div>
        </div>

        {{-- Leyenda de estados --}}
        <div class="mt-4 bg-white rounded-lg shadow-sm p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-3">Leyenda de estados</p>
            <div class="flex flex-wrap gap-3">
                @foreach($colorMap as $status => $color)
                <span class="inline-flex items-center gap-1.5 text-xs text-gray-700">
                    <span class="w-3 h-3 rounded-full flex-shrink-0" style="background-color: {{ $color }}"></span>
                    {{ $status }}
                </span>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Tooltip flotante --}}
    <div id="cal-tooltip"
         class="fixed z-50 hidden bg-gray-900 text-white text-xs rounded-lg shadow-xl p-3 max-w-xs pointer-events-none"
         style="min-width: 200px;">
        <p id="cal-tt-title" class="font-semibold mb-1"></p>
        <p id="cal-tt-status" class="mb-0.5"></p>
        <p id="cal-tt-priority" class="mb-0.5"></p>
        <p id="cal-tt-designer" class="text-gray-300"></p>
        <p id="cal-tt-requester" class="text-gray-300"></p>
    </div>

    @push('styles')
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css" rel="stylesheet">
    <style>
        .fc .fc-toolbar-title { font-size: 1rem; font-weight: 600; }
        .fc .fc-button { font-size: 0.75rem; padding: 0.3rem 0.75rem; }
        .fc .fc-button-primary { background-color: #4f46e5; border-color: #4f46e5; }
        .fc .fc-button-primary:hover { background-color: #4338ca; border-color: #4338ca; }
        .fc .fc-button-primary:not(:disabled).fc-button-active { background-color: #3730a3; border-color: #3730a3; }
        .fc .fc-daygrid-event { font-size: 0.7rem; padding: 1px 4px; border-radius: 3px; }
        .fc .fc-event-title { font-weight: 500; }
        .fc .fc-day-today { background-color: #eef2ff !important; }
    </style>
    @endpush

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const eventsData = @json($events);
        const tooltip    = document.getElementById('cal-tooltip');

        const calendar = new FullCalendar.Calendar(document.getElementById('calendar-container'), {
            locale: 'es',
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,dayGridWeek,listMonth'
            },
            height: 'auto',
            events: eventsData,

            eventClick: function (info) {
                info.jsEvent.preventDefault();
                window.location.href = info.event.extendedProps.url;
            },

            eventMouseEnter: function (info) {
                const p = info.event.extendedProps;
                document.getElementById('cal-tt-title').textContent    = info.event.title;
                document.getElementById('cal-tt-status').textContent   = 'Estado: ' + p.status;
                document.getElementById('cal-tt-priority').textContent = 'Prioridad: ' + p.priority;
                document.getElementById('cal-tt-designer').textContent = 'Diseñador: ' + p.designer;
                document.getElementById('cal-tt-requester').textContent= 'Solicitante: ' + p.requester;
                tooltip.classList.remove('hidden');
            },

            eventMouseLeave: function () {
                tooltip.classList.add('hidden');
            },

            eventDidMount: function (info) {
                info.el.style.cursor = 'pointer';
            },
        });

        calendar.render();

        document.addEventListener('mousemove', function (e) {
            if (!tooltip.classList.contains('hidden')) {
                const offset = 14;
                let left = e.clientX + offset;
                let top  = e.clientY + offset;
                if (left + 220 > window.innerWidth) left = e.clientX - 220 - offset;
                if (top  + 140 > window.innerHeight) top = e.clientY - 140 - offset;
                tooltip.style.left = left + 'px';
                tooltip.style.top  = top  + 'px';
            }
        });
    });
    </script>
    @endpush
</x-app-layout>
