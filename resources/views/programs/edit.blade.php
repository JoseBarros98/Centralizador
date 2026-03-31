<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Editar Programa') }}
        </h2>
    </x-slot>

    <div >
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('programs.update', $program) }}">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Información básica -->
                            <div class="md:col-span-3">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Información Básica</h3>
                            </div>

                            <!-- Primera fila: Código del Programa, Categoría, Nombre del Programa -->
                            <div>
                                <x-label for="code" :value="__('Código del Programa')" />
                                <x-input id="code" class="block mt-1 w-full" type="text" name="code" :value="old('code', $program->code)" required autofocus />
                                @error('code')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <x-label for="category" :value="__('Categoría')" />
                                <select id="category" name="category" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full" required>
                                    <option value="">Seleccionar categoría</option>
                                    <option value="Doctorado" {{ old('category', $program->category) == 'Doctorado' ? 'selected' : '' }}>Doctorado</option>
                                    <option value="Maestria" {{ old('category', $program->category) == 'Maestria' ? 'selected' : '' }}>Maestría</option>
                                    <option value="Diplomado" {{ old('category', $program->category) == 'Diplomado' ? 'selected' : '' }}>Diplomado</option>
                                    <option value="Especialidad" {{ old('category', $program->category) == 'Especialidad' ? 'selected' : '' }}>Especialidad</option>
                                    <option value="Curso" {{ old('category', $program->category) == 'Curso' ? 'selected' : '' }}>Curso</option>
                                </select>
                                @error('category')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <x-label for="name" :value="__('Nombre del Programa')" />
                                <x-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $program->name)" required />
                                @error('name')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                                <p class="text-xs text-gray-500 mt-1">Solo inserte el nombre del programa sin la categoría</p>
                            </div>

                            <!-- Descripción -->
                            <div class="md:col-span-3">
                                <x-label for="description" :value="__('Descripción')" />
                                <textarea id="description" name="description" rows="3" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">{{ old('description', $program->description) }}</textarea>
                                @error('description')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Información académica -->
                            <div class="md:col-span-3 mt-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Información Académica</h3>
                            </div>
                            
                            <!-- Primera fila: Versión, Grupo, Gestión -->
                            <div>
                                <x-label for="version" :value="__('Versión')" />
                                <x-input id="version" class="block mt-1 w-full" type="number" name="version" :value="old('version', $program->version)" required min="1" />
                                @error('version')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <x-label for="group" :value="__('Grupo')" />
                                <x-input id="group" class="block mt-1 w-full" type="number" name="group" :value="old('group', $program->group)" required min="1" />
                                @error('group')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <x-label for="year" :value="__('Gestión')" />
                                <select id="year" name="year" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">
                                    <option value="">Seleccionar año</option>
                                    @for($i = date('Y') + 2; $i >= 2020; $i--)
                                        @php
                                            $yearValue = $i . '-01-01';
                                            $currentYear = $program->year ? $program->year->format('Y-m-d') : null;
                                        @endphp
                                        <option value="{{ $yearValue }}" {{ old('year', $currentYear) == $yearValue ? 'selected' : '' }}>{{ $i }}</option>
                                    @endfor
                                </select>
                                @error('year')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Segunda fila: Código Académico, Código Contable, Área -->
                            <div>
                                <x-label for="academic_code" :value="__('Código Académico')" />
                                <x-input id="academic_code" class="block mt-1 w-full" type="text" name="academic_code" :value="old('academic_code', $program->academic_code)" />
                                @error('academic_code')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <x-label for="accounting_code" :value="__('Código Contable')" />
                                <x-input id="accounting_code" class="block mt-1 w-full" type="text" name="accounting_code" :value="old('accounting_code', $program->accounting_code)" />
                                @error('accounting_code')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <x-label for="area" :value="__('Área')" />
                                <select id="area" name="area" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full" required>
                                    <option value="">Seleccionar área</option>
                                    <option value="Ciencias Empresariales" {{ old('area', $program->area) == 'Ciencias Empresariales' ? 'selected' : '' }}>Ciencias Empresariales</option>
                                    <option value="Ingenieria" {{ old('area', $program->area) == 'Ingenieria' ? 'selected' : '' }}>Ingeniería</option>
                                    <option value="Diseno" {{ old('area', $program->area) == 'Diseno' ? 'selected' : '' }}>Diseño</option>
                                    <option value="Salud" {{ old('area', $program->area) == 'Salud' ? 'selected' : '' }}>Salud</option>
                                    <option value="Social" {{ old('area', $program->area) == 'Social' ? 'selected' : '' }}>Social</option>
                                    <option value="Legal" {{ old('area', $program->area) == 'Legal' ? 'selected' : '' }}>Legal</option>
                                </select>
                                @error('area')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Fechas y estado -->
                            <div class="md:col-span-3 mt-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Fechas y Estado</h3>
                            </div>
                            
                            <!-- Primera fila: Fecha de Inicio, Fecha de Finalización, Estado -->
                            <div>
                                <x-label for="start_date" :value="__('Fecha de Inicio')" />
                                <x-input id="start_date" class="block mt-1 w-full" type="date" name="start_date" :value="old('start_date', $program->start_date?->format('Y-m-d'))" />
                                @error('start_date')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <x-label for="finalization_date" :value="__('Fecha de Finalización')" />
                                <x-input id="finalization_date" class="block mt-1 w-full" type="date" name="finalization_date" :value="old('finalization_date', $program->finalization_date?->format('Y-m-d'))" />
                                @error('finalization_date')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <x-label for="state" :value="__('Estado')" />
                                <select id="state" name="state" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full" required>
                                    <option value="">Seleccionar estado</option>
                                    <option value="No Aprobado" {{ old('state', $program->state) == 'No Aprobado' ? 'selected' : '' }}>No Aprobado</option>
                                    <option value="Aprobado" {{ old('state', $program->state) == 'Aprobado' ? 'selected' : '' }}>Aprobado</option>
                                    <option value="Inscripciones" {{ old('state', $program->state) == 'Inscripciones' ? 'selected' : '' }}>Inscripciones</option>
                                    <option value="Desarrollo" {{ old('state', $program->state) == 'Desarrollo' ? 'selected' : '' }}>Desarrollo</option>
                                    <option value="Finalizado" {{ old('state', $program->state) == 'Finalizado' ? 'selected' : '' }}>Finalizado</option>
                                </select>
                                @error('state')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <!-- Campo activo -->
                            <div class="md:col-span-3 mt-6">
                                <div class="flex items-center">
                                    <label for="active" class="inline-flex items-center">
                                        <input id="active" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" name="active" value="1" {{ old('active', $program->active) ? 'checked' : '' }}>
                                        <span class="ml-2 text-sm text-gray-600">{{ __('Programa Activo') }}</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('programs.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-400 active:bg-gray-500 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150 mr-3">
                                Cancelar
                            </a>
                            <x-button>
                                {{ __('Actualizar') }}
                            </x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
