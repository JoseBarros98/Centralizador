<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leaing-tight">
            {{ __('Crear Docente') }}
        </h2>
    </x-slot>
    <div>
        <div class="max-w-1xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">

                <div class="p-6 bg-white border-b border-gray-200">

                    <form action="{{ route('teachers.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <!-- Datos Personales -->
    <h3 class="text-lg font-semibold mb-2 text-indigo-700">Datos Personales</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Nombre</label>
            <input type="text" name="name" id="name" value="{{ old('name') }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required>
            @error('name')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="paternal_surname" class="block text-sm font-medium text-gray-700">Apellido Paterno</label>
            <input type="text" name="paternal_surname" id="paternal_surname" value="{{ old('paternal_surname') }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
            @error('paternal_surname')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="maternal_surname" class="block text-sm font-medium text-gray-700">Apellido Materno</label>
            <input type="text" name="maternal_surname" id="maternal_surname" value="{{ old('maternal_surname') }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
            @error('maternal_surname')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="birth_date" class="block text-sm font-medium text-gray-700">Fecha de Nacimiento</label>
            <input type="date" name="birth_date" id="birth_date" value="{{ old('birth_date') }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
            @error('birth_date')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="ci" class="block text-sm font-medium text-gray-700">Cédula de Identidad</label>
            <input type="text" name="ci" id="ci" value="{{ old('ci') }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
            @error('ci')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <!-- Datos de Contacto -->
    <h3 class="text-lg font-semibold mb-2 text-indigo-700">Datos de Contacto</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Correo Electrónico</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required>
            @error('email')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="phone" class="block text-sm font-medium text-gray-700">Teléfono</label>
            <input type="text" name="phone" id="phone" value="{{ old('phone') }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
            @error('phone')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div class="md:col-span-2">
            <label for="address" class="block text-sm font-medium text-gray-700">Dirección</label>
            <input type="text" name="address" id="address" value="{{ old('address') }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
            @error('address')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <!-- Datos Profesionales -->
    <h3 class="text-lg font-semibold mb-2 text-indigo-700">Datos Profesionales</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div class="relative">
            <label for="profession-search" class="block text-sm font-medium text-gray-700">
                Profesión
                <button type="button" onclick="openCreateProfessionModal()" class="ml-2 text-indigo-600 hover:text-indigo-900 text-xs">
                    + Nueva profesión
                </button>
            </label>
            <input type="text" 
                   id="profession-search" 
                   placeholder="Buscar profesión..." 
                   class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                   autocomplete="off">
            <input type="hidden" name="profession" id="profession" value="{{ old('profession') }}">
            
            <!-- Contenedor de resultados -->
            <div id="profession-results" class="hidden absolute z-10 w-full bg-white shadow-lg rounded-md mt-1 max-h-60 overflow-auto border border-gray-200"></div>
            
            @error('profession')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="academic_degree" class="block text-sm font-medium text-gray-700">Grado Académico</label>
            <select id="academic_degree" name="academic_degree" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                <option value="">Seleccione un grado académico</option>
                <option value="Lic." {{ old('academic_degree') == 'Lic.' ? 'selected' : '' }}>Lic.</option>
                <option value="Ing." {{ old('academic_degree') == 'Ing.' ? 'selected' : '' }}>Ing.</option>
                <option value="Dr." {{ old('academic_degree') == 'Dr.' ? 'selected' : '' }}>Dr.</option>
                <option value="M.Sc." {{ old('academic_degree') == 'M.Sc.' ? 'selected' : '' }}>M.Sc.</option>
                <option value="Ph.D." {{ old('academic_degree') == 'Ph.D.' ? 'selected' : '' }}>Ph.D.</option>
                <option value="M.Sc. Ing." {{ old('academic_degree') == 'M.Sc. Ing.' ? 'selected' : '' }}>M.Sc. Ing.</option>
                <option value="M.Sc. Lic." {{ old('academic_degree') == 'M.Sc. Lic.' ? 'selected' : '' }}>M.Sc. Lic.</option>
                <option value="M.Sc. Dr." {{ old('academic_degree') == 'M.Sc. Dr.' ? 'selected' : '' }}>M.Sc. Dr.</option>
                <option value="Ph.D. Ing." {{ old('academic_degree') == 'Ph.D. Ing.' ? 'selected' : '' }}>Ph.D. Ing.</option>
                <option value="Ph.D. Lic." {{ old('academic_degree') == 'Ph.D. Lic.' ? 'selected' : '' }}>Ph.D. Lic.</option>
            </select>
        </div>
    </div>

    <!-- Datos Bancarios -->
    <h3 class="text-lg font-semibold mb-2 text-indigo-700">Datos Bancarios</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div>
            <label for="bank" class="block text-sm font-medium text-gray-700">Banco</label>
            <input type="text" name="bank" id="bank" value="{{ old('bank') }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
            @error('bank')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="account_number" class="block text-sm font-medium text-gray-700">Número de Cuenta</label>
            <input type="text" name="account_number" id="account_number" value="{{ old('account_number') }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
            @error('account_number')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="bill" class="block text-sm font-medium text-gray-700">¿Emite Factura?</label>
            <select id="bill" name="bill" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                <option value="No" {{ old('bill', 'No') == 'No' ? 'selected' : '' }}>No</option>
                <option value="Si" {{ old('bill') == 'Si' ? 'selected' : '' }}>Si</option>
            </select>
            @error('bill')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="esam_worker" class="block text-sm font-medium text-gray-700">¿Es trabajador de ESAM?</label>
            <select id="esam_worker" name="esam_worker" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                <option value="No" {{ old('esam_worker', 'No') == 'No' ? 'selected' : '' }}>No</option>
                <option value="Si" {{ old('esam_worker') == 'Si' ? 'selected' : '' }}>Si</option>
            </select>
            @error('esam_worker')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>
    </div>


    <!-- Archivos -->
    <h3 class="text-lg font-semibold mb-2 text-indigo-700">Archivos Adjuntos</h3>
    <div class="mb-6">
        <label class="block text-sm font-medium text-gray-700">Archivos</label>
        <p class="text-xs text-gray-500 mt-1">Tipos de documentos permitidos: PDF, JPG, JPEG, PNG. Peso máximo: 2MB.</p>
        <div id="file-container">
            <div class="file-group mb-2">
                <div class="flex items-center space-x-2">
                    <input type="file" name="files[]" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block shadow-sm sm:text-sm border-gray-300 rounded-md">
                    <button type="button" class="remove-file px-2 py-1 bg-red-500 text-white rounded hover:bg-red-600" style="display: none;">Eliminar</button>
                </div>
                <input type="text" name="file_descriptions[]" placeholder="Descripción del archivo" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
            </div>
        </div>
        <button type="button" id="add-file" class="mt-2 inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Añadir archivo
        </button>
        @error('files')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
        @error('files.*')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex items-center justify-end mt-4">
        <a href="{{ route('teachers.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 active:bg-gray-500 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150 mr-2">
            Cancelar
        </a>
        <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:border-blue-700 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
            Guardar
        </button>
    </div>
