<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-white leading-tight">
                {{ __('Dashboard - Solicitudes de Arte') }}
            </h2>
        </div>
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filtros -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="GET" action="{{ route('art_requests.dashboard') }}" class="space-y-4">
                        <!-- Campos ocultos para mes y año -->
                        <input type="hidden" id="month_hidden" name="month" value="{{ $month }}">
                        <input type="hidden" id="year_hidden" name="year" value="{{ $year }}">

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Filtro por Mes -->
                            <div>
                                <x-label for="month_year" :value="__('Mes y Año')" />
                                <select id="month_year" name="month_year" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full" onchange="updateWeeks(this)">
                                    @foreach($months as $key => $monthLabel)
                                        <option value="{{ $key }}" {{ ($month . '-' . $year) == $key ? 'selected' : '' }}>
                                            {{ $monthLabel }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Filtro por Semana -->
                            <div>
                                <x-label for="week" :value="__('Semana del Mes')" />
                                <select id="week" name="week" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">
                                    @foreach($weeks as $weekNum => $weekData)
                                        <option value="{{ $weekNum }}" {{ ($selectedWeek == $weekNum || (!request('week') && $defaultWeek == $weekNum)) ? 'selected' : '' }}>
                                            Semana {{ $weekNum }}: {{ $weekData['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Filtro por Diseñador -->
                            <div>
                                <x-label for="designer_id" :value="__('Diseñador')" />
                                <select id="designer_id" name="designer_id" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">
                                    <option value="">Todos los Diseñadores</option>
                                    @foreach($designers as $designer)
                                        <option value="{{ $designer->id }}" {{ $designerId == $designer->id ? 'selected' : '' }}>
                                            {{ $designer->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <div class="flex justify-end">
                            <x-button>
                                {{ __('Filtrar') }}
                            </x-button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tarjetas de Estadísticas - Fila Horizontal -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                <!-- Total de Solicitudes -->
                <div class="bg-white rounded-xl border-l-4 border-l-blue-500 border border-gray-100 shadow-sm p-4 flex justify-between items-start">
                    <div>
                        <h2 class="text-4xl leading-none font-bold text-indigo-900">{{ $stats['total'] }}</h2>
                        <p class="text-gray-600 text-sm mt-1">Total Solicitudes</p>
                    </div>
                    <div class="bg-blue-500 p-2.5 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M19 2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zM9 4h6v2H9V4zm10 14H5V4h2v3h10V4h2v14z"/>
                            <text x="12" y="16" text-anchor="middle" fill="white" font-size="8" font-weight="bold"></text>
                        </svg>
                    </div>
                </div>

                <!-- Pendientes -->
                <div class="bg-white rounded-xl border-l-4 border-l-yellow-500 border border-gray-100 shadow-sm p-4 flex justify-between items-start">
                    <div>
                        <h2 class="text-4xl leading-none font-bold text-yellow-900">{{ $stats['pending'] }}</h2>
                        <p class="text-gray-600 text-sm mt-1">Pendientes</p>
                    </div>
                    <div class="bg-yellow-500 p-2.5 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </div>

                <!-- En Progreso -->
                <div class="bg-white rounded-xl border-l-4 border-l-indigo-500 border border-gray-100 shadow-sm p-4 flex justify-between items-start">
                    <div>
                        <h2 class="text-4xl leading-none font-bold text-blue-900">{{ $stats['in_progress'] }}</h2>
                        <p class="text-gray-600 text-sm mt-1">En Progreso</p>
                    </div>
                    <div class="bg-indigo-500 p-2.5 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                    </div>
                </div>

                <!-- Completadas -->
                <div class="bg-white rounded-xl border-l-4 border-l-green-500 border border-gray-100 shadow-sm p-4 flex justify-between items-start">
                    <div>
                        <h2 class="text-4xl leading-none font-bold text-green-900">{{ $stats['completed'] }}</h2>
                        <p class="text-gray-600 text-sm mt-1">Completadas</p>
                    </div>
                    <div class="bg-green-500 p-2.5 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </div>

                <!-- Promedio Entregadas por Día -->
                <div class="bg-white rounded-xl border-l-4 border-l-cyan-500 border border-gray-100 shadow-sm p-4 flex justify-between items-start">
                    <div>
                        <h2 class="text-4xl leading-none font-bold text-cyan-900">{{ number_format($stats['avg_completed_per_day'], 2) }}</h2>
                        <p class="text-gray-600 text-sm mt-1">Promedio entregadas/dia</p>
                        <p class="text-gray-400 text-xs mt-1">Calculado en {{ $stats['days_for_average'] }} dia(s)</p>
                    </div>
                    <div class="bg-cyan-500 p-2.5 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 20V10"></path>
                            <path d="M18 20V4"></path>
                            <path d="M6 20v-6"></path>
                        </svg>
                    </div>
                </div>

                <!-- Retrasadas -->
                <div class="bg-white rounded-xl border-l-4 border-l-red-500 border border-gray-100 shadow-sm p-4 flex justify-between items-start">
                    <div>
                        <h2 class="text-4xl leading-none font-bold text-red-900">{{ $stats['overdue'] }}</h2>
                        <p class="text-gray-600 text-sm mt-1">Retrasadas</p>
                    </div>
                    <div class="bg-red-500 p-2.5 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Gráficos -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                <!-- Por Estado -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Solicitudes por Estado</h3>
                    <div class="relative" style="height: 300px;">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>

                <!-- Por Tipo de Arte -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Solicitudes por Tipo de Arte</h3>
                    <div class="relative" style="height: 300px;">
                        <canvas id="typeOfArtChart"></canvas>
                    </div>
                </div>

                <!-- Por Pilar de Contenido -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Solicitudes por Pilar de Contenido</h3>
                    <div class="relative" style="height: 300px;">
                        <canvas id="contentPillarChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Almacenar datos de semanas por mes
        window.monthsData = {!! json_encode($months) !!};
        
        // Almacenar semanas actuales
        window.currentWeeks = {!! json_encode($weeks) !!};

        console.log('Dashboard data:', {!! json_encode(['stats' => $stats, 'weeks' => array_map(fn($w) => $w['label'], $weeks), 'selectedWeek' => $selectedWeek]) !!});
        console.log('Week Start:', '{{ $weekStart->format("Y-m-d") }}', 'Week End:', '{{ $weekEnd->format("Y-m-d") }}');
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Colores
            const statusColors = {
                'NO INICIADO': 'rgba(107, 114, 128, 0.8)',
                'EN CURSO': 'rgba(59, 130, 246, 0.8)',
                'COMPLETO': 'rgba(34, 197, 94, 0.8)',
                'RETRASADO': 'rgba(239, 68, 68, 0.8)',
                'ESPERANDO APROBACION': 'rgba(234, 179, 8, 0.8)',
                'ESPERANDO INFORMACION': 'rgba(168, 85, 247, 0.8)',
                'CANCELADO': 'rgba(239, 68, 68, 0.8)',
                'EN PAUSA': 'rgba(249, 115, 22, 0.8)'
            };

            // Gráfico por Estado
            const statusData = JSON.parse('{!! $chartData["statusData"] !!}');
            const statusLabels = Object.keys(statusData);
            const statusValues = Object.values(statusData);
            const statusChartColors = statusLabels.map(label => statusColors[label] || 'rgba(156, 163, 175, 0.8)');

            console.log('Status Chart Data:', statusData, statusLabels, statusValues);

            new Chart(document.getElementById('statusChart'), {
                type: 'doughnut',
                data: {
                    labels: statusLabels,
                    datasets: [{
                        data: statusValues,
                        backgroundColor: statusChartColors,
                        borderColor: statusChartColors.map(color => color.replace('0.8', '1')),
                        borderWidth: 2,
                        hoverOffset: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                padding: 15,
                                font: { size: 11, weight: 'bold' },
                                usePointStyle: true,
                                pointStyle: 'circle'
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = total > 0 ? ((context.parsed / total) * 100).toFixed(1) : '0.0';
                                    return `${context.label}: ${context.parsed} (${percentage}%)`;
                                }
                            }
                        }
                    },
                    cutout: '40%'
                }
            });

            // Gráfico por Tipo de Arte
            const typeOfArtData = JSON.parse('{!! $chartData["typeOfArtData"] !!}');
            const typeOfArtLabels = Object.keys(typeOfArtData);
            const typeOfArtValues = Object.values(typeOfArtData);
            const typeOfArtColors = generateColors(typeOfArtLabels.length);

            new Chart(document.getElementById('typeOfArtChart'), {
                type: 'doughnut',
                data: {
                    labels: typeOfArtLabels,
                    datasets: [{
                        data: typeOfArtValues,
                        backgroundColor: typeOfArtColors,
                        borderColor: typeOfArtColors.map(color => color.replace('0.8', '1')),
                        borderWidth: 2,
                        hoverOffset: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                padding: 15,
                                font: { size: 11, weight: 'bold' },
                                usePointStyle: true,
                                pointStyle: 'circle'
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = total > 0 ? ((context.parsed / total) * 100).toFixed(1) : '0.0';
                                    return `${context.label}: ${context.parsed} (${percentage}%)`;
                                }
                            }
                        }
                    },
                    cutout: '40%'
                }
            });

            // Gráfico por Pilar de Contenido
            const contentPillarData = JSON.parse('{!! $chartData["contentPillarData"] !!}');
            const contentPillarLabels = Object.keys(contentPillarData);
            const contentPillarValues = Object.values(contentPillarData);
            const contentPillarColors = generateColors(contentPillarLabels.length);

            new Chart(document.getElementById('contentPillarChart'), {
                type: 'doughnut',
                data: {
                    labels: contentPillarLabels,
                    datasets: [{
                        data: contentPillarValues,
                        backgroundColor: contentPillarColors,
                        borderColor: contentPillarColors.map(color => color.replace('0.8', '1')),
                        borderWidth: 2,
                        hoverOffset: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                padding: 15,
                                font: { size: 11, weight: 'bold' },
                                usePointStyle: true,
                                pointStyle: 'circle'
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = total > 0 ? ((context.parsed / total) * 100).toFixed(1) : '0.0';
                                    return `${context.label}: ${context.parsed} (${percentage}%)`;
                                }
                            }
                        }
                    },
                    cutout: '40%'
                }
            });

            // Función para generar colores dinámicos
            function generateColors(count) {
                const colors = [];
                for (let i = 0; i < count; i++) {
                    const hue = (i * 137) % 360;
                    colors.push(`hsla(${hue}, 70%, 60%, 0.8)`);
                }
                return colors;
            }
        });

        // Manejar cambio de mes
        document.getElementById('month_year').addEventListener('change', function() {
            const [month, year] = this.value.split('-');
            const designerId = document.getElementById('designer_id').value;
            // Construir URL con solo month, year y designer_id (sin week)
            let url = `{{ route('art_requests.dashboard') }}?month=${month}&year=${year}`;
            if (designerId) {
                url += `&designer_id=${designerId}`;
            }
            window.location.href = url;
        });
    </script>
    @endpush
</x-app-layout>
