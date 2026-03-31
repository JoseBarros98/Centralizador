<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-white leading-tight">
                {{ __('Programas') }}
            </h2>
        </div>
    </x-slot>

    <div >
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Barra de búsqueda y filtros -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="GET" action="{{ route('programs.index') }}">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                            <!-- Campo de búsqueda -->
                            <div class="md:col-span-2">
                                <x-label for="search" :value="__('Buscar')" />
                                <input type="text" name="search" id="search" value="{{ request('search') }}" 
                                    class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                                    placeholder="Buscar por nombre, código o código contable">
                            </div>

                            <!-- Filtro por Estado -->
                            <div>
                                <x-label for="status_filter" :value="__('Estado')" />
                                <input type="text" name="status_filter" id="status_filter" value="{{ request('status_filter') }}" 
                                    class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                                    placeholder="Filtrar por estado">
                            </div>

                            <!-- Filtro por Gestión -->
                            <div>
                                <x-label for="year_filter" :value="__('Gestión')" />
                                <select name="year_filter" id="year_filter" class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="">Todas las gestiones</option>
                                    @for($i = date('Y') + 2; $i >= 2020; $i--)
                                        <option value="{{ $i }}" {{ (request('year_filter', $yearFilter ?? date('Y')) == $i) ? 'selected' : '' }}>{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
                            <!-- Botones de acción -->
                            <div class="flex space-x-2">
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                    Buscar
                                </button>
                                <a href="{{ route('programs.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    Limpiar
                                </a>
                            </div>

                            <div class="flex flex-col sm:flex-row gap-2">
                                <button
                                    type="submit"
                                    form="sync-programs-form"
                                    class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700"
                                    onclick="return confirm('Se sincronizarán los programas. ¿Deseas continuar?')"
                                >
                                    Sincronizar Programas
                                </button>

                                <button
                                    type="submit"
                                    form="sync-modules-form"
                                    class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-teal-700"
                                    onclick="return confirm('Se sincronizarán los módulos. ¿Deseas continuar?')"
                                >
                                    Sincronizar Módulos
                                </button>
                            </div>
                        </div>
                    </form>

                    <form id="sync-programs-form" action="{{ route('admin.sync.programs') }}" method="POST" class="hidden">
                        @csrf
                        <input type="hidden" name="redirect_to" value="{{ route('programs.index') }}">
                    </form>

                    <form id="sync-modules-form" action="{{ route('admin.sync.modules') }}" method="POST" class="hidden">
                        @csrf
                        <input type="hidden" name="redirect_to" value="{{ route('programs.index') }}">
                    </form>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código</th>
                                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Programa</th>
                                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fechas</th>
                                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                    <th scope="col" class="px-2 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <div>Inscritos</div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($programs as $program)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-3 py-3 text-xs text-gray-900">
                                            <div class="font-mono">{{ $program->code }}</div>
                                            @if($program->accounting_code)
                                                <div class="text-gray-500 text-xs mt-1">{{ $program->accounting_code }}</div>
                                            @endif
                                        </td>
                                        <td class="px-3 py-3">
                                            <div class="max-w-md">
                                                <a href="{{ route('programs.show', $program) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900 hover:underline line-clamp-2">
                                                    {{ $program->name }}
                                                </a>
                                            </div>
                                        </td>
                                        <td class="px-3 py-3 text-xs text-gray-500">
                                            @if($program->registration_date)
                                                <div class="text-blue-600">M: {{ $program->registration_date->format('d/m/y') }}</div>
                                            @endif
                                            @if($program->start_date)
                                                <div>I: {{ $program->start_date->format('d/m/y') }}</div>
                                            @endif
                                            @if($program->finalization_date)
                                                <div>F: {{ $program->finalization_date->format('d/m/y') }}</div>
                                            @endif
                                            @if(!$program->start_date && !$program->finalization_date && !$program->registration_date)
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-3">
                                            <span class="px-2 py-1 inline-flex text-xs leading-tight font-semibold rounded
                                                {{ in_array($program->status, ['INSCRIPCION']) ? 'bg-blue-100 text-blue-800' : '' }}
                                                {{ $program->status == 'DESARROLLO' ? 'bg-green-100 text-green-800' : '' }}
                                                {{ $program->status == 'NO APROBADO' ? 'bg-red-100 text-red-800' : '' }}
                                                {{ $program->status == 'CONCLUIDO' ? 'bg-gray-100 text-gray-800' : '' }}
                                                {{ $program->status == 'ARMADO CARPETAS' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                                {{ $program->status == 'RECEPCION DE REQUISITOS DE TITULACION' ? 'bg-cyan-100 text-cyan-800' : '' }}
                                                {{ $program->status == 'ELABORACION DE TRABAJOS FINALES' ? 'bg-pink-100 text-pink-800' : '' }}
                                                {{ $program->status == 'PROCESO DE TITULACION' ? 'bg-indigo-100 text-indigo-800' : '' }}
                                                {{ $program->status == 'NO EJECUTADO' ? 'bg-orange-100 text-orange-800' : '' }}">
                                                {{ Str::limit($program->status ?? 'N/A', 20) }}
                                            </span>
                                        </td>
                                        <td class="px-2 py-3 text-center text-xs">
                                            <div class="flex flex-col items-center gap-1">
                                                <div class="flex items-center gap-1">
                                                    <span class="bg-blue-100 text-blue-800 font-semibold px-1.5 py-0.5 rounded" title="Inscritos">
                                                        I: {{ $program->registered_count }}
                                                    </span>
                                                    <span class="bg-yellow-100 text-yellow-800 font-semibold px-1.5 py-0.5 rounded" title="Preinscritos">
                                                        P: {{ $program->preregistered_count }}
                                                    </span>
                                                </div>
                                                @if($program->withdrawn_count > 0)
                                                    <span class="bg-red-100 text-red-800 font-semibold px-1.5 py-0.5 rounded" title="Retirados">
                                                        R: {{ $program->withdrawn_count }}
                                                    </span>
                                                @endif
                                                <div class="text-gray-500 font-medium">
                                                    = {{ $program->total_records }}
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500">
                                            <div class="flex flex-col items-center">
                                                <svg class="w-12 h-12 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                No hay programas para mostrar
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        {{ $programs->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
