<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-white leading-tight">
                {{ __('Detalles de Inscripción') }}
            </h2>
            <div class="flex space-x-2">
                @php
                    $currentUser = auth()->user();
                    $isTeamLeader = $currentUser && $currentUser->leadsActiveMarketingTeam();
                    $canEditInscription = $currentUser
                        && (
                            $currentUser->hasRole('admin')
                            || $currentUser->id === $inscription->created_by
                            || ($isTeamLeader && optional($inscription->creator)->email === 'sistema.externo@centtest.local')
                        )
                        && ($currentUser->can('inscription.edit') || $isTeamLeader);
                @endphp

                @if($canEditInscription)
                <a href="{{ route('inscriptions.edit', $inscription) }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 active:bg-yellow-800 focus:outline-none focus:border-yellow-800 focus:ring ring-yellow-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                    </svg>
                    Editar
                </a>
                @endif
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
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-indigo-700">Datos Personales</h3>
                            @if($inscription->is_synced)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    Sincronizado desde DB Externa
                                    @if($inscription->last_synced_at)
                                        <span class="ml-1">({{ $inscription->last_synced_at->diffForHumans() }})</span>
                                    @endif
                                </span>
                            @endif
                        </div>
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
                                <dt class="text-sm font-medium text-gray-500">Fecha de Nacimiento</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $inscription->birth_date ? $inscription->birth_date->format('d/m/Y') : '' }}</dd>
                            </div>
                            <div>   
                                <dt class="text-sm font-medium text-gray-500">Género</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $inscription->gender }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">CI</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $inscription->ci }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Estado Civil</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $inscription->civil_status }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Teléfono</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $inscription->phone }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Universidad</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    @if($inscription->university)
                                        {{ $inscription->university->initials }} - {{ $inscription->university->name }}
                                    @else
                                        <span class="text-gray-400">No especificada</span>
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Profesión</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    @if($inscription->profession)
                                        {{ $inscription->profession->name }}
                                    @else
                                        <span class="text-gray-400">No especificada</span>
                                    @endif
                                </dd>
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
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ $inscription->getAdvisorName() }}
                                    @if($inscription->external_advisor_name && $inscription->creator->email === 'sistema.externo@centtest.local')
                                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800" title="Asesor externo sin cuenta en el sistema">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Asesor Externo
                                        </span>
                                    @endif
                                </dd>
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
                            
                            @if($inscription->is_synced)
                                <!-- Mostrar estado académico externo -->
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Estado Académico (Externo)</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                            {{ $inscription->external_inscription_status ?? 'N/A' }}
                                        </span>
                                        <span class="ml-2 text-xs text-gray-500">Sincronizado desde DB Externa</span>
                                    </dd>
                                </div>
                                
                                <!-- Mostrar estado de pago local -->
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Estado de Pago (Local)</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        @if($inscription->local_payment_status == 'Completo')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Completo
                                            </span>
                                        @elseif($inscription->local_payment_status == 'Completando')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                Completando
                                            </span>
                                        @elseif($inscription->local_payment_status == 'Adelanto')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                Adelanto
                                            </span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                Pendiente
                                            </span>
                                        @endif
                                    </dd>
                                </div>
                            @else
                                <!-- Para inscripciones no sincronizadas, mostrar solo status -->
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
                            @endif
                            
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
                        </dl>
                    </div>

                    <!-- CHECKLIST DE DOCUMENTOS Y ACCESOS -->
                    <div>
                        <h3 class="text-lg font-semibold text-indigo-700 mb-4">Checklist de Documentos y Accesos</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 border-t border-gray-200 pt-4">
                            <!-- Checklist de Documentos -->
                            <div>
                                <h4 class="text-md font-medium text-gray-700 mb-3">Documentos</h4>
                                <div class="space-y-3">
                                    <label class="flex items-center">
                                        <input type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded document-checkbox" data-inscription="{{ $inscription->id }}" data-field="has_identity_card" {{ $inscription->has_identity_card ? 'checked' : '' }}>
                                        <span class="ml-2 text-sm text-gray-700">Cédula de identidad</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded document-checkbox" data-inscription="{{ $inscription->id }}" data-field="has_degree_title" {{ $inscription->has_degree_title ? 'checked' : '' }}>
                                        <span class="ml-2 text-sm text-gray-700">Título en provisión nacional</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded document-checkbox" data-inscription="{{ $inscription->id }}" data-field="has_academic_diploma" {{ $inscription->has_academic_diploma ? 'checked' : '' }}>
                                        <span class="ml-2 text-sm text-gray-700">Diploma de grado académico</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded document-checkbox" data-inscription="{{ $inscription->id }}" data-field="has_birth_certificate" {{ $inscription->has_birth_certificate ? 'checked' : '' }}>
                                        <span class="ml-2 text-sm text-gray-700">Certificado de nacimiento</span>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Checklist de Accesos -->
                            <div>
                                <h4 class="text-md font-medium text-gray-700 mb-3">Accesos</h4>
                                <div class="space-y-3">
                                    <label class="flex items-center">
                                        <input type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded access-checkbox" data-inscription="{{ $inscription->id }}" data-field="was_added_to_the_group" {{ $inscription->was_added_to_the_group ? 'checked' : '' }}>
                                        <span class="ml-2 text-sm text-gray-700">Se añadió al grupo</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded access-checkbox" data-inscription="{{ $inscription->id }}" data-field="accesses_were_sent" {{ $inscription->accesses_were_sent ? 'checked' : '' }}>
                                        <span class="ml-2 text-sm text-gray-700">Se enviaron accesos</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded access-checkbox" data-inscription="{{ $inscription->id }}" data-field="mail_was_sent" {{ $inscription->mail_was_sent ? 'checked' : '' }}>
                                        <span class="ml-2 text-sm text-gray-700">Se envió correo</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold text-indigo-700 mb-4">Documentos</h3>
                        <dd class="mt-1 text-sm text-gray-900">
                            <div class="border-t border-gray-200 pt-4">
                            @if($inscription->documents->count() > 0)
                                <button 
                                    type="button" 
                                    @click="$dispatch('open-modal', 'documents-modal')"
                                    class="inline-flex items-center px-3 py-1 bg-blue-100 border border-transparent rounded-md font-semibold text-xs text-blue-800 uppercase tracking-widest hover:bg-blue-200 active:bg-blue-300 focus:outline-none focus:border-blue-300 focus:ring ring-blue-200 disabled:opacity-25 transition ease-in-out duration-150 mb-2 mr-2"
                                >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
                                </svg>
                                    Ver {{ $inscription->documents->count() }} documento(s)
                                </button>
                            @endif
                            <button 
                                type="button"
                                @click="$dispatch('open-modal', 'upload-modal')"
                                class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                </svg>
                                Subir archivo
                            </button>
                            </div>
                        </dd>
                    </div>

                    <!-- HISTORIAL DE PAGOS -->
                    <div>
                        <h3 class="text-lg font-semibold text-indigo-700 mb-4">Historial de Cambios de Estado</h3>
                        <div class="border-t border-gray-200 pt-4">
                            @if($inscription->paymentHistory->count() > 0)
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 bg-white">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha del Cambio</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado Anterior</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nuevo Estado</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto Pagado</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cambió por</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200">
                                            @foreach($inscription->paymentHistory->sortByDesc('status_date') as $history)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ $history->status_date->format('d/m/Y') }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                    @if($history->old_status)
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                            @if($history->old_status === 'Pendiente') bg-gray-100 text-gray-800
                                                            @elseif($history->old_status === 'Adelanto') bg-yellow-100 text-yellow-800
                                                            @elseif($history->old_status === 'Completando') bg-blue-100 text-blue-800
                                                            @elseif($history->old_status === 'Completo') bg-green-100 text-green-800
                                                            @endif
                                                        ">
                                                            {{ $history->old_status }}
                                                        </span>
                                                    @else
                                                        <span class="text-gray-400">-</span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                        @if($history->new_status === 'Pendiente') bg-gray-100 text-gray-800
                                                        @elseif($history->new_status === 'Adelanto') bg-yellow-100 text-yellow-800
                                                        @elseif($history->new_status === 'Completando') bg-blue-100 text-blue-800
                                                        @elseif($history->new_status === 'Completo') bg-green-100 text-green-800
                                                        @endif
                                                    ">
                                                        {{ $history->new_status }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    Bs. {{ number_format($history->amount_paid, 2) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    @if($history->changedBy)
                                                        {{ $history->changedBy->name }}
                                                    @else
                                                        <span class="text-gray-400">Sistema</span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 text-sm text-gray-900">
                                                    @if($canEditInscription)
                                                        <div class="flex flex-col gap-2">
                                                            <details>
                                                                <summary class="cursor-pointer text-indigo-600 hover:text-indigo-800 text-xs font-semibold uppercase tracking-wider">Editar</summary>
                                                                <form method="POST" action="{{ route('inscriptions.payment-history.update', ['inscription' => $inscription->id, 'history' => $history->id]) }}" class="mt-2 p-3 border border-gray-200 rounded-md bg-gray-50">
                                                                    @csrf
                                                                    @method('PATCH')

                                                                    <div class="grid grid-cols-1 gap-2">
                                                                        <select name="new_status" class="rounded-md border-gray-300 text-sm" required>
                                                                            <option value="Pendiente" {{ $history->new_status === 'Pendiente' ? 'selected' : '' }}>Pendiente</option>
                                                                            <option value="Adelanto" {{ $history->new_status === 'Adelanto' ? 'selected' : '' }}>Adelanto</option>
                                                                            <option value="Completando" {{ $history->new_status === 'Completando' ? 'selected' : '' }}>Completando</option>
                                                                            <option value="Completo" {{ $history->new_status === 'Completo' ? 'selected' : '' }}>Completo</option>
                                                                        </select>

                                                                        <input type="date" name="status_date" value="{{ optional($history->status_date)->format('Y-m-d') }}" class="rounded-md border-gray-300 text-sm" required>
                                                                        <input type="number" step="0.01" min="0" name="amount_paid" value="{{ $history->amount_paid ?? 0 }}" class="rounded-md border-gray-300 text-sm" required>
                                                                        <input type="text" name="notes" value="{{ $history->notes }}" class="rounded-md border-gray-300 text-sm" placeholder="Notas (opcional)">
                                                                    </div>

                                                                    <button type="submit" class="mt-2 inline-flex items-center px-3 py-1 bg-indigo-600 text-white text-xs font-semibold uppercase tracking-wider rounded-md hover:bg-indigo-700">
                                                                        Guardar
                                                                    </button>
                                                                </form>
                                                            </details>

                                                            <form method="POST" action="{{ route('inscriptions.payment-history.destroy', ['inscription' => $inscription->id, 'history' => $history->id]) }}" onsubmit="return confirm('¿Eliminar este cambio de estado? Esta acción no se puede deshacer.');">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="inline-flex items-center px-3 py-1 bg-red-600 text-white text-xs font-semibold uppercase tracking-wider rounded-md hover:bg-red-700">
                                                                    Eliminar
                                                                </button>
                                                            </form>
                                                        </div>
                                                    @else
                                                        <span class="text-gray-400">-</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-sm text-gray-500">No hay cambios de estado registrados aún.</p>
                            @endif
                        </div>
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
                    Documentos de {{ $inscription->getFullName() }}
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

    <!-- Modal para subir archivos -->
    <x-modal id="upload-modal">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">
                Subir archivo(s)
            </h2>

            <form action="{{ route('inscriptions.update', $inscription) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PATCH')
                
                <!-- Mantener los datos actuales de la inscripción -->
                <input type="hidden" name="full_name" value="{{ $inscription->full_name }}">
                <input type="hidden" name="ci" value="{{ $inscription->ci }}">
                <input type="hidden" name="birth_date" value="{{ $inscription->birth_date ? $inscription->birth_date->format('Y-m-d') : '' }}">
                <input type="hidden" name="email" value="{{ $inscription->email }}">
                <input type="hidden" name="civil_status" value="{{ $inscription->civil_status }}">
                <input type="hidden" name="university_id" value="{{ $inscription->university_id }}">
                <input type="hidden" name="phone" value="{{ $inscription->phone }}">
                <input type="hidden" name="program_id" value="{{ $inscription->program_id }}">
                <input type="hidden" name="payment_plan" value="{{ $inscription->payment_plan }}">
                <input type="hidden" name="payment_method" value="{{ $inscription->payment_method }}">
                <input type="hidden" name="enrollment_fee" value="{{ $inscription->enrollment_fee }}">
                <input type="hidden" name="first_installment" value="{{ $inscription->first_installment }}">
                <input type="hidden" name="total_paid" value="{{ $inscription->total_paid }}">
                <input type="hidden" name="status" value="{{ $inscription->status }}">
                <input type="hidden" name="profession_id" value="{{ $inscription->profession_id }}">
                <input type="hidden" name="residence" value="{{ $inscription->residence }}">
                <input type="hidden" name="location" value="{{ $inscription->location }}">
                <input type="hidden" name="inscription_date" value="{{ $inscription->inscription_date->format('Y-m-d') }}">
                <input type="hidden" name="notes" value="{{ $inscription->notes }}">
                <input type="hidden" name="certification" value="{{ $inscription->certification }}">
                <input type="hidden" name="gender" value="{{ $inscription->gender }}">

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

                <div class="flex justify-end mt-4">
                    <button type="button" @click="$dispatch('close')" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-gray-700 bg-gray-200 hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 mr-2">
                        Cancelar
                    </button>
                    <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Subir
                    </button>
                </div>
            </form>
        </div>
    </x-modal>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Funciones de utilidad
            function showLoading() {
                // Crear modal de carga si no existe
                let loadingModal = document.getElementById('loading-modal');
                if (!loadingModal) {
                    loadingModal = document.createElement('div');
                    loadingModal.id = 'loading-modal';
                    loadingModal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
                    loadingModal.innerHTML = `
                        <div class="bg-white p-4 rounded-lg shadow-lg">
                            <div class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span class="text-gray-800">Procesando...</span>
                            </div>
                        </div>
                    `;
                    document.body.appendChild(loadingModal);
                }
                loadingModal.classList.remove('hidden');
                loadingModal.style.display = 'flex';
            }

            function hideLoading() {
                const loadingModal = document.getElementById('loading-modal');
                if (loadingModal) {
                    loadingModal.classList.add('hidden');
                    loadingModal.style.display = 'none';
                }
            }

            function showNotification(type, message) {
                const notification = document.createElement('div');
                notification.className = `fixed top-4 right-4 z-50 p-4 rounded-md shadow-lg ${type === 'success' ? 'bg-green-500' : 'bg-red-500'} text-white`;
                notification.textContent = message;
                document.body.appendChild(notification);
                setTimeout(() => {
                    notification.remove();
                }, 3000);
            }

            // Manejar cambios en checkboxes de documentos
            document.querySelectorAll('.document-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const inscriptionId = this.dataset.inscription;
                    const field = this.dataset.field;
                    const value = this.checked;
                    showLoading();
                    const formData = new FormData();
                    formData.append('field', field);
                    formData.append('value', value ? 1 : 0);
                    formData.append('_method', 'PATCH');
                    fetch(`/inscriptions/${inscriptionId}/documents`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: formData
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Error en la respuesta del servidor');
                        }
                        return response.json();
                    })
                    .then(data => {
                        hideLoading();
                        if (data.success) {
                            showNotification('success', data.message);
                        } else {
                            this.checked = !value; // Revertir el cambio
                            showNotification('error', data.message || 'Error al actualizar el documento');
                        }
                    })
                    .catch(error => {
                        this.checked = !value; // Revertir el cambio
                        hideLoading();
                        console.error('Error:', error);
                        showNotification('error', 'Ha ocurrido un error. Por favor, inténtalo de nuevo.');
                    });
                });
            });

            // Manejar cambios en checkboxes de accesos
            document.querySelectorAll('.access-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const inscriptionId = this.dataset.inscription;
                    const field = this.dataset.field;
                    const value = this.checked;
                    showLoading();
                    const formData = new FormData();
                    formData.append('field', field);
                    formData.append('value', value ? 1 : 0);
                    formData.append('_method', 'PATCH');
                    fetch(`/inscriptions/${inscriptionId}/access`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        hideLoading();
                        if (data.success) {
                            showNotification('success', data.message);
                        } else {
                            this.checked = !value; // Revertir el cambio
                            showNotification('error', data.message || 'Error al actualizar el acceso.');
                        }
                    })
                    .catch(error => {
                        this.checked = !value; // Revertir el cambio
                        hideLoading();
                        console.error('Error:', error);
                        showNotification('error', 'Ha ocurrido un error. Por favor, inténtalo de nuevo.');
                    });
                });
            });
            
            // --- GESTIÓN DE ARCHIVOS MÚLTIPLES ---
            const fileContainer = document.getElementById('file-container');
            const addFileButton = document.getElementById('add-file');

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

            if (addFileButton) {
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
            }

            document.querySelectorAll('.remove-file').forEach(button => {
                button.addEventListener('click', function() {
                    this.closest('.file-group').remove();
                    updateRemoveButtons();
                });
            });

            updateRemoveButtons();
        });
    </script>
</x-app-layout>
