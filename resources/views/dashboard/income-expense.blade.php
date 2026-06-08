@extends('layouts.app')

@section('content')
<div>
    <div class="w-full sm:px-6 lg:px-8">
        <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Dashboard Ingresos vs Egresos</h2>
                <p class="mt-1 text-gray-600">Comparativa anual de ingresos y egresos por gestión</p>
            </div>

            <form method="GET" action="{{ route('dashboard.income-expense') }}" class="flex items-end gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Gestión</label>
                    <select name="year" class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @foreach($availableYears as $availableYear)
                            <option value="{{ $availableYear }}" @selected((int)$year === (int)$availableYear)>{{ $availableYear }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-medium hover:bg-indigo-700">Aplicar</button>
            </form>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow p-5 border-l-4 border-l-emerald-500">
                <p class="text-sm text-gray-500">Total Ingresos</p>
                <p class="mt-2 text-2xl font-bold text-emerald-700">Bs. {{ number_format($totalIncome, 2) }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-5 border-l-4 border-l-rose-500">
                <p class="text-sm text-gray-500">Total Egresos</p>
                <p class="mt-2 text-2xl font-bold text-rose-700">Bs. {{ number_format($totalExpense, 2) }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-5 border-l-4 {{ $balance >= 0 ? 'border-l-indigo-500' : 'border-l-amber-500' }}">
                <p class="text-sm text-gray-500">Balance</p>
                <p class="mt-2 text-2xl font-bold {{ $balance >= 0 ? 'text-indigo-700' : 'text-amber-700' }}">Bs. {{ number_format($balance, 2) }}</p>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Comparativa Mensual</h3>
            <canvas id="incomeExpenseChart"></canvas>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Acumulado Anual</h3>
                <canvas id="accumulatedChart"></canvas>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Diferencia por Mes (Ingresos - Egresos)</h3>
                <canvas id="monthlyDifferenceChart"></canvas>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Proyección de Cierre Anual</h3>
                <canvas id="annualProjectionChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
    const chartData = @json($chartData);

    function bsTick(value) {
        return 'Bs. ' + Number(value).toLocaleString();
    }

    function renderEmptyState(canvasEl, message) {
        canvasEl.style.display = 'none';
        const div = document.createElement('div');
        div.className = 'flex flex-col items-center justify-center py-12 text-gray-400';
        div.innerHTML = `<svg class="w-12 h-12 mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg><p class="text-sm">${message}</p>`;
        canvasEl.parentNode.appendChild(div);
    }

    const moneyScale = {
        beginAtZero: true,
        ticks: {
            callback: function(value) {
                return bsTick(value);
            }
        }
    };

    const moneyTooltip = {
        callbacks: {
            label: function(ctx) {
                return ctx.dataset.label + ': Bs. ' + Number(ctx.parsed.y).toLocaleString();
            }
        }
    };

    // Comparativa Mensual
    const incomeExpenseCanvas = document.getElementById('incomeExpenseChart');
    const hasMainData = (chartData.incomeSeries || []).some(v => v > 0) || (chartData.expenseSeries || []).some(v => v > 0);
    if (!hasMainData) {
        renderEmptyState(incomeExpenseCanvas, 'Sin datos para el período seleccionado');
    } else {
        new Chart(incomeExpenseCanvas, {
            type: 'bar',
            data: {
                labels: chartData.months,
                datasets: [
                    {
                        type: 'bar',
                        label: 'Ingresos',
                        data: chartData.incomeSeries,
                        backgroundColor: 'rgba(16, 185, 129, 0.45)',
                        borderColor: 'rgb(16, 185, 129)',
                        borderWidth: 1,
                        borderRadius: 4
                    },
                    {
                        type: 'line',
                        label: 'Egresos',
                        data: chartData.expenseSeries,
                        borderColor: 'rgb(244, 63, 94)',
                        backgroundColor: 'rgba(244, 63, 94, 0.12)',
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
                aspectRatio: 2.5,
                plugins: {
                    legend: { display: true },
                    tooltip: moneyTooltip
                },
                scales: {
                    y: moneyScale
                }
            }
        });
    }

    // Acumulado Anual
    const accCanvas = document.getElementById('accumulatedChart');
    const hasAccData = (chartData.incomeAccumulated || []).some(v => v > 0) || (chartData.expenseAccumulated || []).some(v => v > 0);
    if (!hasAccData) {
        renderEmptyState(accCanvas, 'Sin datos acumulados para el período');
    } else {
        new Chart(accCanvas, {
            type: 'line',
            data: {
                labels: chartData.months,
                datasets: [
                    {
                        label: 'Ingresos Acumulados',
                        data: chartData.incomeAccumulated,
                        borderColor: 'rgb(16, 185, 129)',
                        backgroundColor: 'rgba(16, 185, 129, 0.12)',
                        borderWidth: 3,
                        fill: false,
                        tension: 0.25,
                        pointRadius: 2
                    },
                    {
                        label: 'Egresos Acumulados',
                        data: chartData.expenseAccumulated,
                        borderColor: 'rgb(220, 38, 38)',
                        backgroundColor: 'rgba(220, 38, 38, 0.12)',
                        borderWidth: 3,
                        fill: false,
                        tension: 0.25,
                        pointRadius: 2
                    }
                ]
            },
            options: {
                responsive: true,
                aspectRatio: 2,
                plugins: {
                    legend: { display: true },
                    tooltip: moneyTooltip
                },
                scales: {
                    y: moneyScale
                }
            }
        });
    }

    // Diferencia por Mes
    const diffCanvas = document.getElementById('monthlyDifferenceChart');
    const hasDiffData = (chartData.balanceSeries || []).some(v => v !== 0);
    if (!hasDiffData) {
        renderEmptyState(diffCanvas, 'Sin diferencias registradas para el período');
    } else {
        new Chart(diffCanvas, {
            type: 'bar',
            data: {
                labels: chartData.months,
                datasets: [
                    {
                        label: 'Diferencia Mensual',
                        data: chartData.balanceSeries,
                        backgroundColor: chartData.balanceSeries.map(v => v >= 0 ? 'rgba(59, 130, 246, 0.45)' : 'rgba(245, 158, 11, 0.45)'),
                        borderColor: chartData.balanceSeries.map(v => v >= 0 ? 'rgb(59, 130, 246)' : 'rgb(245, 158, 11)'),
                        borderWidth: 1,
                        borderRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                aspectRatio: 2,
                plugins: {
                    legend: { display: true },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                return ctx.dataset.label + ': Bs. ' + Number(ctx.parsed.y).toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        ticks: {
                            callback: function(value) {
                                return bsTick(value);
                            }
                        }
                    }
                }
            }
        });
    }

    // Proyección de Cierre Anual
    const projCanvas = document.getElementById('annualProjectionChart');
    if (!chartData.lastMonthWithData || chartData.lastMonthWithData < 1) {
        renderEmptyState(projCanvas, 'Sin datos suficientes para proyectar');
    } else {
        new Chart(projCanvas, {
            type: 'line',
            data: {
                labels: chartData.months,
                datasets: [
                    {
                        label: 'Proyección Ingresos (cierre anual)',
                        data: chartData.incomeProjectionSeries,
                        borderColor: 'rgb(5, 150, 105)',
                        backgroundColor: 'rgba(5, 150, 105, 0.1)',
                        borderWidth: 3,
                        tension: 0.25,
                        fill: false,
                        pointRadius: 3,
                        spanGaps: false
                    },
                    {
                        label: 'Proyección Egresos (cierre anual)',
                        data: chartData.expenseProjectionSeries,
                        borderColor: 'rgb(225, 29, 72)',
                        backgroundColor: 'rgba(225, 29, 72, 0.1)',
                        borderWidth: 3,
                        tension: 0.25,
                        fill: false,
                        pointRadius: 3,
                        spanGaps: false
                    }
                ]
            },
            options: {
                responsive: true,
                aspectRatio: 2,
                plugins: {
                    legend: { display: true },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                return ctx.dataset.label + ': Bs. ' + Number(ctx.parsed.y).toLocaleString();
                            },
                            footer: function() {
                                return 'Estimación basada hasta ' + chartData.months[chartData.lastMonthWithData - 1];
                            }
                        }
                    }
                },
                scales: {
                    y: moneyScale
                }
            }
        });
    }
</script>
@endsection
