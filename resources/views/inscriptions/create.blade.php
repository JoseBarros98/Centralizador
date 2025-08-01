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
                                    <x-label for="phone" :value="__('Teléfono')" />
                                    <x-input id="phone" class="block mt-1 w-full" type="text" name="phone" :value="old('phone')" required />
                                    @error('phone')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <x-label for="profession" :value="__('Profesión')" />
                                    <x-input id="profession" class="block mt-1 w-full" type="text" name="profession" :value="old('profession')" required autocomplete="off" />
                                    <div id="profession-suggestions" class="absolute z-10 w-full bg-white shadow-lg rounded-md mt-1 max-h-60 overflow-auto hidden"></div>
                                    @error('profession')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <x-label for="residence" :value="__('Residencia')" />
                                    <x-input id="residence" class="block mt-1 w-full" type="text" name="residence" :value="old('residence')" required autocomplete="off" />
                                    <div id="residence-suggestions" class="absolute z-10 w-full bg-white shadow-lg rounded-md mt-1 max-h-60 overflow-auto hidden"></div>
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
                                        @foreach($programs as $id => $name)
                                            <option value="{{ $id }}" {{ old('program_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                    @error('program_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <x-label for="location" :value="__('Sede')" />
                                    <x-input id="location" class="block mt-1 w-full" type="text" name="location" :value="old('location')" required />
                                    @error('location')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
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
                                </div>
                            </div>
                        </div>

                        <!-- DATOS DE PAGO -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-indigo-700 mb-4">Datos de Pago</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <x-label for="payment_plan" :value="__('Plan de Pago')" />
                                    <select id="payment_plan" name="payment_plan" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">
                                        <option value="">Seleccionar plan de pago</option>
                                        <option value="contado" {{ old('payment_plan') == 'contado' ? 'selected' : '' }}>Contado</option>
                                        <option value="credito" {{ old('payment_plan') == 'credito' ? 'selected' : '' }}>Crédito</option>
                                    </select>
                                    @error('payment_plan')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <x-label for="payment_method" :value="__('Medio de Pago')" />
                                    <select id="payment_method" name="payment_method" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">
                                        <option value="">Seleccionar medio de pago</option>
                                        <option value="efectivo" {{ old('payment_method') == 'efectivo' ? 'selected' : '' }}>Efectivo</option>
                                        <option value="QR" {{ old('payment_method') == 'QR' ? 'selected' : '' }}>QR</option>
                                        <option value="deposito" {{ old('payment_method') == 'deposito' ? 'selected' : '' }}>Depósito</option>
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
                                <div>
                                    <x-label for="balance" :value="__('Saldo (Bs)')" />
                                    <x-input id="balance" class="block mt-1 w-full bg-gray-100" type="number" step="0.01" readonly />
                                    <p class="text-xs text-gray-500 mt-1">Campo calculado (Total por pagar - Total Pagado)</p>
                                </div>
                            </div>
                        </div>

                        <!-- DOCUMENTOS -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Archivos</label>
                            <div id="file-container">
                                <div class="file-group mb-2">
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

            // Calcular el saldo
            const totalPaidInput = document.getElementById('total_paid');
            const balanceInput = document.getElementById('balance');

            function calculateBalance() {
                const totalToPay = parseFloat(totalToPayInput.value) || 0;
                const totalPaid = parseFloat(totalPaidInput.value) || 0;
                balanceInput.value = (totalToPay - totalPaid).toFixed(2);
            }

            calculateBalance();
            totalPaidInput.addEventListener('input', calculateBalance);
            

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
            
            // Autocompletado para residencia
            setupAutocomplete('residence', 'residence-suggestions');
            
            // Autocompletado para profesión
            setupAutocomplete('profession', 'profession-suggestions');

            // Autocompletado para genero
            setupAutocomplete('gender', 'gender-suggestions');
            
            // Autocompletado para location (sede)
            setupAutocomplete('location_id', 'location-suggestions');
            
            // Función para configurar autocompletado
            function setupAutocomplete(fieldId, suggestionsId) {
                const input = document.getElementById(fieldId);
                const suggestionsContainer = document.getElementById(suggestionsId) || createSuggestionsContainer(fieldId);
                
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
                if (field === 'residence') {
                    url = `/api/suggestions/residence?query=${encodeURIComponent(query)}`;
                } else if (field === 'profession') {
                    url = `/api/suggestions/profession?query=${encodeURIComponent(query)}`;
                } else if (field === 'location_id') {
                    // Para location, usamos la misma API de residencia temporalmente
                    // En un caso real, deberías crear un endpoint específico para locations
                    url = `/api/suggestions/residence?query=${encodeURIComponent(query)}`;
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
    </script>
</x-app-layout>
