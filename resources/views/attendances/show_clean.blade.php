@extends('layouts.app')

@section('content')
<style>
/* Estilos para badges de asistencia */
.attendance-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.125rem 0.625rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
    margin-left: 0.5rem;
}

.attendance-present { background-color: #dcfce7; color: #166534; }
.attendance-late { background-color: #fef3c7; color: #92400e; }
.attendance-absent { background-color: #fecaca; color: #991b1b; }
.attendance-license { background-color: #dbeafe; color: #1e40af; }

/* Estilos para barras de progreso */
.progress-bar {
    height: 0.625rem;
    border-radius: 9999px;
}
.progress-present { background-color: #059669; }
.progress-late { background-color: #eab308; }
.progress-absent { background-color: #ef4444; }

/* Estilos para badges de estado */
.status-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.125rem 0.625rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
}
.status-present { background-color: #dcfce7; color: #166534; }
.status-late { background-color: #fef3c7; color: #92400e; }
.status-absent { background-color: #fecaca; color: #991b1b; }
.status-license { background-color: #dbeafe; color: #1e40af; }
</style>

<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Asistencia a Clase</h1>
            <div class="text-sm breadcrumbs">
                <ul>
                    <li><a href="{{ route('dashboard') }}">Inicio</a></li>
                    <li><a href="{{ route('programs.show', $program) }}">{{ $program->name }}</a></li>
                    <li><a href="{{ route('programs.modules.show', [$program->id, $module->id]) }}">{{ $module->name }}</a></li>
                    <li>Asistencia</li>
                </ul>
            </div>
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('programs.modules.show', [$program->id, $module->id]) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Volver al Módulo
            </a>
            <a href="{{ route('attendances.show_with_licenses', [$program->id, $module->id, $class->id]) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Gestión de Licencias
            </a>
            <a href="{{ route('attendances.recalculate', [$program->id, $module->id, $class->id]) }}" class="inline-flex items-center px-4 py-2 bg-yellow-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-600 active:bg-yellow-700 focus:outline-none focus:border-yellow-700 focus:ring ring-yellow-300 disabled:opacity-25 transition ease-in-out duration-150">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Recalcular Porcentajes
            </a>
            <a href="{{ route('attendances.upload', [$program->id, $module->id, $class->id]) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                </svg>
                Subir Archivo de Asistencia
            </a>
        </div>
    </div>

    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <h2 class="text-xl font-semibold mb-4">Detalles de la Clase</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p><span class="font-medium">Fecha:</span> {{ $class->class_date->format('d/m/Y') }}</p>
                @if(isset($metadata) && isset($metadata['start_time']) && isset($metadata['end_time']))
                    <p><span class="font-medium">Hora:</span> {{ $metadata['start_time'] }} - {{ $metadata['end_time'] }}</p>
                @else
                    <p><span class="font-medium">Hora:</span> {{ $class->start_time->format('H:i') }} - {{ $class->end_time->format('H:i') }}</p>
                @endif
            </div>
            <div>
                @if(isset($metadata) && isset($metadata['class_duration']))
                    <p><span class="font-medium">Duración:</span> {{ $metadata['class_duration'] }} minutos</p>
                @else
                    <p><span class="font-medium">Duración:</span> {{ $class->end_time->diffInMinutes($class->start_time) }} minutos</p>
                @endif
            </div>
        </div>
    </div>

    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-blue-700">
                    <strong>Nota sobre asistencia:</strong> El estado de asistencia se determina por la duración:
                    <ul class="list-disc ml-5 mt-1">
                        <li>Menos de 45 minutos: Ausente</li>
                        <li>Entre 45 y 99 minutos: Tarde</li>
                        <li>Mayor o igual a 100 minutos: Presente</li>
                    </ul>
                </p>
            </div>
        </div>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold">Participantes Registrados ({{ $attendances->where('is_registered_inscription', true)->count() }})</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duración</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Porcentaje</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($attendances->where('is_registered_inscription', true) as $attendance)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $attendance->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $attendance->duration }} minutos
                            <?php
                                $badgeClass = 'attendance-absent';
                                $badgeText = '< 45 min';
                                if ($attendance->duration >= 100) {
                                    $badgeClass = 'attendance-present';
                                    $badgeText = '≥ 100 min';
                                } elseif ($attendance->duration >= 45) {
                                    $badgeClass = 'attendance-late';
                                    $badgeText = '≥ 45 min';
                                }
                            ?>
                            <span class="attendance-badge {{ $badgeClass }}">{{ $badgeText }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ number_format($attendance->attendance_percentage, 1) }}%</div>
                            <div class="w-full bg-gray-200 rounded-full h-2.5">
                                <?php
                                    $progressClass = 'progress-absent';
                                    if ($attendance->duration >= 100) {
                                        $progressClass = 'progress-present';
                                    } elseif ($attendance->duration >= 45) {
                                        $progressClass = 'progress-late';
                                    }
                                ?>
                                <div class="progress-bar {{ $progressClass }}" style="width: {{ min(100, $attendance->attendance_percentage) }}%"></div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($attendance->license_type && $attendance->status === 'absent')
                                <span class="status-badge status-license">Licencia/Permiso</span>
                            @else
                                <?php
                                    $statusClass = 'status-absent';
                                    $statusText = 'Ausente';
                                    if ($attendance->status == 'present') {
                                        $statusClass = 'status-present';
                                        $statusText = 'Presente';
                                    } elseif ($attendance->status == 'late') {
                                        $statusClass = 'status-late';
                                        $statusText = 'Tarde';
                                    }
                                ?>
                                <span class="status-badge {{ $statusClass }}">{{ $statusText }}</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold">Invitados ({{ $attendances->where('is_registered_inscription', false)->count() }})</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duración</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Porcentaje</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($attendances->where('is_registered_inscription', false) as $attendance)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $attendance->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $attendance->email }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $attendance->duration }} minutos
                            <?php
                                $badgeClass = 'attendance-absent';
                                $badgeText = '< 45 min';
                                if ($attendance->duration >= 100) {
                                    $badgeClass = 'attendance-present';
                                    $badgeText = '≥ 100 min';
                                } elseif ($attendance->duration >= 45) {
                                    $badgeClass = 'attendance-late';
                                    $badgeText = '≥ 45 min';
                                }
                            ?>
                            <span class="attendance-badge {{ $badgeClass }}">{{ $badgeText }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ number_format($attendance->attendance_percentage, 1) }}%</div>
                            <div class="w-full bg-gray-200 rounded-full h-2.5">
                                <?php
                                    $progressClass = 'progress-absent';
                                    if ($attendance->duration >= 100) {
                                        $progressClass = 'progress-present';
                                    } elseif ($attendance->duration >= 45) {
                                        $progressClass = 'progress-late';
                                    }
                                ?>
                                <div class="progress-bar {{ $progressClass }}" style="width: {{ min(100, $attendance->attendance_percentage) }}%"></div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($attendance->license_type && $attendance->status === 'absent')
                                <span class="status-badge status-license">Licencia/Permiso</span>
                            @else
                                <?php
                                    $statusClass = 'status-absent';
                                    $statusText = 'Ausente';
                                    if ($attendance->status == 'present') {
                                        $statusClass = 'status-present';
                                        $statusText = 'Presente';
                                    } elseif ($attendance->status == 'late') {
                                        $statusClass = 'status-late';
                                        $statusText = 'Tarde';
                                    }
                                ?>
                                <span class="status-badge {{ $statusClass }}">{{ $statusText }}</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold">Participantes Ausentes ({{ $absentInscription->count() }})</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Documento</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($absentInscription as $inscription)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $inscription->getFullName() }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $inscription->ci }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($inscription->license_info)
                                <span class="status-badge status-license">Licencia/Permiso</span>
                            @else
                                <span class="status-badge status-absent">Ausente</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
