<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Editar Solicitud de Pago') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <!-- Información no editable -->
                    <div class="mb-8 bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">Información del Módulo (No editable)</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                            <div>
                                <span class="font-medium text-gray-500">Programa:</span>
                                <p class="text-gray-900">{{ $paymentRequest->module->program->category }} en {{ $paymentRequest->module->program->name }}</p>
                            </div>
                            <div>
                                <span class="font-medium text-gray-500">Módulo:</span>
                                <p class="text-gray-900">{{ $paymentRequest->module->name }}</p>
                            </div>
                            <div>
                                <span class="font-medium text-gray-500">Docente:</span>
                                <p class="text-gray-900">{{ $paymentRequest->module->teacher->full_name }}</p>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('payment_requests.update', $paymentRequest) }}">
                        @csrf
                        @method('PUT')

                        <!-- DATOS DE LA SOLICITUD -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-indigo-700 mb-4">Datos de la Solicitud</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <x-label for="payroll_number" :value="__('Número de Planilla')" />
                                    <x-input id="payroll_number" class="block mt-1 w-full" type="text" name="payroll_number" :value="old('payroll_number', $paymentRequest->payroll_number)" />
                                    @error('payroll_number')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <x-label for="request_date" :value="__('Fecha de Solicitud')" />
                                    <x-input id="request_date" class="block mt-1 w-full" type="date" name="request_date" :value="old('request_date', $paymentRequest->request_date?->format('Y-m-d'))" required />
                                    @error('request_date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <x-label for="invoice_number" :value="__('Número de Factura')" />
                                    <x-input id="invoice_number" class="block mt-1 w-full" type="text" name="invoice_number" :value="old('invoice_number', $paymentRequest->invoice_number)" />
                                    @error('invoice_number')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <x-label for="total_amount" :value="__('Monto Total (Bs)')" />
                                    <x-input id="total_amount" class="block mt-1 w-full" type="number" step="0.01" name="total_amount" :value="old('total_amount', $paymentRequest->total_amount)" required />
                                    <p class="text-xs text-gray-500 mt-1">
                                        @if($paymentRequest->module->teacher->esam_worker === 'Si' || $paymentRequest->module->teacher->esam_worker === 'Sí')
                                            @if($paymentRequest->module->teacher->bill === 'Si' || $paymentRequest->module->teacher->bill === 'Sí')
                                                <span class="text-purple-600 font-semibold">Trabajador ESAM con factura - Retención del 30%.</span>
                                                <br>Monto neto: Bs. {{ number_format($paymentRequest->total_amount * 0.70, 2) }}
                                            @else
                                                <span class="text-red-600 font-bold">Trabajador ESAM sin factura - Retención del 41.2%.</span>
                                                <br><span class="text-xs">(30% ESAM + 16% del saldo)</span>
                                                <br>Monto neto: Bs. {{ number_format($paymentRequest->total_amount * 0.588, 2) }}
                                            @endif
                                        @elseif($paymentRequest->module->teacher->bill === 'Si' || $paymentRequest->module->teacher->bill === 'Sí')
                                            Sin retención (Docente factura). Monto neto: Bs. {{ number_format($paymentRequest->total_amount, 2) }}
                                        @else
                                            Con retención del 16%. Monto neto: Bs. {{ number_format($paymentRequest->total_amount * 0.84, 2) }}
                                        @endif
                                    </p>
                                    @error('total_amount')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <x-label for="status" :value="__('Estado')" />
                                    <select id="status" name="status" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full" required>
                                        <option value="Pendiente" {{ old('status', $paymentRequest->status) == 'Pendiente' ? 'selected' : '' }}>Pendiente</option>
                                        <option value="Aprobado" {{ old('status', $paymentRequest->status) == 'Aprobado' ? 'selected' : '' }}>Aprobado</option>
                                        <option value="Rechazado" {{ old('status', $paymentRequest->status) == 'Rechazado' ? 'selected' : '' }}>Rechazado</option>
                                        <option value="Realizado" {{ old('status', $paymentRequest->status) == 'Realizado' ? 'selected' : '' }}>Realizado</option>
                                    </select>
                                    @error('status')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>

                                <div class="md:col-span-2">
                                    <x-label for="observations" :value="__('Observaciones')" />
                                    <textarea id="observations" name="observations" rows="3" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">{{ old('observations', $paymentRequest->observations) }}</textarea>
                                    @error('observations')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('payment_requests.show', $paymentRequest) }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-400 active:bg-gray-500 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150 mr-3">
                                Cancelar
                            </a>
                            <x-button>
                                {{ __('Actualizar Solicitud') }}
                            </x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
