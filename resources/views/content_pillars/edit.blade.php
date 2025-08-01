<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Editar Pilar de Contenido') }}
        </h2>
    </x-slot>

    <div >
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

                    <form id="editForm" action="{{ route('content-pillars.update', $contentPillar) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700">Nombre</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $contentPillar->name) }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required>
                            @error('name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="description" class="block text-sm font-medium text-gray-700">Descripción</label>
                            <textarea name="description" id="description" rows="3" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">{{ old('description', $contentPillar->description) }}</textarea>
                            @error('description')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <div class="flex items-center">
                                <label for="active" class="inline-flex items-center">
                                    <input id="active" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" name="active" value="1" {{ old('active', $contentPillar->active) ? 'checked' : '' }}>
                                    <span class="ml-2 text-sm text-gray-600">{{ __('Activo') }}</span>
                                </label>
                            </div>
                            @error('active')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-end mt-6 pt-4 border-t border-gray-200">
                            <a href="{{ route('content-pillars.show', $contentPillar) }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 active:bg-gray-500 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150 mr-3">
                                Cancelar
                            </a>
                            <button type="submit" id="submitBtn" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:border-blue-700 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                                <span id="submitText">Actualizar</span>
                                <svg id="submitSpinner" class="animate-spin -mr-1 ml-3 h-4 w-4 text-white hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
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
