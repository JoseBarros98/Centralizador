<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">Rol: {{ ucfirst($role->name) }}</h2>
    </x-slot>

    <div>
        <div class="w-full sm:px-6 lg:px-8">

            <div class="flex items-center justify-end gap-2 mb-4">
                @can('role.edit')
                <a href="{{ route('roles.edit', $role) }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 focus:outline-none transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                    </svg>
                    Editar
                </a>
                @endcan
                <a href="{{ route('roles.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:outline-none transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                    </svg>
                    Volver
                </a>
            </div>

            {{-- Info general --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex flex-wrap items-center gap-4">
                        <div class="flex items-center justify-center h-14 w-14 rounded-full bg-indigo-100 flex-shrink-0">
                            <svg class="h-7 w-7 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">{{ ucfirst($role->name) }}</h3>
                            <div class="flex flex-wrap gap-3 mt-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    {{ $role->permissions->count() }} permiso(s)
                                </span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ $usersCount }} usuario(s)
                                </span>
                                @if($role->name === 'admin')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">
                                    Rol de sistema
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Permisos por módulo --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-base font-semibold text-gray-900 mb-5">Permisos asignados</h3>

                    @if($role->permissions->isEmpty())
                        <p class="text-sm text-gray-500">Este rol no tiene permisos asignados.</p>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                            @foreach($groupedPermissions as $module => $group)
                                @php $assigned = collect($group['permissions'])->where('checked', true); @endphp
                                @if($assigned->isNotEmpty())
                                <div class="border border-gray-200 rounded-lg overflow-hidden">
                                    <div class="bg-indigo-50 px-4 py-2.5 border-b border-indigo-100">
                                        <span class="text-xs font-semibold text-indigo-700 uppercase tracking-wider">{{ $group['label'] }}</span>
                                    </div>
                                    <div class="p-3 space-y-1.5">
                                        @foreach($assigned as $permission)
                                        <div class="flex items-center gap-2">
                                            <svg class="h-3.5 w-3.5 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                            </svg>
                                            <span class="text-sm text-gray-700">{{ $permission['action_label'] }}</span>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
