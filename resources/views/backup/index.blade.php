<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            Backup del Sistema
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="w-full sm:px-6 lg:px-8 space-y-6">

            @if($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    <ul class="list-disc list-inside text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Crear nuevo backup -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Crear Backup</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0 mt-1">
                                    <svg class="h-6 w-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2 1 3 3 3h10c2 0 3-1 3-3V7M9 11h6M9 15h6M9 3h6l1 4H8L9 3z"/>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-medium text-gray-800">Solo Base de Datos</h4>
                                    <p class="text-sm text-gray-500 mt-1">Genera un archivo <code>.sql</code> con toda la estructura y datos de la base de datos.</p>
                                    <form method="POST" action="{{ route('backup.database') }}" class="mt-3"
                                          onsubmit="this.querySelector('button').disabled=true; this.querySelector('button').textContent='Generando...';">
                                        @csrf
                                        <button type="submit"
                                            class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-md">
                                            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                            </svg>
                                            Backup BD
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0 mt-1">
                                    <svg class="h-6 w-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-medium text-gray-800">Backup Completo</h4>
                                    <p class="text-sm text-gray-500 mt-1">Genera un archivo <code>.zip</code> con la base de datos y todos los archivos almacenados (documentos, recibos, etc.).</p>
                                    <form method="POST" action="{{ route('backup.full') }}" class="mt-3"
                                          onsubmit="this.querySelector('button').disabled=true; this.querySelector('button').textContent='Generando...';">
                                        @csrf
                                        <button type="submit"
                                            class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-md">
                                            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                            </svg>
                                            Backup Completo
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Importar backup -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-1">Restaurar Backup</h3>
                    <p class="text-sm text-gray-500 mb-4">
                        Sube un archivo <code>.sql</code> (solo BD) o <code>.zip</code> (backup completo) para restaurarlo.
                        <span class="text-red-600 font-medium">Esta acción sobrescribirá los datos actuales.</span>
                    </p>
                    <form method="POST" action="{{ route('backup.import') }}" enctype="multipart/form-data"
                          onsubmit="return confirm('¿Estás seguro de que deseas restaurar este backup? Se sobrescribirán los datos actuales.')">
                        @csrf
                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
                            <input type="file" name="backup_file" accept=".sql,.zip" required
                                class="block text-sm text-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-md">
                                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4 4l4-4m0 0l4 4m-4-4V4"/>
                                </svg>
                                Restaurar
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Lista de backups -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Backups Almacenados</h3>

                    @if($files->isEmpty())
                        <p class="text-sm text-gray-500">No hay backups almacenados aún.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Archivo</th>
                                        <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                        <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Tamaño</th>
                                        <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                        <th class="px-4 py-3 text-right font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($files as $file)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 text-gray-800 font-mono text-xs">{{ $file['name'] }}</td>
                                        <td class="px-4 py-3">
                                            @if($file['type'] === 'database')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-800">Base de Datos</span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Completo</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-gray-600">
                                            @php
                                                $bytes = $file['size'];
                                                if ($bytes >= 1073741824) $size = number_format($bytes / 1073741824, 2) . ' GB';
                                                elseif ($bytes >= 1048576)  $size = number_format($bytes / 1048576, 2) . ' MB';
                                                elseif ($bytes >= 1024)     $size = number_format($bytes / 1024, 2) . ' KB';
                                                else                        $size = $bytes . ' B';
                                            @endphp
                                            {{ $size }}
                                        </td>
                                        <td class="px-4 py-3 text-gray-600">{{ $file['date']->format('d/m/Y H:i') }}</td>
                                        <td class="px-4 py-3 text-right space-x-2">
                                            <a href="{{ route('backup.download', $file['name']) }}"
                                               class="inline-flex items-center px-3 py-1 bg-indigo-100 hover:bg-indigo-200 text-indigo-700 text-xs font-medium rounded">
                                                Descargar
                                            </a>
                                            <form method="POST" action="{{ route('backup.destroy', $file['name']) }}" class="inline"
                                                  onsubmit="return confirm('¿Eliminar este backup?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="inline-flex items-center px-3 py-1 bg-red-100 hover:bg-red-200 text-red-700 text-xs font-medium rounded">
                                                    Eliminar
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
