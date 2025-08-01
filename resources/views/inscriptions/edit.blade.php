<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Editar Inscripción') }}
        </h2>
    </x-slot>

    <div x-data="{}">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('inscriptions.update', $inscription) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- DATOS PERSONALES -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-indigo-700 mb-4">Datos Personales</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <x-label for="first_name" :value="__('Nombre')" />
                                    <x-input id="first_name" class="block mt-1 w-full" type="text" name="first_name" :value="old('first_name', $inscription->first_name)" required autofocus />
                                    @error('first_name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <x-label for="paternal_surname" :value="__('Apellido Paterno')" />
                                    <x-input id="paternal_surname" class="block mt-1 w-full" type="text" name="paternal_surname" :value="old('paternal_surname', $inscription->paternal_surname)" />
                                    @error('paternal_surname')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <x-label for="maternal_surname" :value="__('Apellido Materno')" />
                                    <x-input id="maternal_surname" class="block mt-1 w-full" type="text" name="maternal_surname" :value="old('maternal_surname', $inscription->maternal_surname)" />
                                    @error('maternal_surname')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <x-label for="ci" :value="__('CI')" />
                                    <x-input id="ci" class="block mt-1 w-full" type="text" name="ci" :value="old('ci', $inscription->ci)" required />
                                    @error('ci')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <x-label for="gender" :value="__('Género')" />
                                    <select id="gender" name="gender" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">
                                        <option value="">Seleccionar género</option>
                                        <option value="Femenino" {{ old('gender', $inscription->gender) == 'Femenino' ? 'selected' : '' }}>Femenino</option>
                                        <option value="Masculino" {{ old('gender', $inscription->gender) == 'Masculino' ? 'selected' : '' }}>Masculino</option>
                                    </select>
                                    @error('gender')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <x-label for="phone" :value="__('Teléfono')" />
                                    <x-input id="phone" class="block mt-1 w-full" type="text" name="phone" :value="old('phone', $inscription->phone)" required />
                                    @error('phone')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <x-label for="profession" :value="__('Profesión')" />
                                    <x-input id="profession" class="block mt-1 w-full" type="text" name="profession" :value="old('profession', $inscription->profession)" required />
                                    @error('profession')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <x-label for="residence" :value="__('Residencia')" />
                                    <x-input id="residence" class="block mt-1 w-full" type="text" name="residence" :value="old('residence', $inscription->residence)" required />
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
                                            <option value="{{ $id }}" {{ old('program_id', $inscription->program_id) == $id ? 'selected' : '' }}>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                    @error('program_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <x-label for="location" :value="__('Sede')" />
                                    <x-input id="location" class="block mt-1 w-full" type="text" name="location" :value="old('location', $inscription->location)" required />
                                    @error('location')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <x-label for="inscription_date" :value="__('Fecha de Inscripción')" />
                                    <x-input id="inscription_date" class="block mt-1 w-full" type="date" name="inscription_date" :value="old('inscription_date', $inscription->inscription_date->format('Y-m-d'))" required />
                                    @error('inscription_date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <x-label for="certification" :value="__('Certificación')" />
                                    <x-input id="certification" class="block mt-1 w-full" type="text" name="certification" :value="old('certification', $inscription->certification)" required />
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
                                        <option value="contado" {{ old('payment_plan', $inscription->payment_plan) == 'contado' ? 'selected' : '' }}>Contado</option>
                                        <option value="credito" {{ old('payment_plan', $inscription->payment_plan) == 'credito' ? 'selected' : '' }}>Crédito</option>
                                    </select>
                                    @error('payment_plan')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <x-label for="payment_method" :value="__('Medio de Pago')" />
                                    <select id="payment_method" name="payment_method" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">
                                        <option value="efectivo" {{ old('payment_method', $inscription->payment_method) == 'efectivo' ? 'selected' : '' }}>Efectivo</option>
                                        <option value="QR" {{ old('payment_method', $inscription->payment_method) == 'QR' ? 'selected' : '' }}>QR</option>
                                        <option value="deposito" {{ old('payment_method', $inscription->payment_method) == 'deposito' ? 'selected' : '' }}>Depósito</option>
                                    </select>
                                    @error('payment_method')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <x-label for="status" :value="__('Estado')" />
                                    <select id="status" name="status" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">
                                        <option value="Completo" {{ old('status', $inscription->status) == 'Completo' ? 'selected' : '' }}>Completo</option>
                                        <option value="Completando" {{ old('status', $inscription->status) == 'Completando' ? 'selected' : '' }}>Completando</option>
                                        <option value="Adelanto" {{ old('status', $inscription->status) == 'Adelanto' ? 'selected' : '' }}>Adelanto</option>
                                    </select>
                                    @error('status')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <x-label for="enrollment_fee" :value="__('Matrícula (Bs)')" />
                                    <x-input id="enrollment_fee" class="block mt-1 w-full" type="number" step="0.01" name="enrollment_fee" :value="old('enrollment_fee', $inscription->enrollment_fee)" required />
                                    @error('enrollment_fee')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <x-label for="first_installment" :value="__('Primera Cuota (Bs)')" />
                                    <x-input id="first_installment" class="block mt-1 w-full" type="number" step="0.01" name="first_installment" :value="old('first_installment', $inscription->first_installment)" required />
                                    @error('first_installment')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <x-label for="total_to_pay" :value="__('Total por pagar (Bs)')" />
                                    <x-input id="total_to_pay" class="block mt-1 w-full bg-gray-100" type="number" step="0.01" readonly :value="old('total_to_pay', $inscription->enrollment_fee + $inscription->first_installment)" />
                                    <p class="text-xs text-gray-500 mt-1">Campo calculado (Matrícula + Primera Cuota)</p>
                                </div>
                                <div>
                                    <x-label for="total_paid" :value="__('Total Pagado (Bs)')" />
                                    <x-input id="total_paid" class="block mt-1 w-full" type="number" step="0.01" name="total_paid" :value="old('total_paid', $inscription->total_paid)" required />
                                    @error('total_paid')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <x-label for="balance" :value="__('Saldo (Bs)')" />
                                    <x-input id="balance" class="block mt-1 w-full bg-gray-100" type="number" step="0.01" readonly :value="old('balance', ($inscription->enrollment_fee + $inscription->first_installment) - $inscription->total_paid)" />
                                    <p class="text-xs text-gray-500 mt-1">Campo calculado (Total por pagar - Total pagado)</p>
                                </div>
                            </div>
                        </div>

                        <!-- DOCUMENTOS -->
                        <div class="mb-4">
                            <h3 class="text-lg font-semibold text-indigo-700 mb-4">Archivos</h3>
                            <!-- Mostrar documentos existentes -->
                                @if($inscription->documents->count() > 0)
                                    <div class="mt-3">
                                        <button type="button"
                                                class="inline-flex items-center px-4 py-2 bg-green-100 border border-transparent rounded-md font-semibold text-xs text-green-800 uppercase tracking-widest hover:bg-green-200 active:bg-green-300 focus:outline-none focus:border-green-300 focus:ring ring-green-200 disabled:opacity-25 transition ease-in-out duration-150"
                                                @click="$dispatch('open-modal', 'documents-modal')">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            VER {{ $inscription->documents->count() }} DOCUMENTO(S)
                                        </button>
                                    </div>
                                @endif
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
                                <textarea id="notes" name="notes" rows="3" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">{{ old('notes', $inscription->notes) }}</textarea>
                                @error('notes')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('inscriptions.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-400 active:bg-gray-500 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150 mr-3">
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
        <!-- Modal para ver documentos -->
        <x-modal id="documents-modal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">
                    Documentos de {{ $inscription->first_name }} {{ $inscription->paternal_surname }}
                </h2>


                @if($inscription->documents->count() > 0)
                    <div class="mb-4">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Descripción</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($inscription->documents as $document)
                                        <tr>
                                            <td class="px-4 py-2 align-top">
                                                <span class="font-semibold text-indigo-700">{{ ucfirst(str_replace('_', ' ', $document->document_type)) }}</span>
                                            </td>
                                            <td class="px-4 py-2 align-top">
                                                <span class="text-gray-700">{{ $document->description }}</span>
                                            </td>
                                            <td class="px-4 py-2 align-top">
                                                <div class="flex space-x-2">
                                                    <a href="{{ route('documents.serve', $document) }}" target="_blank" class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" title="Ver">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                        </svg>
                                                    </a>
                                                    <a href="{{ route('documents.serve', $document) }}" download class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500" title="Descargar">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                                        </svg>
                                                    </a>
                                                    <form method="POST" action="{{ route('documents.destroy', ['inscription' => $inscription->id, 'document' => $document->id]) }}" onsubmit="return confirm('¿Está seguro que desea eliminar este documento? Esta acción no se puede deshacer.');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500" title="Eliminar">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700">
                                    No hay documentos asociados a esta inscripción.
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="mt-6 flex justify-end">
                    <button type="button" @click="$dispatch('close')" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-400 active:bg-gray-500 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                        Cerrar
                    </button>
                </div>
            </div>
        </x-modal>
    </div>

                    <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const fileContainer = document.getElementById('file-container');
                        const addFileButton = document.getElementById('add-file');

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

                        document.querySelectorAll('.remove-file').forEach(button => {
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
