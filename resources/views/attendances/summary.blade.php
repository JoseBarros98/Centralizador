<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-white leading-tight">
                {{ __('Resumen de Asistencia') }}: {{ $module->name }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('attendances.summary.pdf', [$program->id, $module->id]) }}" class="inline-flex items-center px-4 py-2 bg-red-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-600 active:bg-red-700 focus:outline-none focus:border-red-700 focus:ring ring-red-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v3.586l-1.293-1.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V8z" clip-rule="evenodd" />
                    </svg>
                    {{ __('Exportar como PDF') }}
                </a>
                <a href="{{ route('programs.modules.show', [$program->id, $module->id]) }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-400 active:bg-gray-500 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                    {{ __('Volver al Módulo') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Resumen de Asistencia por Inscrito') }}</h3>
                    
                    <div class="mb-4 p-4 bg-blue-50 border-l-4 border-blue-400 text-blue-700">
                        <p class="font-medium">Criterios de asistencia:</p>
                        <ul class="list-disc list-inside ml-4 mt-1">
                            <li>Duración menor a 30 minutos: Se considera falta</li>
                            <li>Entre 30 y 59 minutos: Asistencia parcial (tarde)</li>
                            <li>60 minutos o más: Asistencia completa (presente)</li>
                        </ul>
                    </div>
                    
                    @if(count($classes) === 0)
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700">
                                        {{ __('No hay clases registradas para este módulo.') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @elseif(count($inscriptions) === 0)
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700">
                                        {{ __('No hay inscritos registrados para este programa.') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Inscrito') }}</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Documento') }}</th>
                                        @foreach($classes as $class)
                                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                {{ $class->class_date->format('d/m') }}<br>
                                                <span class="text-xs font-normal">{{ $class->start_time->format('H:i') }}</span>
                                            </th>
                                        @endforeach
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Asistencia') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($attendanceMatrix as $row)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $row['inscription']->getFullName() }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $row['inscription']->ci }}
                                            </td>
                                            @foreach($classes as $class)
                                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                                    @if(isset($row['classes'][$class->id]['status']))
                                                        @if($row['classes'][$class->id]['status'] === 'present')
                                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-green-100 text-green-800" title="{{ __('Asistencia completa') }} ({{ $row['classes'][$class->id]['duration'] }} min)">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                                </svg>
                                                            </span>
                                                        @elseif($row['classes'][$class->id]['status'] === 'late')
                                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-yellow-100 text-yellow-800" title="{{ __('Asistencia parcial') }} ({{ $row['classes'][$class->id]['duration'] }} min)">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                </svg>
                                                            </span>
                                                        @elseif($row['classes'][$class->id]['status'] === 'absent')
                                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-red-100 text-red-800" title="{{ __('Ausente') }} ({{ $row['classes'][$class->id]['duration'] }} min < 30 min)">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                                </svg>
                                                            </span>
                                                        @endif
                                                    @else
                                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-red-100 text-red-800" title="{{ __('No asistió') }}">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                            </svg>
                                                        </span>
                                                    @endif
                                                </td>
                                            @endforeach
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                                <div class="flex flex-col items-center">
                                                    <div class="text-sm font-medium">
                                                        {{ $row['stats']['attended'] }}/{{ $row['stats']['total'] }}
                                                    </div>
                                                    <div class="w-full bg-gray-200 rounded-full h-2.5 mt-1">
                                                        <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $row['stats']['percentage'] }}%"></div>
                                                    </div>
                                                    <div class="text-xs text-gray-500 mt-1">
                                                        {{ round($row['stats']['percentage']) }}%
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
