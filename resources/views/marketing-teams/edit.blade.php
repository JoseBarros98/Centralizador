<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-white leading-tight">
                {{ __('Editar Equipo de Marketing') }}
            </h2>
        </div>
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Breadcrumb -->
            <div class="mb-6">
                <nav class="flex" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a href="{{ route('marketing-teams.index') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                                <svg class="w-3 h-3 mr-2.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="m19.707 9.293-2-2-7-7a1 1 0 0 0-1.414 0l-7 7-2 2a1 1 0 0 0 1.414 1.414L2 10.414V18a2 2 0 0 0 2 2h3a1 1 0 0 0 1-1v-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v4a1 1 0 0 0 1 1h3a2 2 0 0 0 2-2v-7.586l.293.293a1 1 0 0 0 1.414-1.414Z"/>
                                </svg>
                                Equipos de Marketing
                            </a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-3 h-3 text-gray-400 mx-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                                <a href="{{ route('marketing-teams.show', $marketingTeam) }}" class="text-sm font-medium text-gray-700 hover:text-blue-600">{{ $marketingTeam->name }}</a>
                            </div>
                        </li>
                        <li aria-current="page">
                            <div class="flex items-center">
                                <svg class="w-3 h-3 text-gray-400 mx-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                                <span class="text-sm font-medium text-gray-500">Editar</span>
                            </div>
                        </li>
                    </ol>
                </nav>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Formulario principal -->
                <div class="lg:col-span-2">
                    <x-card>
                        <x-slot name="header">
                            <h3 class="text-lg font-medium text-gray-900">Información del Equipo</h3>
                            <p class="mt-1 text-sm text-gray-600">Edita la información básica del equipo de marketing</p>
                        </x-slot>

                        <form method="POST" action="{{ route('marketing-teams.update', $marketingTeam) }}" class="space-y-6">
                            @csrf
                            @method('PUT')

                            <div>
                                <x-label for="name" :value="__('Nombre del Equipo')" />
                                <x-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $marketingTeam->name)" required autofocus />
                                @error('name')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <x-label for="leader_id" :value="__('Líder del Equipo')" />
                                <select id="leader_id" name="leader_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                    <option value="">Seleccionar líder...</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ old('leader_id', $marketingTeam->leader_id) == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }} ({{ $user->email }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('leader_id')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <x-label for="description" :value="__('Descripción')" />
                                <textarea id="description" name="description" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" placeholder="Descripción del equipo y sus responsabilidades...">{{ old('description', $marketingTeam->description) }}</textarea>
                                @error('description')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex items-center">
                                <input id="active" type="checkbox" name="active" value="1" {{ old('active', $marketingTeam->active) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <x-label for="active" :value="__('Equipo Activo')" class="ml-2" />
                                @error('active')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <p class="text-sm text-gray-600">Los equipos inactivos no aparecerán en las opciones de selección.</p>

                            <div class="flex items-center justify-between pt-4">
                                <a href="{{ route('marketing-teams.show', $marketingTeam) }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                    </svg>
                                    Cancelar
                                </a>
                                <x-primary-button type="submit">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    {{ __('Actualizar Equipo') }}
                                </x-primary-button>
                            </div>
                        </form>
                    </x-card>
                </div>

                <!-- Panel lateral con información -->
                <div class="lg:col-span-1">
                    <x-card>
                        <x-slot name="header">
                            <h3 class="text-lg font-medium text-gray-900">Información Actual</h3>
                        </x-slot>

                        <div class="space-y-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Creado</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $marketingTeam->created_at->format('d/m/Y H:i') }}</dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-gray-500">Última actualización</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $marketingTeam->updated_at->format('d/m/Y H:i') }}</dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-gray-500">Estadísticas</dt>
                                <dd class="mt-2">
                                    <div class="grid grid-cols-1 gap-4">
                                        <div class="text-center">
                                            <div class="text-2xl font-bold text-indigo-600">{{ $marketingTeam->members()->count() }}</div>
                                            <div class="text-xs text-gray-500">Miembros</div>
                                        </div>
                                    </div>
                                </dd>
                            </div>

                            <div class="border-t border-gray-200 pt-4">
                                <div class="flex items-start space-x-2">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-700">
                                            Al cambiar el líder, el nuevo usuario tendrá acceso a todas las metas y reportes del equipo.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </x-card>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
