@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
        <!-- Encabezado -->
        <div class="mb-8">
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900">Dashboard Contable</h1>
                <p class="mt-2 text-gray-600">Gestión de asignaciones de programas y cobros</p>
            </div>
            
            <!-- Filtros por Mes y Año -->
            <form id="filterForm" method="GET" action="{{ route('dashboard.accounting') }}" class="grid grid-cols-2 gap-4 w-96">
                <div>
                    <label for="mes" class="block text-sm font-medium text-gray-700 mb-2">Filtro por Mes</label>
                    <select id="mes" name="mes" onchange="document.getElementById('filterForm').submit()" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="1" {{ $mes == 1 ? 'selected' : '' }}>Enero</option>
                        <option value="2" {{ $mes == 2 ? 'selected' : '' }}>Febrero</option>
                        <option value="3" {{ $mes == 3 ? 'selected' : '' }}>Marzo</option>
                        <option value="4" {{ $mes == 4 ? 'selected' : '' }}>Abril</option>
                        <option value="5" {{ $mes == 5 ? 'selected' : '' }}>Mayo</option>
                        <option value="6" {{ $mes == 6 ? 'selected' : '' }}>Junio</option>
                        <option value="7" {{ $mes == 7 ? 'selected' : '' }}>Julio</option>
                        <option value="8" {{ $mes == 8 ? 'selected' : '' }}>Agosto</option>
                        <option value="9" {{ $mes == 9 ? 'selected' : '' }}>Septiembre</option>
                        <option value="10" {{ $mes == 10 ? 'selected' : '' }}>Octubre</option>
                        <option value="11" {{ $mes == 11 ? 'selected' : '' }}>Noviembre</option>
                        <option value="12" {{ $mes == 12 ? 'selected' : '' }}>Diciembre</option>
                    </select>
                </div>
                <div>
                    <label for="year" class="block text-sm font-medium text-gray-700 mb-2">Filtro por Año</label>
                    <select id="year" name="year" onchange="document.getElementById('filterForm').submit()" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="2023" {{ $year == 2023 ? 'selected' : '' }}>2023</option>
                        <option value="2024" {{ $year == 2024 ? 'selected' : '' }}>2024</option>
                        <option value="2025" {{ $year == 2025 ? 'selected' : '' }}>2025</option>
                        <option value="2026" {{ $year == 2026 ? 'selected' : '' }}>2026</option>
                    </select>
                </div>
            </form>
        </div>

        <!-- Cards de Totales -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Total Asignación -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    Total Asignado
                                </dt>
                                <dd class="text-lg font-medium text-gray-900">
                                    Bs. {{ number_format($totalAsignacion, 2) }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Cobrado -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    Total Cobrado
                                </dt>
                                <dd class="text-lg font-medium text-green-600">
                                    Bs. {{ number_format($totalCobrado, 2) }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Porcentaje Alcanzado -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    % Alcanzado
                                </dt>
                                <dd class="text-lg font-medium text-purple-600">
                                    {{ number_format($porcentajeTotalAlcanzado, 2) }}%
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráficos -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Gráfico combinado mensual -->
            <div class="bg-white overflow-hidden shadow rounded-lg p-6 lg:col-span-2">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Asignaciones y Cobros por Mes</h3>
                <canvas id="monthlyComparisonChart"></canvas>
            </div>

            <!-- Gráfico de Top Responsables -->
            <div class="bg-white overflow-hidden shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Top Responsables de Cartera</h3>
                <div class="h-64">
                    <canvas id="topAccountantsChart"></canvas>
                </div>
            </div>

            <!-- Gráfico de Categorías -->
            <div class="bg-white overflow-hidden shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Distribución por Categoría</h3>
                <div class="h-64">
                    <canvas id="categoriesChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Gráfico Comparativo Asignación vs Cobros -->
        <div class="mt-8">
            <div class="bg-white overflow-hidden shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Top 10: Asignación vs Cobros por Programa</h3>
                <div class="h-80">
                    <canvas id="programComparisonChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const chartData = @json($chartData);

    function wrapLabel(text, maxCharsPerLine = 26) {
        if (!text || text.length <= maxCharsPerLine) {
            return text;
        }

        const words = text.split(' ');
        const lines = [];
        let currentLine = '';

        words.forEach(word => {
            const candidate = currentLine ? `${currentLine} ${word}` : word;
            if (candidate.length <= maxCharsPerLine) {
                currentLine = candidate;
            } else {
                if (currentLine) {
                    lines.push(currentLine);
                }
                currentLine = word;
            }
        });

        if (currentLine) {
            lines.push(currentLine);
        }

        return lines;
    }

    // Gráfico combinado: Asignaciones y Cobros por Mes
    new Chart(document.getElementById('monthlyComparisonChart'), {
        type: 'bar',
        data: {
            labels: chartData.months,
            datasets: [
                {
                    type: 'bar',
                    label: 'Total Asignado',
                    data: chartData.assignmentsByMonth,
                    backgroundColor: 'rgba(59, 130, 246, 0.45)',
                    borderColor: 'rgb(59, 130, 246)',
                    borderWidth: 1,
                    borderRadius: 4
                },
                {
                    type: 'line',
                    label: 'Total Cobrado',
                    data: chartData.collectionsByMonth,
                    borderColor: 'rgb(34, 197, 94)',
                    backgroundColor: 'rgba(34, 197, 94, 0.12)',
                    borderWidth: 3,
                    tension: 0.3,
                    fill: true,
                    pointRadius: 3,
                    pointHoverRadius: 5
                }
            ]
        },
        options: {
            responsive: true,
            aspectRatio: 2.6,
            plugins: {
                legend: { display: true }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Bs. ' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Gráfico de Top Responsables
    new Chart(document.getElementById('topAccountantsChart'), {
        type: 'doughnut',
        data: {
            labels: chartData.topAccountants.labels,
            datasets: [{
                data: chartData.topAccountants.values,
                backgroundColor: [
                    'rgba(255, 99, 132, 0.5)',
                    'rgba(54, 162, 235, 0.5)',
                    'rgba(255, 206, 86, 0.5)',
                    'rgba(75, 192, 192, 0.5)',
                    'rgba(153, 102, 255, 0.5)'
                ],
                borderColor: [
                    'rgb(255, 99, 132)',
                    'rgb(54, 162, 235)',
                    'rgb(255, 206, 86)',
                    'rgb(75, 192, 192)',
                    'rgb(153, 102, 255)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'right' }
            }
        }
    });

    // Gráfico de Categorías
    new Chart(document.getElementById('categoriesChart'), {
        type: 'pie',
        data: {
            labels: chartData.categories.labels,
            datasets: [{
                data: chartData.categories.values,
                backgroundColor: [
                    'rgba(99, 102, 241, 0.5)',
                    'rgba(239, 68, 68, 0.5)',
                    'rgba(59, 130, 246, 0.5)',
                    'rgba(168, 85, 247, 0.5)'
                ],
                borderColor: [
                    'rgb(99, 102, 241)',
                    'rgb(239, 68, 68)',
                    'rgb(59, 130, 246)',
                    'rgb(168, 85, 247)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });

    // Gráfico Comparativo: Asignación vs Cobros por Programa
    new Chart(document.getElementById('programComparisonChart'), {
        type: 'bar',
        data: {
            labels: chartData.programComparison.labels,
            datasets: [
                {
                    label: 'Asignación',
                    data: chartData.programComparison.assignments,
                    backgroundColor: 'rgba(59, 130, 246, 0.5)',
                    borderColor: 'rgb(59, 130, 246)',
                    borderWidth: 1
                },
                {
                    label: 'Cobrado',
                    data: chartData.programComparison.collections,
                    backgroundColor: 'rgba(34, 197, 94, 0.5)',
                    borderColor: 'rgb(34, 197, 94)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            layout: {
                padding: {
                    left: 12,
                    right: 12
                }
            },
            plugins: {
                legend: { display: true }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Bs. ' + value.toLocaleString();
                        }
                    }
                },
                y: {
                    ticks: {
                        callback: function(value) {
                            const label = this.getLabelForValue(value);
                            return wrapLabel(label, 24);
                        }
                    }
                }
            }
        }
    });

    // Preseleccionar y aplicar automáticamente el mes y año actual al cargar la página
    document.addEventListener('DOMContentLoaded', function() {
        const currentDate = new Date();
        const currentMonth = currentDate.getMonth() + 1;
        const currentYear = currentDate.getFullYear();
        
        // Verificar si los filtros tienen valores por defecto
        const mesSelect = document.getElementById('mes');
        const yearSelect = document.getElementById('year');
        
        // Si la URL no tiene parámetros, redirigir con los valores por defecto
        const urlParams = new URLSearchParams(window.location.search);
        if (!urlParams.has('mes') || !urlParams.has('year')) {
            window.location.href = '{{ route("dashboard.accounting") }}?mes=' + currentMonth + '&year=' + currentYear;
        }
    });
</script>
@endsection
