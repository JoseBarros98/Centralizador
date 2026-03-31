@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Encabezado -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Asistencia a Clase</h1>
            <div class="text-sm text-gray-600 mt-1">
                <span>{{ $program->name }}</span> → 
                <span>{{ $module->name }}</span> → 
                <span>{{ $class->class_date->format('d/m/Y') }}</span>
            </div>
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('programs.modules.show', [$program->id, $module->id]) }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Volver al Módulo
            </a>
            <a href="{{ route('attendances.show', [$program->id, $module->id, $class->id]) }}" 
               class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />
                </svg>
                Vista Estándar
            </a>
            <a href="{{ route('attendances.upload', [$program->id, $module->id, $class->id]) }}" 
               class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                </svg>
                Subir Archivo de Asistencia
            </a>
        </div>
    </div>

    <!-- Alertas -->
    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <!-- Estadísticas -->
    <div class="flex flex-wrap gap-3 mb-6">
        <div class="bg-white overflow-hidden shadow rounded-lg flex-1 min-w-32">
            <div class="p-3">
                <div class="text-center">
                    <div class="flex justify-center mb-1">
                        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                        </svg>
                    </div>
                    <div class="text-lg font-bold text-gray-900">{{ $attendances->count() }}</div>
                    <div class="text-xs text-gray-500">Total</div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg flex-1 min-w-32">
            <div class="p-3">
                <div class="text-center">
                    <div class="flex justify-center mb-1">
                        <svg class="h-4 w-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <div class="text-lg font-bold text-gray-900">{{ $attendances->whereNotNull('inscription_id')->count() }}</div>
                    <div class="text-xs text-gray-500">Inscritos</div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg flex-1 min-w-32">
            <div class="p-3">
                <div class="text-center">
                    <div class="flex justify-center mb-1">
                        <svg class="h-4 w-4 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div class="text-lg font-bold text-gray-900">{{ $attendances->whereNull('inscription_id')->count() }}</div>
                    <div class="text-xs text-gray-500">Invitados</div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg flex-1 min-w-32">
            <div class="p-3">
                <div class="text-center">
                    <div class="flex justify-center mb-1">
                        <svg class="h-4 w-4 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <div class="text-lg font-bold text-gray-900">{{ $attendances->where('status', 'present')->count() }}</div>
                    <div class="text-xs text-gray-500">Presentes</div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg flex-1 min-w-32">
            <div class="p-3">
                <div class="text-center">
                    <div class="flex justify-center mb-1">
                        <svg class="h-4 w-4 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                    <div class="text-lg font-bold text-gray-900">{{ $attendances->where('status', 'absent')->count() }}</div>
                    <div class="text-xs text-gray-500">Ausentes</div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg flex-1 min-w-32">
            <div class="p-3">
                <div class="text-center">
                    <div class="flex justify-center mb-1">
                        <svg class="h-4 w-4 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div class="text-lg font-bold text-gray-900">{{ $attendances->where('has_license', true)->count() }}</div>
                    <div class="text-xs text-gray-500">Con Licencia</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Asistencias (Solo Inscritos) -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold">Lista de Asistencias - Participantes Inscritos</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Estudiante
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Tipo
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Duración
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Estado
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Licencia/Permiso
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @php
                        $registeredAttendances = $attendances->whereNotNull('inscription_id');
                    @endphp
                    @forelse($registeredAttendances as $attendance)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $attendance->inscription ? $attendance->inscription->getFullName() : $attendance->name }}
                                    </div>
                                    @if($attendance->email)
                                        <div class="text-sm text-gray-500">{{ $attendance->email }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($attendance->inscription_id)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    👤 Inscrito
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                    👥 Invitado
                                </span>
                            @endif
                        </td>
                        
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $attendance->duration }} minutos</div>
                            <div class="text-xs text-gray-500">
                                @if($attendance->duration >= 60)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Completo
                                    </span>
                                @elseif($attendance->duration >= 30)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        Parcial
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Mínimo
                                    </span>
                                @endif
                            </div>
                        </td>
                        
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($attendance->status == 'present')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    ✓ Presente
                                </span>
                            @elseif($attendance->status == 'late')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    ⏰ Tarde
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    ✗ Ausente
                                </span>
                            @endif
                        </td>
                        
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($attendance->has_license)
                                <button onclick="showLicenseDetails({{ $attendance->id }}, '{{ $attendance->inscription ? $attendance->inscription->getFullName() : $attendance->name }}', '{{ $attendance->license_type }}', '{{ addslashes($attendance->license_notes) }}', '{{ $attendance->license_granted_by }}', '{{ $attendance->license_granted_at ? $attendance->license_granted_at->format('d/m/Y H:i') : '' }}')"
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 hover:bg-blue-200 cursor-pointer">
                                    @php
                                        $licenseIcons = [
                                            'permiso' => '🔹',
                                            'licencia_medica' => '🏥',
                                            'licencia_laboral' => '💼',
                                            'emergencia_familiar' => '👨‍👩‍👧‍👦',
                                            'otro' => '📝'
                                        ];
                                    @endphp
                                    {{ $licenseIcons[$attendance->license_type] ?? '📝' }}
                                    {{ $attendance->license_type_text }}
                                </button>
                            @else
                                <span class="text-sm text-gray-400">Sin licencia</span>
                            @endif
                        </td>
                        
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            @if($attendance->has_license)
                                <button onclick="revokeLicense({{ $attendance->id }}, {{ $program->id }}, {{ $module->id }}, {{ $class->id }})"
                                        class="text-red-600 hover:text-red-900 mr-3">
                                    <svg class="h-4 w-4 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    Revocar
                                </button>
                            @elseif(!$attendance->inscription_id)
                                {{-- Invitados no pueden recibir licencias --}}
                                <span class="text-gray-400 text-xs">
                                    <svg class="h-4 w-4 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728" />
                                    </svg>
                                    No aplica
                                </span>
                            @elseif($attendance->status == 'present')
                                {{-- Estudiantes que asistieron no necesitan licencia --}}
                                <span class="text-green-600 text-xs">✓ Asistió</span>
                            @else
                                {{-- Para inscritos que aparecen en el archivo pero con poca duración, revisar en sección de ausentes --}}
                                <span class="text-blue-600 text-xs">Ver sección ausentes</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                            No hay registros de asistencia de participantes inscritos para esta clase.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Sección de Participantes Ausentes (Solo estos pueden recibir licencias) -->
    @if(isset($absentInscriptions) && $absentInscriptions->count() > 0)
    <div class="bg-white shadow-md rounded-lg overflow-hidden mt-6">
        <div class="px-6 py-4 border-b border-gray-200 bg-red-50">
            <h2 class="text-xl font-semibold text-red-800">
                📋 Participantes Inscritos Ausentes ({{ $absentInscriptions->count() }})
            </h2>
            <p class="text-sm text-red-600 mt-1">Solo estos participantes pueden recibir licencias o permisos</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Estudiante
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Documento
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Estado
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Licencia/Permiso
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($absentInscriptions as $inscription)
                        @php
                            // Buscar si ya tiene un registro de asistencia con licencia
                            $existingAttendance = $attendances->where('inscription_id', $inscription->id)->first();
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $inscription->getFullName() }}
                                        </div>
                                        @if($inscription->email)
                                            <div class="text-sm text-gray-500">{{ $inscription->email }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $inscription->ci }}
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    ✗ Ausente
                                </span>
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($existingAttendance && $existingAttendance->has_license)
                                    <button onclick="showLicenseDetails({{ $existingAttendance->id }}, '{{ $inscription->getFullName() }}', '{{ $existingAttendance->license_type }}', '{{ addslashes($existingAttendance->license_notes) }}', '{{ $existingAttendance->license_granted_by }}', '{{ $existingAttendance->license_granted_at ? $existingAttendance->license_granted_at->format('d/m/Y H:i') : '' }}')"
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 hover:bg-blue-200 cursor-pointer">
                                        @php
                                            $licenseIcons = [
                                                'permiso' => '🔹',
                                                'licencia_medica' => '🏥',
                                                'licencia_laboral' => '💼',
                                                'emergencia_familiar' => '👨‍👩‍👧‍👦',
                                                'otro' => '📝'
                                            ];
                                        @endphp
                                        {{ $licenseIcons[$existingAttendance->license_type] ?? '📝' }}
                                        {{ $existingAttendance->license_type_text }}
                                    </button>
                                @else
                                    <span class="text-sm text-gray-400">Sin licencia</span>
                                @endif
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                @if($existingAttendance && $existingAttendance->has_license)
                                    <button onclick="revokeLicenseForAbsent({{ $inscription->id }}, {{ $program->id }}, {{ $module->id }}, {{ $class->id }})"
                                            class="text-red-600 hover:text-red-900 mr-3">
                                        <svg class="h-4 w-4 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        Revocar
                                    </button>
                                @else
                                    <button onclick="grantLicenseForAbsent({{ $inscription->id }}, '{{ $inscription->getFullName() }}', {{ $program->id }}, {{ $module->id }}, {{ $class->id }})"
                                            class="text-indigo-600 hover:text-indigo-900">
                                        <svg class="h-4 w-4 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                        </svg>
                                        Otorgar Licencia
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Sección de Invitados -->
    @php
        $guestAttendances = $attendances->where('inscription_id', null);
    @endphp
    @if($guestAttendances->count() > 0)
    <div class="bg-white shadow-md rounded-lg overflow-hidden mt-6">
        <div class="px-6 py-4 border-b border-gray-200 bg-blue-50">
            <h2 class="text-xl font-semibold text-blue-800">
                👥 Invitados ({{ $guestAttendances->count() }})
            </h2>
            <p class="text-sm text-blue-600 mt-1">Participantes externos registrados en la sesión</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Nombre
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Email
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Duración
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Estado
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Hora de Ingreso
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($guestAttendances as $guest)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $guest->name ?? 'Nombre no disponible' }}
                                    </div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        👥 Invitado
                                    </span>
                                </div>
                            </div>
                        </td>
                        
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $guest->email ?? 'No disponible' }}
                        </td>
                        
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $guest->duration }} minutos</div>
                            <div class="text-xs text-gray-500">
                                @if($guest->duration >= 60)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Completo
                                    </span>
                                @elseif($guest->duration >= 30)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        Parcial
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Mínimo
                                    </span>
                                @endif
                            </div>
                        </td>
                        
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($guest->status == 'present')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    ✓ Presente
                                </span>
                            @elseif($guest->status == 'late')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    ⏰ Tarde
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    ✗ Ausente
                                </span>
                            @endif
                        </td>
                        
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $guest->formatted_join_time }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>

<!-- Incluir los modales -->
@include('attendances.license_modals')
@endsection