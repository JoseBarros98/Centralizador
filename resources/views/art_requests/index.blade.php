<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-white leading-tight">
                {{ __('Solicitudes de Arte') }}
            </h2>
        </div>
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filtros -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="GET" action="{{ route('art_requests.index') }}" class="space-y-4">
                        <!-- Campo de búsqueda y botón Nueva Solicitud -->
                        <div class="flex flex-col md:flex-row md:items-end md:justify-between w-full gap-4">
                            <div class="w-full md:w-1/2">
                                <x-label for="search" :value="__('Buscar')" />
                                <div class="mt-1 flex rounded-md shadow-sm">
                                    <input type="text" name="search" id="search" value="{{ request('search') }}" 
                                        class="flex-1 rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                                        placeholder="Buscar por título, descripción o solicitante">
                                    <button type="submit" class="ml-2 inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                        Buscar
                                    </button>
                                </div>
                            </div>
                            
                            @can('content.create')
                            <div>
                                <a href="{{ route('art_requests.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:border-indigo-800 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                    Nueva Solicitud
                                </a>
                            </div>
                            @endcan
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                            <div>
                                <x-label for="status" :value="__('Estado')" />
                                <select id="status" name="status" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">
                                    <option value="">Todos</option>
                                    <option value="NO INICIADO" {{ request('status') == 'NO INICIADO' ? 'selected' : '' }}>No Iniciado</option>
                                    <option value="EN CURSO" {{ request('status') == 'EN CURSO' ? 'selected' : '' }}>En Curso</option>
                                    <option value="COMPLETO" {{ request('status') == 'COMPLETO' ? 'selected' : '' }}>Completo</option>
                                    <option value="RETRASADO" {{ request('status') == 'RETRASADO' ? 'selected' : '' }}>Retrasado</option>
                                    <option value="ESPERANDO APROBACIÓN" {{ request('status') == 'ESPERANDO APROBACIÓN' ? 'selected' : '' }}>Esperando Aprobación</option>
                                    <option value="ESPERANDO INFORMACIÓN" {{ request('status') == 'ESPERANDO INFORMACIÓN' ? 'selected' : '' }}>Esperando Información</option>
                                    <option value="CANCELADO" {{ request('status') == 'CANCELADO' ? 'selected' : '' }}>Cancelado</option>
                                    <option value="EN PAUSA" {{ request('status') == 'EN PAUSA' ? 'selected' : '' }}>En Pausa</option>
                                </select>
                            </div>
                            
                            <div>
                                <x-label for="priority" :value="__('Prioridad')" />
                                <select id="priority" name="priority" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">
                                    <option value="">Todas</option>
                                    <option value="ALTA" {{ request('priority') == 'ALTA' ? 'selected' : '' }}>Alta</option>
                                    <option value="MEDIA" {{ request('priority') == 'MEDIA' ? 'selected' : '' }}>Media</option>
                                    <option value="BAJA" {{ request('priority') == 'BAJA' ? 'selected' : '' }}>Baja</option>
                                </select>
                            </div>
                            
                            <div>
                                <x-label for="designer_id" :value="__('Diseñador')" />
                                <select id="designer_id" name="designer_id" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">
                                    <option value="">Todos</option>
                                    @foreach($designers as $designer)
                                        <option value="{{ $designer->id }}" {{ request('designer_id') == $designer->id ? 'selected' : '' }}>
                                            {{ $designer->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div>
                                <x-label for="content_pillar_id" :value="__('Pilar de Contenido')" />
                                <select id="content_pillar_id" name="content_pillar_id" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">
                                    <option value="">Todos</option>
                                    @foreach($contentPillars as $pillar)
                                        <option value="{{ $pillar->id }}" {{ request('content_pillar_id') == $pillar->id ? 'selected' : '' }}>
                                            {{ $pillar->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <x-label for="type_of_art_id" :value="__('Tipo de Arte')" />
                                <select id="type_of_art_id" name="type_of_art_id" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">
                                    <option value="">Todos</option>
                                    @foreach($typeOfArts as $type)
                                        <option value="{{ $type->id }}" {{ request('type_of_art_id') == $type->id ? 'selected' : '' }}>
                                            {{ $type->name }}
                                        </option>
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
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Estadísticas de Solicitudes</h3>
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <div class="bg-gray-100 p-4 rounded-lg">
                            <p class="text-sm text-gray-600">Total Solicitudes</p>
                            <p class="text-2xl font-bold">{{ $stats['total'] }}</p>
                        </div>
                        <div class="bg-yellow-100 p-4 rounded-lg">
                            <p class="text-sm text-gray-600">Pendientes</p>
                            <p class="text-2xl font-bold">{{ $stats['pending'] }}</p>
                        </div>
                        <div class="bg-blue-100 p-4 rounded-lg">
                            <p class="text-sm text-gray-600">En Progreso</p>
                            <p class="text-2xl font-bold">{{ $stats['in_progress'] }}</p>
                        </div>
                        <div class="bg-green-100 p-4 rounded-lg">
                            <p class="text-sm text-gray-600">Completadas</p>
                            <p class="text-2xl font-bold">{{ $stats['completed'] }}</p>
                        </div>
                        <div class="bg-red-100 p-4 rounded-lg">
                            <p class="text-sm text-gray-600">Retrasadas</p>
                            <p class="text-2xl font-bold">{{ $stats['overdue'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Tabla de solicitudes -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Título</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Solicitante</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Diseñador</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prioridad</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Entrega</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($artRequests as $request)
                                    <tr class="{{ $request->isOverdue() ? 'bg-red-50' : '' }}">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            <div class="flex items-center">
                                                @if($request->isOverdue())
                                                    <svg class="h-5 w-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                    </svg>
                                                @endif
                                                <div>
                                                    {{ $request->title }}
                                                    @if($request->files->count() > 0)
                                                        <div class="text-xs text-gray-500">
                                                            <svg class="inline h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M8 4a3 3 0 00-3 3v4a5 5 0 0010 0V7a1 1 0 112 0v4a7 7 0 11-14 0V7a5 5 0 0110 0v4a3 3 0 11-6 0V7a1 1 0 012 0v4a1 1 0 102 0V7a3 3 0 00-3-3z" clip-rule="evenodd" />
                                                            </svg>
                                                            {{ $request->files->count() }} archivo(s)
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $request->requester->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $request->designer->name ?? 'Sin asignar' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $request->typeOfArt->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @php
                                                $statusColors = [
                                                    'NO INICIADO' => 'bg-gray-100 text-gray-800',
                                                    'EN CURSO' => 'bg-blue-100 text-blue-800',
                                                    'COMPLETO' => 'bg-green-100 text-green-800',
                                                    'RETRASADO' => 'bg-red-100 text-red-800',
                                                    'ESPERANDO APROBACIÓN' => 'bg-yellow-100 text-yellow-800',
                                                    'ESPERANDO INFORMACIÓN' => 'bg-purple-100 text-purple-800',
                                                    'CANCELADO' => 'bg-red-100 text-red-800',
                                                    'EN PAUSA' => 'bg-orange-100 text-orange-800',
                                                ];
                                            @endphp
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusColors[$request->status] ?? 'bg-gray-100 text-gray-800' }}">
                                                {{ $request->status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @php
                                                $priorityColors = [
                                                    'ALTA' => 'bg-red-100 text-red-800',
                                                    'MEDIA' => 'bg-yellow-100 text-yellow-800',
                                                    'BAJA' => 'bg-green-100 text-green-800',
                                                ];
                                            @endphp
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $priorityColors[$request->priority] ?? 'bg-gray-100 text-gray-800' }}">
                                                {{ $request->priority }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $request->delivery_date->format('d/m/Y') }}
                                            @if($request->isOverdue())
                                                <div class="text-xs text-red-500">
                                                    Vencida hace {{ $request->delivery_date->diffForHumans() }}
                                                </div>
                                            @endif
                                        </td>
                                        
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-3">
                                                <a href="{{ route('art_requests.show', $request) }}" 
                                                   class="text-indigo-600 hover:text-indigo-900"
                                                   title="Ver detalles">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                    <span class="sr-only">Ver</span>
                                                </a>
                                                @role(['admin', 'marketing', 'academic'])
                                                @can('content.edit')
                                                <a href="{{ route('art_requests.edit', $request) }}" 
                                                   class="text-yellow-600 hover:text-yellow-900"
                                                   title="Editar solicitud">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                    <span class="sr-only">Editar</span>
                                                </a>
                                                @endcan
                                                
                                                @can('content.delete')
                                                <form method="POST" action="{{ route('art_requests.destroy', $request) }}" class="inline" onsubmit="return confirm('¿Estás seguro de eliminar esta solicitud?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="text-red-600 hover:text-red-900"
                                                            title="Eliminar solicitud">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                        <span class="sr-only">Eliminar</span>
                                                    </button>
                                                </form>
                                                @endcan
                                                @endrole
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No hay solicitudes de arte para mostrar</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        {{ $artRequests->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
