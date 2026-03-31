@extends('layouts.app')

@php
    use Carbon\Carbon;
@endphp

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Dashboard Académico</h1>
            <p class="mt-2 text-sm text-gray-600">Vista analítica de programas e inscripciones académicas</p>
        </div>

        <!-- Filtros -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 mb-8">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Filtros</h3>
            <form method="GET" action="{{ route('dashboard.academic') }}">
                <div class="flex flex-wrap items-end gap-3">
                    

                    <!-- Filtro por Programa -->
                    <div class="flex-1 min-w-[180px]">
                        <label for="program" class="block text-sm font-medium text-gray-700 mb-2">Programa</label>
                        <select id="program" name="program" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="">Todos los programas</option>
                            @foreach($programs as $program)
                                <option value="{{ $program->id }}" {{ request('program') == $program->id ? 'selected' : '' }}>{{ $program->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filtro por Área -->
                    <div class="flex-1 min-w-[180px]">
                        <label for="area" class="block text-sm font-medium text-gray-700 mb-2">Área</label>
                        <select id="area" name="area" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="">Todas las áreas</option>
                            @foreach($areas as $area)
                                <option value="{{ $area }}" {{ request('area') == $area ? 'selected' : '' }}>{{ $area }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filtro por Gestión -->
                    <div class="flex-1 min-w-[150px]">
                        <label for="year" class="block text-sm font-medium text-gray-700 mb-2">Gestión</label>
                        <select id="year" name="year" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            @foreach($years as $year)
                                <option value="{{ $year }}" {{ (request('year') ?? $currentYear) == $year ? 'selected' : '' }}>{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filtro por Estado/Fase -->
                    <div class="flex-1 min-w-[180px]">
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Estado/Fase</label>
                        <select id="status" name="status" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="">Todos los estados</option>
                            @foreach($statuses as $status)
                                <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Botones -->
                    <div class="flex gap-2">
                        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 whitespace-nowrap">
                            Filtrar
                        </button>
                        <a href="{{ route('dashboard.academic') }}" class="bg-gray-300 text-gray-800 px-4 py-2 rounded-md text-sm font-medium hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 whitespace-nowrap">
                            Limpiar
                        </a>
                    </div>
                </div>
            </form>
            
            @if(request()->hasAny(['program', 'area', 'year', 'status']))
                <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex items-center">
                        <svg class="h-5 w-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-sm text-blue-800 font-medium">
                            Filtros aplicados:
                            
                            @if(request('program'))
                                @php
                                    $selectedProgram = $programs->firstWhere('id', request('program'));
                                @endphp
                                @if($selectedProgram)
                                    <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded ml-1">{{ $selectedProgram->name }}</span>
                                @endif
                            @endif
                            @if(request('area'))
                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded ml-1">{{ request('area') }}</span>
                            @endif
                            @if(request('year'))
                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded ml-1">Gestión {{ request('year') }}</span>
                            @endif
                            @if(request('status'))
                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded ml-1">{{ request('status') }}</span>
                            @endif
                        </span>
                    </div>
                </div>
            @endif
        </div>

        <!-- Métricas principales -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-8">
            <!-- Total Registros -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 flex-1 min-w-0">
                <div class="p-3">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                            </svg>
                        </div>
                        <div class="ml-2 w-0 flex-1">
                            <dl>
                                <dt class="text-xs font-medium text-gray-500 truncate">Registros</dt>
                                <dd class="text-lg font-semibold text-blue-600">{{ $totalRegistros }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            @foreach($academicStatusCards as $statusCard)
                <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 flex-1 min-w-0">
                    <div class="p-3">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 {{ $statusCard['color'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div class="ml-2 w-0 flex-1">
                                <dl>
                                    <dt class="text-xs font-medium text-gray-500 truncate">{{ $statusCard['label'] }}</dt>
                                    <dd class="text-lg font-semibold {{ $statusCard['color'] }}">{{ $statusCard['count'] }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

        </div>

        <div class="grid grid-cols-1 gap-8 mb-8">
        <!-- Gráficos principales -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 order-1">
            <!-- Programas por Área -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                <div class="px-4 py-3 border-b border-gray-200">
                    <h3 class="text-base font-medium text-gray-900">Programas por Área</h3>
                </div>
                <div class="p-4 h-80 relative">
                    <canvas id="programsByAreaChart" class="w-full h-full"></canvas>
                </div>
            </div>

            <!-- Programas por Tipo -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                <div class="px-4 py-3 border-b border-gray-200">
                    <h3 class="text-base font-medium text-gray-900">Programas por Tipo</h3>
                </div>
                <div class="p-4 h-80 relative">
                    <canvas id="programsByTypeChart" class="w-full h-full"></canvas>
                </div>
            </div>
        </div>

        <!-- Gráficos adicionales en una fila -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 order-2">
            

            <!-- Programas Más Populares -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                <div class="px-4 py-3 border-b border-gray-200">
                    <h3 class="text-base font-medium text-gray-900">Programas Más Populares</h3>
                    <p class="text-xs text-gray-600">Top 5 programas por inscripciones</p>
                </div>
                <div class="p-4 h-96 relative">
                    <canvas id="popularProgramsChart" class="w-full h-full"></canvas>
                </div>
            </div>

            <!-- Programas por Estado -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                <div class="px-4 py-3 border-b border-gray-200">
                    <h3 class="text-base font-medium text-gray-900">Programas por Estado</h3>
                    <p class="text-xs text-gray-600">Distribución por fase actual</p>
                </div>
                <div class="p-4 h-96 relative">
                    <canvas id="programsByStateChart" class="w-full h-full"></canvas>
                </div>
            </div>

            {{-- <!-- Cumpleaños -->
            <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-slate-200">
                <div class="px-5 py-4 border-b border-slate-200 bg-slate-50">
                    <h3 class="text-base font-semibold text-slate-800">Cumpleanos del Mes</h3>
                    <p class="text-xs text-slate-500">Docentes e inscritos destacados</p>
                </div>
                <div class="p-4 max-h-96 overflow-y-auto space-y-4">
                    @if($birthdayTeachersOnly->count() > 0 || $birthdayInscriptionsToday->count() > 0)
                        <!-- Docentes -->
                        @if($birthdayTeachersOnly->count() > 0)
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <h4 class="text-xs font-semibold text-slate-700 uppercase tracking-wide">Docentes</h4>
                                    <span class="inline-flex items-center rounded-full bg-sky-100 px-2.5 py-1 text-xs font-semibold text-sky-700">{{ $birthdayTeachersOnly->count() }}</span>
                                </div>
                                <div class="space-y-2">
                                    @foreach($birthdayTeachersOnly as $birthday)
                                        <div class="rounded-lg border border-sky-200 bg-sky-50 p-2.5 transition hover:bg-sky-100">
                                            <p class="text-sm font-semibold text-slate-800 leading-tight">{{ $birthday['name'] }}</p>
                                            <p class="text-xs text-sky-700 mt-1">{{ $birthday['day'] }} de {{ Carbon::parse($birthday['birth_date'])->translatedFormat('F') }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Inscritos Hoy -->
                        @if($birthdayInscriptionsToday->count() > 0)
                            <div class="rounded-xl border border-amber-300 bg-gradient-to-r from-amber-50 to-orange-50 p-3">
                                <div class="flex items-center justify-between mb-2">
                                    <h4 class="text-sm font-bold text-amber-900">Cumplen Hoy</h4>
                                    <span class="inline-flex items-center rounded-full bg-amber-200 px-2.5 py-1 text-xs font-semibold text-amber-900">{{ $birthdayInscriptionsToday->count() }}</span>
                                </div>
                                <div class="space-y-2">
                                    @foreach($birthdayInscriptionsToday as $birthday)
                                        <div class="rounded-lg border border-amber-200 bg-white p-2">
                                            <p class="text-sm font-semibold text-slate-800 leading-tight">{{ $birthday['full_name'] }}</p>
                                            <p class="text-xs text-amber-700 mt-1 truncate">{{ $birthday['program_name'] }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-6">
                            <p class="text-xs text-gray-500">Sin cumpleaños este mes</p>
                        </div>
                    @endif
                </div>
            </div>
            --}}
        </div>

        {{-- <!-- Docentes Mejor Valorados -->
        <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-slate-200">
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                <h3 class="text-lg font-semibold text-slate-800">Docentes Mejor Valorados</h3>
                <p class="text-sm text-slate-500">Top 3 segun evaluaciones de modulos</p>
            </div>
            <div class="p-6">
                @if($topTeachers->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @foreach($topTeachers as $index => $teacher)
                            @php
                                $position = $index + 1;
                                $colors = [
                                    1 => ['ring' => 'ring-amber-300', 'badge' => 'bg-amber-500 text-white', 'accent' => 'text-amber-600'],
                                    2 => ['ring' => 'ring-slate-300', 'badge' => 'bg-slate-500 text-white', 'accent' => 'text-slate-600'],
                                    3 => ['ring' => 'ring-orange-300', 'badge' => 'bg-orange-500 text-white', 'accent' => 'text-orange-600'],
                                ];
                                $config = $colors[$position];
                            @endphp

                            <div class="rounded-xl border border-slate-200 bg-gradient-to-b from-white to-slate-50 p-5 ring-1 {{ $config['ring'] }} transition hover:shadow-md">
                                <div class="flex items-start justify-between mb-4">
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Posicion</p>
                                        <p class="text-lg font-bold {{ $config['accent'] }}">Top {{ $position }}</p>
                                    </div>
                                    <div class="h-9 w-9 rounded-full {{ $config['badge'] }} flex items-center justify-center text-sm font-bold">
                                        {{ $position }}
                                    </div>
                                </div>

                                <div class="text-center">
                                    <h4 class="font-semibold text-lg text-slate-800 leading-tight">{{ $teacher->name }} {{ $teacher->paternal_surname }}</h4>
                                    @if($teacher->academic_degree)
                                        <p class="text-slate-500 text-sm mt-1 mb-3">{{ $teacher->academic_degree }}</p>
                                    @endif

                                    <div class="rounded-lg border border-slate-200 bg-white p-3 mb-3">
                                        <div class="flex justify-center mb-1.5">
                                            @php $rating = round($teacher->modules_avg_teacher_rating ?? 0); @endphp
                                            @for($i = 1; $i <= 5; $i++)
                                                @if($i <= $rating)
                                                    <span class="text-amber-400 text-lg">★</span>
                                                @else
                                                    <span class="text-slate-300 text-lg">☆</span>
                                                @endif
                                            @endfor
                                        </div>

                                        <div class="text-2xl font-bold {{ $config['accent'] }}">
                                            {{ number_format($teacher->modules_avg_teacher_rating ?? 0, 1) }}/5.0
                                        </div>
                                    </div>

                                    <div class="rounded-lg bg-slate-100 px-3 py-2">
                                        <div class="text-slate-700 text-sm font-semibold">
                                            {{ $teacher->modules_count }} {{ $teacher->modules_count === 1 ? 'módulo' : 'módulos' }} impartido{{ $teacher->modules_count === 1 ? '' : 's' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Leyenda -->
                    <div class="mt-6 text-center">
                        <p class="text-sm text-slate-500">
                            * Valoración basada en el promedio de calificaciones de módulos finalizados
                        </p>
                    </div>
                @else
                    <div class="text-center py-8">
                        <div class="w-16 h-16 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.7141 15h4.268c.4043 0 .732-.3838.732-.8571V3.85714c0-.47338-.3277-.85714-.732-.85714H6.71411c-.55228 0-1 .44772-1 1v4m10.99999 7v-3h3v3h-3Zm-3 6H6.71411c-.55228 0-1-.4477-1-1 0-1.6569 1.34315-3 3-3h2.99999c1.6569 0 3 1.3431 3 3 0 .5523-.4477 1-1 1Zm-1-9.5c0 1.3807-1.1193 2.5-2.5 2.5s-2.49999-1.1193-2.49999-2.5S8.8334 9 10.2141 9s2.5 1.1193 2.5 2.5Z"/>
                            </svg>
                        </div>
                        <p class="text-gray-500">No hay docentes con valoraciones disponibles</p>
                    </div>
                @endif
            </div>
        </div>
        --}}

        </div>

    </div>
</div>

@push('scripts')
<script>
// Datos para gráficos
const programsByAreaData = @json($programsByArea);
const programsByStateData = @json($programsByState);
const programsByTypeData = @json($programsByType);
const popularProgramsData = @json($popularProgramsData);

// Esperar a que el DOM esté completamente cargado
document.addEventListener('DOMContentLoaded', function() {

function formatProgramLabelLines(label, charsPerLine = 28, maxLines = 3) {
    if (!label) return '';

    const cleanLabel = label.replace(/\s+/g, ' ').trim();
    if (cleanLabel.length <= charsPerLine) return cleanLabel;

    const words = cleanLabel.split(' ');
    const lines = [];
    let currentLine = '';

    for (const word of words) {
        const nextLine = currentLine ? `${currentLine} ${word}` : word;

        if (nextLine.length <= charsPerLine) {
            currentLine = nextLine;
            continue;
        }

        if (currentLine) {
            lines.push(currentLine);
        } else {
            lines.push(word.slice(0, charsPerLine - 1) + '-');
        }

        currentLine = currentLine ? word : word.slice(charsPerLine - 1);

        if (lines.length === maxLines - 1) {
            break;
        }
    }

    if (currentLine && lines.length < maxLines) {
        lines.push(currentLine);
    }

    if (lines.length > maxLines) {
        lines.length = maxLines;
    }

    const usedLength = lines.join(' ').length;
    if (usedLength < cleanLabel.length) {
        const lastIndex = lines.length - 1;
        lines[lastIndex] = lines[lastIndex].slice(0, Math.max(1, charsPerLine - 3)) + '...';
    }

    return lines;
}

// Gráfico de Programas por Área
const areaCtx = document.getElementById('programsByAreaChart').getContext('2d');
new Chart(areaCtx, {
    type: 'doughnut',
    data: {
        labels: Object.keys(programsByAreaData),
        datasets: [{
            data: Object.values(programsByAreaData),
            backgroundColor: [
                '#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#06B6D4', '#EC4899', '#84CC16'
            ],
            borderColor: '#ffffff',
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    font: {
                        size: 11
                    },
                    padding: 15,
                    usePointStyle: true,
                    pointStyle: 'circle'
                }
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const label = context.label || '';
                        const value = context.parsed;
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = ((value / total) * 100).toFixed(1);
                        return `${label}: ${value} (${percentage}%)`;
                    }
                }
            }
        }
    }
});

// Gráfico de Programas por Tipo
const programTypeCtx = document.getElementById('programsByTypeChart').getContext('2d');
new Chart(programTypeCtx, {
    type: 'doughnut',
    data: {
        labels: programsByTypeData.map(item => item.program_type),
        datasets: [{
            data: programsByTypeData.map(item => item.total),
            backgroundColor: [
                '#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#06B6D4', '#EC4899', '#84CC16', '#F97316'
            ],
            borderColor: '#ffffff',
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    font: {
                        size: 11
                    },
                    padding: 15,
                    usePointStyle: true,
                    pointStyle: 'circle'
                }
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const label = context.label || '';
                        const value = context.parsed;
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = ((value / total) * 100).toFixed(1);
                        return `${label}: ${value} (${percentage}%)`;
                    }
                }
            }
        }
    }
});

/* // Gráfico de Inscripciones por Año - Canvas no existe en la vista
const yearCtx = document.getElementById('inscriptionsByYearChart').getContext('2d');
new Chart(yearCtx, {
    type: 'line',
    data: {
        labels: inscriptionsByYearData.map(item => item.year),
        datasets: [{
            label: 'Inscripciones',
            data: inscriptionsByYearData.map(item => item.total),
            borderColor: '#3B82F6',
            backgroundColor: '#3B82F6',
            fill: false,
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
*/

// Gráfico de Programas Más Populares
const popularCtx = document.getElementById('popularProgramsChart');
if (popularCtx && popularProgramsData && popularProgramsData.length > 0) {
    const ctx = popularCtx.getContext('2d');

    // Crear labels en varias líneas para evitar cortes visuales
    const shortProgramLabels = popularProgramsData.map(item => formatProgramLabelLines(item.name, 30, 3));
    const fullProgramLabels = popularProgramsData.map(item => item.name);

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: shortProgramLabels,
            datasets: [{
                label: 'Inscritos',
                data: popularProgramsData.map(item => item.inscriptions_count),
                backgroundColor: [
                    '#2563EB',
                    '#0891B2',
                    '#F59E0B',
                    '#EF4444',
                    '#7C3AED'
                ],
                borderColor: [
                    '#1D4ED8',
                    '#0E7490',
                    '#D97706',
                    '#DC2626',
                    '#6D28D9'
                ],
                borderWidth: 1.5,
                borderRadius: 8,
                barThickness: 28,
                maxBarThickness: 32
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            layout: {
                padding: {
                    left: 16,
                    right: 10,
                    top: 10,
                    bottom: 10
                }
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        font: {
                            size: 12,
                            weight: 'bold'
                        },
                        padding: 12,
                        boxWidth: 12,
                        usePointStyle: true,
                        pointStyle: 'rectRounded'
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    titleFont: { size: 12, weight: 'bold' },
                    bodyFont: { size: 11 },
                    callbacks: {
                        title: function(context) {
                            // Mostrar el nombre completo en el tooltip
                            return fullProgramLabels[context[0].dataIndex];
                        },
                        label: function(context) {
                            return 'Inscritos: ' + context.parsed.x;
                        }
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        font: {
                            size: 11,
                            weight: '600'
                        }
                    },
                    grid: {
                        color: 'rgba(148, 163, 184, 0.18)',
                        drawBorder: false
                    }
                },
                y: {
                    ticks: {
                        font: {
                            size: 11,
                            weight: '600'
                        },
                        padding: 8,
                        autoSkip: false
                    },
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
} else {
    // Si no hay datos, mostrar mensaje en el canvas
    if (popularCtx) {
        const parent = popularCtx.parentElement;
        popularCtx.style.display = 'none';
        const msg = document.createElement('div');
        msg.className = 'flex items-center justify-center h-full';
        msg.innerHTML = '<div class="text-center"><p class="text-gray-500">No hay programas con inscripciones</p></div>';
        parent.appendChild(msg);
    }
}

// Grafico: Estado por Programa
// Gráfico de Programas por Estado
const stateCtx = document.getElementById('programsByStateChart').getContext('2d');
new Chart(stateCtx, {
    type: 'bar',
    data: {
        labels: Object.keys(programsByStateData),
        datasets: [{
            label: 'Programas',
            data: Object.values(programsByStateData),
            backgroundColor: [
                '#0EA5E9', '#2563EB', '#06B6D4', '#14B8A6', '#0891B2', '#0F766E', '#7C3AED'
            ],
            borderColor: [
                '#0284C7', '#1D4ED8', '#0891B2', '#0F766E', '#0E7490', '#115E59', '#6D28D9'
            ],
            borderWidth: 1.5,
            borderRadius: 8,
            barThickness: 18,
            maxBarThickness: 22
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        indexAxis: 'y',
        layout: {
            padding: {
                left: 8,
                right: 6,
                top: 8,
                bottom: 4
            }
        },
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: 'rgba(15, 23, 42, 0.92)',
                padding: 10,
                titleFont: { size: 11, weight: 'bold' },
                bodyFont: { size: 11 },
                callbacks: {
                    label: function(context) {
                        return 'Programas: ' + context.parsed.x;
                    }
                }
            }
        },
        scales: {
            x: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1,
                    font: { size: 11, weight: '600' },
                    color: '#475569'
                },
                grid: {
                    color: 'rgba(148, 163, 184, 0.2)',
                    drawBorder: false
                }
            },
            y: {
                ticks: {
                    font: { size: 11, weight: '600' },
                    color: '#334155'
                },
                grid: { display: false }
            }
        }
    }
});

}); // Fin de DOMContentLoaded

</script>
@endpush
@endsection