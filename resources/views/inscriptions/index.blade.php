<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-white leading-tight">
                {{ __('Inscripciones') }}
            </h2>
        </div>
    </x-slot>

    <div >
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filtros -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="GET" action="{{ route('inscriptions.index') }}" class="space-y-4">
                        <!-- Campo de búsqueda y botón Nueva Inscripción -->
                        <div class="flex flex-col md:flex-row md:items-end md:justify-between w-full gap-4">
                            <div class="w-full md:w-1/2">
                                <x-label for="search" :value="__('Buscar')" />
                                <div class="mt-1 flex rounded-md shadow-sm">
                                    <input type="text" name="search" id="search" value="{{ request('search') }}" 
                                        class="flex-1 rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                                        placeholder="Buscar por código, nombre, apellidos o CI">
                                    <button type="submit" class="ml-2 inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                        Buscar
                                    </button>
                                </div>
                            </div>
                            
                            @can('inscription.create')
                            <div>
                                <a href="{{ route('inscriptions.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:border-indigo-800 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                    Nueva Inscripción
                                </a>
                            </div>
                            @endcan
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                            <div>
                                <x-label for="month" :value="__('Mes')" />
                                <select id="month" name="month" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">
                                    <option value="all" {{ request('month') == 'all' ? 'selected' : '' }}>Todos los meses</option>
                                    @php
                                        // Establecer la localización en español para Carbon
                                        \Carbon\Carbon::setLocale('es');
                                    @endphp
                                    @for ($i = 1; $i <= 12; $i++)
                                        <option value="{{ $i }}" {{ request('month', date('n')) == $i && request('month') != 'all' ? 'selected' : '' }}>
                                            {{ ucfirst(\Carbon\Carbon::createFromDate(null, $i, 1)->translatedFormat('F')) }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                            
                            <div>
                                <x-label for="year" :value="__('Año')" />
                                <select id="year" name="year" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">
                                    <option value="all" {{ request('year') == 'all' ? 'selected' : '' }}>Todos los años</option>
                                    @for ($i = date('Y'); $i >= date('Y') - 5; $i--)
                                        <option value="{{ $i }}" {{ request('year', date('Y')) == $i && request('year') != 'all' ? 'selected' : '' }}>
                                            {{ $i }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                            
                            <div>
                                <x-label for="status" :value="__('Estado')" />
                                <select id="status" name="status" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">
                                    <option value="">Todos</option>
                                    <option value="Completo" {{ request('status') == 'Completo' ? 'selected' : '' }}>Completo</option>
                                    <option value="Completando" {{ request('status') == 'Completando' ? 'selected' : '' }}>Completando</option>
                                    <option value="Adelanto" {{ request('status') == 'Adelanto' ? 'selected' : '' }}>Adelanto</option>
                                </select>
                            </div>
                            
                            <div>
                                <x-label for="program_id" :value="__('Programa')" />
                                <select id="program_id" name="program_id" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">
                                    <option value="">Todos</option>
                                    @foreach($programs as $id => $name)
                                        <option value="{{ $id }}" {{ request('program_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <!-- Filtro por usuario que creó la inscripción -->
                            <div>
                                <x-label for="created_by" :value="__('Asesor')" />
                                <select id="created_by" name="created_by" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">
                                    <option value="">Todos</option>
                                    @foreach($creators as $id => $name)
                                        <option value="{{ $id }}" {{ request('created_by') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <div class="flex justify-end">
                            <x-button>
                                {{ __('Filtrar') }}
                            </x-button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Estadísticas -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    {{-- <h3 class="text-lg font-medium text-gray-900 mb-4">{{ $statsTitle ?? 'Estadísticas' }}</h3> --}}
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <div class="bg-gray-100 p-4 rounded-lg">
                            <p class="text-sm text-gray-600">Total Inscritos</p>
                            <p class="text-2xl font-bold">{{ $stats['total'] }}</p>
                        </div>
                        <div class="bg-green-100 p-4 rounded-lg">
                            <p class="text-sm text-gray-600">Completos</p>
                            <p class="text-2xl font-bold">{{ $stats['completo'] }}</p>
                        </div>
                        <div class="bg-yellow-100 p-4 rounded-lg">
                            <p class="text-sm text-gray-600">Completando</p>
                            <p class="text-2xl font-bold">{{ $stats['completando'] }}</p>
                        </div>
                        <div class="bg-blue-100 p-4 rounded-lg">
                            <p class="text-sm text-gray-600">Adelantos</p>
                            <p class="text-2xl font-bold">{{ $stats['adelanto'] }}</p>
                        </div>
                        <div class="bg-purple-100 p-4 rounded-lg">
                            <p class="text-sm text-gray-600">Total Pagado (Bs)</p>
                            <p class="text-2xl font-bold">{{ number_format($stats['total_paid'], 2) }}</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Tabla de inscripciones -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CI</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Programa</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Creador</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Pagado</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($inscriptions as $inscription)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $inscription->code }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $inscription->inscription_date->format('d/m/Y') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $inscription->first_name }} {{ $inscription->paternal_surname }} {{ $inscription->maternal_surname }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $inscription->ci }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $inscription->program->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $inscription->creator->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($inscription->status == 'Completo')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    Completo
                                                </span>
                                            @elseif($inscription->status == 'Completando')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                    Completando
                                                </span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                    Adelanto
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($inscription->total_paid, 2) }} Bs</td>
                                        
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-3">
                                                <a href="{{ route('inscriptions.show', $inscription) }}" 
                                                   class="text-indigo-600 hover:text-indigo-900"
                                                   title="Ver detalles">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                    <span class="sr-only">Ver</span>
                                                </a>
                                                
                                                @can('inscription.edit')
                                                    @if(auth()->user()->id === $inscription->created_by)
                                                        <a href="{{ route('inscriptions.edit', $inscription) }}" 
                                                        class="text-yellow-600 hover:text-yellow-900"
                                                        title="Editar inscripción">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                            </svg>
                                                            <span class="sr-only">Editar</span>
                                                        </a>
                                                    @endif
                                                @endcan
                                                
                                                @can('inscription.delete')
                                                @if(auth()->user()->id === $inscription->created_by)
                                                <form method="POST" action="{{ route('inscriptions.destroy', $inscription) }}" class="inline" onsubmit="return confirm('¿Estás seguro de eliminar esta inscripción?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="text-red-600 hover:text-red-900"
                                                            title="Eliminar inscripción">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                        <span class="sr-only">Eliminar</span>
                                                    </button>
                                                </form>
                                                @endif
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No hay inscripciones para mostrar</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        {{ $inscriptions->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
