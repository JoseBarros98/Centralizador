<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Nueva Solicitud de Pago a Docente') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            @if(!isset($selectedModule) || !$selectedModule)
                <div class="mb-4 bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">⚠️ No se ha seleccionado ningún módulo. Por favor, accede a esta página desde la vista de un programa o docente.</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <!-- Formulario de Solicitud de Pago -->
                    <form id="payment-request-form" method="POST" action="{{ route('payment_requests.store') }}">
                        @csrf

                        <input type="hidden" name="module_id" id="module_id" value="{{ isset($selectedModule) && $selectedModule ? $selectedModule->id : '' }}" required>

                        <!-- DATOS DEL PROGRAMA -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-indigo-700 mb-4">Datos del Programa</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-gray-50 p-4 rounded-lg">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Código Contable</label>
                                    <p id="program-accounting-code" class="mt-1 text-sm text-gray-900 font-semibold">
                                        {{ isset($selectedModule) && $selectedModule ? ($selectedModule->program->accounting_code ?? 'N/A') : '-' }}
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Nombre del Programa</label>
                                    <p id="program-name" class="mt-1 text-sm text-gray-900 font-semibold">
                                        {{ isset($selectedModule) && $selectedModule ? ($selectedModule->program->name ?? 'N/A') : '-' }}
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Área</label>
                                    <p id="program-area" class="mt-1 text-sm text-gray-900 font-semibold">
                                        {{ isset($selectedModule) && $selectedModule ? ($selectedModule->program->area ?? 'N/A') : '-' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- DATOS DEL MÓDULO -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-indigo-700 mb-4">Datos del Módulo</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-gray-50 p-4 rounded-lg">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Nombre del Módulo</label>
                                    <p id="module-name" class="mt-1 text-sm text-gray-900 font-semibold">
                                        {{ isset($selectedModule) && $selectedModule ? $selectedModule->name : '-' }}
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Fecha de Inicio</label>
                                    <p id="module-start-date" class="mt-1 text-sm text-gray-900 font-semibold">
                                        {{ isset($selectedModule) && $selectedModule && $selectedModule->start_date ? $selectedModule->start_date->format('d/m/Y') : '-' }}
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Fecha de Finalización</label>
                                    <p id="module-end-date" class="mt-1 text-sm text-gray-900 font-semibold">
                                        {{ isset($selectedModule) && $selectedModule && $selectedModule->finalization_date ? $selectedModule->finalization_date->format('d/m/Y') : '-' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- DATOS DEL DOCENTE -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-indigo-700 mb-4">Datos del Docente</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-gray-50 p-4 rounded-lg">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Nombre Completo</label>
                                    <p id="teacher-name" class="mt-1 text-sm text-gray-900 font-semibold">
                                        {{ isset($selectedModule) && $selectedModule ? ($selectedModule->teacher->full_name ?? 'N/A') : '-' }}
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">CI</label>
                                    <p id="teacher-ci" class="mt-1 text-sm text-gray-900 font-semibold">
                                        {{ isset($selectedModule) && $selectedModule ? ($selectedModule->teacher->ci ?? 'N/A') : '-' }}
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Emite Factura</label>
                                    <p id="teacher-bill" class="mt-1 text-sm text-gray-900 font-semibold">
                                        {{ isset($selectedModule) && $selectedModule ? ($selectedModule->teacher->bill ?? 'No') : '-' }}
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Banco</label>
                                    <p id="teacher-bank" class="mt-1 text-sm text-gray-900 font-semibold">
                                        {{ isset($selectedModule) && $selectedModule ? ($selectedModule->teacher->bank ?? 'N/A') : '-' }}
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Número de Cuenta</label>
                                    <p id="teacher-account" class="mt-1 text-sm text-gray-900 font-semibold">
                                        {{ isset($selectedModule) && $selectedModule ? ($selectedModule->teacher->account_number ?? 'N/A') : '-' }}
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Total Inscritos Activos</label>
                                    <p id="total-students" class="mt-1 text-sm text-gray-900 font-semibold">
                                        @if(isset($selectedModule) && $selectedModule)
                                            @php
                                                $totalActiveStudents = \App\Models\Inscription::where('program_id', $selectedModule->program_id)
                                                    ->where('academic_status', 'Activo')
                                                    ->count();
                                            @endphp
                                            {{ $totalActiveStudents }}
                                        @else
                                            -
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- DATOS DE LA SOLICITUD -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-indigo-700 mb-4">Datos de la Solicitud</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <x-label for="payroll_number" :value="__('Número de Planilla')" />
                                    <x-input id="payroll_number" class="block mt-1 w-full" type="text" name="payroll_number" :value="old('payroll_number')" />
                                    @error('payroll_number')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <x-label for="request_date" :value="__('Fecha de Solicitud')" />
                                    <x-input id="request_date" class="block mt-1 w-full" type="date" name="request_date" :value="old('request_date', date('Y-m-d'))" required />
                                    @error('request_date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <x-label for="invoice_number" :value="__('Número de Factura')" />
                                    <x-input id="invoice_number" class="block mt-1 w-full" type="text" name="invoice_number" :value="old('invoice_number')" />
                                    @error('invoice_number')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <x-label for="total_amount" :value="__('Total a Pagar (Bs)')" />
                                    <x-input id="total_amount" class="block mt-1 w-full" type="number" step="0.01" name="total_amount" :value="old('total_amount')" required />
                                    @error('total_amount')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <x-label for="status" :value="__('Estado')" />
                                    <select id="status" name="status" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full" required>
                                        <option value="Pendiente" {{ old('status') == 'Pendiente' ? 'selected' : '' }}>Pendiente</option>
                                        <option value="Aprobado" {{ old('status') == 'Aprobado' ? 'selected' : '' }}>Aprobado</option>
                                        <option value="Rechazado" {{ old('status') == 'Rechazado' ? 'selected' : '' }}>Rechazado</option>
                                        <option value="Realizado" {{ old('status') == 'Realizado' ? 'selected' : '' }}>Realizado</option>
                                    </select>
                                    @error('status')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>

                                <div class="md:col-span-2">
                                    <x-label for="observations" :value="__('Observaciones')" />
                                    <textarea id="observations" name="observations" rows="3" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">{{ old('observations') }}</textarea>
                                    @error('observations')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('payment_requests.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-400 active:bg-gray-500 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150 mr-3">
                                Cancelar
                            </a>
                            <x-button id="submit-button" {{ isset($selectedModule) && $selectedModule ? '' : 'disabled' }}>
                                {{ __('Crear Solicitud') }}
                            </x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if(!isset($selectedModule) || !$selectedModule)
    <script>
        // Redirigir a la página de listado si no hay módulo seleccionado
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                window.location.href = "{{ route('payment_requests.index') }}";
            }, 3000);
        });
    </script>
    @endif
</x-app-layout>
