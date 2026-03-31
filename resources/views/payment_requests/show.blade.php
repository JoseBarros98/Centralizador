<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-white leading-tight">
                {{ __('Detalle de Solicitud de Pago') }}
            </h2>
            <a href="{{ route('payment_requests.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-800 focus:outline-none focus:border-gray-700 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                Volver al Listado
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <!-- Estado y Acciones -->
                    <div class="mb-6 flex justify-between items-center">
                        <div>
                            <span class="px-4 py-2 text-sm font-semibold rounded-full 
                                @if($paymentRequest->status === 'Pendiente') bg-yellow-100 text-yellow-800
                                @elseif($paymentRequest->status === 'Aprobado') bg-green-100 text-green-800
                                @elseif($paymentRequest->status === 'Realizado') bg-blue-100 text-blue-800
                                @else bg-red-100 text-red-800
                                @endif">
                                {{ $paymentRequest->status }}
                            </span>
                        </div>
                        @if($paymentRequest->status === 'Pendiente')
                            <a href="{{ route('payment_requests.edit', $paymentRequest) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                                Editar Solicitud
                            </a>
                        @endif
                    </div>

                    <!-- DATOS DE LA SOLICITUD -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-indigo-700 mb-4 border-b pb-2">Datos de la Solicitud</h3>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Tipo de Solicitud</label>
                                <p class="mt-1 text-base font-semibold">
                                    <span class="px-3 py-1 rounded-full text-sm
                                        @if($paymentRequest->request_type === 'Tutoria') bg-orange-100 text-orange-800
                                        @else bg-blue-100 text-blue-800
                                        @endif">
                                        {{ $paymentRequest->request_type === 'Tutoria' ? '📚 Tutoría' : '💼 Módulo' }}
                                    </span>
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Número de Planilla</label>
                                <p class="mt-1 text-base text-gray-900 font-semibold">{{ $paymentRequest->payroll_number ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Fecha de Solicitud</label>
                                <p class="mt-1 text-base text-gray-900 font-semibold">
                                    {{ $paymentRequest->request_date ? $paymentRequest->request_date->format('d/m/Y') : 'N/A' }}
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Número de Factura</label>
                                <p class="mt-1 text-base text-gray-900 font-semibold">{{ $paymentRequest->invoice_number ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Monto Total</label>
                                <p class="mt-1 text-xl text-gray-800 font-bold">Bs. {{ number_format($paymentRequest->total_amount, 2) }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Retención</label>
                                <div class="mt-1">
                                    @if($paymentRequest->retention_amount > 0)
                                        @php
                                            $breakdown = $paymentRequest->retention_breakdown;
                                        @endphp
                                        
                                        @if($breakdown['esam'] > 0 && $breakdown['no_factura'] > 0)
                                            {{-- Trabajador ESAM que no factura: doble retención --}}
                                            <p class="text-sm text-purple-600 font-semibold">
                                                🏢 ESAM (30%): - Bs. {{ number_format($breakdown['esam'], 2) }}
                                            </p>
                                            <p class="text-sm text-orange-600 font-semibold">
                                                📄 No Factura (16% del saldo): - Bs. {{ number_format($breakdown['no_factura'], 2) }}
                                            </p>
                                            <p class="text-lg text-red-600 font-bold border-t border-gray-300 mt-1 pt-1">
                                                Total Retención: - Bs. {{ number_format($paymentRequest->retention_amount, 2) }}
                                            </p>
                                            <span class="text-xs text-gray-600">({{ number_format($paymentRequest->retention_percentage, 1) }}% del total)</span>
                                        @elseif($breakdown['esam'] > 0)
                                            {{-- Solo retención ESAM --}}
                                            <p class="text-lg text-purple-600 font-semibold">
                                                - Bs. {{ number_format($paymentRequest->retention_amount, 2) }}
                                            </p>
                                            <span class="text-xs text-purple-600">(30% - Trabajador ESAM con factura)</span>
                                        @else
                                            {{-- Solo retención por no facturar --}}
                                            <p class="text-lg text-red-600 font-semibold">
                                                - Bs. {{ number_format($paymentRequest->retention_amount, 2) }}
                                            </p>
                                            <span class="text-xs text-orange-600">(16% - No factura)</span>
                                        @endif
                                    @else
                                        <p class="text-lg text-green-600 font-semibold">Bs. 0.00</p>
                                        <span class="text-xs text-green-600">(Sin retención - Factura)</span>
                                    @endif
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Monto Neto a Pagar</label>
                                <p class="mt-1 text-2xl text-green-600 font-bold">Bs. {{ number_format($paymentRequest->net_amount, 2) }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">
                                    {{ $paymentRequest->request_type === 'Tutoria' ? 'Estudiantes en Tutoría' : 'Total Inscritos Activos' }}
                                </label>
                                <p class="mt-1 text-base text-gray-900 font-semibold">{{ $paymentRequest->students_count }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- DATOS DEL PROGRAMA -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-indigo-700 mb-4 border-b pb-2">Datos del Programa</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-gray-50 p-4 rounded-lg">
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Código Contable</label>
                                <p class="mt-1 text-base text-gray-900 font-semibold">{{ $paymentRequest->module->program->accounting_code ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Nombre del Programa</label>
                                <p class="mt-1 text-base text-gray-900 font-semibold">{{ $paymentRequest->module->program->category }} en {{ $paymentRequest->module->program->name ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Área</label>
                                <p class="mt-1 text-base text-gray-900 font-semibold">{{ $paymentRequest->module->program->area ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- DATOS DEL MÓDULO -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-indigo-700 mb-4 border-b pb-2">
                            Datos del Módulo
                            @if($paymentRequest->request_type === 'Tutoria')
                                <span class="text-sm text-orange-600">(Datos de Tutoría)</span>
                            @endif
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-gray-50 p-4 rounded-lg">
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Nombre del Módulo</label>
                                <p class="mt-1 text-base text-gray-900 font-semibold">{{ $paymentRequest->module->name }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Fecha de Inicio</label>
                                <p class="mt-1 text-base text-gray-900 font-semibold">
                                    {{ $paymentRequest->start_date ? $paymentRequest->start_date->format('d/m/Y') : 'N/A' }}
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Fecha de Finalización</label>
                                <p class="mt-1 text-base text-gray-900 font-semibold">
                                    {{ $paymentRequest->end_date ? $paymentRequest->end_date->format('d/m/Y') : 'N/A' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- DATOS DEL DOCENTE -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-indigo-700 mb-4 border-b pb-2">
                            Datos del Docente
                            @if($paymentRequest->request_type === 'Tutoria' && $paymentRequest->tutoringTeacher)
                                <span class="text-sm text-orange-600">(Docente de Tutoría)</span>
                            @endif
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-gray-50 p-4 rounded-lg">
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Nombre Completo</label>
                                <p class="mt-1 text-base text-gray-900 font-semibold">{{ $paymentRequest->teacher->full_name ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">CI</label>
                                <p class="mt-1 text-base text-gray-900 font-semibold">{{ $paymentRequest->teacher->ci ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Emite Factura</label>
                                <p class="mt-1 text-base text-gray-900 font-semibold">{{ $paymentRequest->teacher->bill ?? 'No' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Banco</label>
                                <p class="mt-1 text-base text-gray-900 font-semibold">{{ $paymentRequest->teacher->bank ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Número de Cuenta</label>
                                <p class="mt-1 text-base text-gray-900 font-semibold">{{ $paymentRequest->teacher->account_number ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- OBSERVACIONES -->
                    @if($paymentRequest->observations)
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-indigo-700 mb-4 border-b pb-2">Observaciones</h3>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="text-gray-700">{{ $paymentRequest->observations }}</p>
                            </div>
                        </div>
                    @endif

                    <!-- CAMBIAR ESTADO -->
                    @if($paymentRequest->status !== 'Realizado')
                        <div class="mt-8 border-t pt-6">
                            <h3 class="text-lg font-semibold text-indigo-700 mb-4">Cambiar Estado</h3>
                            <form action="{{ route('payment_requests.cambiar_estado', $paymentRequest) }}" method="POST" x-data="{ selectedStatus: '{{ $paymentRequest->status }}', showDateField: false }">
                                @csrf
                                @method('PATCH')
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="status" class="block text-sm font-medium text-gray-700">Nuevo Estado</label>
                                        <select name="status" id="status" 
                                                x-model="selectedStatus"
                                                @change="showDateField = ($event.target.value === 'Rechazado')"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                            <option value="Pendiente">Pendiente</option>
                                            <option value="Aprobado">Aprobado</option>
                                            <option value="Rechazado">Rechazado</option>
                                            <option value="Realizado">Realizado</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="observations" class="block text-sm font-medium text-gray-700">Observaciones (opcional)</label>
                                        <textarea name="observations" id="observations" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"></textarea>
                                    </div>
                                </div>
                                
                                <!-- Campo de fecha para reprogramación (solo visible si se rechaza) -->
                                <div x-show="showDateField" x-cloak class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                                    <label for="new_request_date" class="block text-sm font-medium text-gray-700 mb-2">
                                        <span class="text-red-600">*</span> Fecha para Nueva Solicitud
                                    </label>
                                    <input type="date" 
                                           name="new_request_date" 
                                           id="new_request_date"
                                           min="{{ \Carbon\Carbon::tomorrow()->format('Y-m-d') }}"
                                           class="block w-full md:w-1/2 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                           :required="showDateField">
                                    <p class="mt-2 text-sm text-gray-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Al rechazar esta solicitud, se creará automáticamente una nueva solicitud con la fecha que especifiques.
                                    </p>
                                    @error('new_request_date')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="mt-4">
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                                        Actualizar Estado
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif

                    <!-- INFORMACIÓN DE AUDITORÍA -->
                    @role('admin')
                    <div class="mt-8 border-t pt-6">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">Información de Auditoría</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600">
                            <div>
                                <span class="font-medium">Creado por:</span> {{ $paymentRequest->creator->name ?? 'N/A' }}
                            </div>
                            <div>
                                <span class="font-medium">Fecha de creación:</span> {{ $paymentRequest->created_at->format('d/m/Y H:i') }}
                            </div>
                            @if($paymentRequest->updated_at != $paymentRequest->created_at)
                                <div>
                                    <span class="font-medium">Última actualización:</span> {{ $paymentRequest->updated_at->format('d/m/Y H:i') }}
                                </div>
                            @endif
                        </div>
                    </div>
                    @endrole
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
