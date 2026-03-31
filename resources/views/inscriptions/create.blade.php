<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Crear Nueva Inscripción') }}
        </h2>
    </x-slot>

    <div >
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Alerta para CI existente -->
            <div id="ci-alert-container" class="mb-4"></div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('inscriptions.store') }}" enctype="multipart/form-data">
                        @csrf

                        <!-- DATOS PERSONALES -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-indigo-700 mb-4">Datos Personales</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <x-label for="first_name" :value="__('Nombre')" />
                                    <x-input id="first_name" class="block mt-1 w-full" type="text" name="first_name" :value="old('first_name')" required autofocus />
                                    @error('first_name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <x-label for="paternal_surname" :value="__('Apellido Paterno')" />
                                    <x-input id="paternal_surname" class="block mt-1 w-full" type="text" name="paternal_surname" :value="old('paternal_surname')" />
                                    @error('paternal_surname')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <x-label for="maternal_surname" :value="__('Apellido Materno')" />
                                    <x-input id="maternal_surname" class="block mt-1 w-full" type="text" name="maternal_surname" :value="old('maternal_surname')" />
                                    @error('maternal_surname')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                                
                                <div>
                                    <x-label for="birth_date" :value="__('Fecha de Nacimiento')" />
                                    <x-input id="birth_date" class="block mt-1 w-full" type="date" name="birth_date" :value="old('birth_date')" />
                                    @error('birth_date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <x-label for="ci" :value="__('CI')" />
                                    <x-input id="ci" class="block mt-1 w-full" type="text" name="ci" :value="old('ci')" required />
                                    @error('ci')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <x-label for="gender" :value="__('Género')" />
                                    <select id="gender" name="gender" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">
                                        <option value="">Seleccionar género</option>
                                        <option value="Masculino" {{ old('gender') == 'Masculino' ? 'selected' : '' }}>Masculino</option>
                                        <option value="Femenino" {{ old('gender') == 'Femenino' ? 'selected' : '' }}>Femenino</option>
                                    </select>
                                    @error('gender')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <x-label for="email" :value="__('Correo Electrónico')" />
                                    <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required />
                                    @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                                
                                <div>
                                    <x-label for="phone" :value="__('Teléfono')" />
                                    <x-input id="phone" class="block mt-1 w-full" type="text" name="phone" :value="old('phone')" required />
                                    @error('phone')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <x-label for="civil_status" :value="__('Estado Civil')" />
                                    <select id="civil_status" name="civil_status" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">
                                        <option value="">Seleccionar estado civil</option>
                                        <option value="Soltero" {{ old('civil_status') == 'Soltero' ? 'selected' : '' }}>Soltero</option>
                                        <option value="Casado" {{ old('civil_status') == 'Casado' ? 'selected' : '' }}>Casado</option>
                                        <option value="Divorciado" {{ old('civil_status') == 'Divorciado' ? 'selected' : '' }}>Divorciado</option>
                                        <option value="Viudo" {{ old('civil_status') == 'Viudo' ? 'selected' : '' }}>Viudo</option>
                                    </select>
                                    @error('civil_status')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>

                                <div class="relative">
                                    <div class="flex items-center justify-between mb-1">
                                        <x-label for="profession" :value="__('Profesión')" />
                                        <button type="button" onclick="openCreateProfessionModal()" 
                                                class="text-xs text-indigo-600 hover:text-indigo-800 flex items-center gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                            </svg>
                                            Nueva Profesión
                                        </button>
                                    </div>
                                    <input type="text" id="profession-search" placeholder="Buscar profesión..." 
                                           class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                           autocomplete="off" value="{{ old('profession_id') ? $professions[old('profession_id')] : '' }}">
                                    <input type="hidden" id="profession" name="profession_id" value="{{ old('profession_id') }}">
                                    <div id="profession-results" class="absolute z-10 w-full bg-white shadow-lg rounded-md mt-1 max-h-60 overflow-auto hidden"></div>
                                    @error('profession_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>

                                <div class="relative">
                                    <div class="flex items-center justify-between mb-1">
                                        <x-label for="university" :value="__('Universidad')" />
                                        <button type="button" onclick="openCreateUniversityModal()" 
                                                class="text-xs text-indigo-600 hover:text-indigo-800 flex items-center gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                            </svg>
                                            Nueva Universidad
                                        </button>
                                    </div>
                                    <input type="text" id="university-search" placeholder="Buscar universidad..." 
                                           class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                           autocomplete="off" value="{{ old('university_id') ? $universities[old('university_id')] : '' }}">
                                    <input type="hidden" id="university" name="university_id" value="{{ old('university_id') }}">
                                    <div id="university-results" class="absolute z-10 w-full bg-white shadow-lg rounded-md mt-1 max-h-60 overflow-auto hidden"></div>
                                    @error('university_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <x-label for="residence" :value="__('Departamento de Residencia')" />
                                    <select id="residence" name="residence" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                        <option value="">Seleccionar Residencia</option>
                                        <option value="La Paz" {{ old('residence') == 'La Paz' ? 'selected' : '' }}>La Paz</option>
                                        <option value="Cochabamba" {{ old('residence') == 'Cochabamba' ? 'selected' : '' }}>Cochabamba</option>
                                        <option value="Santa Cruz" {{ old('residence') == 'Santa Cruz' ? 'selected' : '' }}>Santa Cruz</option>
                                        <option value="Oruro" {{ old('residence') == 'Oruro' ? 'selected' : '' }}>Oruro</option>
                                        <option value="Potosí" {{ old('residence') == 'Potosí' ? 'selected' : '' }}>Potosí</option>
                                        <option value="Tarija" {{ old('residence') == 'Tarija' ? 'selected' : '' }}>Tarija</option>
                                        <option value="Chuquisaca" {{ old('residence') == 'Chuquisaca' ? 'selected' : '' }}>Chuquisaca</option>
                                        <option value="Beni" {{ old('residence') == 'Beni' ? 'selected' : '' }}>Beni</option>
                                        <option value="Pando" {{ old('residence') == 'Pando' ? 'selected' : '' }}>Pando</option>
                                    </select>
                                    @error('residence')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                            </div>
                        </div>

                        <!-- DATOS DEL PROGRAMA -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-indigo-700 mb-4">Datos del Programa</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <x-label for="program_id" :value="__('Programa')" />
                                    <select id="program_id" name="program_id" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">
                                        <option value="">Seleccionar programa</option>
                                        @foreach($programs as $program)
                                            <option value="{{ $program->id }}" {{ old('program_id') == $program->id ? 'selected' : '' }}>{{ $program->category }} en {{ $program->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('program_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <x-label for="location" :value="__('Sede del asesor')" />
                                    <select id="location" name="location" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                        <option value="">Seleccionar sede</option>
                                        <option value="ESAM Latam" {{ old('location') == 'ESAM Latam' ? 'selected' : '' }}>ESAM Latam</option>
                                        <option value="Otra Sede" {{ old('location') == 'Otra Sede' ? 'selected' : '' }}>Otra Sede</option>
                                    </select>
                                    @error('location')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                    <p class="text-xs text-gray-500 mt-1">Si el asesor no es de la sede seleccione "Otra Sede"</p>
                                </div>
                                <div>
                                    <x-label for="inscription_date" :value="__('Fecha de Inscripción')" />
                                    <x-input id="inscription_date" class="block mt-1 w-full" type="date" name="inscription_date" :value="old('inscription_date', date('Y-m-d'))" required />
                                    @error('inscription_date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <x-label for="certification" :value="__('Certificación')" />
                                    <x-input id="certification" class="block mt-1 w-full" type="text" name="certification" :value="old('certification')" required />
                                    @error('certification')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                    <p class="text-xs text-gray-500 mt-1">Inserte las siglas en mayusculas Ej: UNSXX, ISPI</p>
                                </div>
                            </div>
                        </div>

                        <!-- DATOS DE PAGO -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-indigo-700 mb-4">Datos de Pago</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <x-label for="payment_plan" :value="__('Plan de Pago')" />
                                    <x-input id="payment_plan" class="block mt-1 w-full" type="text" name="payment_plan" :value="old('payment_plan')" />
                                    <p class="text-xs text-gray-500 mt-1">Ej: Contado, Crédito, Descuento por Planilla, etc.</p>
                                    @error('payment_plan')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <x-label for="payment_method" :value="__('Medio de Pago')" />
                                    <select id="payment_method" name="payment_method" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">
                                        <option value="">Seleccionar medio de pago</option>
                                        <option value="Efectivo" {{ old('payment_method') == 'Efectivo' ? 'selected' : '' }}>Efectivo</option>
                                        <option value="QR" {{ old('payment_method') == 'QR' ? 'selected' : '' }}>QR</option>
                                        <option value="Deposito" {{ old('payment_method') == 'Deposito' ? 'selected' : '' }}>Depósito</option>
                                        <option value="Transferencia" {{ old('payment_method') == 'Transferencia' ? 'selected' : '' }}>Transferencia</option>
                                    </select>
                                    @error('payment_method')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <x-label for="status" :value="__('Estado')" />
                                    <select id="status" name="status" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">
                                        <option value="">Seleccionar estado</option>
                                        <option value="Completo" {{ old('status') == 'Completo' ? 'selected' : '' }}>Completo</option>
                                        <option value="Completando" {{ old('status') == 'Completando' ? 'selected' : '' }}>Completando</option>
                                        <option value="Adelanto" {{ old('status') == 'Adelanto' ? 'selected' : '' }}>Adelanto</option>
                                    </select>
                                    @error('status')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <x-label for="enrollment_fee" :value="__('Matrícula (Bs)')" />
                                    <x-input id="enrollment_fee" class="block mt-1 w-full" type="number" step="0.01" name="enrollment_fee" :value="old('enrollment_fee')" required />
                                    @error('enrollment_fee')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <x-label for="first_installment" :value="__('Primera Cuota (Bs)')" />
                                    <x-input id="first_installment" class="block mt-1 w-full" type="number" step="0.01" name="first_installment" :value="old('first_installment')" required />
                                    @error('first_installment')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <x-label for="total_to_pay" :value="__('Total por pagar (Bs)')" />
                                    <x-input id="total_to_pay" class="block mt-1 w-full bg-gray-100" type="number" step="0.01" readonly />
                                    <p class="text-xs text-gray-500 mt-1">Campo calculado (Matrícula + Primera Cuota)</p>
                                </div>
                                <div>
                                    <x-label for="total_paid" :value="__('Total Pagado (Bs)')" />
                                    <x-input id="total_paid" class="block mt-1 w-full" type="number" step="0.01" name="total_paid" :value="old('total_paid')" required />
                                    @error('total_paid')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                            </div>
                        </div>

                        <!-- DOCUMENTOS -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Archivos</label>
                            <p class="text-xs text-gray-500 mt-1">Tipos de documentos permitidos: PDF, JPG, JPEG, PNG. Peso máximo: 2MB.</p>
                            <div id="file-container">
                                <div class="file-group mb-2">
                                    <div class="flex items-center space-x-2">
                                        <select name="document_types[]" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block shadow-sm sm:text-sm border-gray-300 rounded-md">
                                            <option value="">Tipo de documento</option>
                                            <option value="ci">Cédula de Identidad</option>
                                            <option value="titulo">Título en Provisión Nacional</option>
                                            <option value="diploma">Diploma de Grado Académico</option>
                                            <option value="nacimiento">Certificado de Nacimiento</option>
                                            <option value="documentacion_completa">Documentación Completa</option>
                                            <option value="compromiso">Carta de Compromiso</option>
                                            <option value="recibo">Recibo</option>
                                            <option value="factura">Factura</option>
                                            <option value="comprobante_pago">Comprobante de Pago</option>
                                        </select>
                                        <input type="file" name="document_files[]" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block shadow-sm sm:text-sm border-gray-300 rounded-md" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx">
                                        <button type="button" class="remove-file px-2 py-1 bg-red-500 text-white rounded hover:bg-red-600" style="display: none;">Eliminar</button>
                                    </div>
                                    <input type="text" name="document_descriptions[]" placeholder="Descripción del archivo" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                </div>
                            </div>
                            <button type="button" id="add-file" class="mt-2 inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Añadir archivo
                            </button>
                            @error('document_files')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                            @error('document_files.*')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <!-- NOTAS -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-indigo-700 mb-4">Notas</h3>
                            <div>
                                <x-label for="notes" :value="__('Notas')" />
                                <textarea id="notes" name="notes" rows="3" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">{{ old('notes') }}</textarea>
                                @error('notes')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('inscriptions.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-400 active:bg-gray-500 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150 mr-3">
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
        // Esperar a que el DOM esté completamente cargado
        document.addEventListener('DOMContentLoaded', function() {
            // ========== BÚSQUEDA DE PROFESIONES Y UNIVERSIDADES ==========
            setupSearchField('profession', '/api/inscriptions/search-professions');
            setupSearchField('university', '/api/inscriptions/search-universities');

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
                        resultsContainer.innerHTML = '';
                        return;
                    }

                    searchTimeout = setTimeout(() => {
                        fetch(`${apiUrl}?q=${encodeURIComponent(query)}`)
                            .then(response => response.json())
                            .then(data => {
                                displayResults(data, fieldName);
                            })
                            .catch(error => {
                                console.error('Error al buscar:', error);
                            });
                    }, 300);
                });

                // Mostrar resultados
                function displayResults(data, fieldName) {
                    const resultsContainer = document.getElementById(`${fieldName}-results`);
                    const searchInput = document.getElementById(`${fieldName}-search`);
                    
                    if (data.length === 0) {
                        resultsContainer.innerHTML = '<div class="p-2 text-gray-500 text-sm">No se encontraron resultados</div>';
                        resultsContainer.classList.remove('hidden');
                        return;
                    }

                    let html = '<ul class="divide-y divide-gray-200">';
                    data.forEach(item => {
                        const displayText = fieldName === 'university' && item.initials 
                            ? `${item.initials} - ${item.name}` 
                            : item.name;
                        
                        html += `
                            <li class="p-2 hover:bg-indigo-50 cursor-pointer text-sm" data-id="${item.id}" data-name="${item.name}">
                                ${displayText}
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
                            
                            document.getElementById(fieldName).value = id;
                            searchInput.value = this.textContent.trim();
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
                        if (this.value.trim() === '') {
                            hiddenInput.value = '';
                        }
                    }, 200);
                });
            }

            // ========== CÁLCULO DEL TOTAL A PAGAR ==========
            // Calcular el total por pagar
            const enrollmentFeeInput = document.getElementById('enrollment_fee');
            const firstInstallmentInput = document.getElementById('first_installment');
            const totalToPayInput = document.getElementById('total_to_pay');
            
            function calculateTotalToPay() {
                const enrollmentFee = parseFloat(enrollmentFeeInput.value) || 0;
                const firstInstallment = parseFloat(firstInstallmentInput.value) || 0;
                totalToPayInput.value = (enrollmentFee + firstInstallment).toFixed(2);
            }
            
            calculateTotalToPay();
            enrollmentFeeInput.addEventListener('input', calculateTotalToPay);
            firstInstallmentInput.addEventListener('input', calculateTotalToPay);

            // Variables para almacenar datos del CI encontrado
            let foundPersonData = null;
            
            // Verificación de CI
            const ciInput = document.getElementById('ci');
            const ciAlertContainer = document.getElementById('ci-alert-container');
            
            // Función para verificar CI
            function checkCI(ci) {
                if (!ci || ci.length < 5) return;
                
                fetch(`/api/check-ci/${ci}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.exists) {
                            foundPersonData = data.data;
                            showCIAlert(data.data, data.message);
                        } else {
                            ciAlertContainer.innerHTML = '';
                            foundPersonData = null;
                        }
                    })
                    .catch(error => {
                        console.error('Error al verificar CI:', error);
                    });
            }
            
            // Función para mostrar alerta de CI
            function showCIAlert(personData, message) {
                const alert = document.createElement('div');
                alert.className = 'bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-4';
                alert.innerHTML = `
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="font-bold">${message || 'CI encontrado'}</p>
                            <p>Este CI ya existe en el sistema.</p>
                        </div>
                        <div>
                            <button id="load-data-btn" type="button" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Cargar datos
                            </button>
                            <button id="dismiss-alert-btn" type="button" class="ml-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                                Ignorar
                            </button>
                        </div>
                    </div>
                `;
                
                ciAlertContainer.innerHTML = '';
                ciAlertContainer.appendChild(alert);
                
                // Evento para cargar datos
                document.getElementById('load-data-btn').addEventListener('click', function() {
                    if (personData) {
                        document.getElementById('first_name').value = personData.first_name || '';
                        document.getElementById('paternal_surname').value = personData.paternal_surname || '';
                        document.getElementById('maternal_surname').value = personData.maternal_surname || '';
                        document.getElementById('phone').value = personData.phone || '';
                        document.getElementById('gender').value = personData.gender || '';
                        document.getElementById('profession').value = personData.profession || '';
                        document.getElementById('residence').value = personData.residence || '';
                        
                        ciAlertContainer.innerHTML = '';
                    }
                });
                
                // Evento para ignorar
                document.getElementById('dismiss-alert-btn').addEventListener('click', function() {
                    ciAlertContainer.innerHTML = '';
                });
            }
            
            // Verificar CI después de que el usuario deje de escribir
            let ciTimeout;
            ciInput.addEventListener('input', function() {
                clearTimeout(ciTimeout);
                ciTimeout = setTimeout(() => {
                    checkCI(this.value);
                }, 500);
            });
            
            // Autocompletado solo para campos de texto
            // Solo profesión es un input de texto, los demás son selects
            setupAutocomplete('profession', 'profession-suggestions');
            
            // Función para configurar autocompletado
            function setupAutocomplete(fieldId, suggestionsId) {
                const input = document.getElementById(fieldId);
                if (!input) {
                    console.warn(`Elemento con ID '${fieldId}' no encontrado para autocompletado`);
                    return;
                }
                
                const suggestionsContainer = document.getElementById(suggestionsId) || createSuggestionsContainer(fieldId);
                if (!suggestionsContainer) {
                    return;
                }
                
                let timeout;
                input.addEventListener('input', function() {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => {
                        const query = this.value.trim();
                        if (query.length >= 2) {
                            fetchSuggestions(fieldId, query, suggestionsContainer);
                        } else {
                            suggestionsContainer.classList.add('hidden');
                        }
                    }, 300);
                });
                
                input.addEventListener('blur', function() {
                    setTimeout(() => {
                        suggestionsContainer.classList.add('hidden');
                    }, 200);
                });
            }
            
            // Función para crear contenedor de sugerencias si no existe
            function createSuggestionsContainer(fieldId) {
                const input = document.getElementById(fieldId);
                if (!input) {
                    console.warn(`Elemento con ID '${fieldId}' no encontrado`);
                    return null;
                }
                const container = document.createElement('div');
                container.id = fieldId + '-suggestions';
                container.className = 'absolute z-10 w-full bg-white shadow-lg rounded-md mt-1 max-h-60 overflow-auto hidden';
                input.parentNode.appendChild(container);
                return container;
            }
            
            // Función para obtener sugerencias
            function fetchSuggestions(field, query, container) {
                // Determinar la URL correcta según el campo
                let url;
                if (field === 'profession') {
                    url = `/api/suggestions/profession?query=${encodeURIComponent(query)}`;
                }
                
                if (!url) return;
                
                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        container.innerHTML = '';
                        
                        if (data.length > 0) {
                            data.forEach(suggestion => {
                                const div = document.createElement('div');
                                div.textContent = suggestion;
                                div.className = 'p-2 cursor-pointer hover:bg-gray-100';
                                div.addEventListener('click', function() {
                                    document.getElementById(field).value = suggestion;
                                    container.classList.add('hidden');
                                });
                                container.appendChild(div);
                            });
                            container.classList.remove('hidden');
                        } else {
                            container.classList.add('hidden');
                        }
                    })
                    .catch(error => {
                        console.error(`Error al obtener sugerencias para ${field}:`, error);
                    });
            }
            
            // Cerrar sugerencias al hacer clic fuera
            document.addEventListener('click', function(event) {
                const suggestionsContainers = document.querySelectorAll('[id$="-suggestions"]');
                suggestionsContainers.forEach(container => {
                    const inputId = container.id.replace('-suggestions', '');
                    const input = document.getElementById(inputId);
                    
                    if (input && !input.contains(event.target) && !container.contains(event.target)) {
                        container.classList.add('hidden');
                    }
                });
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const fileContainer = document.getElementById('file-container');
            const addFileButton = document.getElementById('add-file');

            // Inicializa los botones de eliminar
            updateRemoveButtons();

            addFileButton.addEventListener('click', function() {
                const fileGroup = document.createElement('div');
                fileGroup.className = 'file-group mb-2';
                fileGroup.innerHTML = `
                    <div class="flex items-center space-x-2">
                        <select name="document_types[]" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block shadow-sm sm:text-sm border-gray-300 rounded-md" required>
                            <option value="">Tipo de documento</option>
                            <option value="ci">Cédula de Identidad</option>
                            <option value="titulo">Título en Provisión Nacional</option>
                            <option value="diploma">Diploma de Grado Académico</option>
                            <option value="nacimiento">Certificado de Nacimiento</option>
                            <option value="documentacion_completa">Documentación Completa</option>
                            <option value="compromiso">Carta de Compromiso</option>
                            <option value="congelamiento">Carta de Congelamiento</option>
                            <option value="recibo">Recibo</option>
                            <option value="factura">Factura</option>
                            <option value="comprobante_pago">Comprobante de Pago</option>
                        </select>
                        <input type="file" name="document_files[]" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block shadow-sm sm:text-sm border-gray-300 rounded-md" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx" required>
                        <button type="button" class="remove-file px-2 py-1 bg-red-500 text-white rounded hover:bg-red-600">Eliminar</button>
                    </div>
                    <input type="text" name="document_descriptions[]" placeholder="Descripción del archivo" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                `;
                fileContainer.appendChild(fileGroup);
                updateRemoveButtons();
                fileGroup.querySelector('.remove-file').addEventListener('click', function() {
                    fileGroup.remove();
                    updateRemoveButtons();
                });
            });

            // Botón eliminar para el grupo inicial
            document.querySelectorAll('.remove-file').forEach(button => {
                button.addEventListener('click', function() {
                    this.closest('.file-group').remove();
                    updateRemoveButtons();
                });
            });

            // Mostrar/ocultar botón eliminar según cantidad de grupos
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

        document.addEventListener('DOMContentLoaded', function() {
            // Calcular el total por pagar
            const enrollmentFeeInput = document.getElementById('enrollment_fee');
            const firstInstallmentInput = document.getElementById('first_installment');
            const totalToPayInput = document.getElementById('total_to_pay');
            
            function calculateTotalToPay() {
                const enrollmentFee = parseFloat(enrollmentFeeInput.value) || 0;
                const firstInstallment = parseFloat(firstInstallmentInput.value) || 0;
                totalToPayInput.value = (enrollmentFee + firstInstallment).toFixed(2);
            }
            
            calculateTotalToPay();
            enrollmentFeeInput.addEventListener('input', calculateTotalToPay);
            firstInstallmentInput.addEventListener('input', calculateTotalToPay);

            // Variables para almacenar datos del CI encontrado
            let foundPersonData = null;
            
            // Verificación de CI
            const ciInput = document.getElementById('ci');
            const ciAlertContainer = document.getElementById('ci-alert-container');
            
            // Función para verificar CI
            function checkCI(ci) {
                if (!ci || ci.length < 5) return;
                
                fetch(`/api/check-ci/${ci}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.exists) {
                            foundPersonData = data.data;
                            showCIAlert(data.data, data.message);
                        } else {
                            ciAlertContainer.innerHTML = '';
                            foundPersonData = null;
                        }
                    })
                    .catch(error => {
                        console.error('Error al verificar CI:', error);
                    });
            }
            
            // Función para mostrar alerta de CI
            function showCIAlert(personData, message) {
                const alert = document.createElement('div');
                alert.className = 'bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-4';
                alert.innerHTML = `
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="font-bold">${message || 'CI encontrado'}</p>
                            <p>Este CI ya existe en el sistema.</p>
                        </div>
                        <div>
                            <button id="load-data-btn" type="button" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Cargar datos
                            </button>
                            <button id="dismiss-alert-btn" type="button" class="ml-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                                Ignorar
                            </button>
                        </div>
                    </div>
                `;
                
                ciAlertContainer.innerHTML = '';
                ciAlertContainer.appendChild(alert);
                
                // Evento para cargar datos
                document.getElementById('load-data-btn').addEventListener('click', function() {
                    if (personData) {
                        document.getElementById('first_name').value = personData.first_name || '';
                        document.getElementById('paternal_surname').value = personData.paternal_surname || '';
                        document.getElementById('maternal_surname').value = personData.maternal_surname || '';
                        document.getElementById('phone').value = personData.phone || '';
                        document.getElementById('gender').value = personData.gender || '';
                        document.getElementById('profession').value = personData.profession || '';
                        document.getElementById('residence').value = personData.residence || '';
                        
                        ciAlertContainer.innerHTML = '';
                    }
                });
                
                // Evento para ignorar
                document.getElementById('dismiss-alert-btn').addEventListener('click', function() {
                    ciAlertContainer.innerHTML = '';
                });
            }
            
            // Verificar CI después de que el usuario deje de escribir
            let ciTimeout;
            ciInput.addEventListener('input', function() {
                clearTimeout(ciTimeout);
                ciTimeout = setTimeout(() => {
                    checkCI(this.value);
                }, 500);
            });
            
            // Autocompletado solo para campos de texto
            // Solo profesión es un input de texto, los demás son selects
            setupAutocomplete('profession', 'profession-suggestions');
            
            // Función para configurar autocompletado
            function setupAutocomplete(fieldId, suggestionsId) {
                const input = document.getElementById(fieldId);
                if (!input) {
                    console.warn(`Elemento con ID '${fieldId}' no encontrado para autocompletado`);
                    return;
                }
                
                const suggestionsContainer = document.getElementById(suggestionsId) || createSuggestionsContainer(fieldId);
                if (!suggestionsContainer) {
                    return;
                }
                
                let timeout;
                input.addEventListener('input', function() {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => {
                        const query = this.value.trim();
                        if (query.length >= 2) {
                            fetchSuggestions(fieldId, query, suggestionsContainer);
                        } else {
                            suggestionsContainer.classList.add('hidden');
                        }
                    }, 300);
                });
                
                input.addEventListener('blur', function() {
                    setTimeout(() => {
                        suggestionsContainer.classList.add('hidden');
                    }, 200);
                });
            }
            
            // Función para crear contenedor de sugerencias si no existe
            function createSuggestionsContainer(fieldId) {
                const input = document.getElementById(fieldId);
                if (!input) {
                    console.warn(`Elemento con ID '${fieldId}' no encontrado`);
                    return null;
                }
                const container = document.createElement('div');
                container.id = fieldId + '-suggestions';
                container.className = 'absolute z-10 w-full bg-white shadow-lg rounded-md mt-1 max-h-60 overflow-auto hidden';
                input.parentNode.appendChild(container);
                return container;
            }
            
            // Función para obtener sugerencias
            function fetchSuggestions(field, query, container) {
                // Determinar la URL correcta según el campo
                let url;
                if (field === 'profession') {
                    url = `/api/suggestions/profession?query=${encodeURIComponent(query)}`;
                }
                
                if (!url) return;
                
                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        container.innerHTML = '';
                        
                        if (data.length > 0) {
                            data.forEach(suggestion => {
                                const div = document.createElement('div');
                                div.textContent = suggestion;
                                div.className = 'p-2 cursor-pointer hover:bg-gray-100';
                                div.addEventListener('click', function() {
                                    document.getElementById(field).value = suggestion;
                                    container.classList.add('hidden');
                                });
                                container.appendChild(div);
                            });
                            container.classList.remove('hidden');
                        } else {
                            container.classList.add('hidden');
                        }
                    })
                    .catch(error => {
                        console.error(`Error al obtener sugerencias para ${field}:`, error);
                    });
            }
            
            // Cerrar sugerencias al hacer clic fuera
            document.addEventListener('click', function(event) {
                const suggestionsContainers = document.querySelectorAll('[id$="-suggestions"]');
                suggestionsContainers.forEach(container => {
                    const inputId = container.id.replace('-suggestions', '');
                    const input = document.getElementById(inputId);
                    
                    if (input && !input.contains(event.target) && !container.contains(event.target)) {
                        container.classList.add('hidden');
                    }
                });
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const fileContainer = document.getElementById('file-container');
            const addFileButton = document.getElementById('add-file');

            // Inicializa los botones de eliminar
            updateRemoveButtons();

            addFileButton.addEventListener('click', function() {
                const fileGroup = document.createElement('div');
                fileGroup.className = 'file-group mb-2';
                fileGroup.innerHTML = `
                    <div class="flex items-center space-x-2">
                        <select name="document_types[]" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block shadow-sm sm:text-sm border-gray-300 rounded-md" required>
                            <option value="">Tipo de documento</option>
                            <option value="ci">Cédula de Identidad</option>
                            <option value="titulo">Título en Provisión Nacional</option>
                            <option value="diploma">Diploma de Grado Académico</option>
                            <option value="nacimiento">Certificado de Nacimiento</option>
                            <option value="documentacion_completa">Documentación Completa</option>
                            <option value="compromiso">Carta de Compromiso</option>
                            <option value="congelamiento">Carta de Congelamiento</option>
                            <option value="recibo">Recibo</option>
                            <option value="factura">Factura</option>
                            <option value="comprobante_pago">Comprobante de Pago</option>
                        </select>
                        <input type="file" name="document_files[]" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block shadow-sm sm:text-sm border-gray-300 rounded-md" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx" required>
                        <button type="button" class="remove-file px-2 py-1 bg-red-500 text-white rounded hover:bg-red-600">Eliminar</button>
                    </div>
                    <input type="text" name="document_descriptions[]" placeholder="Descripción del archivo" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                `;
                fileContainer.appendChild(fileGroup);
                updateRemoveButtons();
                fileGroup.querySelector('.remove-file').addEventListener('click', function() {
                    fileGroup.remove();
                    updateRemoveButtons();
                });
            });

            // Botón eliminar para el grupo inicial
            document.querySelectorAll('.remove-file').forEach(button => {
                button.addEventListener('click', function() {
                    this.closest('.file-group').remove();
                    updateRemoveButtons();
                });
            });

            // Mostrar/ocultar botón eliminar según cantidad de grupos
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

        document.addEventListener('DOMContentLoaded', function() {
            // Calcular el total por pagar
            const enrollmentFeeInput = document.getElementById('enrollment_fee');
            const firstInstallmentInput = document.getElementById('first_installment');
            const totalToPayInput = document.getElementById('total_to_pay');
            
            function calculateTotalToPay() {
                const enrollmentFee = parseFloat(enrollmentFeeInput.value) || 0;
                const firstInstallment = parseFloat(firstInstallmentInput.value) || 0;
                totalToPayInput.value = (enrollmentFee + firstInstallment).toFixed(2);
            }
            
            calculateTotalToPay();
            enrollmentFeeInput.addEventListener('input', calculateTotalToPay);
            firstInstallmentInput.addEventListener('input', calculateTotalToPay);

            // Variables para almacenar datos del CI encontrado
            let foundPersonData = null;
            
            // Verificación de CI
            const ciInput = document.getElementById('ci');
            const ciAlertContainer = document.getElementById('ci-alert-container');
            
            // Función para verificar CI
            function checkCI(ci) {
                if (!ci || ci.length < 5) return;
                
                fetch(`/api/check-ci/${ci}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.exists) {
                            foundPersonData = data.data;
                            showCIAlert(data.data, data.message);
                        } else {
                            ciAlertContainer.innerHTML = '';
                            foundPersonData = null;
                        }
                    })
                    .catch(error => {
                        console.error('Error al verificar CI:', error);
                    });
            }
            
            // Función para mostrar alerta de CI
            function showCIAlert(personData, message) {
                const alert = document.createElement('div');
                alert.className = 'bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-4';
                alert.innerHTML = `
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="font-bold">${message || 'CI encontrado'}</p>
                            <p>Este CI ya existe en el sistema.</p>
                        </div>
                        <div>
                            <button id="load-data-btn" type="button" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Cargar datos
                            </button>
                            <button id="dismiss-alert-btn" type="button" class="ml-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                                Ignorar
                            </button>
                        </div>
                    </div>
                `;
                
                ciAlertContainer.innerHTML = '';
                ciAlertContainer.appendChild(alert);
                
                // Evento para cargar datos
                document.getElementById('load-data-btn').addEventListener('click', function() {
                    if (personData) {
                        document.getElementById('first_name').value = personData.first_name || '';
                        document.getElementById('paternal_surname').value = personData.paternal_surname || '';
                        document.getElementById('maternal_surname').value = personData.maternal_surname || '';
                        document.getElementById('phone').value = personData.phone || '';
                        document.getElementById('gender').value = personData.gender || '';
                        document.getElementById('profession').value = personData.profession || '';
                        document.getElementById('residence').value = personData.residence || '';
                        
                        ciAlertContainer.innerHTML = '';
                    }
                });
                
                // Evento para ignorar
                document.getElementById('dismiss-alert-btn').addEventListener('click', function() {
                    ciAlertContainer.innerHTML = '';
                });
            }
            
            // Verificar CI después de que el usuario deje de escribir
            let ciTimeout;
            ciInput.addEventListener('input', function() {
                clearTimeout(ciTimeout);
                ciTimeout = setTimeout(() => {
                    checkCI(this.value);
                }, 500);
            });
            
            // Autocompletado solo para campos de texto
            // Solo profesión es un input de texto, los demás son selects
            setupAutocomplete('profession', 'profession-suggestions');
            
            // Función para configurar autocompletado
            function setupAutocomplete(fieldId, suggestionsId) {
                const input = document.getElementById(fieldId);
                if (!input) {
                    console.warn(`Elemento con ID '${fieldId}' no encontrado para autocompletado`);
                    return;
                }
                
                const suggestionsContainer = document.getElementById(suggestionsId) || createSuggestionsContainer(fieldId);
                if (!suggestionsContainer) {
                    return;
                }
                
                let timeout;
                input.addEventListener('input', function() {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => {
                        const query = this.value.trim();
                        if (query.length >= 2) {
                            fetchSuggestions(fieldId, query, suggestionsContainer);
                        } else {
                            suggestionsContainer.classList.add('hidden');
                        }
                    }, 300);
                });
                
                input.addEventListener('blur', function() {
                    setTimeout(() => {
                        suggestionsContainer.classList.add('hidden');
                    }, 200);
                });
            }
            
            // Función para crear contenedor de sugerencias si no existe
            function createSuggestionsContainer(fieldId) {
                const input = document.getElementById(fieldId);
                if (!input) {
                    console.warn(`Elemento con ID '${fieldId}' no encontrado`);
                    return null;
                }
                const container = document.createElement('div');
                container.id = fieldId + '-suggestions';
                container.className = 'absolute z-10 w-full bg-white shadow-lg rounded-md mt-1 max-h-60 overflow-auto hidden';
                input.parentNode.appendChild(container);
                return container;
            }
            
            // Función para obtener sugerencias
            function fetchSuggestions(field, query, container) {
                // Determinar la URL correcta según el campo
                let url;
                if (field === 'profession') {
                    url = `/api/suggestions/profession?query=${encodeURIComponent(query)}`;
                }
                
                if (!url) return;
                
                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        container.innerHTML = '';
                        
                        if (data.length > 0) {
                            data.forEach(suggestion => {
                                const div = document.createElement('div');
                                div.textContent = suggestion;
                                div.className = 'p-2 cursor-pointer hover:bg-gray-100';
                                div.addEventListener('click', function() {
                                    document.getElementById(field).value = suggestion;
                                    container.classList.add('hidden');
                                });
                                container.appendChild(div);
                            });
                            container.classList.remove('hidden');
                        } else {
                            container.classList.add('hidden');
                        }
                    })
                    .catch(error => {
                        console.error(`Error al obtener sugerencias para ${field}:`, error);
                    });
            }
            
            // Cerrar sugerencias al hacer clic fuera
            document.addEventListener('click', function(event) {
                const suggestionsContainers = document.querySelectorAll('[id$="-suggestions"]');
                suggestionsContainers.forEach(container => {
                    const inputId = container.id.replace('-suggestions', '');
                    const input = document.getElementById(inputId);
                    
                    if (input && !input.contains(event.target) && !container.contains(event.target)) {
                        container.classList.add('hidden');
                    }
                });
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const fileContainer = document.getElementById('file-container');
            const addFileButton = document.getElementById('add-file');

            // Inicializa los botones de eliminar
            updateRemoveButtons();

            addFileButton.addEventListener('click', function() {
                const fileGroup = document.createElement('div');
                fileGroup.className = 'file-group mb-2';
                fileGroup.innerHTML = `
                    <div class="flex items-center space-x-2">
                        <select name="document_types[]" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block shadow-sm sm:text-sm border-gray-300 rounded-md" required>
                            <option value="">Tipo de documento</option>
                            <option value="ci">Cédula de Identidad</option>
                            <option value="titulo">Título en Provisión Nacional</option>
                            <option value="diploma">Diploma de Grado Académico</option>
                            <option value="nacimiento">Certificado de Nacimiento</option>
                            <option value="documentacion_completa">Documentación Completa</option>
                            <option value="compromiso">Carta de Compromiso</option>
                            <option value="congelamiento">Carta de Congelamiento</option>
                            <option value="recibo">Recibo</option>
                            <option value="factura">Factura</option>
                            <option value="comprobante_pago">Comprobante de Pago</option>
                        </select>
                        <input type="file" name="document_files[]" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block shadow-sm sm:text-sm border-gray-300 rounded-md" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx" required>
                        <button type="button" class="remove-file px-2 py-1 bg-red-500 text-white rounded hover:bg-red-600">Eliminar</button>
                    </div>
                    <input type="text" name="document_descriptions[]" placeholder="Descripción del archivo" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                `;
                fileContainer.appendChild(fileGroup);
                updateRemoveButtons();
                fileGroup.querySelector('.remove-file').addEventListener('click', function() {
                    fileGroup.remove();
                    updateRemoveButtons();
                });
            });

            // Botón eliminar para el grupo inicial
            document.querySelectorAll('.remove-file').forEach(button => {
                button.addEventListener('click', function() {
                    this.closest('.file-group').remove();
                    updateRemoveButtons();
                });
            });

            // Mostrar/ocultar botón eliminar según cantidad de grupos
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

        // ========== FUNCIONES PARA MODALES DE CREACIÓN ==========
        function openCreateProfessionModal() {
            document.getElementById('new-profession-name').value = '';
            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'create-profession-modal' }));
        }

        function openCreateUniversityModal() {
            document.getElementById('new-university-initials').value = '';
            document.getElementById('new-university-name').value = '';
            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'create-university-modal' }));
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
                    document.getElementById('profession').value = data.profession.id;
                    
                    // Cerrar modal
                    closeModal('create-profession-modal');
                    
                    // Mostrar mensaje de éxito
                    alert('Profesión creada exitosamente');
                } else {
                    alert('Error al crear la profesión: ' + (data.message || 'Error desconocido'));
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

        // Función para crear universidad
        function createUniversity() {
            const initials = document.getElementById('new-university-initials').value.trim();
            const name = document.getElementById('new-university-name').value.trim();
            
            if (!initials || !name) {
                alert('Por favor complete todos los campos');
                return;
            }

            // Mostrar indicador de carga
            const submitBtn = document.getElementById('create-university-btn');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = 'Guardando...';

            fetch('/universities', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ initials: initials, name: name })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Actualizar el campo de búsqueda con la nueva universidad
                    const displayText = data.university.initials + ' - ' + data.university.name;
                    document.getElementById('university-search').value = displayText;
                    document.getElementById('university').value = data.university.id;
                    
                    // Cerrar modal
                    closeModal('create-university-modal');
                    
                    // Mostrar mensaje de éxito
                    alert('Universidad creada exitosamente');
                } else {
                    alert('Error al crear la universidad: ' + (data.message || 'Error desconocido'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al crear la universidad');
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
                        class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-400 active:bg-gray-500 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150 mr-2">
                    {{ __('Cancelar') }}
                </button>
                <button type="button" id="create-profession-btn" onclick="createProfession()" 
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                    {{ __('Guardar') }}
                </button>
            </div>
        </div>
    </x-modal>

    <!-- Modal para crear nueva universidad -->
    <x-modal id="create-university-modal">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">
                {{ __('Nueva Universidad') }}
            </h2>

            <div class="mb-4">
                <x-label for="new-university-initials" :value="__('Siglas')" />
                <x-input id="new-university-initials" class="block mt-1 w-full" type="text" required autofocus />
            </div>

            <div class="mb-4">
                <x-label for="new-university-name" :value="__('Nombre')" />
                <x-input id="new-university-name" class="block mt-1 w-full" type="text" required />
            </div>

            <div class="flex justify-end">
                <button type="button" onclick="closeModal('create-university-modal')" 
                        class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-400 active:bg-gray-500 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150 mr-2">
                    {{ __('Cancelar') }}
                </button>
                <button type="button" id="create-university-btn" onclick="createUniversity()" 
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                    {{ __('Guardar') }}
                </button>
            </div>
        </div>
    </x-modal>
</x-app-layout>
