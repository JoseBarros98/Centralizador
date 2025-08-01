<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-white leading-tight">
                {{ __('Iniciar Seguimiento') }}
            </h2>
            <a href="{{ route('grades.summary', [$program->id, $module->id]) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-800 focus:outline-none focus:border-gray-800 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                </svg>
                Volver al Resumen
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

                    <form method="POST" action="{{ route('grade_followups.store', [$program->id, $module->id, $grade->id]) }}">
                        @csrf
                        
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900">Observaciones Generales</h3>
                            
                            <div class="mt-4">
                                <div class="mb-4">
                                    <label for="observations" class="block text-sm font-medium text-gray-700">Observaciones</label>
                                    <textarea id="observations" name="observations" rows="4" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">{{ $followup->observations ?? '' }}</textarea>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Guardar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
