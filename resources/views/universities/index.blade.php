<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Universidades') }}
        </h2>
    </x-slot>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <!-- Formulario de búsqueda -->
                <div class="mb-4 flex justify-between items-center">
                    <form method="GET" action="{{ route('universities.index') }}" class="flex-1 max-w-lg">
                        <div class="flex gap-2">
                            <input type="text" name="search" value="{{ request('search') }}" 
                                   placeholder="Buscar por siglas o nombre..." 
                                   class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                Buscar
                            </button>
                            @if(request('search'))
                                <a href="{{ route('universities.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Limpiar
                                </a>
                            @endif
                        </div>
                    </form>
                </div>

                <div class="flex justify-end mb-4">
                    <button onclick="openCreateModal()" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                        {{ __('Nueva Universidad') }}
                    </button>
                </div>

                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Siglas') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Nombre') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Estado') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Acciones') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($universities as $university)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $university->initials }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $university->name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if($university->active)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            {{ __('Activo') }}
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            {{ __('Inactivo') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end items-center space-x-3">
                                        <button onclick='openEditModal({{ json_encode($university) }})' 
                                                class="text-indigo-600 hover:text-indigo-900" 
                                                title="Editar">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <form action="{{ route('universities.toggleStatus', $university) }}" method="POST" class="inline-block">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" 
                                                    class="{{ $university->active ? 'text-red-600 hover:text-red-900' : 'text-green-600 hover:text-green-900' }}" 
                                                    title="{{ $university->active ? 'Desactivar' : 'Activar' }}">
                                                @if($university->active)
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                                    </svg>
                                                @else
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                @endif
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                    @if(request('search'))
                                        No se encontraron universidades que coincidan con "{{ request('search') }}".
                                    @else
                                        {{ __('No hay universidades registradas.') }}
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <!-- Paginación con parámetros de búsqueda -->
                @if($universities->hasPages())
                    <div class="mt-4">
                        {{ $universities->appends(request()->except('page'))->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modal para crear una nueva universidad -->
    <x-modal id="create-university-modal">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">
                {{ __('Nueva Universidad') }}
            </h2>

            <form method="POST" action="{{ route('universities.store') }}">
                @csrf

                <div class="mb-4">
                    <x-label for="initials" :value="__('Siglas')" />
                    <x-input id="initials" class="block mt-1 w-full" type="text" name="initials" required autofocus />
                    @error('initials')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="mb-4">
                    <x-label for="name" :value="__('Nombre')" />
                    <x-input id="name" class="block mt-1 w-full" type="text" name="name" required />
                    @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="flex justify-end">
                    <button type="button" onclick="closeModal('create-university-modal')" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-400 active:bg-gray-500 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150 mr-2">
                        {{ __('Cancelar') }}
                    </button>
                    <x-button>
                        {{ __('Guardar') }}
                    </x-button>
                </div>
            </form>
        </div>
    </x-modal>

    <!-- Modal para editar una universidad -->
    <x-modal id="edit-university-modal">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">
                {{ __('Editar Universidad') }}
            </h2>

            <form method="POST" id="edit-university-form" action="">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <x-label for="edit-initials" :value="__('Siglas')" />
                    <x-input id="edit-initials" class="block mt-1 w-full" type="text" name="initials" required autofocus />
                    @error('initials')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="mb-4">
                    <x-label for="edit-name" :value="__('Nombre')" />
                    <x-input id="edit-name" class="block mt-1 w-full" type="text" name="name" required />
                    @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="flex justify-end">
                    <button type="button" onclick="closeModal('edit-university-modal')" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-400 active:bg-gray-500 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150 mr-2">
                        {{ __('Cancelar') }}
                    </button>
                    <x-button>
                        {{ __('Actualizar') }}
                    </x-button>
                </div>
            </form>
        </div>
    </x-modal>

    <script>
        function openCreateModal() {
            // Limpiar el formulario
            document.getElementById('initials').value = '';
            document.getElementById('name').value = '';
            
            // Disparar evento para abrir el modal
            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'create-university-modal' }));
        }

        function openEditModal(university) {
            // Llenar el formulario con los datos
            document.getElementById('edit-initials').value = university.initials;
            document.getElementById('edit-name').value = university.name;
            document.getElementById('edit-university-form').action = `/universities/${university.id}`;
            
            // Disparar evento para abrir el modal
            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'edit-university-modal' }));
        }

        function closeModal(modalId) {
            window.dispatchEvent(new CustomEvent('close-modal', { detail: modalId }));
        }
    </script>
</x-app-layout>