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
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
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
        <div>
            <label for="profession" class="block text-sm font-medium text-gray-700">Profesión</label>
            <input type="text" name="profession" id="profession" value="{{ old('profession') }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
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

    <!-- Archivos -->
    <h3 class="text-lg font-semibold mb-2 text-indigo-700">Archivos Adjuntos</h3>
    <div class="mb-6">
        <label class="block text-sm font-medium text-gray-700">Archivos</label>
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
    </script>
</x-app-layout>