<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-white leading-tight">
                {{ __('Participante:') }} {{ $inscription->first_name }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('programs.inscriptions', $program) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-800 focus:outline-none focus:border-gray-800 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                    </svg>
                    <span>Volver</span>
                </a>
            </div>
        </div>
    </x-slot>
    <div >
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Datos personales -->
            <div class="bg-white shadow rounded-lg p-6 mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Datos del Participante</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <div class="text-sm text-gray-500">Nombre completo</div>
                        <div class="font-semibold text-gray-900">{{ $inscription->getFullName() }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">CI</div>
                        <div class="font-semibold text-gray-900">{{ $inscription->ci }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Teléfono</div>
                        <div class="font-semibold text-gray-900">
                            @if($inscription->phone)
                                <a href="https://wa.me/591{{ preg_replace('/[^0-9]/', '', $inscription->phone) }}" 
                                   target="_blank" 
                                   class="inline-flex items-center text-green-600 hover:text-green-800 transition-colors duration-200">
                                    <svg class="w-5 h-5 mr-1" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                                    </svg>
                                    {{ $inscription->phone }}
                                </a>
                            @else
                                <span class="text-gray-400">No registrado</span>
                            @endif
                        </div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Profesión</div>
                        <div class="font-semibold text-gray-900">
                            @php
                                $profession = $inscription->profession;
                                if (is_string($profession)) {
                                    // Si es un string JSON, decodificarlo
                                    $professionData = json_decode($profession, true);
                                    echo $professionData['name'] ?? $profession;
                                } elseif (is_object($profession) && isset($profession->name)) {
                                    // Si es un objeto con propiedad name
                                    echo $profession->name;
                                } else {
                                    echo $profession ?? 'No especificada';
                                }
                            @endphp
                        </div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Residencia</div>
                        <div class="font-semibold text-gray-900">{{ $inscription->residence }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Certificación</div>
                        <div class="font-semibold text-gray-900">{{ $inscription->certification }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Asesor</div>
                        <div class="font-semibold text-gray-900">{{ optional($inscription->creator)->name }}</div>
                    </div>
                    

                    @php
                        $inscriptionStatus = strtoupper(trim((string) ($inscription->external_inscription_status ?: '-')));
                        $academicStatusExternal = strtoupper(trim((string) ($inscription->external_academic_status ?: '-')));
                        $degreeStatus = strtoupper(trim((string) ($inscription->external_degree_status ?: '-')));

                        $getBadgeClasses = function ($status) {
                            if ($status === '-' || $status === '') return 'bg-gray-100 text-gray-700';
                            if (str_contains($status, 'PREINSCRIT')) return 'bg-yellow-100 text-yellow-800';
                            if (str_contains($status, 'ACTIVO') || str_contains($status, 'VIGENTE') || str_contains($status, 'INSCRITO') || str_contains($status, 'APROB')) return 'bg-green-100 text-green-800';
                            if (str_contains($status, 'PROCESO') || str_contains($status, 'CURSO') || str_contains($status, 'PENDIENTE')) return 'bg-yellow-100 text-yellow-800';
                            if (str_contains($status, 'RETIR') || str_contains($status, 'BAJA') || str_contains($status, 'REPROB') || str_contains($status, 'NO')) return 'bg-red-100 text-red-800';
                            return 'bg-blue-100 text-blue-800';
                        };
                    @endphp

                    <div class="md:col-span-2">
                        <div class="text-sm text-gray-500 mb-2">Estados externos</div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <p class="text-xs text-gray-500 mb-1">Estado Inscripción</p>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $getBadgeClasses($inscriptionStatus) }}">
                                    {{ $inscription->external_inscription_status ?: '-' }}
                                </span>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 mb-1">Estado Académico</p>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $getBadgeClasses($academicStatusExternal) }}">
                                    {{ $inscription->external_academic_status ?: '-' }}
                                </span>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 mb-1">Estado Titulación</p>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $getBadgeClasses($degreeStatus) }}">
                                    {{ $inscription->external_degree_status ?: '-' }}
                                </span>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 mb-1">Inscrito Universidad</p>
                                @if(is_null($inscription->external_university_enrolled))
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">-</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $inscription->external_university_enrolled ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $inscription->external_university_enrolled ? 'Sí' : 'No' }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Checklist de Documentos y Accesos-->
            <div class="flex flex-col md:flex-row md:space-x-6 mb-6">
                <div class="bg-white shadow rounded-lg p-6 mb-6 md:mb-0 md:w-1/2">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Checklist de Documentos</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <label class="flex items-center">
                            <input type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded document-checkbox" data-inscription="{{ $inscription->id }}" data-field="has_identity_card" {{ $inscription->has_identity_card ? 'checked' : '' }}>
                            <span class="ml-2 text-sm">Cédula de identidad</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded document-checkbox" data-inscription="{{ $inscription->id }}" data-field="has_degree_title" {{ $inscription->has_degree_title ? 'checked' : '' }}>
                            <span class="ml-2 text-sm">Título en provisión nacional</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded document-checkbox" data-inscription="{{ $inscription->id }}" data-field="has_academic_diploma" {{ $inscription->has_academic_diploma ? 'checked' : '' }}>
                            <span class="ml-2 text-sm">Diploma de grado académico</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded document-checkbox" data-inscription="{{ $inscription->id }}" data-field="has_birth_certificate" {{ $inscription->has_birth_certificate ? 'checked' : '' }}>
                            <span class="ml-2 text-sm">Certificado de nacimiento</span>
                        </label>
                    </div>
                </div>
                <div class="bg-white shadow rounded-lg p-6 mb-6 md:mb-0 md:w-1/2">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Checklist de Accesos</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <label class="flex items-center">
                            <input type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded access-checkbox" data-inscription="{{ $inscription->id }}" data-field="was_added_to_the_group" {{ $inscription->was_added_to_the_group ? 'checked' : '' }}>
                            <span class="ml-2 text-sm">Se añadió al grupo</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded access-checkbox" data-inscription="{{ $inscription->id }}" data-field="accesses_were_sent" {{ $inscription->accesses_were_sent ? 'checked' : '' }}>
                            <span class="ml-2 text-sm">Se enviaron accesos</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded access-checkbox" data-inscription="{{ $inscription->id }}" data-field="mail_was_sent" {{ $inscription->mail_was_sent ? 'checked' : '' }}>
                            <span class="ml-2 text-sm">Se envió correo</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Documentos Adjuntos y Seguimiento Académico -->
            <div class="flex flex-col md:flex-row md:space-x-6 mb-6">
                <div class="bg-white shadow rounded-lg p-6 mb-6 md:mb-0 md:w-1/2">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Documentos Adjuntos</h3>
                    @if($inscription->documents->count() > 0)
                        <button type="button" class="open-commitment-modal inline-flex items-center px-3 py-1 bg-blue-100 border border-transparent rounded-md font-semibold text-xs text-blue-800 uppercase tracking-widest hover:bg-blue-200 active:bg-blue-300 focus:outline-none focus:border-blue-300 focus:ring ring-blue-200 disabled:opacity-25 transition ease-in-out duration-150 mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
                            </svg>
                            Ver {{ $inscription->documents->count() }} documento(s)
                        </button>
                    @endif
                    <button type="button" class="upload-button inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        Subir archivo
                    </button>
                </div>
                <div class="bg-white shadow rounded-lg p-6 mb-6 md:mb-0 md:w-1/2">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Seguimiento Académico</h3>
                    <div class="flex flex-col md:flex-row md:items-center md:space-x-6">
                        @php
                            $hasFollowup = $inscription->documentFollowups()->exists();
                        @endphp
                        <div class="mb-4 md:mb-0">
                            @if($hasFollowup)
                                <a href="{{ route('document_followups.show', ['program' => $program->id, 'inscription' => $inscription->id]) }}"
                                   class="inline-flex items-center px-3 py-1 bg-green-100 border border-transparent rounded-md font-semibold text-xs text-green-800 uppercase tracking-widest hover:bg-green-200 active:bg-green-300 focus:outline-none focus:border-green-300 focus:ring ring-green-200 disabled:opacity-25 transition ease-in-out duration-150 mb-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
                                    </svg>
                                    Ver Seguimiento
                                </a>
                            @else
                                <a href="{{ route('document_followups.create', ['program' => $program->id, 'inscription' => $inscription->id]) }}"
                                   class="inline-flex items-center px-3 py-1 bg-blue-100 border border-transparent rounded-md font-semibold text-xs text-blue-800 uppercase tracking-widest hover:bg-blue-200 active:bg-blue-300 focus:outline-none focus:border-blue-300 focus:ring ring-blue-200 disabled:opacity-25 transition ease-in-out duration-150 mb-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M12 2a2 2 0 00-2 2v4H8a2 2 0 00-2 2v4a2 2 0 002 2h4a2 2 0 002-2v-4a2 2 0 00-2-2h-2V4a2 2 0 012-2z" clip-rule="evenodd" />
                                    </svg>
                                    Crear Seguimiento
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Aquí termina el contenido principal -->
    </div>

    <!-- Modal de carga -->
    <div id="loading-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white p-4 rounded-lg shadow-lg">
            <div class="flex items-center">
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-gray-800">Procesando...</span>
            </div>
        </div>
    </div>

    <!-- Modal para ver documentos -->
    <div id="commitment_letter-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-xl p-6 w-full relative" style="max-width: 45rem;">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-medium text-gray-900">Documentos de {{ $inscription->first_name }} {{ $inscription->paternal_surname }} {{$inscription->maternal_surname}}</h2>
                <button id="close-commitment-modal" class="text-gray-400 hover:text-gray-500">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
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
                                                <input type="hidden" name="program_id" value="{{ $program->id }}">
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
        </div>
    </div>
    <!-- Modal para subir archivos -->
    <div id="upload-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-xl p-6 w-full" style="max-width: 45rem;">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Subir archivo(s)</h3>
                <button id="close-modal" class="text-gray-400 hover:text-gray-500">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form id="upload-form" action="{{ route('programs.inscriptions.documents.upload', ['program' => $program->id, 'inscription' => $inscription->id]) }}" method="POST" enctype="multipart/form-data">
                @csrf
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
                    <button type="button" id="cancel-upload" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-gray-700 bg-gray-200 hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 mr-2">
                        Cancelar
                    </button>
                    <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Subir
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal para solicitar observación al desmarcar -->
    <div id="observation-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">¿Por qué desmarca este campo?</h3>
                <button id="close-observation-modal" class="text-gray-400 hover:text-gray-500">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="mb-4">
                <p class="text-sm text-gray-600 mb-2">Está a punto de desmarcar: <strong id="field-being-unmarked"></strong></p>
                <label for="observation-text" class="block text-sm font-medium text-gray-700 mb-2">
                    Observación (opcional):
                </label>
                <textarea id="observation-text" rows="4" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="Explique por qué se desmarca este campo..."></textarea>
            </div>
            <div class="flex justify-end space-x-2">
                <button id="cancel-observation" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-gray-700 bg-gray-200 hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                    Cancelar
                </button>
                <button id="confirm-observation" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Continuar
                </button>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // --- MULTIARCHIVO EN MODAL DE VER ---
            const fileContainerModal = document.getElementById('file-container-modal');
            const addFileButtonModal = document.getElementById('add-file-modal');
            function updateRemoveButtonsModal() {
                const fileGroups = document.querySelectorAll('#file-container-modal .file-group');
                fileGroups.forEach((group, index) => {
                    const removeButton = group.querySelector('.remove-file-modal');
                    if (fileGroups.length > 1) {
                        removeButton.style.display = 'block';
                    } else {
                        removeButton.style.display = 'none';
                    }
                });
            }
            if (addFileButtonModal) {
                addFileButtonModal.addEventListener('click', function() {
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
                            <button type="button" class="remove-file-modal px-2 py-1 bg-red-500 text-white rounded hover:bg-red-600">Eliminar</button>
                        </div>
                        <input type="text" name="document_descriptions[]" placeholder="Descripción del archivo" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                    `;
                    fileContainerModal.appendChild(fileGroup);
                    updateRemoveButtonsModal();
                    fileGroup.querySelector('.remove-file-modal').addEventListener('click', function() {
                        fileGroup.remove();
                        updateRemoveButtonsModal();
                    });
                });
            }
            document.querySelectorAll('.remove-file-modal').forEach(button => {
                button.addEventListener('click', function() {
                    this.closest('.file-group').remove();
                    updateRemoveButtonsModal();
                });
            });
            updateRemoveButtonsModal();
            // Abrir modal de ver documentos
            document.querySelectorAll('.open-commitment-modal').forEach(btn => {
                btn.addEventListener('click', function() {
                    const modal = document.getElementById('commitment_letter-modal');
                    if (modal) {
                        modal.classList.remove('hidden');
                    }
                });
            });
            // Cerrar modal de ver documentos
            const closeCommitmentModal = document.getElementById('close-commitment-modal');
            const commitmentModal = document.getElementById('commitment_letter-modal');
            if (closeCommitmentModal) {
                closeCommitmentModal.addEventListener('click', function() {
                    commitmentModal.classList.add('hidden');
                });
            }
            window.addEventListener('click', function(event) {
                if (event.target === commitmentModal) {
                    commitmentModal.classList.add('hidden');
                }
            });


            // Abrir modal de subida de archivos
            document.querySelectorAll('.upload-button').forEach(btn => {
                btn.addEventListener('click', function() {
                    const uploadModal = document.getElementById('upload-modal');
                    if (uploadModal) {
                        uploadModal.classList.remove('hidden');
                    }
                });
            });

            // Cerrar modal de subida de archivos
            const closeModal = document.getElementById('close-modal');
            const cancelUpload = document.getElementById('cancel-upload');
            const uploadModal = document.getElementById('upload-modal');
            if (closeModal) {
                closeModal.addEventListener('click', function() {
                    uploadModal.classList.add('hidden');
                });
            }
            if (cancelUpload) {
                cancelUpload.addEventListener('click', function() {
                    uploadModal.classList.add('hidden');
                });
            }
            window.addEventListener('click', function(event) {
                if (event.target === uploadModal) {
                    uploadModal.classList.add('hidden');
                }
            });

            // Funciones de utilidad
            function showLoading() {
                document.getElementById('loading-modal').classList.remove('hidden');
            }
            function hideLoading() {
                document.getElementById('loading-modal').classList.add('hidden');
            }
            function handleError(error) {
                console.error('Error:', error);
                hideLoading();
                showNotification('error', 'Ha ocurrido un error. Por favor, inténtalo de nuevo.');
            }

            // Variables globales para el modal de observación
            let pendingCheckboxUpdate = null;
            const observationModal = document.getElementById('observation-modal');
            const closeObservationModal = document.getElementById('close-observation-modal');
            const cancelObservation = document.getElementById('cancel-observation');
            const confirmObservation = document.getElementById('confirm-observation');
            const observationText = document.getElementById('observation-text');
            const fieldBeingUnmarked = document.getElementById('field-being-unmarked');

            // Función para mostrar el modal de observación
            function showObservationModal(checkbox, inscriptionId, field, checklistType) {
                const fieldNames = {
                    'has_identity_card': 'Cédula de identidad',
                    'has_degree_title': 'Título en provisión nacional',
                    'has_academic_diploma': 'Diploma de grado académico',
                    'has_birth_certificate': 'Certificado de nacimiento',
                    'was_added_to_the_group': 'Se añadió al grupo',
                    'accesses_were_sent': 'Se enviaron accesos',
                    'mail_was_sent': 'Se envió correo'
                };

                fieldBeingUnmarked.textContent = fieldNames[field] || field;
                observationText.value = '';
                observationModal.classList.remove('hidden');
                
                pendingCheckboxUpdate = { checkbox, inscriptionId, field, checklistType };
            }

            // Cerrar modal de observación
            function closeObservationModalFn() {
                if (pendingCheckboxUpdate) {
                    pendingCheckboxUpdate.checkbox.checked = true; // Revertir cambio
                    pendingCheckboxUpdate = null;
                }
                observationModal.classList.add('hidden');
            }

            closeObservationModal.addEventListener('click', closeObservationModalFn);
            cancelObservation.addEventListener('click', closeObservationModalFn);

            // Confirmar observación y proceder
            confirmObservation.addEventListener('click', function() {
                if (!pendingCheckboxUpdate) return;

                const observation = observationText.value.trim();
                const { checkbox, inscriptionId, field, checklistType } = pendingCheckboxUpdate;
                
                observationModal.classList.add('hidden');
                pendingCheckboxUpdate = null;

                // Proceder con la actualización
                performCheckboxUpdate(inscriptionId, field, false, checklistType, observation);
            });

            // Función para realizar la actualización del checkbox
            function performCheckboxUpdate(inscriptionId, field, value, checklistType, observation = null) {
                showLoading();
                const formData = new FormData();
                formData.append('field', field);
                formData.append('value', value ? 1 : 0);
                formData.append('_method', 'PATCH');
                if (observation) {
                    formData.append('observation', observation);
                }

                const endpoint = checklistType === 'document' 
                    ? `/inscriptions/${inscriptionId}/documents` 
                    : `/inscriptions/${inscriptionId}/access`;

                fetch(endpoint, {
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
                        // Revertir el cambio en el checkbox
                        const checkbox = document.querySelector(`[data-field="${field}"]`);
                        if (checkbox) checkbox.checked = !value;
                        showNotification('error', data.message || 'Error al actualizar');
                    }
                })
                .catch(error => {
                    // Revertir el cambio en el checkbox
                    const checkbox = document.querySelector(`[data-field="${field}"]`);
                    if (checkbox) checkbox.checked = !value;
                    handleError(error);
                });
            }

            // Manejar cambios en checkboxes de documentos
            document.querySelectorAll('.document-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const inscriptionId = this.dataset.inscription;
                    const field = this.dataset.field;
                    const value = this.checked;
                    
                    // Si se está desmarcando, mostrar modal de observación
                    if (!value) {
                        showObservationModal(this, inscriptionId, field, 'document');
                        return;
                    }
                    
                    // Si se está marcando, proceder normalmente
                    performCheckboxUpdate(inscriptionId, field, value, 'document');
                });
            });

            // Manejar cambios en checkboxes de accesos
            document.querySelectorAll('.access-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const inscriptionId = this.dataset.inscription;
                    const field = this.dataset.field;
                    const value = this.checked;
                    
                    // Si se está desmarcando, mostrar modal de observación
                    if (!value) {
                        showObservationModal(this, inscriptionId, field, 'access');
                        return;
                    }
                    
                    // Si se está marcando, proceder normalmente
                    performCheckboxUpdate(inscriptionId, field, value, 'access');
                });
            });

            // Manejar cambios en el select de estado académico
            document.querySelectorAll('.academic-status-select').forEach(select =>{
                select.addEventListener('change', function(){
                    const inscriptionId = this.dataset.inscription;
                    const value = this.value;
                    showLoading();
                    const formData = new FormData();
                    formData.append('academic_status', value);
                    formData.append('_method', 'PATCH');
                    fetch(`/inscriptions/${inscriptionId}/academic-status`,{
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data =>{
                        hideLoading();
                        if (data.success) {
                            showNotification('success', data.message);
                        } else {
                            showNotification('error', data.message || 'Error al actualizar el estado académico.');
                        }
                    })
                    .catch(error => {
                        hideLoading();
                        showNotification('error', 'Ha ocurrido un error. Por Favor, inténtalo de nuevo.');
                    });
                });
            });
            // Función para mostrar notificaciones
            function showNotification(type, message) {
                const notification = document.createElement('div');
                notification.className = `fixed top-4 right-4 z-50 p-4 rounded-md shadow-lg ${type === 'success' ? 'bg-green-500' : 'bg-red-500'} text-white`;
                notification.textContent = message;
                document.body.appendChild(notification);
                setTimeout(() => {
                    notification.remove();
                }, 3000);
            }
            // --- MULTIARCHIVO ---
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