</form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function(){
            const fileContainer = document.getElementById('file-container');
            const addFileButton = document.getElementById('add-file');

            updateRemoveButtons();

            addFileButton.addEventListener('click', function(){
                const fileGroup = document.createElement('div');
                fileGroup.className = 'file-group mb-2';
                fileGroup.innerHTML = `
                    <div class="flex items-center space-x-2">
                        <input type="file" name="files[]" class="mt-1 focus:ring-indigo-500 focus:boder-indigo-500 block shadow-sm sm:text-sm border-gray-300 rounded-md">
                        <button type="button" class="remove-file px-2 py-1 bg-red-500 text-white rounded hover:bg-red-600">Eliminar</button>
                    </div>
                    <input type="text" name="file_descriptions[]"
                    placeholder="Descripción del archivo" class="my-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                `;

                fileContainer.appendChild(fileGroup);
                updateRemoveButtons();

                fileGroup.querySelector('.remove-file').addEventListener('click', function() {
                    fileGroup.remove();
                    updateRemoveButtons();
                });
            });

            document.querySelectorAll('.remove-file').forEach(button =>{
                button.addEventListener('click', function() {
                    this.closest('.file-group').remove();
                    updateRemoveButtons();
                });
            });

            function updateRemoveButtons() {
                const fileGroups = document.querySelectorAll('.file-group');
                fileGroups.forEach((group, index) => {
                    const removeButton = group.querySelector('.remove-file');
                    if (fileGroups.length > 1) {
                        removeButton.style.display = 'block';
                    } else {
                        removeButton.style.display = 'none';
                    }
                });
            }
        });

        // ========== BÚSQUEDA DE PROFESIONES ==========
        document.addEventListener('DOMContentLoaded', function() {
            setupSearchField('profession', '/api/inscriptions/search-professions');

            function setupSearchField(fieldName, apiUrl) {
                const searchInput = document.getElementById(`${fieldName}-search`);
                const hiddenInput = document.getElementById(fieldName);
                const resultsContainer = document.getElementById(`${fieldName}-results`);
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
                        fetch(`${apiUrl}?query=${encodeURIComponent(query)}`)
                            .then(response => response.json())
                            .then(data => displayResults(data, fieldName))
                            .catch(error => console.error('Error:', error));
                    }, 300);
                });

                // Mostrar resultados
                function displayResults(data, fieldName) {
                    const resultsContainer = document.getElementById(`${fieldName}-results`);
                    const searchInput = document.getElementById(`${fieldName}-search`);
                    
                    if (data.length === 0) {
                        resultsContainer.innerHTML = '<p class="p-2 text-gray-500 text-sm">No se encontraron resultados</p>';
                        resultsContainer.classList.remove('hidden');
                        return;
                    }

                    let html = '<ul class="divide-y divide-gray-200">';
                    data.forEach(item => {
                        html += `
                            <li class="p-2 hover:bg-gray-100 cursor-pointer" data-id="${item.id}" data-name="${item.name}">
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

        // ========== FUNCIONES PARA MODAL DE CREACIÓN ==========
        function openCreateProfessionModal() {
            document.getElementById('new-profession-name').value = '';
            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'create-profession-modal' }));
        }

        function closeModal(modalId) {
            window.dispatchEvent(new CustomEvent('close-modal', { detail: modalId }));
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
                    document.getElementById('profession-search').value = data.profession.name;
                    document.getElementById('profession').value = data.profession.name;
                    
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