<x-app-layout>
    @section('header-title', 'Detalles de Inscripción')
    
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Botones de acción -->
            <div class="mb-6 flex justify-end space-x-2">
                @can('inscription.edit')
                <a href="{{ route('inscriptions.edit', $inscription) }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 active:bg-yellow-800 focus:outline-none focus:border-yellow-800 focus:ring ring-yellow-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                    </svg>
                    Editar
                </a>
                @endcan
                @can('viewAny', App\Models\Receipt::class)
                <a href="{{ route('receipts.index', $inscription) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:border-blue-800 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
                    </svg>
                    Historial de Recibos
                </a>
                @endcan
                <a href="{{ route('inscriptions.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-800 focus:outline-none focus:border-gray-800 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                    </svg>
                    Volver
                </a>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Datos personales -->
                        <div class="col-span-1 md:col-span-3">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Datos Personales</h3>
                            <div class="border-t border-gray-200 pt-4">
                                <dl class="grid grid-cols-1 md:grid-cols-3 gap-x-4 gap-y-6">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Código</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $inscription->code }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Nombre</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $inscription->first_name }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Apellido Paterno</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $inscription->paternal_surname }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Apellido Materno</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $inscription->maternal_surname }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">CI</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $inscription->ci }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Teléfono</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $inscription->phone }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Profesión</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $inscription->profession }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Residencia</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $inscription->residence }}</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        <!-- Datos del programa -->
                        <div class="col-span-1 md:col-span-3">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Datos del Programa</h3>
                            <div class="border-t border-gray-200 pt-4">
                                <dl class="grid grid-cols-1 md:grid-cols-3 gap-x-4 gap-y-6">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Programa</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $inscription->program->name }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Asesor</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $inscription->creator->name }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Sede</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $inscription->location->name }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Fecha de Inscripción</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $inscription->inscription_date->format('d/m/Y') }}</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        <!-- Datos de pago -->
                        <div class="col-span-1 md:col-span-3">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Datos de Pago</h3>
                            <div class="border-t border-gray-200 pt-4">
                                <dl class="grid grid-cols-1 md:grid-cols-3 gap-x-4 gap-y-6">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Plan de Pago</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ ucfirst($inscription->payment_plan) }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Medio de Pago</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $inscription->payment_method }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Estado</dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            @if($inscription->status == 'Completo')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    Completo
                                                </span>
                                            @elseif($inscription->status == 'Completando')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                    Completando
                                                </span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                    Adelanto
                                                </span>
                                            @endif
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Matrícula</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ number_format($inscription->enrollment_fee, 2) }} Bs</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Primera Cuota</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ number_format($inscription->first_installment, 2) }} Bs</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Total Pagado</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ number_format($inscription->total_paid, 2) }} Bs</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Número de Recibo</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $inscription->receipt_number }}</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        <!-- Notas -->
                        @if($inscription->notes)
                        <div class="col-span-1 md:col-span-3">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Notas</h3>
                            <div class="border-t border-gray-200 pt-4">
                                <p class="text-sm text-gray-900">{{ $inscription->notes }}</p>
                            </div>
                        </div>
                        @endif

                        <!-- Información del sistema -->
                        <div class="col-span-1 md:col-span-3">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Información del Sistema</h3>
                            <div class="border-t border-gray-200 pt-4">
                                <dl class="grid grid-cols-1 md:grid-cols-3 gap-x-4 gap-y-6">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Creado por</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $inscription->creator->name }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Fecha de Creación</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $inscription->created_at->format('d/m/Y H:i') }}</dd>
                                    </div>
                                    @if($inscription->updated_by)
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Actualizado por</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $inscription->updater->name }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Fecha de Actualización</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $inscription->updated_at->format('d/m/Y H:i') }}</dd>
                                    </div>
                                    @endif
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
