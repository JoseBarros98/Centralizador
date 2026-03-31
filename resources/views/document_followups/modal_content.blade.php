<!-- Información del participante -->
<div class="bg-gray-50 p-4 rounded-lg mb-6">
    <h4 class="text-md font-medium text-gray-900 mb-4">Información del Participante</h4>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <p class="text-sm text-gray-600">Nombre completo:</p>
            <p class="font-medium">{{ $inscription->first_name }} {{ $inscription->paternal_surname }} {{ $inscription->maternal_surname }}</p>
        </div>
        <div>
            <p class="text-sm text-gray-600">CI:</p>
            <p class="font-medium">{{ $inscription->ci }}</p>
        </div>
        <div>
            <p class="text-sm text-gray-600">Programa:</p>
            <p class="font-medium">{{ $inscription->program->name }}</p>
        </div>
        <div>
            <p class="text-sm text-gray-600">Teléfono:</p>
            <p class="font-medium">{{ $inscription->phone }}</p>
        </div>
    </div>
</div>

<!-- Observaciones del seguimiento -->
<div class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
    <h4 class="text-md font-medium text-gray-900 mb-4">Observaciones del Seguimiento</h4>
    <div class="bg-gray-50 p-4 rounded-lg">
        @if($followup->observations)
            <p class="text-sm text-gray-700 whitespace-pre-line">{{ $followup->observations }}</p>
        @else
            <p class="text-sm text-gray-500 italic">No hay observaciones registradas.</p>
        @endif
    </div>
    <div class="mt-4 text-xs text-gray-500">
        <p>Creado por: {{ $followup->creator->name ?? 'N/A' }}</p>
        <p>Fecha: {{ $followup->created_at->format('d/m/Y H:i') }}</p>
    </div>
</div>

<!-- Historial de contactos -->
<div class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
    <div class="flex justify-between items-center mb-4">
        <h4 class="text-md font-medium text-gray-900">Historial de Contactos</h4>
        <div class="flex space-x-2">
            <button type="button" class="add-contact-btn inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    data-type="call" data-url="{{ route('document_followups.add_contact', ['program' => $program->id, 'inscription' => $inscription->id, 'type' => 'call']) }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                </svg>
                Añadir Llamada
            </button>
            <button type="button" class="add-contact-btn inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                    data-type="message" data-url="{{ route('document_followups.add_contact', ['program' => $program->id, 'inscription' => $inscription->id, 'type' => 'message']) }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
                Añadir Mensaje
            </button>
        </div>
    </div>

    <!-- Tabs para Llamadas y Mensajes -->
    <div class="border-b border-gray-200">
        <nav class="-mb-px flex">
            <button class="contact-tab py-2 px-4 text-sm font-medium text-center border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none active" 
                    data-tab="calls">
                Llamadas ({{ $calls->count() }})
            </button>
            <button class="contact-tab py-2 px-4 text-sm font-medium text-center border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none ml-8" 
                    data-tab="messages">
                Mensajes ({{ $messages->count() }})
            </button>
        </nav>
    </div>

    <!-- Contenido de Llamadas -->
    <div id="calls-content" class="tab-content">
        @if($calls->count() > 0)
            <div class="space-y-4 mt-4">
                @foreach($calls as $call)
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <div class="flex items-center mb-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg>
                                    <span class="text-sm font-medium text-gray-900">{{ $call->contact_date->format('d/m/Y H:i') }}</span>
                                    <span class="ml-2 px-2 py-1 text-xs font-semibold rounded-full bg-indigo-100 text-indigo-800">
                                        {{ $call->contacted ? 'Contactado' : 'No contactado' }}
                                    </span>
                                </div>
                                @if($call->notes)
                                    <p class="text-sm text-gray-700 mt-2">{{ $call->notes }}</p>
                                @endif
                            </div>
                            <button type="button" class="delete-contact-btn ml-4 text-red-600 hover:text-red-800"
                                    data-url="{{ route('document_followups.delete_contact', ['program' => $program->id, 'inscription' => $inscription->id, 'contact' => $call->id]) }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8">
                <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                </svg>
                <p class="mt-2 text-sm text-gray-500">No hay llamadas registradas</p>
            </div>
        @endif
    </div>

    <!-- Contenido de Mensajes -->
    <div id="messages-content" class="tab-content hidden">
        @if($messages->count() > 0)
            <div class="space-y-4 mt-4">
                @foreach($messages as $message)
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <div class="flex items-center mb-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                    </svg>
                                    <span class="text-sm font-medium text-gray-900">{{ $message->contact_date->format('d/m/Y H:i') }}</span>
                                    <span class="ml-2 px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        {{ $message->contacted ? 'Respondió' : 'No respondió' }}
                                    </span>
                                </div>
                                @if($message->notes)
                                    <p class="text-sm text-gray-700 mt-2">{{ $message->notes }}</p>
                                @endif
                            </div>
                            <button type="button" class="delete-contact-btn ml-4 text-red-600 hover:text-red-800"
                                    data-url="{{ route('document_followups.delete_contact', ['program' => $program->id, 'inscription' => $inscription->id, 'contact' => $message->id]) }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8">
                <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
                <p class="mt-2 text-sm text-gray-500">No hay mensajes registrados</p>
            </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Funcionalidad de tabs
    const tabButtons = document.querySelectorAll('.contact-tab');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetTab = this.getAttribute('data-tab');
            
            // Remover clase activa de todos los botones
            tabButtons.forEach(btn => {
                btn.classList.remove('border-indigo-500', 'text-indigo-600', 'active');
                btn.classList.add('border-transparent', 'text-gray-500');
            });
            
            // Agregar clase activa al botón clickeado
            this.classList.remove('border-transparent', 'text-gray-500');
            this.classList.add('border-indigo-500', 'text-indigo-600', 'active');
            
            // Ocultar todos los contenidos
            tabContents.forEach(content => {
                content.classList.add('hidden');
            });
            
            // Mostrar el contenido objetivo
            document.getElementById(targetTab + '-content').classList.remove('hidden');
        });
    });
    
    // Activar el primer tab por defecto
    const firstTab = document.querySelector('.contact-tab[data-tab="calls"]');
    if (firstTab && !document.querySelector('.contact-tab.active')) {
        firstTab.click();
    }
});
</script>
