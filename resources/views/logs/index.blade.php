<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Visualizador de Logs') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Mensajes de éxito/error -->
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    
                    <!-- Controles -->
                    <div class="mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <!-- Selector de archivo -->
                            <div>
                                <label for="file" class="block text-sm font-medium text-gray-700 mb-1">
                                    Archivo de Log
                                </label>
                                <select id="file" name="file" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    @foreach($logFiles as $file)
                                        <option value="{{ $file }}" {{ $selectedFile === $file ? 'selected' : '' }}>
                                            {{ $file }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Número de líneas -->
                            <div>
                                <label for="lines" class="block text-sm font-medium text-gray-700 mb-1">
                                    Líneas a mostrar
                                </label>
                                <select id="lines" name="lines" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option value="50" {{ $lines == 50 ? 'selected' : '' }}>50</option>
                                    <option value="100" {{ $lines == 100 ? 'selected' : '' }}>100</option>
                                    <option value="200" {{ $lines == 200 ? 'selected' : '' }}>200</option>
                                    <option value="500" {{ $lines == 500 ? 'selected' : '' }}>500</option>
                                </select>
                            </div>

                            <!-- Filtro por nivel -->
                            <div>
                                <label for="level" class="block text-sm font-medium text-gray-700 mb-1">
                                    Filtrar por nivel
                                </label>
                                <select id="level" name="level" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option value="all" {{ $level === 'all' ? 'selected' : '' }}>Todos</option>
                                    <option value="emergency" {{ $level === 'emergency' ? 'selected' : '' }}>Emergency</option>
                                    <option value="alert" {{ $level === 'alert' ? 'selected' : '' }}>Alert</option>
                                    <option value="critical" {{ $level === 'critical' ? 'selected' : '' }}>Critical</option>
                                    <option value="error" {{ $level === 'error' ? 'selected' : '' }}>Error</option>
                                    <option value="warning" {{ $level === 'warning' ? 'selected' : '' }}>Warning</option>
                                    <option value="notice" {{ $level === 'notice' ? 'selected' : '' }}>Notice</option>
                                    <option value="info" {{ $level === 'info' ? 'selected' : '' }}>Info</option>
                                    <option value="debug" {{ $level === 'debug' ? 'selected' : '' }}>Debug</option>
                                </select>
                            </div>

                            <!-- Botones de acción -->
                            <div class="flex space-x-2">
                                <button id="refresh-btn" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm">
                                    Actualizar
                                </button>
                                <a href="{{ route('logs.download', ['file' => $selectedFile]) }}" 
                                   class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-sm inline-block text-center">
                                    Descargar
                                </a>
                                <form method="POST" action="{{ route('logs.clear', ['file' => $selectedFile]) }}" class="inline" 
                                      onsubmit="return confirm('¿Estás seguro de que quieres limpiar este archivo de log?')">
                                    @csrf
                                    <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded text-sm">
                                        Limpiar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Información del archivo -->
                    <div class="mb-4 p-4 bg-gray-50 rounded-lg">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                            <div>
                                <strong>Archivo actual:</strong> {{ $selectedFile }}
                            </div>
                            @if(!isset($logContent['error']))
                                <div>
                                    <strong>Total de líneas:</strong> {{ number_format($logContent['total_lines']) }}
                                </div>
                                <div>
                                    <strong>Mostrando:</strong> {{ number_format($logContent['showing_lines']) }} líneas
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Contenido del log -->
                    <div class="bg-gray-900 text-gray-100 p-4 rounded-lg overflow-x-auto" style="max-height: 600px; overflow-y: auto;">
                        @if(isset($logContent['error']))
                            <div class="text-red-400">
                                {{ $logContent['error'] }}
                            </div>
                        @elseif(empty($logContent['entries']))
                            <div class="text-gray-400">
                                No hay entradas de log para mostrar con los filtros seleccionados.
                            </div>
                        @else
                            @foreach($logContent['entries'] as $entry)
                                <div class="mb-4 border-b border-gray-700 pb-2">
                                    <div class="flex items-start space-x-2 mb-1">
                                        <span class="text-gray-400 text-xs">{{ $entry['timestamp'] }}</span>
                                        @php
                                            $logController = new \App\Http\Controllers\LogController();
                                            $levelColor = $logController->getLevelColor($entry['level']);
                                        @endphp
                                        <span class="px-2 py-1 rounded text-xs font-semibold {{ $levelColor }}">
                                            {{ $entry['level'] }}
                                        </span>
                                    </div>
                                    <div class="text-sm">
                                        <div class="mb-1">{{ $entry['message'] }}</div>
                                        @if(!empty($entry['context']))
                                            <details class="text-xs text-gray-400">
                                                <summary class="cursor-pointer hover:text-gray-300">Ver detalles</summary>
                                                <pre class="mt-2 whitespace-pre-wrap">{{ implode("\n", $entry['context']) }}</pre>
                                            </details>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>

                    <!-- Auto-refresh -->
                    <div class="mt-4 flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <input type="checkbox" id="auto-refresh" class="rounded">
                            <label for="auto-refresh" class="text-sm text-gray-600">Auto-actualizar cada 30 segundos</label>
                        </div>
                        <div class="text-sm text-gray-500">
                            Última actualización: <span id="last-update">{{ now()->format('H:i:s') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const fileSelect = document.getElementById('file');
            const linesSelect = document.getElementById('lines');
            const levelSelect = document.getElementById('level');
            const refreshBtn = document.getElementById('refresh-btn');
            const autoRefreshCheckbox = document.getElementById('auto-refresh');
            const lastUpdateSpan = document.getElementById('last-update');
            
            let autoRefreshInterval;

            // Función para actualizar la página con los parámetros actuales
            function updatePage() {
                const params = new URLSearchParams({
                    file: fileSelect.value,
                    lines: linesSelect.value,
                    level: levelSelect.value
                });
                
                window.location.href = `{{ route('logs.index') }}?${params.toString()}`;
            }

            // Eventos de cambio en los selectores
            fileSelect.addEventListener('change', updatePage);
            linesSelect.addEventListener('change', updatePage);
            levelSelect.addEventListener('change', updatePage);
            refreshBtn.addEventListener('click', updatePage);

            // Auto-refresh
            autoRefreshCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    autoRefreshInterval = setInterval(() => {
                        updatePage();
                    }, 30000);
                } else {
                    clearInterval(autoRefreshInterval);
                }
            });

            // Actualizar timestamp cada segundo
            setInterval(() => {
                lastUpdateSpan.textContent = new Date().toLocaleTimeString();
            }, 1000);
        });
    </script>
</x-app-layout>
