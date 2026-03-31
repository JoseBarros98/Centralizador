@extends('layouts.app')

@section('header-title', 'Calendario de Clases')

@push('styles')
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css' rel='stylesheet' />
<style>
    :root {
        --calendar-brand-900: #312e81;
        --calendar-brand-800: #3730a3;
        --calendar-brand-700: #4338ca;
        --calendar-brand-600: #4f46e5;
        --calendar-brand-200: #c7d2fe;
        --calendar-brand-100: #e0e7ff;
        --calendar-slate-700: #334155;
        --calendar-slate-600: #475569;
        --calendar-slate-200: #cbd5e1;
        --calendar-slate-100: #e2e8f0;
        --calendar-slate-50: #f8fafc;
    }
    #calendar {
        --fc-border-color: var(--calendar-slate-100);
        --fc-today-bg-color: var(--calendar-brand-100);
        --fc-page-bg-color: #ffffff;
        --fc-neutral-bg-color: var(--calendar-slate-50);
        --fc-list-event-hover-bg-color: #f1f5f9;
    }
    .fc .fc-toolbar {
        gap: 0.75rem;
        margin-bottom: 1rem;
    }
    .fc .fc-toolbar-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--calendar-brand-900);
        text-transform: capitalize;
    }
    .fc .fc-button {
        background: #ffffff;
        border: 1px solid var(--calendar-brand-200);
        color: var(--calendar-brand-800);
        border-radius: 0.5rem;
        padding: 0.35rem 0.7rem;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06);
    }
    .fc .fc-button:hover {
        background: var(--calendar-brand-100);
        border-color: var(--calendar-brand-600);
        color: var(--calendar-brand-900);
    }
    .fc .fc-button-primary:not(:disabled).fc-button-active {
        background: var(--calendar-brand-800);
        border-color: var(--calendar-brand-800);
        color: #ffffff;
    }
    .fc .fc-col-header-cell-cushion {
        color: #475569;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.72rem;
        letter-spacing: 0.06em;
        padding: 0.5rem 0;
    }
    .fc .fc-daygrid-day-number {
        color: var(--calendar-slate-700);
        font-weight: 600;
        font-size: 0.82rem;
    }
    .fc .fc-daygrid-day-frame {
        min-height: 122px;
        max-height: 122px;
        overflow: hidden;
        padding: 0.15rem 0.25rem 0.25rem;
    }
    .fc-event {
        cursor: pointer;
        border-radius: 0.45rem;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.12);
    }
    .fc-event,
    .fc-event .fc-event-title,
    .fc-event .fc-event-time,
    .fc-event .fc-list-event-title,
    .fc-event .fc-list-event-time {
        color: #1e1b4b !important;
    }
    .fc .fc-daygrid-event-dot {
        border-color: #4338ca;
    }
    .fc-event-title {
        font-weight: 600;
    }
    .fc-daygrid-event {
        white-space: nowrap !important;
        overflow: hidden;
        text-overflow: ellipsis;
        font-size: 0.72rem;
        line-height: 1.1rem;
        padding: 0.1rem 0.35rem;
        margin-top: 2px;
    }
    .fc .fc-daygrid-more-link {
        color: var(--calendar-brand-700);
        font-size: 0.7rem;
        font-weight: 700;
        margin-top: 2px;
    }
    .fc .fc-popover {
        border: 1px solid var(--calendar-brand-200);
        border-radius: 0.75rem;
        box-shadow: 0 14px 35px rgba(15, 23, 42, 0.16);
    }
    .calendar-shell {
        position: relative;
        border: 1px solid var(--calendar-slate-100);
        border-radius: 0.8rem;
        padding: 0.9rem;
        background: linear-gradient(180deg, #ffffff 0%, #eef2ff 100%);
    }
    .calendar-loader {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.85);
        z-index: 20;
        border-radius: 0.5rem;
    }
    .calendar-loader.hidden {
        display: none;
    }
    .event-modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0,0,0,0.45);
        padding: 1rem;
    }
    .event-modal-content {
        background-color: #ffffff;
        margin: 4rem auto;
        padding: 20px;
        width: 95%;
        max-width: 500px;
        border-radius: 10px;
        box-shadow: 0 18px 40px rgba(0,0,0,0.22);
    }
    .event-detail-row {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.45rem 0;
        border-bottom: 1px solid #f1f5f9;
    }
    .event-detail-label {
        font-size: 0.8rem;
        font-weight: 600;
        color: #475569;
        min-width: 150px;
    }
    .event-detail-value {
        font-size: 0.875rem;
        color: #0f172a;
        text-align: right;
        word-break: break-word;
    }
    .event-chip {
        display: inline-flex;
        align-items: center;
        padding: 0.2rem 0.6rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
        background: var(--calendar-brand-100);
        color: var(--calendar-brand-800);
    }
    .close-modal {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
    }
    .close-modal:hover,
    .close-modal:focus {
        color: black;
        text-decoration: none;
        cursor: pointer;
    }
    @media (max-width: 640px) {
        .fc .fc-daygrid-day-frame {
            min-height: 96px;
            max-height: 96px;
        }
        .fc .fc-toolbar-title {
            font-size: 0.95rem;
        }
        .event-detail-row {
            flex-direction: column;
            gap: 0.25rem;
        }
        .event-detail-value {
            text-align: left;
        }
    }
