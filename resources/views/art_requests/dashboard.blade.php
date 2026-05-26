<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-white leading-tight">
                {{ __('Dashboard - Solicitudes de Arte') }}
            </h2>
        </div>
    </x-slot>

    <div>
        <div class="w-full sm:px-6 lg:px-8">
            <!-- Filtros -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="GET" action="{{ route('art_requests.dashboard') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                        <!-- Desde -->
                        <div>
                            <x-label for="date_from" :value="__('Desde')" />
                            <input type="date" id="date_from" name="date_from"
                                value="{{ $dateFrom->format('Y-m-d') }}"
                                max="{{ now()->format('Y-m-d') }}"
                                class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full text-sm">
                        </div>

                        <!-- Hasta -->
                        <div>
                            <x-label for="date_to" :value="__('Hasta')" />
                            <input type="date" id="date_to" name="date_to"
                                value="{{ $dateTo->format('Y-m-d') }}"
                                max="{{ now()->format('Y-m-d') }}"
                                class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full text-sm">
                        </div>

                        <!-- Diseñador -->
                        <div>
                            <x-label for="designer_id" :value="__('Diseñador')" />
                            <select id="designer_id" name="designer_id" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full text-sm">
                                <option value="">Todos los Diseñadores</option>
                                @foreach($designers as $designer)
                                    <option value="{{ $designer->id }}" {{ $designerId == $designer->id ? 'selected' : '' }}>
                                        {{ $designer->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Acciones -->
                        <div class="flex gap-2">
                            <button type="submit" class="flex-1 px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-md hover:bg-indigo-700">
                                Filtrar
                            </button>
                            <a href="{{ route('art_requests.dashboard') }}" class="px-4 py-2 bg-gray-200 text-gray-700 text-sm rounded-md hover:bg-gray-300">
                                Limpiar
                            </a>
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
                        <p class="text-gray-400 text-xs mt-1">{{ $dateFrom->format('d/m/Y') }} — {{ $dateTo->format('d/m/Y') }} ({{ $stats['days_for_average'] }} día(s))</p>
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

            <!-- Gráficos: Estado (barra) + Tipo + Pilar -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                <!-- Por Estado — barra horizontal para comparar 6+ estados fácilmente -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Solicitudes por Estado</h3>
                    <div class="relative" style="height: 300px;">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>

                <!-- Por Tipo de Arte -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Por Tipo de Arte</h3>
                    <div class="relative" style="height: 300px;">
                        <canvas id="typeOfArtChart"></canvas>
                    </div>
                </div>

                <!-- Por Pilar de Contenido -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Por Pilar de Contenido</h3>
                    <div class="relative" style="height: 300px;">
                        <canvas id="contentPillarChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Tendencia diaria: ingresadas vs completadas -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Tendencia Diaria</h3>
                        <p class="text-sm text-gray-500">Solicitudes ingresadas vs completadas por día en el período</p>
                    </div>
                </div>
                <div class="relative" style="height: 260px;">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function generateColors(count) {
            const colors = [];
            for (let i = 0; i < count; i++) {
                const hue = (i * 137) % 360;
                colors.push(`hsla(${hue}, 70%, 60%, 0.8)`);
            }
            return colors;
        }

        function renderEmptyState(canvasEl, message = 'Sin datos para este período') {
            if (!canvasEl) return;
            canvasEl.style.display = 'none';
            const div = document.createElement('div');
            div.className = 'flex flex-col items-center justify-center h-full text-gray-400';
            div.innerHTML = `
                <svg class="w-10 h-10 mb-2 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <p class="text-sm font-medium">${message}</p>`;
            canvasEl.parentElement.appendChild(div);
        }

        document.addEventListener('DOMContentLoaded', function() {
            const doughnutTooltip = {
                callbacks: {
                    label: function(context) {
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const pct = total > 0 ? ((context.parsed / total) * 100).toFixed(1) : '0.0';
                        return `${context.label}: ${context.parsed} (${pct}%)`;
                    }
                }
            };

            const doughnutLegend = {
                position: 'bottom',
                labels: { boxWidth: 12, padding: 14, font: { size: 11 }, usePointStyle: true, pointStyle: 'circle' }
            };

            // ── Gráfico por Estado (barra horizontal) ──────────────────────
            const statusColors = {
                'NO INICIADO':           'rgba(107, 114, 128, 0.8)',
                'EN CURSO':              'rgba(59,  130, 246, 0.8)',
                'COMPLETO':              'rgba(34,  197,  94, 0.8)',
                'RETRASADO':             'rgba(239,  68,  68, 0.8)',
                'ESPERANDO APROBACION':  'rgba(234, 179,   8, 0.8)',
                'ESPERANDO INFORMACION': 'rgba(168,  85, 247, 0.8)',
                'CANCELADO':             'rgba(156, 163, 175, 0.8)',
                'EN PAUSA':              'rgba(249, 115,  22, 0.8)',
            };

            const statusData   = JSON.parse('{!! $chartData["statusData"] !!}');
            const statusLabels = Object.keys(statusData);
            const statusValues = Object.values(statusData);

            if (statusLabels.length === 0 || statusValues.every(v => v === 0)) {
                renderEmptyState(document.getElementById('statusChart'));
            } else {
                const statusBg = statusLabels.map(l => statusColors[l] || 'rgba(156,163,175,0.8)');
                new Chart(document.getElementById('statusChart'), {
                    type: 'bar',
                    data: {
                        labels: statusLabels,
                        datasets: [{
                            data: statusValues,
                            backgroundColor: statusBg,
                            borderColor: statusBg.map(c => c.replace('0.8', '1')),
                            borderWidth: 1,
                            borderRadius: 4,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        indexAxis: 'y',
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function(ctx) {
                                        const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                        const pct = total > 0 ? ((ctx.parsed.x / total) * 100).toFixed(1) : '0.0';
                                        return `${ctx.parsed.x} solicitudes (${pct}%)`;
                                    }
                                }
                            }
                        },
                        scales: {
                            x: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: 'rgba(0,0,0,0.05)' } },
                            y: { grid: { display: false } }
                        }
                    }
                });
            }

            // ── Gráfico por Tipo de Arte (dona) ────────────────────────────
            const typeOfArtData   = JSON.parse('{!! $chartData["typeOfArtData"] !!}');
            const typeOfArtLabels = Object.keys(typeOfArtData);
            const typeOfArtValues = Object.values(typeOfArtData);

            if (typeOfArtLabels.length === 0 || typeOfArtValues.every(v => v === 0)) {
                renderEmptyState(document.getElementById('typeOfArtChart'));
            } else {
                const artColors = generateColors(typeOfArtLabels.length);
                new Chart(document.getElementById('typeOfArtChart'), {
                    type: 'doughnut',
                    data: {
                        labels: typeOfArtLabels,
                        datasets: [{ data: typeOfArtValues, backgroundColor: artColors,
                            borderColor: artColors.map(c => c.replace('0.8','1')), borderWidth: 2, hoverOffset: 8 }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, cutout: '45%',
                        plugins: { legend: doughnutLegend, tooltip: doughnutTooltip } }
                });
            }

            // ── Gráfico por Pilar de Contenido (dona) ──────────────────────
            const contentPillarData   = JSON.parse('{!! $chartData["contentPillarData"] !!}');
            const contentPillarLabels = Object.keys(contentPillarData);
            const contentPillarValues = Object.values(contentPillarData);

            if (contentPillarLabels.length === 0 || contentPillarValues.every(v => v === 0)) {
                renderEmptyState(document.getElementById('contentPillarChart'));
            } else {
                const pillarColors = generateColors(contentPillarLabels.length);
                new Chart(document.getElementById('contentPillarChart'), {
                    type: 'doughnut',
                    data: {
                        labels: contentPillarLabels,
                        datasets: [{ data: contentPillarValues, backgroundColor: pillarColors,
                            borderColor: pillarColors.map(c => c.replace('0.8','1')), borderWidth: 2, hoverOffset: 8 }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, cutout: '45%',
                        plugins: { legend: doughnutLegend, tooltip: doughnutTooltip } }
                });
            }

            // ── Tendencia diaria: ingresadas vs completadas ─────────────────
            const dailyLabels    = JSON.parse('{!! $chartData["dailyLabels"] !!}');
            const dailyTotals    = JSON.parse('{!! $chartData["dailyTotals"] !!}');
            const dailyCompleted = JSON.parse('{!! $chartData["dailyCompleted"] !!}');

            if (dailyLabels.length === 0) {
                renderEmptyState(document.getElementById('trendChart'), 'Sin actividad en el período seleccionado');
            } else {
                new Chart(document.getElementById('trendChart'), {
                    type: 'bar',
                    data: {
                        labels: dailyLabels,
                        datasets: [
                            {
                                type: 'bar',
                                label: 'Ingresadas',
                                data: dailyTotals,
                                backgroundColor: 'rgba(59, 130, 246, 0.45)',
                                borderColor: 'rgb(59, 130, 246)',
                                borderWidth: 1,
                                borderRadius: 4,
                                order: 2,
                            },
                            {
                                type: 'line',
                                label: 'Completadas',
                                data: dailyCompleted,
                                borderColor: 'rgb(34, 197, 94)',
                                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                                borderWidth: 2.5,
                                tension: 0.3,
                                pointRadius: 3,
                                pointHoverRadius: 5,
                                fill: false,
                                order: 1,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: true, position: 'top',
                                labels: { boxWidth: 12, padding: 14, font: { size: 11 }, usePointStyle: true } }
                        },
                        scales: {
                            y: { beginAtZero: true, ticks: { stepSize: 1, precision: 0 },
                                grid: { color: 'rgba(0,0,0,0.05)' } },
                            x: { grid: { display: false },
                                ticks: { maxTicksLimit: 20, font: { size: 10 } } }
                        }
                    }
                });
            }
        });
    </script>
    @endpush
</x-app-layout>
