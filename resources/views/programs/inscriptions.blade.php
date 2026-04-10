<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-white leading-tight">
                {{ __('Inscritos en') }} {{ $program->name }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('programs.show', $program) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-800 focus:outline-none focus:border-gray-800 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                    </svg>
                    Volver
                </a>
            </div>
        </div>
    </x-slot>

    <div >
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Estadísticas -->
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total Inscritos</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ $inscriptionsStats->count() }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Con Documentos Completos</dt>
                                    <dd class="text-lg font-medium text-gray-900">
                                        {{ $inscriptionsStats->filter(function($i) {
                                            return $i->has_identity_card && 
                                                   $i->has_degree_title && 
                                                   $i->has_academic_diploma && 
                                                   $i->has_birth_certificate;
                                        })->count() }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                @php
                    $withCommitment = $inscriptionsStats->filter(fn($i) => $i->documents->where('document_type', 'compromiso')->isNotEmpty())->count();
                    $withoutCommitment = $inscriptionsStats->count() - $withCommitment;
                    $withFreezing = $inscriptionsStats->filter(fn($i) => $i->documents->where('document_type', 'congelamiento')->isNotEmpty())->count();
                    $withoutFreezing = $inscriptionsStats->count() - $withFreezing;
                @endphp
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Carta de Compromiso</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ $withCommitment }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Carta de Congelamiento</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ $withFreezing }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 15.5c-.77.833.192 2.5 1.732 2.5z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Con Seguimiento</dt>
                                    <dd class="text-lg font-medium text-gray-900">
                                        {{ $inscriptionsStats->filter(function($i) {
                                            return $i->documentFollowups->count() > 0;
                                        })->count() }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lista de Inscritos -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Lista de Inscritos</h3>
                    </div>

                    <form method="GET" action="{{ route('programs.inscriptions', $program) }}" class="mb-4">
                        <div class="flex flex-col sm:flex-row gap-3">
                            <input
                                type="text"
                                name="search"
                                value="{{ $search ?? '' }}"
                                placeholder="Buscar por nombre, CI o estado"
                                class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 w-full sm:w-96"
                            >
                            <div class="flex gap-2">
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white tracking-widest hover:bg-indigo-700 focus:outline-none focus:border-indigo-700 focus:ring ring-indigo-300 transition ease-in-out duration-150">
                                    Buscar
                                </button>
                                @if(!empty($search))
                                    <a href="{{ route('programs.inscriptions', $program) }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 tracking-widest hover:bg-gray-300 transition ease-in-out duration-150">
                                        Limpiar
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Opciones</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre y CI</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado Inscripción</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado Académico</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado Titulación</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Inscrito Universidad</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Documentos</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($inscriptions as $inscription)
                                    <tr id="inscription-{{ $inscription->id }}">
                                        {{-- Opciones --}}
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-3">
                                                <a href="{{ route('programs.inscription_show', ['program' => $program->id, 'inscription' => $inscription->id]) }}" title="Ver detalles">
                                                    <x-action-icons action="view" />
                                                </a>
                                                
                                                @can('program.edit')
                                                <a href="{{ route('inscriptions.edit', $inscription) }}" title="Editar datos personales">
                                                    <x-action-icons action="edit" />
                                                </a>
                                                @endcan
                                            </div>
                                        </td>

                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <p class="text-sm font-medium text-gray-900">{{ $inscription->getFullName() }}</p>
                                            <p class="text-xs text-gray-500">CI: {{ $inscription->ci ?: '-' }}</p>
                                        </td>

                                        @php
                                            $inscriptionStatus = strtoupper(trim((string) ($inscription->external_inscription_status ?: '-')));
                                            $rawAcademicStatus = $inscription->estado_academico ?? $inscription->external_academic_status;
                                            $academicStatus = strtoupper(trim((string) ($rawAcademicStatus ?: '-')));
                                            $degreeStatus = strtoupper(trim((string) ($inscription->external_degree_status ?: '-')));

                                            $getBadgeClasses = function ($status) {
                                                if ($status === '-' || $status === '') return 'bg-gray-100 text-gray-700';
                                                if (str_contains($status, 'PREINSCRIT')) {
                                                    return 'bg-yellow-100 text-yellow-800';
                                                }
                                                if (str_contains($status, 'ACTIVO') || str_contains($status, 'VIGENTE') || str_contains($status, 'INSCRITO') || str_contains($status, 'APROB')) {
                                                    return 'bg-green-100 text-green-800';
                                                }
                                                if (str_contains($status, 'PROCESO') || str_contains($status, 'CURSO') || str_contains($status, 'PENDIENTE')) {
                                                    return 'bg-yellow-100 text-yellow-800';
                                                }
                                                if (str_contains($status, 'RETIR') || str_contains($status, 'BAJA') || str_contains($status, 'REPROB') || str_contains($status, 'NO')) {
                                                    return 'bg-red-100 text-red-800';
                                                }
                                                return 'bg-blue-100 text-blue-800';
                                            };
                                        @endphp

                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $getBadgeClasses($inscriptionStatus) }}">
                                                {{ $inscription->external_inscription_status ?: '-' }}
                                            </span>
                                        </td>

                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $getBadgeClasses($academicStatus) }}">
                                                {{ $rawAcademicStatus ?: '-' }}
                                            </span>
                                        </td>

                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $getBadgeClasses($degreeStatus) }}">
                                                {{ $inscription->external_degree_status ?: '-' }}
                                            </span>
                                        </td>

                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            @if(is_null($inscription->external_university_enrolled))
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">-</span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $inscription->external_university_enrolled ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                    {{ $inscription->external_university_enrolled ? 'Sí' : 'No' }}
                                                </span>
                                            @endif
                                        </td>

                                        {{-- Documentos --}}
                                        @php
                                            $docs = [
                                                ['short' => 'CI', 'full' => 'Cédula de Identidad', 'has' => $inscription->has_identity_card],
                                                ['short' => 'Tít.', 'full' => 'Título Profesional', 'has' => $inscription->has_degree_title],
                                                ['short' => 'Dipl', 'full' => 'Diploma Académico', 'has' => $inscription->has_academic_diploma],
                                                ['short' => 'Nac.', 'full' => 'Certificado de Nacimiento', 'has' => $inscription->has_birth_certificate],
                                                ['short' => 'Comp', 'full' => 'Carta de Compromiso', 'has' => $inscription->documents->where('document_type', 'compromiso')->isNotEmpty()],
                                                ['short' => 'Cong', 'full' => 'Carta de Congelamiento', 'has' => $inscription->documents->where('document_type', 'congelamiento')->isNotEmpty()],
                                            ];
                                            $allDocs = collect($docs)->every(fn($doc) => $doc['has']);
                                        @endphp
                                        <td class="px-6 py-4 text-sm">
                                            @if($allDocs)
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800 border border-green-200">
                                                    <svg class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                    Completos
                                                </span>
                                            @else
                                                <div class="flex flex-wrap gap-2 max-w-xs">
                                                    @foreach($docs as $doc)
                                                        <span
                                                            title="{{ $doc['full'] }}"
                                                            aria-label="{{ $doc['full'] }}"
                                                            class="inline-flex items-center gap-1.5 px-2 py-1 rounded-md text-xs font-semibold leading-none border shadow-sm whitespace-nowrap {{ $doc['has'] ? 'bg-green-50 text-green-700 border-green-200' : 'bg-red-50 text-red-700 border-red-200' }}"
                                                        >
                                                            <span class="inline-block h-1.5 w-1.5 rounded-full {{ $doc['has'] ? 'bg-green-500' : 'bg-red-500' }}"></span>
                                                            {{ $doc['short'] }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No hay inscritos para mostrar</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    @if($inscriptions->hasPages())
                        <div class="mt-6">
                            {{ $inscriptions->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
