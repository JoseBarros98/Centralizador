<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Cargar asistencia') }} - {{ $program->name }} - {{ $module->name }} - {{ $class->date }}
        </h2>
    </x-slot>

    <div >
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    @if (session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline">{{ session('error') }}</span>
                        </div>
                    @endif

                    <h3 class="text-lg font-medium text-gray-900 mb-4">Cargar archivo de asistencia</h3>
                    
                    <p class="mb-4">Por favor, cargue un archivo XLSX con los datos de asistencia. El archivo debe contener al menos las siguientes columnas:</p>
                    
                    <ul class="list-disc list-inside mb-4">
                        <li>Nombre del participante</li>
                        <li>Apellido del participante</li>
                        <li>Correo electrónico</li>
                        <li>Duración</li>
                        <li>Hora a la que se unió</li>
                        <li>Hora a la que abandonó la reunión</li>
                    </ul>

                    <form action="{{ route('attendances.upload', [$program->id, $module->id, $class->id]) }}" method="POST" enctype="multipart/form-data" class="mb-4">
                        @csrf
                        <div class="mb-4">
                            <label for="attendance_file" class="block text-sm font-medium text-gray-700">Archivo de asistencia</label>
                            <input type="file" name="attendance_file" id="attendance_file" class="mt-1 block w-full" required>
                            <p class="mt-1 text-sm text-gray-500">Solo se permiten archivos XLSX.</p>
                        </div>
                        
                        <div class="flex items-center">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Cargar y procesar
                            </button>
                        </div>
                    </form>

                    <div class="mt-6">
                        <a href="{{ route('attendances.show', [$program->id, $module->id, $class->id]) }}" class="text-blue-600 hover:text-blue-900">Volver a la lista de asistencias</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
