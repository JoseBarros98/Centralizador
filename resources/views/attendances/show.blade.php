@extends('layouts.app')

@section('content')
<style>
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
    <!-- Header -->
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
        </div>
    </div>

    <!-- Class Information -->
    <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Información de la Clase</h3>
        </div>
        <div class="border-t border-gray-200 px-4 py-5 sm:px-6">
            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-3">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Fecha</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $class->class_date->format('d/m/Y') }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Total de Asistencias</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $attendances->count() }} estudiantes</dd>
                </div>
            </dl>
        </div>
    </div>

    <!-- Attendance Table -->
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Lista de Asistencia</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estudiante</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Documento</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($attendances as $attendance)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">
                                        @if($attendance->inscription)
                                            {{ $attendance->inscription->getFullName() }}
                                        @else
                                            {{ $attendance->name ?? 'Nombre no disponible' }}
                                            @if(!$attendance->is_registered_inscription)
                                                <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Invitado</span>
                                            @endif
                                        @endif
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        @if($attendance->inscription)
                                            {{ $attendance->inscription->email }}
                                        @else
                                            {{ $attendance->email ?? 'Email no disponible' }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            @if($attendance->inscription)
                                {{ $attendance->inscription->ci }}
                            @else
                                <span class="text-gray-500">{{ !$attendance->is_registered_inscription ? 'Invitado' : 'N/A' }}</span>
                            @endif
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
</div>
@endsection
