@extends('layouts.app')

@section('header-title', 'Detalles de la Clase')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6">
    <div class="mb-6">
        <h2 class="text-2xl font-bold mb-2">Clase del {{ \Carbon\Carbon::parse($class->class_date)->format('d/m/Y') }}</h2>
        <p class="text-gray-600">
            <span class="font-semibold">Programa:</span> {{ $program->name }}
        </p>
        <p class="text-gray-600">
            <span class="font-semibold">Módulo:</span> {{ $module->name }}
        </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="bg-gray-50 p-4 rounded-lg">
            <h3 class="text-lg font-semibold mb-2">Información de la Clase</h3>
            <p><span class="font-medium">Fecha:</span> {{ \Carbon\Carbon::parse($class->class_date)->format('d/m/Y') }}</p>
            <p><span class="font-medium">Hora de inicio:</span> {{ \Carbon\Carbon::parse($class->start_time)->format('H:i') }}</p>
            <p><span class="font-medium">Hora de fin:</span> {{ \Carbon\Carbon::parse($class->end_time)->format('H:i') }}</p>
            <p><span class="font-medium">Duración:</span> 
                {{ \Carbon\Carbon::parse($class->start_time)->diffInMinutes(\Carbon\Carbon::parse($class->end_time)) }} minutos
            </p>
            @if($class->class_link)
            <p class="mt-2">
                <span class="font-medium">Enlace de la clase:</span> 
                <a href="{{ $class->class_link }}" target="_blank" class="text-blue-600 hover:underline">
                    {{ $class->class_link }}
                </a>
            </p>
            @endif
        </div>

        <div class="bg-gray-50 p-4 rounded-lg">
            <h3 class="text-lg font-semibold mb-2">Asistencia</h3>
            @if($class->attendance_file)
            <p class="text-green-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block mr-1" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                Asistencia registrada
            </p>
            <a href="{{ route('attendances.show', [$program->id, $module->id, $class->id]) }}" class="inline-block mt-2 bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded">
                Ver asistencia
            </a>
            @else
            <p class="text-yellow-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block mr-1" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                Asistencia no registrada
            </p>
            <a href="{{ route('attendances.upload', [$program->id, $module->id, $class->id]) }}" class="inline-block mt-2 bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded">
                Subir asistencia
            </a>
            @endif
        </div>
    </div>

    <div class="flex flex-wrap gap-2 mt-6">
        <a href="{{ route('programs.modules.show', [$program->id, $module->id]) }}" class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded">
            Volver al módulo
        </a>
        
        <a href="{{ route('programs.modules.classes.edit', [$program->id, $module->id, $class->id]) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white font-medium py-2 px-4 rounded">
            Editar clase
        </a>
        
        <form action="{{ route('programs.modules.classes.destroy', [$program->id, $module->id, $class->id]) }}" method="POST" class="inline-block">
            @csrf
            @method('DELETE')
            <button type="submit" class="bg-red-500 hover:bg-red-600 text-white font-medium py-2 px-4 rounded" onclick="return confirm('¿Estás seguro de que deseas eliminar esta clase?')">
                Eliminar clase
            </button>
        </form>
    </div>
</div>
@endsection
