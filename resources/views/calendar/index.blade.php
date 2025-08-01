@extends('layouts.app')

@section('header-title', 'Calendario de Clases')

@push('styles')
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css' rel='stylesheet' />
<style>
    .fc-event {
        cursor: pointer;
    }
    .fc-event-title {
        font-weight: 500;
    }
    .fc-daygrid-event {
        white-space: normal !important;
        align-items: normal !important;
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
        background-color: rgba(0,0,0,0.4);
    }
    .event-modal-content {
        background-color: #fefefe;
        margin: 15% auto;
        padding: 20px;
        border: 1px solid #888;
        width: 80%;
        max-width: 500px;
        border-radius: 8px;
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
</style>
@endpush

@section('content')
<div class="bg-white rounded-lg shadow-md p-6">
    <div id='calendar'></div>
</div>

<!-- Modal para mostrar detalles del evento -->
<div id="eventModal" class="event-modal">
    <div class="event-modal-content">
        <span class="close-modal">&times;</span>
        <h2 id="eventTitle" class="text-xl font-bold mb-4"></h2>
        <div class="mb-4">
            <p><strong>Programa:</strong> <span id="eventProgram"></span></p>
            <p><strong>Módulo:</strong> <span id="eventModule"></span></p>
            <p><strong>Fecha:</strong> <span id="eventDate"></span></p>
            <p><strong>Hora:</strong> <span id="eventTime"></span></p>
        </div>
        <div id="eventLinkContainer" class="mb-4 hidden">
            <p><strong>Enlace de clase:</strong> <a id="eventLink" href="#" target="_blank" class="text-blue-600 hover:underline"></a></p>
        </div>
        <div class="flex justify-end">
            <a id="viewClassBtn" href="#" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                Ver detalles de la clase
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/locales-all.min.js'></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar el calendario
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
            },
            locale: 'es',
            events: '{{ route("calendar.events") }}',
            eventTimeFormat: {
                hour: '2-digit',
                minute: '2-digit',
                meridiem: false,
                hour12: false
            },
            eventClick: function(info) {
                // Prevenir la navegación automática
                info.jsEvent.preventDefault();
                showEventModal(info.event);
            }
        });
        calendar.render();

        // Modal de eventos
        var modal = document.getElementById("eventModal");
        var span = document.getElementsByClassName("close-modal")[0];

        // Cerrar el modal al hacer clic en la X
        span.onclick = function() {
            modal.style.display = "none";
        }

        // Cerrar el modal al hacer clic fuera de él
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }

        // Función para mostrar el modal con los detalles del evento
        function showEventModal(event) {
            document.getElementById("eventTitle").textContent = event.title;
            document.getElementById("eventProgram").textContent = event.extendedProps.program;
            document.getElementById("eventModule").textContent = event.extendedProps.module;
            
            // Formatear fecha
            var startDate = new Date(event.start);
            var endDate = new Date(event.end);
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
            var endTime = endDate.toLocaleTimeString('es-ES', { 
                hour: '2-digit', 
                minute: '2-digit' 
            });
            document.getElementById("eventTime").textContent = startTime + " - " + endTime;
            
            // Enlace de la clase
            var linkContainer = document.getElementById("eventLinkContainer");
            var link = document.getElementById("eventLink");
            if (event.extendedProps.class_link) {
                link.href = event.extendedProps.class_link;
                link.textContent = event.extendedProps.class_link;
                linkContainer.classList.remove("hidden");
            } else {
                linkContainer.classList.add("hidden");
            }
            
            // Botón para ver detalles de la clase
            document.getElementById("viewClassBtn").href = event.url;
            
            // Mostrar el modal
            modal.style.display = "block";
        }
    });
</script>
@endpush
