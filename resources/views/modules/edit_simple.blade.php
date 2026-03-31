<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Editar Módulo') }} - {{ $program->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Alerta informativa -->
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            <strong>Nota:</strong> Los datos del módulo (nombre, fechas, estado) se sincronizan automáticamente desde la base de datos externa.
                            Solo puedes editar los campos de gestión local: asignación de docente/monitor, recuperación y calificación.
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <!-- Información sincronizada (solo lectura) -->
                    <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                        <h3 class="text-lg font-semibold mb-4 text-gray-700">📋 Información Sincronizada (Solo Lectura)</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Nombre del Módulo</label>
                                <p class="mt-1 text-sm text-gray-900 font-semibold">{{ $module->name }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Fecha de Inicio</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $module->start_date ? $module->start_date->format('d/m/Y') : 'No definida' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Fecha de Finalización</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $module->finalization_date ? $module->finalization_date->format('d/m/Y') : 'No definida' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Estado</label>
                                <p class="mt-1">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        {{ $module->status == 'Finalizado' ? 'bg-green-100 text-green-800' : 
                                           ($module->status == 'Desarrollo' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') }}">
                                        {{ $module->status ?? 'Pendiente' }}
                                    </span>
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Docente</label>
                                @if($module->teacher)
                                    <div class="mt-1 flex items-center space-x-2">
                                        <p class="text-sm text-gray-900 font-medium">{{ $module->teacher->full_name }}</p>
                                        @if($module->teacher->is_external)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd" />
                                                </svg>
                                                Sincronizado
                                            </span>
                                        @endif
                                    </div>
                                @elseif($module->teacher_name)
                                    <div class="mt-1 flex items-center space-x-2">
                                        <p class="text-sm text-gray-900">{{ $module->teacher_name }}</p>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                            Sin vincular
                                        </span>
                                    </div>
                                @else
                                    <p class="mt-1 text-sm text-gray-400">No asignado</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Formulario de campos editables -->
                    <form method="POST" action="{{ route('programs.modules.update', [$program->id, $module->id]) }}">
                        @csrf
                        @method('PUT')

                        <h3 class="text-lg font-semibold mb-4 text-gray-700">✏️ Campos Editables Localmente</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            <!-- Monitor -->
                            <div>
                                <x-label for="monitor_id" :value="__('Encargado de Monitoreo')" />
                                <select id="monitor_id" name="monitor_id" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">
                                    <option value="">Seleccionar monitor</option>
                                    @foreach($monitors as $id => $name)
                                        <option value="{{ $id }}" {{ old('monitor_id', $module->monitor_id) == $id ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                                @error('monitor_id')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                        </div>

                        <!-- Sección de Recuperación -->
                        <div class="mt-6 p-4 bg-yellow-50 rounded-lg border border-yellow-200">
                            <h4 class="text-md font-semibold mb-4 text-gray-700">🔄 Recuperación de Módulo</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Fecha de Inicio de Recuperación -->
                                <div>
                                    <x-label for="recovery_start_date" :value="__('Fecha de Inicio de Recuperación')" />
                                    <x-input id="recovery_start_date" class="block mt-1 w-full" type="date" name="recovery_start_date" :value="old('recovery_start_date', $module->recovery_start_date ? $module->recovery_start_date->format('Y-m-d') : '')" />
                                    @error('recovery_start_date')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Fecha de Fin de Recuperación -->
                                <div>
                                    <x-label for="recovery_end_date" :value="__('Fecha de Fin de Recuperación')" />
                                    <x-input id="recovery_end_date" class="block mt-1 w-full" type="date" name="recovery_end_date" :value="old('recovery_end_date', $module->recovery_end_date ? $module->recovery_end_date->format('Y-m-d') : '')" />
                                    @error('recovery_end_date')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Notas de Recuperación -->
                                <div class="md:col-span-2">
                                    <x-label for="recovery_notes" :value="__('Notas de Recuperación')" />
                                    <textarea id="recovery_notes" name="recovery_notes" rows="3" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">{{ old('recovery_notes', $module->recovery_notes) }}</textarea>
                                    @error('recovery_notes')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Botones de Acción -->
                        <div class="flex items-center justify-end mt-6 gap-2">
                            <a href="{{ route('programs.modules.show', [$program->id, $module->id]) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-800 focus:outline-none focus:border-gray-800 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Cancelar
                            </a>
                            <x-button class="ml-3">
                                {{ __('Actualizar Módulo') }}
                            </x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
