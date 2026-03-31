<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semiblod text-xl text-white leading-tight">
                {{__('Docentes')}}
            </h2>
            {{-- @can('teacher.create') --}}
            <a href="{{ route('teachers.create')}}" class="inline-flex items-center px-4 py-2 bg-blue-600 bordes-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <svg xmls="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                {{ __('Nuevo Docente')}}
            </a>
            {{-- @endcan --}}
        </div>
    </x-slot>

    <div>
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6 bg-white border-b border-gray-200">
                <form method="GET" action="{{ route('teachers.index')}}">
                    <div class="flex flex-col md:flex-row md:items-end md:justify-between w-full gap-4">
                        <div class="w-full md:w-1/2">
                            <x-label for="search" :value="__('Buscar')"/>
                            <div class="mt-1 flex rounded-md shadow-sm">
                                <input type="text" name="search" id="search" value="{{ request('search')}}" class="flex-1 rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" placeholder="Buscar por nombre, profesión, títulos académicos (ej: 'administración', 'MAESTRÍA', 'ingenieria sistemas')">
                                <button type="submit" class="ml-2 inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                    Buscar
                                </button>
                            </div>
                            <div class="mt-2 text-xs text-gray-500">
                                <strong>Búsqueda flexible:</strong> "administración" encuentra "Maestría en Administración", "MAESTRIA" encuentra "Maestría", "ingenieria" encuentra "Ingeniería de Sistemas"
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    @if($teachers->isEmpty())
                        <div class="text-center py-4">
                            <p class="text-grat-500">No hay docentes registrados</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Nombre
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Telefono
                                        </th>
                                        
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Valoración Promedio
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Acciones
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($teachers as $teacher)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex flex-col">
                                                    <div class="text-sm text-gray-900 font-medium">{{ $teacher->academic_degree }} {{ $teacher->full_name }}</div>
                                                    
                                                    @php
                                                        // Obtener información académica de todos los archivos del docente
                                                        $academicTitles = collect();
                                                        foreach($teacher->files as $file) {
                                                            if($file->academic_info) {
                                                                // Manejar tanto array como string JSON
                                                                $info = is_array($file->academic_info) 
                                                                    ? $file->academic_info 
                                                                    : json_decode($file->academic_info, true);
                                                                    
                                                                if(is_array($info)) {
                                                                    foreach($info as $academic) {
                                                                        if(isset($academic['type']) && isset($academic['title'])) {
                                                                            $academicTitles->push([
                                                                                'type' => $academic['type'],
                                                                                'title' => $academic['title']
                                                                            ]);
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }
                                                        // Ordenar por tipo de grado (Doctorado > Maestría > Especialidad > Licenciatura > otros)
                                                        $typeOrder = ['Doctorado' => 1, 'Maestría' => 2, 'Maestria' => 2, 'Especialidad' => 3, 'Licenciatura' => 4, 'Diplomado' => 5];
                                                        $sortedTitles = $academicTitles->unique('title')->sortBy(function($item) use ($typeOrder) {
                                                            return $typeOrder[$item['type']] ?? 99;
                                                        });
                                                        $uniqueTitles = $sortedTitles->take(2);
                                                    @endphp
                                                    
                                                    @if($uniqueTitles->count() > 0)
                                                        <div class="mt-2 space-y-1">
                                                            @foreach($uniqueTitles as $academic)
                                                                <div class="flex items-start gap-2 text-xs">
                                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full font-semibold whitespace-nowrap
                                                                        @if($academic['type'] === 'Doctorado') bg-purple-100 text-purple-800
                                                                        @elseif($academic['type'] === 'Maestría' || $academic['type'] === 'Maestria') bg-blue-100 text-blue-800
                                                                        @elseif($academic['type'] === 'Especialidad') bg-orange-100 text-orange-800
                                                                        @elseif($academic['type'] === 'Licenciatura') bg-yellow-100 text-yellow-800
                                                                        @else bg-green-100 text-green-800 @endif">
                                                                        {{ $academic['type'] === 'Maestria' ? 'Maestría' : $academic['type'] }}
                                                                    </span>
                                                                    <span class="text-gray-700 truncate" title="{{ $academic['title'] }}">
                                                                        {{ Str::limit($academic['title'], 35) }}
                                                                    </span>
                                                                </div>
                                                            @endforeach
                                                            @if($academicTitles->count() > 2)
                                                                <div class="text-xs text-gray-500 font-medium mt-1">
                                                                    +{{ $academicTitles->count() - 2 }} títulos más
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @endif
                                                </div>
                                            </td>
                                            
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($teacher->phone)
                                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $teacher->phone) }}" 
                                                       target="_blank" 
                                                       class="text-green-600 hover:text-green-800 hover:underline inline-flex items-center" 
                                                       title="Enviar mensaje por WhatsApp">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 24 24">
                                                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.890-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488"/>
                                                        </svg>
                                                        <div class="text-sm text-gray-900">{{ $teacher->phone }}</div>
                                                    </a>
                                                @else
                                                    <div class="text-sm text-gray-400">Sin teléfono</div>
                                                @endif
                                            </td>
                                            
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                @php
                                                    $finishedModules = $teacher->modules->where('status', 'CONCLUIDO');
                                                    $ratedModules = $finishedModules->whereNotNull('teacher_rating');
                                                    $averageRating = $ratedModules->count() > 0 ? round($ratedModules->avg('teacher_rating'), 1) : null;
                                                @endphp
                                                
                                                @if($averageRating)
                                                    <div class="flex items-center justify-center">
                                                        <div class="flex items-center space-x-1">
                                                            @for($i = 1; $i <= 5; $i++)
                                                                <span class="text-sm {{ $i <= round($averageRating) ? 'text-yellow-400' : 'text-gray-300' }}">★</span>
                                                            @endfor
                                                        </div>
                                                        <span class="ml-2 text-sm text-gray-600 font-medium">{{ $averageRating }}</span>
                                                    </div>
                                                    <div class="text-xs text-gray-500 mt-1">
                                                        {{ $ratedModules->count() }} de {{ $finishedModules->count() }} módulos
                                                    </div>
                                                @elseif($finishedModules->count() > 0)
                                                    <div class="text-sm text-gray-500">
                                                        <div class="flex items-center justify-center space-x-1">
                                                            @for($i = 1; $i <= 5; $i++)
                                                                <span class="text-sm text-gray-300">★</span>
                                                            @endfor
                                                        </div>
                                                        <div class="text-xs text-gray-500 mt-1">Sin valorar</div>
                                                        <div class="text-xs text-gray-400">{{ $finishedModules->count() }} módulo{{ $finishedModules->count() != 1 ? 's' : '' }}</div>
                                                    </div>
                                                @else
                                                    <div class="text-sm text-gray-400">
                                                        <div class="text-center">Sin módulos finalizados</div>
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <div class="flex space-x-2">
                                                    {{-- @can('teacher.show') --}}
                                                        <a href="{{ route('teachers.show', $teacher) }}" class="text-blue-600 hover:text-blue-900" title="Ver">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                            </svg>
                                                        </a>
                                                    {{-- @endcan --}}

                                                    {{-- @can('teacher.edit') --}}
                                                        <a href="{{ route('teachers.edit', $teacher) }}" class="text-indigo-600 hover:text-indigo-900" title="Editar">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                            </svg>
                                                        </a>
                                                    {{-- @endcan --}}
                                                    @hasrole("admin")
                                                        <form action="{{ route('teachers.destroy', $teacher) }}" method="POST" class="inline-block">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="text-red-600 hover:text-red-900" title="Eliminar" onclick="return confirm('¿Estás seguro de eliminar este docente?')">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                                </svg>
                                                            </button>
                                                        </form>
                                                    @endhasrole
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">
                            {{ $teachers->links()}}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>