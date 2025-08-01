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
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Nombre</label>
            <input type="text" name="name" id="name" value="{{ old('name', $teacher->name) }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required>
            @error('name')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="paternal_surname" class="block text-sm font-medium text-gray-700">Apellido Paterno</label>
            <input type="text" name="paternal_surname" id="paternal_surname" value="{{ old('paternal_surname', $teacher->paternal_surname) }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
            @error('paternal_surname')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="maternal_surname" class="block text-sm font-medium text-gray-700">Apellido Materno</label>
            <input type="text" name="maternal_surname" id="maternal_surname" value="{{ old('maternal_surname', $teacher->maternal_surname) }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
            @error('maternal_surname')
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
        <div>
            <label for="profession" class="block text-sm font-medium text-gray-700">Profesión</label>
            <input type="text" name="profession" id="profession" value="{{ old('profession', $teacher->profession) }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
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
    </script>
</x-app-layout>