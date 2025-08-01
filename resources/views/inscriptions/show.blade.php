<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-white leading-tight">
                {{ __('Detalles de Inscripción') }}
            </h2>
            <div class="flex space-x-2">
                @can('inscription.edit')
                @if(auth()->user()->id === $inscription->created_by)
                <a href="{{ route('inscriptions.edit', $inscription) }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 active:bg-yellow-800 focus:outline-none focus:border-yellow-800 focus:ring ring-yellow-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                    </svg>
                    Editar
                </a>
                @endif
                @endcan
                <a href="{{ route('inscriptions.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-800 focus:outline-none focus:border-gray-800 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                    </svg>
                    Volver
                </a>
            </div>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="space-y-10">

                    <!-- DATOS PERSONALES -->
                    <div>
                        <h3 class="text-lg font-semibold text-indigo-700 mb-4">Datos Personales</h3>
                        <dl class="grid grid-cols-1 md:grid-cols-3 gap-x-4 gap-y-6 border-t border-gray-200 pt-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Código</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $inscription->code }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Nombre Completo</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $inscription->getFullName() }}</dd>
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

                    <!-- DATOS DEL PROGRAMA -->
                    <div>
                        <h3 class="text-lg font-semibold text-indigo-700 mb-4">Datos del Programa</h3>
                        <dl class="grid grid-cols-1 md:grid-cols-3 gap-x-4 gap-y-6 border-t border-gray-200 pt-4">
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
                                <dd class="mt-1 text-sm text-gray-900">{{ $inscription->location }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Fecha de Inscripción</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $inscription->inscription_date->format('d/m/Y') }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Certificación</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $inscription->certification}}</dd>
                            </div>
                        </dl>
                    </div>

                    <!-- DATOS DE PAGO -->
                    <div>
                        <h3 class="text-lg font-semibold text-indigo-700 mb-4">Datos de Pago</h3>
                        <dl class="grid grid-cols-1 md:grid-cols-3 gap-x-4 gap-y-6 border-t border-gray-200 pt-4">
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
                                <dt class="text-sm font-medium text-gray-500">Total a pagar</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ number_format($inscription->first_installment + $inscription->enrollment_fee, 2) }} Bs</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Total Pagado</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ number_format($inscription->total_paid, 2) }} Bs</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Saldo</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ number_format(($inscription->first_installment + $inscription->enrollment_fee) - $inscription->total_paid, 2) }} Bs</dd>
                            </div>
                        </dl>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-indigo-700 mb-4">Documentos</h3>
                        <dd class="mt-1 text-sm text-gray-900">
                            <div class="border-t border-gray-200 pt-4">
                            @if($inscription->documents->count() > 0)
                                <button 
                                    type="button" 
                                    @click="$dispatch('open-modal', 'documents-modal')"
                                    class="inline-flex items-center px-3 py-1 bg-blue-100 border border-transparent rounded-md font-semibold text-xs text-blue-800 uppercase tracking-widest hover:bg-blue-200 active:bg-blue-300 focus:outline-none focus:border-blue-300 focus:ring ring-blue-200 disabled:opacity-25 transition ease-in-out duration-150"
                                >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
                                </svg>
                                    Ver {{ $inscription->documents->count() }} documento(s)
                                </button>
                                    @else
                                        <span class="text-gray-500">No hay documento</span>
                                    @endif
                            </div>
                        </dd>
                    </div>
                    <!-- NOTAS -->
                    @if($inscription->notes)
                    <div>
                        <h3 class="text-lg font-semibold text-indigo-700 mb-4">Notas</h3>
                        <div class="border-t border-gray-200 pt-4">
                            <p class="text-sm text-gray-900">{{ $inscription->notes }}</p>
                        </div>
                    </div>
                    @endif

                    <!-- INFORMACIÓN DEL SISTEMA -->
                    @role('admin')
                    <div>
                        <h3 class="text-lg font-semibold text-indigo-700 mb-4">Información del Sistema</h3>
                        <dl class="grid grid-cols-1 md:grid-cols-3 gap-x-4 gap-y-6 border-t border-gray-200 pt-4">
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
                    @endrole
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para ver documentos -->
        <x-modal id="documents-modal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">
                    Documentos de {{ $inscription->first_name }} {{ $inscription->paternal_surname }} {{$inscription->maternal_surname}}
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
                                                    @can('inscription.edit')
                                                    @if(auth()->user()->id === $inscription->created_by)
                                                    <a href="{{ route('documents.serve', $document) }}" download class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500" title="Descargar">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                                        </svg>
                                                    </a>
                                                    @endif
                                                    @endcan
                                                    @can('inscription.delete')
                                                    @if(auth()->user()->id === $inscription->created_by)
                                                    
                                                    <form method="POST" action="{{ route('documents.destroy', ['inscription' => $inscription->id, 'document' => $document->id]) }}" onsubmit="return confirm('¿Está seguro que desea eliminar este documento? Esta acción no se puede deshacer.');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500" title="Eliminar">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg>
                                                        </button>
                                                    </form>
                                                    @endif
                                                    @endcan
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
</x-app-layout>
