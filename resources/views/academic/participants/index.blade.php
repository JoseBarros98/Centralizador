<style>
/* Columnas fijas al hacer scroll horizontal */
.sticky-col { position: sticky; z-index: 2; }
.col-num     { left: 0;     min-width: 2.5rem; }
.col-nombre  { left: 2.5rem; min-width: 11rem; }
.col-ci      { left: 13.5rem; min-width: 6rem; }
.col-prog    { left: 19.5rem; min-width: 13rem; }

/* Fondo por contexto para que no se vea el contenido detrás */
thead tr:first-child .sticky-col { background: #1f2937; } /* gray-800 */
thead tr:last-child  .sticky-col { background: #4b5563; } /* gray-600 */
tbody tr             .sticky-col { background: #ffffff; }
tbody tr:hover       .sticky-col { background: #f9fafb; } /* gray-50 */

/* Sombra en la última columna fija para separar visualmente */
.col-prog { box-shadow: 4px 0 6px -2px rgba(0,0,0,0.15); }
</style>

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            Participantes — Todos los Programas
        </h2>
    </x-slot>

    <div class="w-full sm:px-6 lg:px-8">

        {{-- Filtro de búsqueda --}}
        <form method="GET" action="{{ route('academic.participants.index') }}" class="flex items-center gap-2 mb-4">
            <input
                type="text"
                name="search"
                value="{{ $search }}"
                placeholder="Buscar por nombre o CI..."
                class="px-4 py-2 border border-gray-300 rounded-md text-sm w-72 focus:outline-none focus:ring-2 focus:ring-indigo-500"
            >
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700 transition">
                Buscar
            </button>
            @if($search)
                <a href="{{ route('academic.participants.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded-md text-sm hover:bg-gray-600 transition">
                    Limpiar
                </a>
            @endif
        </form>

        {{-- Tabla --}}
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-4 border-b border-gray-200">
                <p class="text-sm text-gray-500">
                    {{ $rows->total() }} resultado(s) encontrado(s)
                </p>
            </div>

            @if($rows->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-xs">
                    <thead>
                        <tr class="bg-gray-800 text-white">
                            <th rowspan="2" class="sticky-col col-num px-3 py-3 text-left font-medium uppercase tracking-wider whitespace-nowrap">#</th>
                            <th rowspan="2" class="sticky-col col-nombre px-3 py-3 text-left font-medium uppercase tracking-wider whitespace-nowrap">Nombre Completo</th>
                            <th rowspan="2" class="sticky-col col-ci px-3 py-3 text-left font-medium uppercase tracking-wider whitespace-nowrap">CI / Ext.</th>
                            <th rowspan="2" class="sticky-col col-prog px-3 py-3 text-left font-medium uppercase tracking-wider whitespace-nowrap">Programa</th>
                            <th rowspan="2" class="px-3 py-3 text-center font-medium uppercase tracking-wider whitespace-nowrap">Est. Participante</th>
                            <th rowspan="2" class="px-3 py-3 text-center font-medium uppercase tracking-wider whitespace-nowrap">Est. Contable</th>
                            <th colspan="5" class="px-3 py-3 text-center font-medium uppercase tracking-wider border-l border-gray-600">Requisitos de Inscripción</th>
                            <th colspan="6" class="px-3 py-3 text-center font-medium uppercase tracking-wider border-l border-gray-600">Requisitos de Titulación</th>
                            <th colspan="3" class="px-3 py-3 text-center font-medium uppercase tracking-wider border-l border-gray-600">Trabajo Final</th>
                            <th colspan="5" class="px-3 py-3 text-center font-medium uppercase tracking-wider border-l border-gray-600">Estado de Titulación</th>
                        </tr>
                        <tr class="bg-gray-600 text-white border-t border-gray-500">
                            {{-- Req. Inscripción --}}
                            <th class="px-3 py-2 text-center font-medium uppercase whitespace-nowrap">Título Acad.</th>
                            <th class="px-3 py-2 text-center font-medium uppercase whitespace-nowrap">Tít. Prov. Nac.</th>
                            <th class="px-3 py-2 text-center font-medium uppercase whitespace-nowrap">Cédula</th>
                            <th class="px-3 py-2 text-center font-medium uppercase whitespace-nowrap">Cert. Nacim.</th>
                            <th class="px-3 py-2 text-center font-medium uppercase whitespace-nowrap">% Completo</th>
                            {{-- Req. Titulación --}}
                            <th class="px-3 py-2 text-center font-medium uppercase whitespace-nowrap border-l border-gray-500">Tít. Acad. Legal.</th>
                            <th class="px-3 py-2 text-center font-medium uppercase whitespace-nowrap">Tít. Prov. Legal.</th>
                            <th class="px-3 py-2 text-center font-medium uppercase whitespace-nowrap">Cédula</th>
                            <th class="px-3 py-2 text-center font-medium uppercase whitespace-nowrap">Cert. Nacim. Orig.</th>
                            <th class="px-3 py-2 text-center font-medium uppercase whitespace-nowrap">Fotos</th>
                            <th class="px-3 py-2 text-center font-medium uppercase whitespace-nowrap">% Completo</th>
                            {{-- Trabajo Final (3 cols: Col1, Col2, %) --}}
                            <th class="px-3 py-2 text-center font-medium uppercase whitespace-nowrap border-l border-gray-500">Etapa 1</th>
                            <th class="px-3 py-2 text-center font-medium uppercase whitespace-nowrap">Etapa 2</th>
                            <th class="px-3 py-2 text-center font-medium uppercase whitespace-nowrap">% / Estado</th>
                            {{-- Estado Titulación --}}
                            <th class="px-3 py-2 text-center font-medium uppercase whitespace-nowrap border-l border-gray-500">Trámite</th>
                            <th class="px-3 py-2 text-center font-medium uppercase whitespace-nowrap">Recepcionado</th>
                            <th class="px-3 py-2 text-center font-medium uppercase whitespace-nowrap">Entrega Docs.</th>
                            <th class="px-3 py-2 text-center font-medium uppercase whitespace-nowrap">Entrega Diplomas</th>
                            <th class="px-3 py-2 text-center font-medium uppercase whitespace-nowrap">% Completo</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @foreach($rows as $row)
                        @php
                            $isDiplomado = str_starts_with($row->program_name, 'Diplomado');
                            $isMaestria  = str_starts_with($row->program_name, 'Maestría');

                            // Req. inscripción
                            $reqInsc = [$row->has_degree_title, $row->has_academic_diploma, $row->has_identity_card, $row->has_birth_certificate];
                            $reqInscPct = intval(array_sum($reqInsc) / 4 * 100);

                            // Req. titulación
                            $reqTit = [$row->has_legalized_degree_title, $row->has_legalized_academic_diploma, $row->has_identity_card_graduation, $row->has_birth_certificate_original, $row->has_photos];
                            $reqTitPct = intval(array_sum($reqTit) / 5 * 100);

                            // Trabajo final
                            if ($isDiplomado) {
                                $tfItems = [$row->has_monograph_elaboration, $row->has_monograph_received];
                                $tfPct = intval(array_sum($tfItems) / 2 * 100);
                            } elseif ($isMaestria) {
                                $tfItems = [$row->has_degree_work_presentation, $row->has_tutor_approval_report];
                                $tfPct = intval(array_sum($tfItems) / 2 * 100);
                            }

                            // Estado titulación
                            $estTit = [$row->has_graduation_procedure, $row->has_graduation_received, $row->has_documents_delivered, $row->has_diplomas_delivered];
                            $estTitPct = intval(array_sum($estTit) / 4 * 100);

                            // Estado contable (calculado desde columnas reales, igual que los accessors del modelo)
                            $saldo = max(0, (float)($row->cuotas_retrasadas_importe ?? 0) - (float)($row->cuotas_retrasadas_cobrado ?? 0))
                                   + max(0, (float)($row->cuota_vigente_importe ?? 0) - (float)($row->cuota_vigente_cobrado ?? 0));
                            if ($row->ad_excluido ?? false) {
                                $contableLabel = 'Excluido';
                                $contableClass = 'bg-gray-100 text-gray-600';
                            } elseif (is_null($row->cuotas_retrasadas_importe)) {
                                $contableLabel = 'Sin asignación';
                                $contableClass = 'bg-gray-100 text-gray-500';
                            } elseif ($saldo > 0) {
                                $contableLabel = 'Con saldo';
                                $contableClass = 'bg-red-100 text-red-700';
                            } else {
                                $contableLabel = 'Al día';
                                $contableClass = 'bg-green-100 text-green-700';
                            }

                            // Badge programa
                            $programBadgeClass = match($row->program_status) {
                                'Activo', 'activo'       => 'bg-green-100 text-green-800',
                                'Finalizado'             => 'bg-blue-100 text-blue-800',
                                'Suspendido'             => 'bg-red-100 text-red-800',
                                default                  => 'bg-gray-100 text-gray-600',
                            };
                        @endphp
                        <tr class="hover:bg-gray-50">
                            {{-- # --}}
                            <td class="sticky-col col-num px-3 py-3 whitespace-nowrap text-gray-500">
                                {{ ($rows->currentPage() - 1) * $rows->perPage() + $loop->iteration }}
                            </td>

                            {{-- Nombre completo --}}
                            <td class="sticky-col col-nombre px-3 py-3 whitespace-nowrap font-medium text-gray-900">
                                @if($row->paternal_surname || $row->maternal_surname)
                                    {{ trim(($row->paternal_surname ?? '') . ' ' . ($row->maternal_surname ?? '') . ', ' . ($row->name ?? '')) }}
                                @else
                                    {{ $row->full_name ?? '-' }}
                                @endif
                            </td>

                            {{-- CI / Ext --}}
                            <td class="sticky-col col-ci px-3 py-3 whitespace-nowrap text-gray-600">
                                {{ $row->ci ?? '-' }}
                                @if($row->extension)
                                    <span class="text-gray-400">({{ $row->extension }})</span>
                                @endif
                            </td>

                            {{-- Programa + badge --}}
                            <td class="sticky-col col-prog px-3 py-3 text-gray-800">
                                <div>{{ $row->program_name }}</div>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium mt-1 {{ $programBadgeClass }}">
                                    {{ $row->program_status ?? 'Sin estado' }}
                                </span>
                            </td>

                            {{-- Estado participante --}}
                            <td class="px-3 py-3 whitespace-nowrap text-center">
                                @can('participants.edit')
                                <select
                                    class="participant-status-select px-2 py-1 border border-gray-300 rounded text-xs"
                                    data-program-id="{{ $row->program_id }}"
                                    data-inscription-id="{{ $row->inscription_id }}"
                                    data-requirement="participant_status">
                                    <option value="">—</option>
                                    @foreach(['VIGENTE','DEVOLUCIÓN','ABANDON','INSCRIPCIÓN INCOMPLETA'] as $opt)
                                        <option value="{{ $opt }}" {{ $row->participant_status === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                    @endforeach
                                </select>
                                @else
                                    <span class="text-gray-700">{{ $row->participant_status ?? '-' }}</span>
                                @endcan
                            </td>

                            {{-- Estado contable --}}
                            <td class="px-3 py-3 whitespace-nowrap text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $contableClass }}">
                                    {{ $contableLabel }}
                                </span>
                            </td>

                            {{-- ── Req. Inscripción ── --}}
                            @foreach([
                                ['has_degree_title',    'Título Académico'],
                                ['has_academic_diploma','Tít. Prov. Nac.'],
                                ['has_identity_card',   'Cédula'],
                                ['has_birth_certificate','Cert. Nacim.'],
                            ] as [$field, $label])
                            <td class="px-3 py-3 text-center">
                                @can('participants.edit')
                                <input type="checkbox"
                                    class="requirement-checkbox"
                                    data-program-id="{{ $row->program_id }}"
                                    data-inscription-id="{{ $row->inscription_id }}"
                                    data-requirement="{{ $field }}"
                                    data-pct-group="insc"
                                    {{ $row->{$field} ? 'checked' : '' }}>
                                @else
                                    @if($row->{$field})
                                        <span class="text-green-600">✓</span>
                                    @else
                                        <span class="text-red-400">✗</span>
                                    @endif
                                @endcan
                            </td>
                            @endforeach
                            {{-- % Req. Inscripción --}}
                            <td class="px-3 py-3 whitespace-nowrap text-center font-semibold {{ $reqInscPct == 100 ? 'text-green-600' : ($reqInscPct >= 50 ? 'text-yellow-600' : 'text-red-600') }}"
                                data-pct-cell="insc"
                                data-inscription-id="{{ $row->inscription_id }}"
                                data-program-id="{{ $row->program_id }}"
                                data-total="4">
                                {{ $reqInscPct }}%
                            </td>

                            {{-- ── Req. Titulación ── --}}
                            @foreach([
                                ['has_legalized_degree_title',    'legalized_degree_title_date',    'legalized_degree_title_obs'],
                                ['has_legalized_academic_diploma','legalized_academic_diploma_date','legalized_academic_diploma_obs'],
                                ['has_identity_card_graduation',  'identity_card_graduation_date',  'identity_card_graduation_obs'],
                                ['has_birth_certificate_original','birth_certificate_original_date','birth_certificate_original_obs'],
                                ['has_photos',                    'photos_date',                    'photos_obs'],
                            ] as [$boolF, $dateF, $obsF])
                            <td class="px-2 py-2 text-center {{ $loop->first ? 'border-l border-gray-100' : '' }}">
                                <div class="flex flex-col items-center gap-1 min-w-[100px]">
                                    @can('participants.edit')
                                    <input type="checkbox"
                                        class="requirement-checkbox"
                                        data-program-id="{{ $row->program_id }}"
                                        data-inscription-id="{{ $row->inscription_id }}"
                                        data-requirement="{{ $boolF }}"
                                        data-pct-group="tit"
                                        {{ $row->{$boolF} ? 'checked' : '' }}>
                                    <input type="date"
                                        class="req-date-field w-full px-1 py-0.5 text-xs border border-gray-200 rounded"
                                        data-program-id="{{ $row->program_id }}"
                                        data-inscription-id="{{ $row->inscription_id }}"
                                        data-requirement="{{ $dateF }}"
                                        value="{{ $row->{$dateF} ?? '' }}">
                                    <input type="text"
                                        class="req-obs-field w-full px-1 py-0.5 text-xs border border-gray-200 rounded"
                                        data-program-id="{{ $row->program_id }}"
                                        data-inscription-id="{{ $row->inscription_id }}"
                                        data-requirement="{{ $obsF }}"
                                        value="{{ $row->{$obsF} ?? '' }}"
                                        placeholder="Obs.">
                                    @else
                                        {{ $row->{$boolF} ? '✓' : '✗' }}
                                    @endcan
                                </div>
                            </td>
                            @endforeach
                            {{-- % Req. Titulación --}}
                            <td class="px-3 py-3 whitespace-nowrap text-center font-semibold {{ $reqTitPct == 100 ? 'text-green-600' : ($reqTitPct >= 50 ? 'text-yellow-600' : 'text-red-600') }}"
                                data-pct-cell="tit"
                                data-inscription-id="{{ $row->inscription_id }}"
                                data-program-id="{{ $row->program_id }}"
                                data-total="5">
                                {{ $reqTitPct }}%
                            </td>

                            {{-- ── Trabajo Final ── --}}
                            @if($isDiplomado)
                                @foreach([
                                    ['has_monograph_elaboration','monograph_elaboration_date','monograph_elaboration_obs','Elaboración Monografía'],
                                    ['has_monograph_received',   'monograph_received_date',  'monograph_received_obs',  'Monografía en Sede'],
                                ] as [$boolF, $dateF, $obsF, $lbl])
                                <td class="px-2 py-2 text-center {{ $loop->first ? 'border-l border-gray-100' : '' }}">
                                    <div class="flex flex-col items-center gap-1 min-w-[100px]">
                                        @can('participants.edit')
                                        <span class="text-xs text-gray-500">{{ $lbl }}</span>
                                        <input type="checkbox"
                                            class="requirement-checkbox"
                                            data-program-id="{{ $row->program_id }}"
                                            data-inscription-id="{{ $row->inscription_id }}"
                                            data-requirement="{{ $boolF }}"
                                            data-pct-group="tf"
                                            {{ $row->{$boolF} ? 'checked' : '' }}>
                                        <input type="date"
                                            class="req-date-field w-full px-1 py-0.5 text-xs border border-gray-200 rounded"
                                            data-program-id="{{ $row->program_id }}"
                                            data-inscription-id="{{ $row->inscription_id }}"
                                            data-requirement="{{ $dateF }}"
                                            value="{{ $row->{$dateF} ?? '' }}">
                                        <input type="text"
                                            class="req-obs-field w-full px-1 py-0.5 text-xs border border-gray-200 rounded"
                                            data-program-id="{{ $row->program_id }}"
                                            data-inscription-id="{{ $row->inscription_id }}"
                                            data-requirement="{{ $obsF }}"
                                            value="{{ $row->{$obsF} ?? '' }}"
                                            placeholder="Obs.">
                                        @else
                                            {{ $row->{$boolF} ? '✓' : '✗' }}
                                        @endcan
                                    </div>
                                </td>
                                @endforeach
                                <td class="px-3 py-3 whitespace-nowrap text-center font-semibold border-l border-gray-100 {{ $tfPct == 100 ? 'text-green-600' : ($tfPct >= 50 ? 'text-yellow-600' : 'text-red-600') }}"
                                    data-pct-cell="tf"
                                    data-inscription-id="{{ $row->inscription_id }}"
                                    data-program-id="{{ $row->program_id }}"
                                    data-total="2">
                                    {{ $tfPct }}%
                                </td>
                            @elseif($isMaestria)
                                @foreach([
                                    ['has_degree_work_presentation','degree_work_presentation_date','degree_work_presentation_obs','Pres. Trabajo de Grado'],
                                    ['has_tutor_approval_report',   'tutor_approval_report_date',   'tutor_approval_report_obs',   'Informe Aprobación Tutor'],
                                ] as [$boolF, $dateF, $obsF, $lbl])
                                <td class="px-2 py-2 text-center {{ $loop->first ? 'border-l border-gray-100' : '' }}">
                                    <div class="flex flex-col items-center gap-1 min-w-[110px]">
                                        @can('participants.edit')
                                        <span class="text-xs text-gray-500">{{ $lbl }}</span>
                                        <input type="checkbox"
                                            class="requirement-checkbox"
                                            data-program-id="{{ $row->program_id }}"
                                            data-inscription-id="{{ $row->inscription_id }}"
                                            data-requirement="{{ $boolF }}"
                                            data-pct-group="tf"
                                            {{ ($row->{$boolF} ?? false) ? 'checked' : '' }}>
                                        <input type="date"
                                            class="req-date-field w-full px-1 py-0.5 text-xs border border-gray-200 rounded"
                                            data-program-id="{{ $row->program_id }}"
                                            data-inscription-id="{{ $row->inscription_id }}"
                                            data-requirement="{{ $dateF }}"
                                            value="{{ $row->{$dateF} ?? '' }}">
                                        <input type="text"
                                            class="req-obs-field w-full px-1 py-0.5 text-xs border border-gray-200 rounded"
                                            data-program-id="{{ $row->program_id }}"
                                            data-inscription-id="{{ $row->inscription_id }}"
                                            data-requirement="{{ $obsF }}"
                                            value="{{ $row->{$obsF} ?? '' }}"
                                            placeholder="Obs.">
                                        @else
                                            {{ ($row->{$boolF} ?? false) ? '✓' : '✗' }}
                                        @endcan
                                    </div>
                                </td>
                                @endforeach
                                <td class="px-3 py-3 whitespace-nowrap text-center font-semibold border-l border-gray-100 {{ $tfPct == 100 ? 'text-green-600' : ($tfPct >= 50 ? 'text-yellow-600' : 'text-red-600') }}"
                                    data-pct-cell="tf"
                                    data-inscription-id="{{ $row->inscription_id }}"
                                    data-program-id="{{ $row->program_id }}"
                                    data-total="2">
                                    {{ $tfPct }}%
                                </td>
                            @else
                                <td colspan="3" class="px-3 py-3 text-center text-gray-300 border-l border-gray-100">—</td>
                            @endif

                            {{-- ── Estado de Titulación ── --}}
                            @foreach([
                                ['has_graduation_procedure', 'graduation_procedure_date', 'graduation_procedure_obs'],
                                ['has_graduation_received',  'graduation_received_date',  'graduation_received_obs'],
                                ['has_documents_delivered',  'documents_delivered_date',  'documents_delivered_obs'],
                                ['has_diplomas_delivered',   'diplomas_delivered_date',   'diplomas_delivered_obs'],
                            ] as [$boolF, $dateF, $obsF])
                            <td class="px-2 py-2 text-center {{ $loop->first ? 'border-l border-gray-100' : '' }}">
                                <div class="flex flex-col items-center gap-1 min-w-[100px]">
                                    @can('participants.edit')
                                    <input type="checkbox"
                                        class="requirement-checkbox"
                                        data-program-id="{{ $row->program_id }}"
                                        data-inscription-id="{{ $row->inscription_id }}"
                                        data-requirement="{{ $boolF }}"
                                        data-pct-group="est"
                                        {{ $row->{$boolF} ? 'checked' : '' }}>
                                    <input type="date"
                                        class="req-date-field w-full px-1 py-0.5 text-xs border border-gray-200 rounded"
                                        data-program-id="{{ $row->program_id }}"
                                        data-inscription-id="{{ $row->inscription_id }}"
                                        data-requirement="{{ $dateF }}"
                                        value="{{ $row->{$dateF} ?? '' }}">
                                    <input type="text"
                                        class="req-obs-field w-full px-1 py-0.5 text-xs border border-gray-200 rounded"
                                        data-program-id="{{ $row->program_id }}"
                                        data-inscription-id="{{ $row->inscription_id }}"
                                        data-requirement="{{ $obsF }}"
                                        value="{{ $row->{$obsF} ?? '' }}"
                                        placeholder="Obs.">
                                    @else
                                        {{ $row->{$boolF} ? '✓' : '✗' }}
                                    @endcan
                                </div>
                            </td>
                            @endforeach
                            {{-- % Estado Titulación --}}
                            <td class="px-3 py-3 whitespace-nowrap text-center font-semibold {{ $estTitPct == 100 ? 'text-green-600' : ($estTitPct >= 50 ? 'text-yellow-600' : 'text-red-600') }}"
                                data-pct-cell="est"
                                data-inscription-id="{{ $row->inscription_id }}"
                                data-program-id="{{ $row->program_id }}"
                                data-total="4">
                                {{ $estTitPct }}%
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Paginación --}}
            <div class="px-4 py-3 border-t border-gray-200">
                {{ $rows->appends(['search' => $search])->links() }}
            </div>

            @else
            <div class="p-6">
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                    <p class="text-sm text-yellow-700">No se encontraron participantes.</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content
        || document.querySelector('input[name="_token"]')?.value
        || '';

    function saveRequirement(programId, inscriptionId, requirement, value) {
        return fetch(`/programs/${programId}/inscriptions/${inscriptionId}/update-requirement`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({ requirement, value }),
        }).then(r => r.json());
    }

    function flash(el, success) {
        const color = success ? '#10b981' : '#ef4444';
        el.style.borderColor = color;
        el.style.outlineColor = color;
        setTimeout(() => {
            el.style.borderColor = '';
            el.style.outlineColor = '';
        }, 2000);
    }

    function updatePctCell(group, inscriptionId, programId) {
        const checkboxes = document.querySelectorAll(
            `.requirement-checkbox[data-pct-group="${group}"][data-inscription-id="${inscriptionId}"][data-program-id="${programId}"]`
        );
        const cell = document.querySelector(
            `[data-pct-cell="${group}"][data-inscription-id="${inscriptionId}"][data-program-id="${programId}"]`
        );
        if (!cell || !checkboxes.length) return;

        const total = parseInt(cell.dataset.total, 10);
        const checked = Array.from(checkboxes).filter(cb => cb.checked).length;
        const pct = Math.round(checked / total * 100);

        cell.textContent = pct + '%';
        cell.classList.remove('text-green-600', 'text-yellow-600', 'text-red-600');
        if (pct === 100) cell.classList.add('text-green-600');
        else if (pct >= 50) cell.classList.add('text-yellow-600');
        else cell.classList.add('text-red-600');
    }

    // Checkboxes de requisitos
    document.querySelectorAll('.requirement-checkbox').forEach(cb => {
        cb.addEventListener('change', function () {
            const group = this.dataset.pctGroup;
            const inscriptionId = this.dataset.inscriptionId;
            const programId = this.dataset.programId;

            saveRequirement(programId, inscriptionId, this.dataset.requirement, this.checked)
                .then(data => {
                    if (data.success) {
                        if (group) updatePctCell(group, inscriptionId, programId);
                    } else {
                        this.checked = !this.checked;
                        alert('Error al guardar: ' + (data.message ?? ''));
                    }
                })
                .catch(() => { this.checked = !this.checked; });
        });
    });

    // Campos de fecha de requisito
    document.querySelectorAll('.req-date-field').forEach(input => {
        input.addEventListener('change', function () {
            saveRequirement(this.dataset.programId, this.dataset.inscriptionId, this.dataset.requirement, this.value)
                .then(data => flash(this, data.success))
                .catch(() => flash(this, false));
        });
    });

    // Campos de observación de requisito
    document.querySelectorAll('.req-obs-field').forEach(input => {
        input.addEventListener('blur', function () {
            saveRequirement(this.dataset.programId, this.dataset.inscriptionId, this.dataset.requirement, this.value)
                .then(data => flash(this, data.success))
                .catch(() => flash(this, false));
        });
    });

    // Select de estado participante
    document.querySelectorAll('.participant-status-select').forEach(sel => {
        sel.addEventListener('change', function () {
            saveRequirement(this.dataset.programId, this.dataset.inscriptionId, this.dataset.requirement, this.value)
                .then(data => {
                    if (data.success) {
                        this.style.borderColor = '#10b981';
                        setTimeout(() => { this.style.borderColor = ''; }, 2000);
                    } else {
                        alert('Error al guardar: ' + (data.message ?? ''));
                    }
                });
        });
    });
});
</script>
