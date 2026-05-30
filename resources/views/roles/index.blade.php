<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">Roles</h2>
    </x-slot>

    <div>
        <div class="w-full sm:px-6 lg:px-8">

            @can('role.create')
            <div class="flex items-center justify-end gap-2 mb-4">
                <a href="{{ route('roles.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Nuevo Rol
                </a>
            </div>
            @endcan

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead>
                            <tr class="bg-gray-800 text-white text-xs">
                                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Rol</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Permisos</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Usuarios</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-white uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($roles as $role)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-indigo-100">
                                            <svg class="h-4 w-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                            </svg>
                                        </span>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900">{{ ucfirst($role->name) }}</p>
                                            @if($role->name === 'admin')
                                                <span class="text-xs text-indigo-600 font-medium">Sistema</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        {{ $role->permissions_count }} permiso(s)
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ $role->users_count }} usuario(s)
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <div class="inline-flex items-center justify-end gap-3">
                                        <a href="{{ route('roles.show', $role) }}" class="text-indigo-600 hover:text-indigo-900" title="Ver detalles">
                                            <x-action-icons action="view" />
                                        </a>
                                        @can('role.edit')
                                        <a href="{{ route('roles.edit', $role) }}" class="text-yellow-600 hover:text-yellow-900" title="Editar rol">
                                            <x-action-icons action="edit" />
                                        </a>
                                        @endcan
                                        @can('role.delete')
                                        @if($role->name !== 'admin')
                                        <form method="POST" action="{{ route('roles.destroy', $role) }}" class="inline"
                                              onsubmit="return confirm('¿Eliminar el rol \"{{ $role->name }}\"? Esta acción no se puede deshacer.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900" title="Eliminar rol">
                                                <x-action-icons action="delete" />
                                            </button>
                                        </form>
                                        @endif
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-sm text-gray-500">
                                    No hay roles registrados.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
