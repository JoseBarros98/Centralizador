<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">Nuevo Rol</h2>
    </x-slot>

    <div>
        <div class="w-full sm:px-6 lg:px-8">

            <div class="flex items-center justify-end gap-2 mb-4">
                <a href="{{ route('roles.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:outline-none transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                    </svg>
                    Volver
                </a>
            </div>

            <form method="POST" action="{{ route('roles.store') }}">
                @csrf

                {{-- Nombre del rol --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-base font-semibold text-gray-900 mb-4">Información del Rol</h3>
                        <div class="max-w-sm">
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                Nombre del Rol <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="name" name="name" value="{{ old('name') }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('name') border-red-300 @enderror"
                                   placeholder="ej. supervisor, coordinador...">
                            @error('name')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Permisos --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
                            <h3 class="text-base font-semibold text-gray-900">Permisos</h3>
                            <div class="flex gap-3">
                                <button type="button" onclick="setAll(true)"
                                        class="text-xs text-indigo-600 hover:text-indigo-800 font-medium underline">
                                    Seleccionar todos
                                </button>
                                <button type="button" onclick="setAll(false)"
                                        class="text-xs text-gray-500 hover:text-gray-700 font-medium underline">
                                    Limpiar todos
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                            @foreach($groupedPermissions as $module => $group)
                            <div class="border border-gray-200 rounded-lg overflow-hidden">
                                <div class="bg-gray-50 px-4 py-2.5 flex items-center justify-between border-b border-gray-200">
                                    <span class="text-xs font-semibold text-gray-700 uppercase tracking-wider">{{ $group['label'] }}</span>
                                    <label class="flex items-center gap-1.5 cursor-pointer" title="Seleccionar todos en {{ $group['label'] }}">
                                        <input type="checkbox"
                                               class="module-toggle rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                               data-module="{{ $module }}"
                                               onchange="toggleModule('{{ $module }}', this.checked)">
                                        <span class="text-xs text-gray-500">Todos</span>
                                    </label>
                                </div>
                                <div class="p-3 space-y-2">
                                    @foreach($group['permissions'] as $permission)
                                    <label class="flex items-center gap-2.5 cursor-pointer group">
                                        <input type="checkbox"
                                               name="permissions[]"
                                               value="{{ $permission['name'] }}"
                                               class="perm-check perm-{{ $module }} rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                               {{ $permission['checked'] || old('permissions') && in_array($permission['name'], old('permissions', [])) ? 'checked' : '' }}
                                               onchange="syncModuleToggle('{{ $module }}')">
                                        <span class="text-sm text-gray-700 group-hover:text-gray-900">{{ $permission['action_label'] }}</span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @error('permissions')
                            <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('roles.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 focus:outline-none transition">
                        Cancelar
                    </a>
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">
                        Crear Rol
                    </button>
                </div>
            </form>

        </div>
    </div>

    @push('scripts')
    <script>
        function toggleModule(module, checked) {
            document.querySelectorAll(`.perm-${module}`).forEach(cb => cb.checked = checked);
        }

        function syncModuleToggle(module) {
            const all = document.querySelectorAll(`.perm-${module}`);
            const checked = document.querySelectorAll(`.perm-${module}:checked`);
            const toggle = document.querySelector(`[data-module="${module}"]`);
            if (!toggle) return;
            toggle.checked = all.length === checked.length;
            toggle.indeterminate = checked.length > 0 && checked.length < all.length;
        }

        function setAll(checked) {
            document.querySelectorAll('.perm-check').forEach(cb => cb.checked = checked);
            document.querySelectorAll('.module-toggle').forEach(cb => {
                cb.checked = checked;
                cb.indeterminate = false;
            });
        }

        // Init indeterminate state on load
        document.querySelectorAll('.module-toggle').forEach(toggle => {
            syncModuleToggle(toggle.dataset.module);
        });
    </script>
    @endpush
</x-app-layout>