</style>
@endpush

@section('content')
<div class="bg-white rounded-lg shadow-md p-6">
    <div class="calendar-shell">
        <div id="calendarLoader" class="calendar-loader">
            <div class="flex items-center gap-2 text-indigo-700 font-medium text-sm">
                <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                </svg>
                Cargando clases...
            </div>
        </div>
        <div id='calendar'></div>
    </div>
</div>

<!-- Modal para mostrar detalles del evento -->
<div id="eventModal" class="event-modal">
    <div class="event-modal-content">
        <span class="close-modal" aria-label="Cerrar">&times;</span>
        <div class="mb-3">
            <h2 id="eventTitle" class="text-xl font-bold text-gray-900 mb-2"></h2>
            <span id="eventProgramChip" class="event-chip"></span>
        </div>

        <div class="mb-4 space-y-1">
            <div class="event-detail-row">
                <span class="event-detail-label">Programa</span>
                <span id="eventProgram" class="event-detail-value"></span>
            </div>
            <div class="event-detail-row">
                <span class="event-detail-label">Módulo</span>
                <span id="eventModule" class="event-detail-value"></span>
            </div>
            <div class="event-detail-row">
                <span class="event-detail-label">Docente</span>
                <span id="eventTeacher" class="event-detail-value"></span>
            </div>
            <div class="event-detail-row">
                <span class="event-detail-label">Encargado de Monitoreo</span>
                <span id="eventMonitor" class="event-detail-value"></span>
            </div>
            <div class="event-detail-row">
                <span class="event-detail-label">Fecha</span>
                <span id="eventDate" class="event-detail-value"></span>
            </div>
            <div class="event-detail-row">
                <span class="event-detail-label">Hora</span>
                <span id="eventTime" class="event-detail-value"></span>
            </div>
            <div class="event-detail-row">
                <span class="event-detail-label">Serie de Calendar</span>
                <span id="eventSeries" class="event-detail-value"></span>
            </div>
        </div>

        <div id="calendarEventLinkContainer" class="mb-4 hidden">
            <p class="text-sm font-semibold text-gray-700 mb-1">Evento en Google Calendar</p>
            <a id="calendarEventLink" href="#" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:underline text-sm break-all"></a>
        </div>

        <div id="eventLinkContainer" class="mb-4 hidden">
            <p class="text-sm font-semibold text-gray-700 mb-1">Enlace de clase</p>
            <div class="flex items-center gap-2">
                <a id="eventLink" href="#" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:underline text-sm break-all"></a>
                <button id="copyLinkBtn" type="button" class="inline-flex items-center px-2 py-1 text-xs rounded bg-indigo-100 text-indigo-700 hover:bg-indigo-200">
                    Copiar
                </button>
            </div>
        </div>
        {{-- <div class="flex justify-end">
            <a id="viewClassBtn" href="#" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                Ver detalles de la clase
            </a>
        </div> --}}
    </div>
</div>
@endsection

