<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Editar Módulo') }} - {{ $program->name }}
        </h2>
    </x-slot>

    <div >
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('programs.modules.update', [$program->id, $module->id]) }}">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Nombre del Módulo -->
                            <div>
                                <x-label for="name" :value="__('Nombre del Módulo')" />
                                <x-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $module->name)" required autofocus />
                                @error('name')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            

                            <!-- Monitor -->
                            <div>
                                <x-label for="monitor_id" :value="__('Encargado de Monitoreo')" />
                                <select id="monitor_id" name="monitor_id" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full" required>
                                    <option value="">Seleccionar monitor</option>
                                    @foreach($monitors as $id => $name)
                                        <option value="{{ $id }}" {{ old('monitor_id', $module->monitor_id) == $id ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                                @error('monitor_id')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Estado -->
                            <div>
                                <x-label for="status" :value="__('Estado del Módulo')" />
                                <select id="status" name="status" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full" required>
                                    <option value="Pendiente" {{ old('status', $module->status) == 'Pendiente' ? 'selected' : '' }}>Pendiente</option>
                                    <option value="Desarrollo" {{ old('status', $module->status) == 'Desarrollo' ? 'selected' : '' }}>En Desarrollo</option>
                                    <option value="Finalizado" {{ old('status', $module->status) == 'Finalizado' ? 'selected' : '' }}>Finalizado</option>
                                </select>
                                @error('status')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Fecha de Inicio -->
                            <div>
                                <x-label for="start_date" :value="__('Fecha de Inicio')" />
                                <x-input id="start_date" class="block mt-1 w-full" type="date" name="start_date" :value="old('start_date', $module->start_date ? $module->start_date->format('Y-m-d') : '')" />
                                @error('start_date')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Fecha de Finalización -->
                            <div>
                                <x-label for="finalization_date" :value="__('Fecha de Finalización')" />
                                <x-input id="finalization_date" class="block mt-1 w-full" type="date" name="finalization_date" :value="old('finalization_date', $module->finalization_date ? $module->finalization_date->format('Y-m-d') : '')" />
                                @error('finalization_date')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('programs.modules.show', [$program->id, $module->id]) }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-400 active:bg-gray-500 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150 mr-3">
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

    <script>
        // ========== BÚSQUEDA DE DOCENTES ==========
        document.addEventListener('DOMContentLoaded', function() {
            setupTeacherSearch();

            function setupTeacherSearch() {
                const searchInput = document.getElementById('teacher_search');
                const hiddenInput = document.getElementById('teacher_id');
                const resultsContainer = document.getElementById('teacher_results');
                let searchTimeout;

                if (!searchInput || !hiddenInput || !resultsContainer) return;

                // Búsqueda con debounce
                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    const query = this.value.trim();

                    if (query.length < 2) {
                        resultsContainer.classList.add('hidden');
                        return;
                    }

                    searchTimeout = setTimeout(() => {
                        fetch(`/api/teachers/search?query=${encodeURIComponent(query)}`)
                            .then(response => response.json())
                            .then(data => displayTeacherResults(data))
                            .catch(error => console.error('Error:', error));
                    }, 300);
                });

                // Mostrar resultados
                function displayTeacherResults(data) {
                    if (data.length === 0) {
                        resultsContainer.innerHTML = '<p class="p-2 text-gray-500 text-sm">No se encontraron resultados</p>';
                        resultsContainer.classList.remove('hidden');
                        return;
                    }

                    let html = '<ul class="divide-y divide-gray-200">';
                    data.forEach(teacher => {
                        const fullName = `${teacher.academic_degree || ''} ${teacher.name} ${teacher.paternal_surname || ''} ${teacher.maternal_surname || ''}`.trim();
                        const profession = teacher.profession ? `<span class="text-xs text-gray-500">${teacher.profession}</span>` : '';
                        
                        html += `
                            <li class="p-2 hover:bg-gray-100 cursor-pointer" data-id="${teacher.id}" data-name="${fullName}">
                                <div class="text-sm font-medium">${fullName}</div>
                                ${profession}
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
                            searchInput.value = name;
                            hiddenInput.value = id;
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
            }
        });

        // ========== FUNCIONES PARA MODAL DE CREACIÓN DE DOCENTE ==========
        function openCreateTeacherModal() {
            // Limpiar todos los campos
            document.getElementById('new-teacher-name').value = '';
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
            const name = document.getElementById('new-teacher-name').value.trim();
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
                alert('Por favor complete los campos requeridos (Nombre y Email)');
                return;
            }

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
                    const fullName = `${academicDegree} ${name} ${paternalSurname} ${maternalSurname}`.trim();
                    document.getElementById('teacher_search').value = fullName;
                    document.getElementById('teacher_id').value = data.teacher.id;
                    
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

                    resultsContainer.querySelectorAll('li').forEach(li => {
                        li.addEventListener('click', function() {
                            const name = this.getAttribute('data-name');
                            searchInput.value = name;
                            hiddenInput.value = name;
                            resultsContainer.classList.add('hidden');
                        });
                    });
                }

                document.addEventListener('click', function(event) {
                    if (!searchInput.contains(event.target) && !resultsContainer.contains(event.target)) {
                        resultsContainer.classList.add('hidden');
                    }
                });
            }
        });

        // ========== FUNCIONES PARA MODAL DE CREACIÓN DE PROFESIÓN ==========
        function openCreateProfessionModal() {
            document.getElementById('new-profession-name').value = '';
            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'create-profession-modal' }));
        }

        function createProfession() {
            const name = document.getElementById('new-profession-name').value.trim();
            
            if (!name) {
                alert('Por favor ingrese el nombre de la profesión');
                return;
            }

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
                    document.getElementById('new-teacher-profession-search').value = data.profession.name;
                    document.getElementById('new-teacher-profession').value = data.profession.name;
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
                    <x-label for="new-teacher-name" :value="__('Nombre *')" />
                    <x-input id="new-teacher-name" class="block mt-1 w-full" type="text" required />
                </div>
                <div>
                    <x-label for="new-teacher-birth-date" :value="__('Fecha de Nacimiento')" />
                    <x-input id="new-teacher-birth-date" class="block mt-1 w-full" type="date" />
                </div>
                <div>
                    <x-label for="new-teacher-ci" :value="__('CI')" />
                    <x-input id="new-teacher-ci" class="block mt-1 w-full" type="text" />
                </div>
            </div>

            <!-- Datos de Contacto -->
            <h3 class="text-md font-semibold mb-2 text-indigo-700">Datos de Contacto</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <x-label for="new-teacher-email" :value="__('Email *')" />
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
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
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
                        <option value="Si">Sí</option>
                    </select>
                </div>
                <div>
                    <x-label for="new-teacher-esam" :value="__('¿Es trabajador de ESAM?')" />
                    <select id="new-teacher-esam" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">
                        <option value="No">No</option>
                        <option value="Si">Sí</option>
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
