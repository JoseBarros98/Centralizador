<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-white leading-tight">
                {{ __('Añadir ' . ($type === 'call' ? 'Llamada' : 'Mensaje')) }}
            </h2>
            <a href="{{ route('document_followups.show', ['program' => $program->id, 'inscription' => $inscription->id]) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-800 focus:outline-none focus:border-gray-800 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                </svg>
                Volver
            </a>
        </div>
    </x-slot>

    <div >
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Información del Participante</h3>
                    <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
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

                    <form method="POST" action="{{ route('document_followups.store_contact', ['program' => $program->id, 'inscription' => $inscription->id]) }}">
                        @csrf
                        <input type="hidden" name="type" value="{{ $type }}">
                        
                        <div class="mb-4">
                            <label for="contact_date" class="block text-sm font-medium text-gray-700">Fecha de Contacto</label>
                            <input type="date" id="contact_date" name="contact_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" value="{{ old('contact_date', date('Y-m-d')) }}" required>
                            @error('contact_date')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="response_status" class="block text-sm font-medium text-gray-700">Estado de Respuesta</label>
                            <select id="response_status" name="response_status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                                <option value="answered" {{ old('response_status') === 'answered' ? 'selected' : '' }}>Contestado</option>
                                <option value="not_answered" {{ old('response_status') === 'not_answered' ? 'selected' : '' }}>No Contestado</option>
                            </select>
                            @error('response_status')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4 response-date-container" style="{{ old('response_status') === 'not_answered' ? 'display: none;' : '' }}">
                            <label for="response_date" class="block text-sm font-medium text-gray-700">Fecha de Respuesta</label>
                            <input type="date" id="response_date" name="response_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" value="{{ old('response_date', date('Y-m-d')) }}">
                            @error('response_date')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="notes" class="block text-sm font-medium text-gray-700">Notas</label>
                            <textarea id="notes" name="notes" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>{{ old('notes') }}</textarea>
                            @error('notes')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:border-indigo-800 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Guardar {{ $type === 'call' ? 'Llamada' : 'Mensaje' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const responseStatusSelect = document.getElementById('response_status');
            const responseDateContainer = document.querySelector('.response-date-container');
            const responseDate = document.getElementById('response_date');

            responseStatusSelect.addEventListener('change', function() {
                if (this.value === 'answered') {
                    responseDateContainer.style.display = 'block';
                    responseDate.setAttribute('required', 'required');
                } else {
                    responseDateContainer.style.display = 'none';
                    responseDate.removeAttribute('required');
                }
            });
        });
    </script>
</x-app-layout>
