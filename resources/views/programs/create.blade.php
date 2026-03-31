<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Crear Nuevo Programa') }}
        </h2>
    </x-slot>

    <div >
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('programs.store') }}">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Información básica -->
                            <div class="md:col-span-3">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Información Básica</h3>
                            </div>

                            <div>
                                <x-label for="code" :value="__('Código del Programa')" />
                                <x-input id="code" class="block mt-1 w-full" type="text" name="code" :value="old('code')" required autofocus />
                                @error('code')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <x-label for="category" :value="__('Categoría')" />
                                <select id="category" name="category" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full" required>
                                    <option value="">Seleccionar categoría</option>
                                    <option value="Doctorado" {{ old('category') == 'Doctorado' ? 'selected' : '' }}>Doctorado</option>
                                    <option value="Maestria" {{ old('category') == 'Maestria' ? 'selected' : '' }}>Maestría</option>
                                    <option value="Diplomado" {{ old('category') == 'Diplomado' ? 'selected' : '' }}>Diplomado</option>
                                    <option value="Especialidad" {{ old('category') == 'Especialidad' ? 'selected' : '' }}>Especialidad</option>
                                    <option value="Curso" {{ old('category') == 'Curso' ? 'selected' : '' }}>Curso</option>
                                </select>
                                @error('category')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <x-label for="name" :value="__('Nombre del Programa')" />
                                <x-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required />
                                @error('name')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                                <p class="text-xs text-gray-500 mt-1">Solo inserte el nombre del programa sin la categoría</p>
                            </div>

                            <div class="md:col-span-3">
                                <x-label for="description" :value="__('Descripción')" />
                                <textarea id="description" name="description" rows="3" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">{{ old('description') }}</textarea>
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
                                <x-input id="version" class="block mt-1 w-full" type="number" name="version" :value="old('version', 1)" required min="1" />
                                @error('version')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <x-label for="group" :value="__('Grupo')" />
                                <x-input id="group" class="block mt-1 w-full" type="number" name="group" :value="old('group', 1)" required min="1" />
                                @error('group')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <x-label for="year" :value="__('Gestión')" />
                                <select id="year" name="year" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">
                                    <option value="">Seleccionar año</option>
                                    @for($i = date('Y') + 2; $i >= 2020; $i--)
                                        <option value="{{ $i }}-01-01" {{ old('year') == $i.'-01-01' ? 'selected' : '' }}>{{ $i }}</option>
                                    @endfor
                                </select>
                                @error('year')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Segunda fila: Código Académico, Código Contable, Área -->
                            <div>
                                <x-label for="academic_code" :value="__('Código Académico')" />
                                <x-input id="academic_code" class="block mt-1 w-full" type="text" name="academic_code" :value="old('academic_code')" />
                                @error('academic_code')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <x-label for="accounting_code" :value="__('Código Contable')" />
                                <x-input id="accounting_code" class="block mt-1 w-full" type="text" name="accounting_code" :value="old('accounting_code')" />
                                @error('accounting_code')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <x-label for="area" :value="__('Área')" />
                                <select id="area" name="area" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full" required>
                                    <option value="">Seleccionar área</option>
                                    <option value="Ciencias Empresariales" {{ old('area') == 'Ciencias Empresariales' ? 'selected' : '' }}>Ciencias Empresariales</option>
                                    <option value="Ingenieria" {{ old('area') == 'Ingenieria' ? 'selected' : '' }}>Ingeniería</option>
                                    <option value="Diseno" {{ old('area') == 'Diseno' ? 'selected' : '' }}>Diseño</option>
                                    <option value="Salud" {{ old('area') == 'Salud' ? 'selected' : '' }}>Salud</option>
                                    <option value="Social" {{ old('area') == 'Social' ? 'selected' : '' }}>Social</option>
                                    <option value="Legal" {{ old('area') == 'Legal' ? 'selected' : '' }}>Legal</option>
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
                                <x-input id="start_date" class="block mt-1 w-full" type="date" name="start_date" :value="old('start_date')" />
                                @error('start_date')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <x-label for="finalization_date" :value="__('Fecha de Finalización')" />
                                <x-input id="finalization_date" class="block mt-1 w-full" type="date" name="finalization_date" :value="old('finalization_date')" />
                                @error('finalization_date')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <x-label for="state" :value="__('Estado')" />
                                <select id="state" name="state" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full" required>
                                    <option value="">Seleccionar estado</option>
                                    <option value="No Aprobado" {{ old('state') == 'No Aprobado' ? 'selected' : '' }}>No Aprobado</option>
                                    <option value="Aprobado" {{ old('state') == 'Aprobado' ? 'selected' : '' }}>Aprobado</option>
                                    <option value="Inscripciones" {{ old('state') == 'Inscripciones' ? 'selected' : '' }}>Inscripciones</option>
                                    <option value="Desarrollo" {{ old('state') == 'Desarrollo' ? 'selected' : '' }}>Desarrollo</option>
                                    <option value="Finalizado" {{ old('state') == 'Finalizado' ? 'selected' : '' }}>Finalizado</option>
                                </select>
                                @error('state')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="hidden">
                                <label for="active" class="inline-flex items-center">
                                    <input id="active" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" name="active" value="1" {{ old('active', '1') ? 'checked' : '' }}>
                                    <span class="ml-2 text-sm text-gray-600">{{ __('Programa Activo') }}</span>
                                </label>
                            </div>
                        </div>

        <!-- Módulos del Programa -->
        <div class="mt-8 border-t pt-6">
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Módulos del Programa</h3>
                <p class="text-sm text-gray-600 mb-4">Puede agregar módulos al programa. Los módulos se guardarán una vez que el programa sea creado.</p>
            </div>

            <div id="modules-container">
                <!-- Módulo inicial -->
                <div class="module-item bg-gray-50 p-4 rounded-lg mb-4 border">
                    <div class="flex justify-between items-center mb-3">
                        <h4 class="text-md font-medium text-gray-800">Módulo 1</h4>
                        <button type="button" class="remove-module text-red-600 hover:text-red-800 hidden">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-label for="modules[0][name]" :value="__('Nombre del Módulo')" />
                            <x-input id="modules[0][name]" class="block mt-1 w-full" type="text" name="modules[0][name]" :value="old('modules.0.name')" />
                        </div>
                        <div class="relative">
                            <label for="modules[0][teacher_search]" class="block font-medium text-sm text-gray-700">
                                Nombre del Docente
                                <button type="button" onclick="openCreateTeacherModal(0)" class="ml-2 text-indigo-600 hover:text-indigo-900 text-xs">
                                    + Nuevo docente
                                </button>
                            </label>
                            <input type="text" 
                                   id="modules[0][teacher_search]" 
                                   class="teacher-search rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full"
                                   placeholder="Buscar docente..." 
                                   autocomplete="off"
                                   data-module-index="0">
                            <input type="hidden" name="modules[0][teacher_id]" id="modules[0][teacher_id]" value="{{ old('modules.0.teacher_id') }}">
                            
                            <!-- Contenedor de resultados -->
                            <div id="modules[0][teacher_results]" class="teacher-results hidden absolute z-10 w-full bg-white shadow-lg rounded-md mt-1 max-h-60 overflow-auto border border-gray-200"></div>
                        </div>
                        <div>
                            <x-label for="modules[0][monitor_id]" :value="__('Encargado de Monitoreo')" />
                            <select id="modules[0][monitor_id]" name="modules[0][monitor_id]" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">
                                <option value="">Seleccionar monitor</option>
                                @foreach($monitors as $id => $name)
                                    <option value="{{ $id }}" {{ old('modules.0.monitor_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-label for="modules[0][start_date]" :value="__('Fecha de Inicio')" />
                            <x-input id="modules[0][start_date]" class="block mt-1 w-full" type="date" name="modules[0][start_date]" :value="old('modules.0.start_date')" />
                        </div>
                        <div>
                            <x-label for="modules[0][finalization_date]" :value="__('Fecha de Finalización')" />
                            <x-input id="modules[0][finalization_date]" class="block mt-1 w-full" type="date" name="modules[0][finalization_date]" :value="old('modules.0.finalization_date')" />
                        </div>
                        <div class="hidden">
                            <input type="hidden" name="modules[0][active]" value="1" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <button type="button" id="add-module" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-800 focus:outline-none focus:border-green-800 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Agregar Módulo
                </button>
            </div>
        </div>                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('programs.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-400 active:bg-gray-500 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150 mr-3">
                                Cancelar
                            </a>
                            <x-button>
                                {{ __('Guardar') }}
                            </x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let moduleCount = 1;
            const addModuleBtn = document.getElementById('add-module');
            const modulesContainer = document.getElementById('modules-container');

            // Opciones para los selects
            const teacherOptions = @json($teachers->map(function($name, $id) { return ['id' => $id, 'name' => $name]; })->values());
            const monitorOptions = @json($monitors->map(function($name, $id) { return ['id' => $id, 'name' => $name]; })->values());

            // Función para generar opciones de select
            function generateSelectOptions(options, selectedValue = '') {
                let optionsHtml = '<option value="">Seleccione una opción</option>';
                options.forEach(option => {
                    const selected = selectedValue == option.id ? 'selected' : '';
                    optionsHtml += `<option value="${option.id}" ${selected}>${option.name}</option>`;
                });
                return optionsHtml;
            }

            // Función para actualizar la visibilidad de los botones de eliminar
            function updateRemoveButtons() {
                const removeButtons = document.querySelectorAll('.remove-module');
                const moduleItems = document.querySelectorAll('.module-item');
                
                if (moduleItems.length > 1) {
                    removeButtons.forEach(btn => btn.classList.remove('hidden'));
                } else {
                    removeButtons.forEach(btn => btn.classList.add('hidden'));
                }
            }

            // Función para renumerar los módulos
            function renumberModules() {
                const moduleItems = document.querySelectorAll('.module-item');
                moduleItems.forEach((item, index) => {
                    const title = item.querySelector('h4');
                    title.textContent = `Módulo ${index + 1}`;
                    
                    // Actualizar nombres de campos
                    const inputs = item.querySelectorAll('input, textarea');
                    inputs.forEach(input => {
                        const name = input.getAttribute('name');
                        if (name) {
                            const newName = name.replace(/modules\[\d+\]/, `modules[${index}]`);
                            input.setAttribute('name', newName);
                            input.setAttribute('id', newName);
                        }
                    });
                    
                    // Actualizar labels
                    const labels = item.querySelectorAll('label');
                    labels.forEach(label => {
                        const forAttr = label.getAttribute('for');
                        if (forAttr) {
                            const newFor = forAttr.replace(/modules\[\d+\]/, `modules[${index}]`);
                            label.setAttribute('for', newFor);
                        }
                    });
                });
            }

            // Agregar nuevo módulo
            addModuleBtn.addEventListener('click', function() {
                const moduleTemplate = `
                    <div class="module-item bg-gray-50 p-4 rounded-lg mb-4 border">
                        <div class="flex justify-between items-center mb-3">
                            <h4 class="text-md font-medium text-gray-800">Módulo ${moduleCount + 1}</h4>
                            <button type="button" class="remove-module text-red-600 hover:text-red-800">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-medium text-sm text-gray-700" for="modules[${moduleCount}][name]">Nombre del Módulo</label>
                                <input class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full" id="modules[${moduleCount}][name]" type="text" name="modules[${moduleCount}][name]" />
                            </div>
                            <div class="relative">
                                <label for="modules[${moduleCount}][teacher_search]" class="block font-medium text-sm text-gray-700">
                                    Nombre del Docente
                                    <button type="button" onclick="openCreateTeacherModal(${moduleCount})" class="ml-2 text-indigo-600 hover:text-indigo-900 text-xs">
                                        + Nuevo docente
                                    </button>
                                </label>
                                <input type="text" 
                                       id="modules[${moduleCount}][teacher_search]" 
                                       class="teacher-search rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full"
                                       placeholder="Buscar docente..." 
                                       autocomplete="off"
                                       data-module-index="${moduleCount}">
                                <input type="hidden" name="modules[${moduleCount}][teacher_id]" id="modules[${moduleCount}][teacher_id]">
                                
                                <!-- Contenedor de resultados -->
                                <div id="modules[${moduleCount}][teacher_results]" class="teacher-results hidden absolute z-10 w-full bg-white shadow-lg rounded-md mt-1 max-h-60 overflow-auto border border-gray-200"></div>
                            </div>
                            <div>
                                <label class="block font-medium text-sm text-gray-700" for="modules[${moduleCount}][monitor_id]">Encargado de Monitoreo</label>
                                <select id="modules[${moduleCount}][monitor_id]" name="modules[${moduleCount}][monitor_id]" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">
                                    ${generateSelectOptions(monitorOptions)}
                                </select>
                            </div>
                            <div>
                                <label class="block font-medium text-sm text-gray-700" for="modules[${moduleCount}][start_date]">Fecha de Inicio</label>
                                <input class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full" id="modules[${moduleCount}][start_date]" type="date" name="modules[${moduleCount}][start_date]" />
                            </div>
                            <div>
                                <label class="block font-medium text-sm text-gray-700" for="modules[${moduleCount}][finalization_date]">Fecha de Finalización</label>
                                <input class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full" id="modules[${moduleCount}][finalization_date]" type="date" name="modules[${moduleCount}][finalization_date]" />
                            </div>
                            <div>
                                <label class="block font-medium text-sm text-gray-700" for="modules[${moduleCount}][class_count]">Número de Clases</label>
                                <input class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full" id="modules[${moduleCount}][class_count]" type="number" name="modules[${moduleCount}][class_count]" value="1" min="1" />
                            </div>
                            <div class="hidden">
                                <input type="hidden" name="modules[${moduleCount}][active]" value="1" />
                            </div>
                        </div>
                    </div>
                `;
                
                modulesContainer.insertAdjacentHTML('beforeend', moduleTemplate);
                moduleCount++;
                updateRemoveButtons();
            });

            // Eliminar módulo
            modulesContainer.addEventListener('click', function(e) {
                if (e.target.closest('.remove-module')) {
                    const moduleItem = e.target.closest('.module-item');
                    moduleItem.remove();
                    renumberModules();
                    updateRemoveButtons();
                    
                    // Actualizar el contador
                    const remainingModules = document.querySelectorAll('.module-item');
                    moduleCount = remainingModules.length;
                }
            });

            // Inicializar
            updateRemoveButtons();
        });

        // ========== BÚSQUEDA DE DOCENTES ==========
        document.addEventListener('DOMContentLoaded', function() {
            setupTeacherSearch();

            function setupTeacherSearch() {
                let searchTimeout;

                // Usar delegación de eventos para manejar búsquedas en módulos dinámicos
                document.addEventListener('input', function(e) {
                    if (e.target.classList.contains('teacher-search')) {
                        clearTimeout(searchTimeout);
                        const searchInput = e.target;
                        const moduleIndex = searchInput.getAttribute('data-module-index');
                        const query = searchInput.value.trim();
                        const resultsContainer = document.getElementById(`modules[${moduleIndex}][teacher_results]`);
                        const hiddenInput = document.getElementById(`modules[${moduleIndex}][teacher_id]`);

                        if (query.length < 2) {
                            resultsContainer.classList.add('hidden');
                            hiddenInput.value = '';
                            return;
                        }

                        searchTimeout = setTimeout(() => {
                            fetch(`/api/teachers/search?query=${encodeURIComponent(query)}`)
                                .then(response => response.json())
                                .then(data => displayTeacherResults(data, moduleIndex))
                                .catch(error => console.error('Error:', error));
                        }, 300);
                    }
                });

                // Mostrar resultados
                function displayTeacherResults(data, moduleIndex) {
                    const resultsContainer = document.getElementById(`modules[${moduleIndex}][teacher_results]`);
                    
                    if (data.length === 0) {
                        resultsContainer.innerHTML = '<p class="p-2 text-gray-500 text-sm">No se encontraron resultados</p>';
                        resultsContainer.classList.remove('hidden');
                        return;
                    }

                    let html = '<ul class="divide-y divide-gray-200">';
                    data.forEach(teacher => {
                        const fullName = `${teacher.academic_degree || ''} ${teacher.name} ${teacher.paternal_surname || ''} ${teacher.maternal_surname || ''}`.trim();
                        html += `
                            <li class="p-2 hover:bg-gray-100 cursor-pointer" data-id="${teacher.id}" data-name="${fullName}">
                                <span class="text-sm font-medium">${fullName}</span>
                                ${teacher.profession ? `<br><span class="text-xs text-gray-500">${teacher.profession}</span>` : ''}
                            </li>
                        `;
                    });
                    html += '</ul>';

                    resultsContainer.innerHTML = html;
                    resultsContainer.classList.remove('hidden');

                    // Manejar selección
                    resultsContainer.querySelectorAll('li').forEach(li => {
                        li.addEventListener('click', function() {
                            const id = this.getAttribute('data-id');
                            const name = this.getAttribute('data-name');
                            const searchInput = document.getElementById(`modules[${moduleIndex}][teacher_search]`);
                            const hiddenInput = document.getElementById(`modules[${moduleIndex}][teacher_id]`);
                            
                            searchInput.value = name;
                            hiddenInput.value = id;
                            resultsContainer.classList.add('hidden');
                        });
                    });
                }

                // Cerrar resultados al hacer clic fuera
                document.addEventListener('click', function(event) {
                    const teacherResults = document.querySelectorAll('.teacher-results');
                    teacherResults.forEach(resultsContainer => {
                        const searchInput = resultsContainer.previousElementSibling.previousElementSibling;
                        if (searchInput && !searchInput.contains(event.target) && !resultsContainer.contains(event.target)) {
                            resultsContainer.classList.add('hidden');
                        }
                    });
                });
            }
        });

        // ========== FUNCIONES PARA MODAL DE CREACIÓN DE DOCENTE ==========
        let currentModuleIndex = 0;

        function openCreateTeacherModal(moduleIndex) {
            currentModuleIndex = moduleIndex;
            // Limpiar todos los campos
            document.getElementById('new-teacher-name').value = '';
            document.getElementById('new-teacher-paternal').value = '';
            document.getElementById('new-teacher-maternal').value = '';
            document.getElementById('new-teacher-birth-date').value = '';
            document.getElementById('new-teacher-ci').value = '';
            document.getElementById('new-teacher-email').value = '';
            document.getElementById('new-teacher-phone').value = '';
            document.getElementById('new-teacher-address').value = '';
            document.getElementById('new-teacher-profession-search').value = '';
            document.getElementById('new-teacher-profession').value = '';
            document.getElementById('new-teacher-degree').value = '';
            document.getElementById('new-teacher-bank').value = '';
            document.getElementById('new-teacher-account').value = '';
            document.getElementById('new-teacher-bill').value = 'No';
            document.getElementById('new-teacher-esam').value = 'No';
            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'create-teacher-modal' }));
        }

        function closeModal(modalId) {
            window.dispatchEvent(new CustomEvent('close-modal', { detail: modalId }));
        }

        // Función para crear docente
        function createTeacher() {
            // Recoger todos los datos del formulario
            const name = document.getElementById('new-teacher-name').value.trim();
            const paternalSurname = document.getElementById('new-teacher-paternal').value.trim();
            const maternalSurname = document.getElementById('new-teacher-maternal').value.trim();
            const birthDate = document.getElementById('new-teacher-birth-date').value;
            const ci = document.getElementById('new-teacher-ci').value.trim();
            const email = document.getElementById('new-teacher-email').value.trim();
            const phone = document.getElementById('new-teacher-phone').value.trim();
            const address = document.getElementById('new-teacher-address').value.trim();
            const profession = document.getElementById('new-teacher-profession').value.trim();
            const academicDegree = document.getElementById('new-teacher-degree').value;
            const bank = document.getElementById('new-teacher-bank').value.trim();
            const accountNumber = document.getElementById('new-teacher-account').value.trim();
            const bill = document.getElementById('new-teacher-bill').value;
            const esamWorker = document.getElementById('new-teacher-esam').value;
            
            if (!name || !email) {
                alert('Por favor ingrese al menos el nombre y correo electrónico del docente');
                return;
            }

            // Mostrar indicador de carga
            const submitBtn = document.getElementById('create-teacher-btn');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = 'Guardando...';

            fetch('/teachers', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    name: name,
                    paternal_surname: paternalSurname,
                    maternal_surname: maternalSurname,
                    birth_date: birthDate,
                    ci: ci,
                    email: email,
                    phone: phone,
                    address: address,
                    profession: profession,
                    academic_degree: academicDegree,
                    bank: bank,
                    account_number: accountNumber,
                    bill: bill,
                    esam_worker: esamWorker
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Actualizar el campo de búsqueda con el nuevo docente
                    const fullName = `${data.teacher.academic_degree || ''} ${data.teacher.name} ${data.teacher.paternal_surname || ''} ${data.teacher.maternal_surname || ''}`.trim();
                    document.getElementById(`modules[${currentModuleIndex}][teacher_search]`).value = fullName;
                    document.getElementById(`modules[${currentModuleIndex}][teacher_id]`).value = data.teacher.id;
                    
                    // Cerrar modal
                    closeModal('create-teacher-modal');
                    
                    alert('Docente creado exitosamente');
                } else {
                    alert(data.message || 'Error al crear el docente');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al crear el docente');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        }

        // ========== BÚSQUEDA DE PROFESIÓN EN MODAL ==========
        document.addEventListener('DOMContentLoaded', function() {
            setupProfessionSearch();

            function setupProfessionSearch() {
                const searchInput = document.getElementById('new-teacher-profession-search');
                const hiddenInput = document.getElementById('new-teacher-profession');
                const resultsContainer = document.getElementById('new-teacher-profession-results');
                let searchTimeout;

                if (!searchInput || !hiddenInput || !resultsContainer) return;

                // Búsqueda con debounce
                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    const query = this.value.trim();

                    if (query.length < 2) {
                        resultsContainer.classList.add('hidden');
                        hiddenInput.value = '';
                        return;
                    }

                    searchTimeout = setTimeout(() => {
                        fetch(`/api/inscriptions/search-professions?query=${encodeURIComponent(query)}`)
                            .then(response => response.json())
                            .then(data => displayProfessionResults(data))
                            .catch(error => console.error('Error:', error));
                    }, 300);
                });

                // Mostrar resultados
                function displayProfessionResults(data) {
                    if (data.length === 0) {
                        resultsContainer.innerHTML = '<p class="p-2 text-gray-500 text-sm">No se encontraron resultados</p>';
                        resultsContainer.classList.remove('hidden');
                        return;
                    }

                    let html = '<ul class="divide-y divide-gray-200">';
                    data.forEach(item => {
                        html += `
                            <li class="p-2 hover:bg-gray-100 cursor-pointer" data-name="${item.name}">
                                <span class="text-sm">${item.name}</span>
                            </li>
                        `;
                    });
                    html += '</ul>';

                    resultsContainer.innerHTML = html;
                    resultsContainer.classList.remove('hidden');

                    // Manejar selección
                    resultsContainer.querySelectorAll('li').forEach(li => {
                        li.addEventListener('click', function() {
                            const name = this.getAttribute('data-name');
                            searchInput.value = name;
                            hiddenInput.value = name;
                            resultsContainer.classList.add('hidden');
                        });
                    });
                }

                // Cerrar resultados al hacer clic fuera
                document.addEventListener('click', function(event) {
                    if (!searchInput.contains(event.target) && !resultsContainer.contains(event.target)) {
                        resultsContainer.classList.add('hidden');
                    }
                });

                // Limpiar valor oculto si el usuario borra el campo de búsqueda
                searchInput.addEventListener('blur', function() {
                    setTimeout(() => {
                        if (!this.value.trim()) {
                            hiddenInput.value = '';
                        }
                    }, 200);
                });
            }
        });

        // ========== FUNCIONES PARA MODAL DE CREACIÓN DE PROFESIÓN ==========
        function openCreateProfessionModal() {
            document.getElementById('new-profession-name').value = '';
            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'create-profession-modal' }));
        }

        // Función para crear profesión
        function createProfession() {
            const name = document.getElementById('new-profession-name').value.trim();
            
            if (!name) {
                alert('Por favor ingrese el nombre de la profesión');
                return;
            }

            // Mostrar indicador de carga
            const submitBtn = document.getElementById('create-profession-btn');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = 'Guardando...';

            fetch('/professions', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ name: name })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Actualizar el campo de búsqueda con la nueva profesión
                    document.getElementById('new-teacher-profession-search').value = data.profession.name;
                    document.getElementById('new-teacher-profession').value = data.profession.name;
                    
                    // Cerrar modal
                    closeModal('create-profession-modal');
                    
                    alert('Profesión creada exitosamente');
                } else {
                    alert(data.message || 'Error al crear la profesión');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al crear la profesión');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        }
    </script>

    <!-- Modal para crear nuevo docente -->
    <x-modal id="create-teacher-modal">
        <div class="p-6 max-h-[80vh] overflow-y-auto">
            <h2 class="text-lg font-medium text-gray-900 mb-4">
                {{ __('Nuevo Docente') }}
            </h2>

            <!-- Datos Personales -->
            <h3 class="text-md font-semibold mb-2 text-indigo-700">Datos Personales</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div>
                    <x-label for="new-teacher-name" :value="__('Nombre')" />
                    <x-input id="new-teacher-name" class="block mt-1 w-full" type="text" required />
                </div>

                <div>
                    <x-label for="new-teacher-paternal" :value="__('Apellido Paterno')" />
                    <x-input id="new-teacher-paternal" class="block mt-1 w-full" type="text" />
                </div>

                <div>
                    <x-label for="new-teacher-maternal" :value="__('Apellido Materno')" />
                    <x-input id="new-teacher-maternal" class="block mt-1 w-full" type="text" />
                </div>

                <div>
                    <x-label for="new-teacher-birth-date" :value="__('Fecha de Nacimiento')" />
                    <x-input id="new-teacher-birth-date" class="block mt-1 w-full" type="date" />
                </div>

                <div>
                    <x-label for="new-teacher-ci" :value="__('Cédula de Identidad')" />
                    <x-input id="new-teacher-ci" class="block mt-1 w-full" type="text" />
                </div>
            </div>

            <!-- Datos de Contacto -->
            <h3 class="text-md font-semibold mb-2 text-indigo-700">Datos de Contacto</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <x-label for="new-teacher-email" :value="__('Correo Electrónico')" />
                    <x-input id="new-teacher-email" class="block mt-1 w-full" type="email" required />
                </div>

                <div>
                    <x-label for="new-teacher-phone" :value="__('Teléfono')" />
                    <x-input id="new-teacher-phone" class="block mt-1 w-full" type="text" />
                </div>

                <div class="md:col-span-2">
                    <x-label for="new-teacher-address" :value="__('Dirección')" />
                    <x-input id="new-teacher-address" class="block mt-1 w-full" type="text" />
                </div>
            </div>

            <!-- Datos Profesionales -->
            <h3 class="text-md font-semibold mb-2 text-indigo-700">Datos Profesionales</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div class="relative">
                    <label for="new-teacher-profession-search" class="block text-sm font-medium text-gray-700">
                        Profesión
                        <button type="button" onclick="openCreateProfessionModal()" class="ml-2 text-indigo-600 hover:text-indigo-900 text-xs">
                            + Nueva profesión
                        </button>
                    </label>
                    <input type="text" 
                           id="new-teacher-profession-search" 
                           placeholder="Buscar profesión..." 
                           class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                           autocomplete="off">
                    <input type="hidden" id="new-teacher-profession">
                    
                    <!-- Contenedor de resultados -->
                    <div id="new-teacher-profession-results" class="hidden absolute z-10 w-full bg-white shadow-lg rounded-md mt-1 max-h-60 overflow-auto border border-gray-200"></div>
                </div>

                <div>
                    <x-label for="new-teacher-degree" :value="__('Grado Académico')" />
                    <select id="new-teacher-degree" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">
                        <option value="">Seleccione un grado</option>
                        <option value="Lic.">Lic.</option>
                        <option value="Ing.">Ing.</option>
                        <option value="Dr.">Dr.</option>
                        <option value="M.Sc.">M.Sc.</option>
                        <option value="Ph.D.">Ph.D.</option>
                        <option value="M.Sc. Ing.">M.Sc. Ing.</option>
                        <option value="M.Sc. Lic.">M.Sc. Lic.</option>
                        <option value="M.Sc. Dr.">M.Sc. Dr.</option>
                        <option value="Ph.D. Ing.">Ph.D. Ing.</option>
                        <option value="Ph.D. Lic.">Ph.D. Lic.</option>
                    </select>
                </div>
            </div>

            <!-- Datos Bancarios -->
            <h3 class="text-md font-semibold mb-2 text-indigo-700">Datos Bancarios</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div>
                    <x-label for="new-teacher-bank" :value="__('Banco')" />
                    <x-input id="new-teacher-bank" class="block mt-1 w-full" type="text" />
                </div>

                <div>
                    <x-label for="new-teacher-account" :value="__('Número de Cuenta')" />
                    <x-input id="new-teacher-account" class="block mt-1 w-full" type="text" />
                </div>

                <div>
                    <x-label for="new-teacher-bill" :value="__('¿Emite Factura?')" />
                    <select id="new-teacher-bill" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">
                        <option value="No">No</option>
                        <option value="Si">Si</option>
                    </select>
                </div>

                <div>
                    <x-label for="new-teacher-esam" :value="__('¿Es trabajador de ESAM?')" />
                    <select id="new-teacher-esam" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">
                        <option value="No">No</option>
                        <option value="Si">Si</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-end mt-6 pt-4 border-t">
                <button type="button" onclick="closeModal('create-teacher-modal')" 
                        class="mr-3 inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                    Cancelar
                </button>
                <button type="button" id="create-teacher-btn" onclick="createTeacher()" 
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:border-blue-700 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                    Guardar
                </button>
            </div>
        </div>
    </x-modal>

    <!-- Modal para crear nueva profesión -->
    <x-modal id="create-profession-modal">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">
                {{ __('Nueva Profesión') }}
            </h2>

            <div class="mb-4">
                <x-label for="new-profession-name" :value="__('Nombre de la Profesión')" />
                <x-input id="new-profession-name" class="block mt-1 w-full" type="text" required autofocus />
            </div>

            <div class="flex justify-end">
                <button type="button" onclick="closeModal('create-profession-modal')" 
                        class="mr-3 inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                    Cancelar
                </button>
                <button type="button" id="create-profession-btn" onclick="createProfession()" 
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:border-blue-700 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                    Guardar
                </button>
            </div>
        </div>
    </x-modal>
</x-app-layout>