@push('scripts')
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/locales-all.min.js'></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const loader = document.getElementById('calendarLoader');

        function getToolbarConfig() {
            const isMobile = window.matchMedia('(max-width: 768px)').matches;
            return isMobile
                ? {
                    left: 'prev,next',
                    center: 'title',
                    right: 'dayGridMonth,listMonth'
                }
                : {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
                };
        }

        function colorFromText(text) {
            const palette = ['#312e81', '#3730a3', '#4338ca', '#4f46e5', '#6366f1', '#1e40af', '#334155'];
            let hash = 0;
            for (let i = 0; i < text.length; i++) {
                hash = text.charCodeAt(i) + ((hash << 5) - hash);
            }
            return palette[Math.abs(hash) % palette.length];
        }

        function softenColor(hexColor, mix = 0.78) {
            const hex = String(hexColor || '').replace('#', '');
            if (hex.length !== 6) {
                return '#e0e7ff';
            }

            const r = parseInt(hex.slice(0, 2), 16);
            const g = parseInt(hex.slice(2, 4), 16);
            const b = parseInt(hex.slice(4, 6), 16);

            const mixedR = Math.round(r + (255 - r) * mix);
            const mixedG = Math.round(g + (255 - g) * mix);
            const mixedB = Math.round(b + (255 - b) * mix);

            return `rgb(${mixedR}, ${mixedG}, ${mixedB})`;
        }

        // Inicializar el calendario
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: window.matchMedia('(max-width: 768px)').matches ? 'listMonth' : 'dayGridMonth',
            headerToolbar: getToolbarConfig(),
            locale: 'es',
            dayMaxEventRows: 3,
            dayMaxEvents: true,
            moreLinkClick: 'popover',
            fixedWeekCount: false,
            events: '{{ route("calendar.events") }}',
            loading: function(isLoading) {
                if (!loader) return;
                loader.classList.toggle('hidden', !isLoading);
            },
            eventTimeFormat: {
                hour: '2-digit',
                minute: '2-digit',
                meridiem: false,
                hour12: false
            },
            eventDidMount: function(info) {
                const baseColor = colorFromText(info.event.extendedProps.program || info.event.title || 'evento');
                info.el.style.backgroundColor = softenColor(baseColor, 0.78);
                info.el.style.borderColor = softenColor(baseColor, 0.6);
                info.el.style.color = '#1e1b4b';
            },
            eventClick: function(info) {
                // Prevenir la navegación automática
                info.jsEvent.preventDefault();
                showEventModal(info.event);
            }
        });
        calendar.render();

        let resizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                calendar.setOption('headerToolbar', getToolbarConfig());
            }, 150);
        });

        // Modal de eventos
        var modal = document.getElementById("eventModal");
        var span = document.getElementsByClassName("close-modal")[0];
        var copyLinkBtn = document.getElementById("copyLinkBtn");
        var eventLink = document.getElementById("eventLink");
        var calendarEventLink = document.getElementById("calendarEventLink");

        // Cerrar el modal al hacer clic en la X
        span.addEventListener('click', function() {
            modal.style.display = "none";
        });

        // Cerrar el modal al hacer clic fuera de él
        window.addEventListener('click', function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        });

        // Cerrar con tecla Escape
        window.addEventListener('keydown', function(event) {
            if (event.key === 'Escape' && modal.style.display === 'block') {
                modal.style.display = 'none';
            }
        });

        copyLinkBtn.addEventListener('click', async function() {
            const url = eventLink.getAttribute('href');
            if (!url || url === '#') return;
            try {
                await navigator.clipboard.writeText(url);
                copyLinkBtn.textContent = 'Copiado';
                setTimeout(() => {
                    copyLinkBtn.textContent = 'Copiar';
                }, 1200);
            } catch (e) {
                copyLinkBtn.textContent = 'Error';
                setTimeout(() => {
                    copyLinkBtn.textContent = 'Copiar';
                }, 1200);
            }
        });

        // Función para mostrar el modal con los detalles del evento
        function showEventModal(event) {
            document.getElementById("eventTitle").textContent = event.title;
            document.getElementById("eventProgram").textContent = event.extendedProps.program;
            document.getElementById("eventProgramChip").textContent = event.extendedProps.program || 'Clase';
            document.getElementById("eventModule").textContent = event.extendedProps.module;
            document.getElementById("eventTeacher").textContent = event.extendedProps.teacher || 'No asignado';
            document.getElementById("eventMonitor").textContent = event.extendedProps.monitor || 'No asignado';
            const seriesLabel = event.extendedProps.is_recurring_series
                ? 'Recurrente (' + (event.extendedProps.recurring_series_size || 0) + ' sesiones)'
                : 'Individual';
            document.getElementById("eventSeries").textContent = seriesLabel;
            
            // Formatear fecha
            var startDate = new Date(event.start);
            var endDate = event.end ? new Date(event.end) : null;
            var formattedDate = startDate.toLocaleDateString('es-ES', { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });
            document.getElementById("eventDate").textContent = formattedDate;
            
            // Formatear hora
            var startTime = startDate.toLocaleTimeString('es-ES', { 
                hour: '2-digit', 
                minute: '2-digit' 
            });
            if (event.allDay) {
                document.getElementById("eventTime").textContent = 'Todo el día';
            } else if (endDate) {
                var endTime = endDate.toLocaleTimeString('es-ES', {
                    hour: '2-digit',
                    minute: '2-digit'
                });
                document.getElementById("eventTime").textContent = startTime + " - " + endTime;
            } else {
                document.getElementById("eventTime").textContent = startTime;
            }
            
            // Enlace de la clase
            var linkContainer = document.getElementById("eventLinkContainer");
            if (event.extendedProps.class_link) {
                eventLink.href = event.extendedProps.class_link;
                eventLink.textContent = event.extendedProps.class_link;
                linkContainer.classList.remove("hidden");
            } else {
                linkContainer.classList.add("hidden");
            }

            // Enlace del evento real en Google Calendar
            var calendarLinkContainer = document.getElementById("calendarEventLinkContainer");
            if (event.extendedProps.google_calendar_event_link) {
                calendarEventLink.href = event.extendedProps.google_calendar_event_link;
                calendarEventLink.textContent = event.extendedProps.google_calendar_event_link;
                calendarLinkContainer.classList.remove("hidden");
            } else {
                calendarLinkContainer.classList.add("hidden");
            }
            
            // Botón para ver detalles de la clase
            //document.getElementById("viewClassBtn").href = event.url || '#';
            
            // Mostrar el modal
            modal.style.display = "block";
        }
    });
</script>
@endpush
