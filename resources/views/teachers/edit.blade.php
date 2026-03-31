<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Editar Docente') }}
        </h2>
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    @if ($errors->any())
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form id="editForm" action="{{ route('teachers.update', $teacher) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <!-- Datos Personales -->
    <h3 class="text-lg font-semibold mb-2 text-indigo-700">Datos Personales</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Nombre</label>
            <input type="text" name="name" id="name" value="{{ old('name', $teacher->name) }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required>
            @error('name')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="birth_date" class="block text-sm font-medium text-gray-700">Fecha de Nacimiento</label>
            <input type="date" name="birth_date" id="birth_date" value="{{ old('birth_date', $teacher->birth_date) }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
            @error('birth_date')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="ci" class="block text-sm font-medium text-gray-700">Cédula de Identidad</label>
            <input type="text" name="ci" id="ci" value="{{ old('ci', $teacher->ci) }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
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
            <input type="email" name="email" id="email" value="{{ old('email', $teacher->email) }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
            @error('email')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="phone" class="block text-sm font-medium text-gray-700">Teléfono</label>
            <input type="text" name="phone" id="phone" value="{{ old('phone', $teacher->phone) }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
            @error('phone')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div class="md:col-span-2">
            <label for="address" class="block text-sm font-medium text-gray-700">Dirección</label>
            <input type="text" name="address" id="address" value="{{ old('address', $teacher->address) }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
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
                   value="{{ old('profession', $teacher->profession) }}"
                   class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                   autocomplete="off">
            <input type="hidden" name="profession" id="profession" value="{{ old('profession', $teacher->profession) }}">
            
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
                <option value="Lic." {{ old('academic_degree', $teacher->academic_degree) == 'Lic.' ? 'selected' : '' }}>Lic.</option>
                <option value="Ing." {{ old('academic_degree', $teacher->academic_degree) == 'Ing.' ? 'selected' : '' }}>Ing.</option>
                <option value="Dr." {{ old('academic_degree', $teacher->academic_degree) == 'Dr.' ? 'selected' : '' }}>Dr.</option>
                <option value="M.Sc." {{ old('academic_degree', $teacher->academic_degree) == 'M.Sc.' ? 'selected' : '' }}>M.Sc.</option>
                <option value="Ph.D." {{ old('academic_degree', $teacher->academic_degree) == 'Ph.D.' ? 'selected' : '' }}>Ph.D.</option>
                <option value="M.Sc. Ing." {{ old('academic_degree', $teacher->academic_degree) == 'M.Sc. Ing.' ? 'selected' : '' }}>M.Sc. Ing.</option>
                <option value="M.Sc. Lic." {{ old('academic_degree', $teacher->academic_degree) == 'M.Sc. Lic.' ? 'selected' : '' }}>M.Sc. Lic.</option>
                <option value="M.Sc. Dr." {{ old('academic_degree', $teacher->academic_degree) == 'M.Sc. Dr.' ? 'selected' : '' }}>M.Sc. Dr.</option>
                <option value="Ph.D. Ing." {{ old('academic_degree', $teacher->academic_degree) == 'Ph.D. Ing.' ? 'selected' : '' }}>Ph.D. Ing.</option>
                <option value="Ph.D. Lic." {{ old('academic_degree', $teacher->academic_degree) == 'Ph.D. Lic.' ? 'selected' : '' }}>Ph.D. Lic.</option>
            </select>
        </div>
    </div>

    <!-- Datos Bancarios -->
    <h3 class="text-lg font-semibold mb-2 text-indigo-700">Datos Bancarios</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div>
            <label for="bank" class="block text-sm font-medium text-gray-700">Banco</label>
            <input type="text" name="bank" id="bank" value="{{ old('bank', $teacher->bank) }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
            @error('bank')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="account_number" class="block text-sm font-medium text-gray-700">Número de cuenta</label>
            <input type="text" name="account_number" id="account_number" value="{{ old('account_number', $teacher->account_number) }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
            @error('account_number')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="bill" class="block text-sm font-medium text-gray-700">¿Emite Factura?</label>
            <select id="bill" name="bill" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                <option value="">Seleccione una opción</option>
                <option value="Si" {{ old('bill', $teacher->bill) == 'Si' ? 'selected' : '' }}>Sí</option>
                <option value="No" {{ old('bill', $teacher->bill) == 'No' ? 'selected' : '' }}>No</option>
            </select>
        </div>
        <div>
            <label for="esam_worker" class="block text-sm font-medium text-gray-700">¿Es trabajador de ESAM?</label>
            <select id="esam_worker" name="esam_worker" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                <option value="">Seleccione una opción</option>
                <option value="Si" {{ old('esam_worker', $teacher->esam_worker) == 'Si' ? 'selected' : '' }}>Sí</option>
                <option value="No" {{ old('esam_worker', $teacher->esam_worker) == 'No' ? 'selected' : '' }}>No</option>
            </select>
        </div>
    </div>

    <div class="flex items-center justify-end mt-6 pt-4 border-t border-gray-200">
        <a href="{{ route('teachers.show', $teacher) }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 active:bg-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150 mr-3">
            Cancelar
        </a>
        <button type="submit" id="submitBtn" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:border-blue-800 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
            <span id="submitText">Actualizar</span>
            <svg id="submitSpinner" class="animate-spin -mr-1 ml-3 h-4 w-4 text-white hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647.z"></path>
            </svg>
        </button>
    </div>
