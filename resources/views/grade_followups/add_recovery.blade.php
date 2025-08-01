<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-white leading-tight">
                {{ __('Configurar Recuperatorio') }}
            </h2>
            <a href="{{ route('grade_followups.show', [$program->id, $module->id, $grade->id]) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-800 focus:outline-none focus:border-gray-800 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                </svg>
                Volver al Seguimiento
            </a>
        </div>
    </x-slot>

    <div >
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900">Información del Estudiante</h3>
                        <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <span class="block text-sm font-medium text-gray-700">Nombre:</span>
                                <span class="block mt-1 text-sm text-gray-900">{{ $grade->name }} {{ $grade->last_name }}</span>
                            </div>
                            <div>
                                <span class="block text-sm font-medium text-gray-700">Módulo:</span>
                                <span class="block mt-1 text-sm text-gray-900">{{ $module->name }}</span>
                            </div>
                            <div>
                                <span class="block text-sm font-medium text-gray-700">Calificación:</span>
                                <span class="block mt-1 text-sm font-semibold {{ $grade->getColorClass() }} px-2 py-1 rounded-full inline-flex">{{ number_format($grade->grade, 2) }}</span>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('grade_followups.store_recovery', [$program->id, $module->id, $grade->id]) }}">
                        @csrf
                        
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900">Configuración de Recuperatorio</h3>
                            
                            <div class="mt-4">
                                <div class="flex items-center mb-4">
                                    <input id="has_recovery" name="has_recovery" type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" {{ $followup->has_recovery ? 'checked' : '' }} value="1">
                                    <label for="has_recovery" class="ml-2 block text-sm text-gray-900">
                                        Asignar recuperatorio
                                    </label>
                                </div>
                                
                                <div id="recovery_dates" class="grid grid-cols-1 md:grid-cols-2 gap-4 {{ $followup->has_recovery ? '' : 'hidden' }}">
                                    <div>
                                        <label for="recovery_start_date" class="block text-sm font-medium text-gray-700">Fecha de inicio</label>
                                        <input type="date" name="recovery_start_date" id="recovery_start_date" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" value="{{ $followup->recovery_start_date ? $followup->recovery_start_date->format('Y-m-d') : '' }}">
                                        @error('recovery_start_date')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    
                                    <div>
                                        <label for="recovery_end_date" class="block text-sm font-medium text-gray-700">Fecha de fin</label>
                                        <input type="date" name="recovery_end_date" id="recovery_end_date" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" value="{{ $followup->recovery_end_date ? $followup->recovery_end_date->format('Y-m-d') : '' }}">
                                        @error('recovery_end_date')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Guardar Recuperatorio
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const hasRecoveryCheckbox = document.getElementById('has_recovery');
            const recoveryDatesDiv = document.getElementById('recovery_dates');
            
            hasRecoveryCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    recoveryDatesDiv.classList.remove('hidden');
                } else {
                    recoveryDatesDiv.classList.add('hidden');
                }
            });
        });
    </script>
</x-app-layout>