</form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const fileContainer = document.getElementById('file-container');
            const addFileButton = document.getElementById('add-file');
            const form = document.getElementById('editForm');
            const submitBtn = document.getElementById('submitBtn');
            const submitText = document.getElementById('submitText');
            const submitSpinner = document.getElementById('submitSpinner');
            
            // Función para actualizar la visibilidad de los botones de eliminar
            function updateRemoveButtons() {
                const fileGroups = document.querySelectorAll('.file-group');
                fileGroups.forEach((group, index) => {
                    const removeButton = group.querySelector('.remove-file');
                    const fileInput = group.querySelector('.file-input');
                    
                    // Solo mostrar el botón de eliminar si hay más de un grupo O si el input tiene un archivo
                    if (fileGroups.length > 1 || fileInput.files.length > 0) {
                        removeButton.style.display = 'block';
                    } else {
                        removeButton.style.display = 'none';
                    }
                });
            }
            
            // Función para añadir un nuevo grupo de archivos
            function addFileGroup() {
                const fileGroup = document.createElement('div');
                fileGroup.className = 'file-group mb-3 p-3 border border-gray-200 rounded-lg';
                fileGroup.innerHTML = `
                    <div class="flex items-center space-x-2 mb-2">
                        <input type="file" name="files[]" class="file-input block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        <button type="button" class="remove-file px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600 text-sm">
                            Eliminar
                        </button>
                    </div>
                    <input type="text" name="file_descriptions[]" placeholder="Descripción del archivo (opcional)" class="mt-1 block w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                `;
                
                fileContainer.appendChild(fileGroup);
                updateRemoveButtons();
                
                // Añadir evento al nuevo botón de eliminar
                const removeButton = fileGroup.querySelector('.remove-file');
                removeButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    fileGroup.remove();
                    updateRemoveButtons();
                });
                
                // Añadir evento al input de archivo para actualizar botones
                const fileInput = fileGroup.querySelector('.file-input');
                fileInput.addEventListener('change', function() {
                    updateRemoveButtons();
                });
            }
            
            // Evento para añadir archivo
            addFileButton.addEventListener('click', function(e) {
                e.preventDefault();
                addFileGroup();
            });
            
            // Eventos para botones de eliminar existentes
            document.querySelectorAll('.remove-file').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    this.closest('.file-group').remove();
                    updateRemoveButtons();
                });
            });
            
            // Eventos para inputs de archivo existentes
            document.querySelectorAll('.file-input').forEach(input => {
                input.addEventListener('change', function() {
                    updateRemoveButtons();
                });
            });
            
            // Manejar el envío del formulario
            form.addEventListener('submit', function(e) {
                // Prevenir múltiples envíos
                submitBtn.disabled = true;
                submitText.textContent = 'Actualizando...';
                submitSpinner.classList.remove('hidden');
                
                // Reactivar el botón después de 10 segundos por si hay un error
                setTimeout(function() {
                    submitBtn.disabled = false;
                    submitText.textContent = 'Actualizar';
                    submitSpinner.classList.add('hidden');
                }, 10000);
            });
            
            // Inicializar la visibilidad de los botones
            updateRemoveButtons();
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

        // ========== FUNCIONES PARA MODAL DE CREACIÓN DE PROFESIÓN ==========
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