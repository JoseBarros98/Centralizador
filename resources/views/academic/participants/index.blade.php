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

        {{-- Filtros --}}
        <form method="GET" action="{{ route('academic.participants.index') }}" class="flex flex-wrap items-center gap-2 mb-4" id="participants-filter-form">

            {{-- Gestión --}}
            <select name="gestion" id="filter-gestion"
                    style="min-width:170px;"
                    class="px-3 py-2 border rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors
                           {{ $gestion ? 'border-indigo-400 bg-indigo-50 text-indigo-800 font-semibold' : 'border-gray-300 text-gray-700' }}">
                <option value="">Todas las gestiones</option>
                @foreach($availableYears as $year)
                    <option value="{{ $year }}" @selected($gestion == $year)>{{ $year }}</option>
                @endforeach
            </select>

            {{-- Programa (combobox JS puro) --}}
            <input type="hidden" name="program" id="filter-program-id" value="{{ $program }}">
            <div class="flex items-center border rounded-md text-sm transition-colors overflow-hidden {{ $program ? 'border-indigo-400 bg-indigo-50' : 'border-gray-300 bg-white' }}" id="filter-program-wrap">
                <input type="text"
                       id="filter-program-input"
                       value="{{ $program ? ($availablePrograms->firstWhere('id', $program)?->name ?? '') : '' }}"
                       placeholder="Buscar programa..."
                       class="px-3 py-2 text-sm bg-transparent focus:outline-none w-56 {{ $program ? 'text-indigo-800 font-semibold' : 'text-gray-700' }}"
                       autocomplete="off">
                <button type="button" id="filter-program-clear"
                        class="px-2 text-indigo-400 hover:text-indigo-600 {{ $program ? '' : 'hidden' }}">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Búsqueda --}}
            <input type="text" name="search" value="{{ $search }}"
                   placeholder="Buscar por nombre o CI..."
                   class="px-4 py-2 border border-gray-300 rounded-md text-sm w-56 focus:outline-none focus:ring-2 focus:ring-indigo-500">

            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700 transition">
                Buscar
            </button>

            @if($search || $gestion || $program)
                <a href="{{ route('academic.participants.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded-md text-sm hover:bg-gray-600 transition">
                    Limpiar
                </a>
            @endif
        </form>

        {{-- Dropdown de programas (renderizado en body por JS) --}}
        <ul id="program-combobox-list"
            style="display:none;position:fixed;z-index:9999;width:320px;max-height:240px;overflow-y:auto;background:#fff;border:1px solid #e5e7eb;border-radius:6px;box-shadow:0 4px 16px rgba(0,0,0,0.12);font-size:0.875rem;"></ul>

        <script>
        (function () {
            const allPrograms = @json($availablePrograms);

            const gestionSel  = document.getElementById('filter-gestion');
            const input       = document.getElementById('filter-program-input');
            const hiddenId    = document.getElementById('filter-program-id');
            const clearBtn    = document.getElementById('filter-program-clear');
            const wrap        = document.getElementById('filter-program-wrap');
            const list        = document.getElementById('program-combobox-list');

            // Mover la lista al body para evitar cualquier clipping
            document.body.appendChild(list);

            function getPrograms() {
                const g = gestionSel.value;
                return g ? allPrograms.filter(p => String(p.year) === String(g)) : allPrograms;
            }

            function renderList(query) {
                const q = query.toLowerCase().trim();
                const items = getPrograms().filter(p => !q || p.name.toLowerCase().includes(q));
                list.innerHTML = '';
                if (!items.length) {
                    list.innerHTML = '<li style="padding:8px 12px;color:#9ca3af;font-style:italic;">Sin resultados</li>';
                } else {
                    items.forEach(p => {
                        const li = document.createElement('li');
                        li.textContent = p.name;
                        li.style.cssText = 'padding:8px 12px;cursor:pointer;color:#374151;';
                        if (String(p.id) === hiddenId.value) {
                            li.style.background = '#eef2ff';
                            li.style.color = '#3730a3';
                            li.style.fontWeight = '600';
                        }
                        li.addEventListener('mouseover', () => li.style.background = '#eef2ff');
                        li.addEventListener('mouseout',  () => {
                            li.style.background = String(p.id) === hiddenId.value ? '#eef2ff' : '';
                        });
                        li.addEventListener('mousedown', e => {
                            e.preventDefault();
                            selectProgram(p);
                        });
                        list.appendChild(li);
                    });
                }
            }

            function openList() {
                const rect = input.getBoundingClientRect();
                list.style.top  = (rect.bottom + 4) + 'px';
                list.style.left = rect.left + 'px';
                list.style.display = 'block';
                renderList(input.value);
            }

            function closeList() {
                list.style.display = 'none';
            }

            function selectProgram(p) {
                hiddenId.value = p.id;
                input.value    = p.name;
                input.classList.remove('text-gray-700');
                input.classList.add('text-indigo-800', 'font-semibold');
                wrap.classList.remove('border-gray-300', 'bg-white');
                wrap.classList.add('border-indigo-400', 'bg-indigo-50');
                clearBtn.classList.remove('hidden');
                closeList();
            }

            function clearProgram() {
                hiddenId.value = '';
                input.value    = '';
                input.classList.remove('text-indigo-800', 'font-semibold');
                input.classList.add('text-gray-700');
                wrap.classList.remove('border-indigo-400', 'bg-indigo-50');
                wrap.classList.add('border-gray-300', 'bg-white');
                clearBtn.classList.add('hidden');
                closeList();
            }

            input.addEventListener('focus', openList);
            input.addEventListener('input', openList);
            clearBtn.addEventListener('click', clearProgram);
            gestionSel.addEventListener('change', () => { clearProgram(); });
            document.addEventListener('click', e => {
                if (!wrap.contains(e.target) && !list.contains(e.target)) closeList();
            });
        })();
        </script>

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
                                @php
                                    if ($row->paternal_surname || $row->maternal_surname) {
                                        $displayName = trim(
                                            ($row->paternal_surname ?? '') . ' ' .
                                            ($row->maternal_surname ?? '') . ', ' .
                                            ($row->name ?? '')
                                        );
                                    } else {
                                        $raw = $row->full_name ?? '-';
                                        $displayName = preg_replace('/([a-záéíóúüñ])([A-ZÁÉÍÓÚÜÑ])/u', '$1 $2', $raw);
                                    }
                                @endphp
                                <div class="flex items-center gap-1.5">
                                    <span>{{ $displayName }}</span>
                                    <button type="button"
                                            class="docs-btn flex-shrink-0 p-0.5 rounded text-gray-300 hover:text-indigo-600 hover:bg-indigo-50 transition-colors"
                                            data-inscription-id="{{ $row->inscription_id }}"
                                            data-name="{{ $displayName }}"
                                            title="Ver documentos">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                        </svg>
                                    </button>
                                </div>
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
                                    <button type="button"
                                        class="obs-btn w-full text-left rounded px-1.5 py-0.5 border transition-colors {{ $row->{$obsF} ? 'border-amber-300 bg-amber-50 hover:bg-amber-100' : 'border-dashed border-gray-200 hover:border-gray-400' }}"
                                        data-program-id="{{ $row->program_id }}"
                                        data-inscription-id="{{ $row->inscription_id }}"
                                        data-requirement="{{ $obsF }}"
                                        data-value="{{ $row->{$obsF} ?? '' }}"
                                        title="{{ $row->{$obsF} ?? 'Añadir observación' }}">
                                        @if($row->{$obsF})
                                            <span class="inline-flex items-center gap-1 max-w-full">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-400 flex-shrink-0"></span>
                                                <span class="text-gray-600 text-xs truncate" style="max-width:4.5rem;">{{ $row->{$obsF} }}</span>
                                            </span>
                                        @else
                                            <span class="text-gray-300 text-xs">+ obs</span>
                                        @endif
                                    </button>
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
                                        <button type="button"
                                            class="obs-btn w-full text-left rounded px-1.5 py-0.5 border transition-colors {{ $row->{$obsF} ? 'border-amber-300 bg-amber-50 hover:bg-amber-100' : 'border-dashed border-gray-200 hover:border-gray-400' }}"
                                            data-program-id="{{ $row->program_id }}"
                                            data-inscription-id="{{ $row->inscription_id }}"
                                            data-requirement="{{ $obsF }}"
                                            data-value="{{ $row->{$obsF} ?? '' }}"
                                            title="{{ $row->{$obsF} ?? 'Añadir observación' }}">
                                            @if($row->{$obsF})
                                                <span class="inline-flex items-center gap-1 max-w-full">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400 flex-shrink-0"></span>
                                                    <span class="text-gray-600 text-xs truncate" style="max-width:4.5rem;">{{ $row->{$obsF} }}</span>
                                                </span>
                                            @else
                                                <span class="text-gray-300 text-xs">+ obs</span>
                                            @endif
                                        </button>
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
                                        <button type="button"
                                            class="obs-btn w-full text-left rounded px-1.5 py-0.5 border transition-colors {{ $row->{$obsF} ? 'border-amber-300 bg-amber-50 hover:bg-amber-100' : 'border-dashed border-gray-200 hover:border-gray-400' }}"
                                            data-program-id="{{ $row->program_id }}"
                                            data-inscription-id="{{ $row->inscription_id }}"
                                            data-requirement="{{ $obsF }}"
                                            data-value="{{ $row->{$obsF} ?? '' }}"
                                            title="{{ $row->{$obsF} ?? 'Añadir observación' }}">
                                            @if($row->{$obsF})
                                                <span class="inline-flex items-center gap-1 max-w-full">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400 flex-shrink-0"></span>
                                                    <span class="text-gray-600 text-xs truncate" style="max-width:4.5rem;">{{ $row->{$obsF} }}</span>
                                                </span>
                                            @else
                                                <span class="text-gray-300 text-xs">+ obs</span>
                                            @endif
                                        </button>
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
                                    <button type="button"
                                        class="obs-btn w-full text-left rounded px-1.5 py-0.5 border transition-colors {{ $row->{$obsF} ? 'border-amber-300 bg-amber-50 hover:bg-amber-100' : 'border-dashed border-gray-200 hover:border-gray-400' }}"
                                        data-program-id="{{ $row->program_id }}"
                                        data-inscription-id="{{ $row->inscription_id }}"
                                        data-requirement="{{ $obsF }}"
                                        data-value="{{ $row->{$obsF} ?? '' }}"
                                        title="{{ $row->{$obsF} ?? 'Añadir observación' }}">
                                        @if($row->{$obsF})
                                            <span class="inline-flex items-center gap-1 max-w-full">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-400 flex-shrink-0"></span>
                                                <span class="text-gray-600 text-xs truncate" style="max-width:4.5rem;">{{ $row->{$obsF} }}</span>
                                            </span>
                                        @else
                                            <span class="text-gray-300 text-xs">+ obs</span>
                                        @endif
                                    </button>
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
                {{ $rows->appends(['search' => $search, 'gestion' => $gestion])->links() }}
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

{{-- Modal de documentos --}}
<div id="docs-modal" class="hidden fixed inset-0 z-50 p-4"
     style="background:rgba(0,0,0,0.5);">
    <div class="flex items-center justify-center w-full h-full">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg max-h-[85vh] flex flex-col">
        {{-- Header --}}
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Documentos del participante</p>
                <h3 id="docs-modal-name" class="text-base font-bold text-gray-900 mt-0.5"></h3>
            </div>
            <button id="docs-modal-close"
                    class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        {{-- Body --}}
        <div id="docs-modal-body" class="flex-1 overflow-y-auto px-5 py-4">
        </div>
    </div>
    </div>
</div>

{{-- Popover flotante de observaciones --}}
<div id="obs-popover"
     style="display:none;position:fixed;z-index:9999;width:260px;background:#fff;border:1px solid #d1d5db;border-radius:10px;box-shadow:0 10px 30px rgba(0,0,0,0.18);padding:0.75rem;">
    <p style="font-size:0.65rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:0.4rem;">Observación</p>
    <textarea id="obs-textarea" rows="4"
              placeholder="Escribe una observación…"
              style="width:100%;resize:vertical;border:1px solid #d1d5db;border-radius:6px;font-size:0.75rem;padding:0.4rem 0.5rem;outline:none;font-family:inherit;line-height:1.5;"
              onfocus="this.style.borderColor='#6366f1';this.style.boxShadow='0 0 0 2px rgba(99,102,241,0.15)'"
              onblur="this.style.borderColor='#d1d5db';this.style.boxShadow='none'"></textarea>
    <p style="font-size:0.65rem;color:#9ca3af;margin:0.3rem 0 0.5rem;">
        <kbd style="background:#f3f4f6;border:1px solid #e5e7eb;border-radius:3px;padding:1px 4px;">Ctrl+Enter</kbd> para guardar &middot;
        <kbd style="background:#f3f4f6;border:1px solid #e5e7eb;border-radius:3px;padding:1px 4px;">Esc</kbd> para cancelar
    </p>
    <div style="display:flex;gap:0.4rem;">
        <button id="obs-save-btn"
                style="flex:1;padding:0.35rem 0;background:#4f46e5;color:#fff;border:none;border-radius:6px;font-size:0.75rem;font-weight:600;cursor:pointer;"
                onmouseover="this.style.background='#4338ca'" onmouseout="this.style.background='#4f46e5'">
            Guardar
        </button>
        <button id="obs-cancel-btn"
                style="flex:1;padding:0.35rem 0;background:#f3f4f6;color:#374151;border:none;border-radius:6px;font-size:0.75rem;font-weight:600;cursor:pointer;"
                onmouseover="this.style.background='#e5e7eb'" onmouseout="this.style.background='#f3f4f6'">
            Cancelar
        </button>
    </div>
</div>

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

    // ── Popover de observaciones ──────────────────────────────────────
    const popover   = document.getElementById('obs-popover');
    const popTA     = document.getElementById('obs-textarea');
    const popSave   = document.getElementById('obs-save-btn');
    const popCancel = document.getElementById('obs-cancel-btn');
    let   activeBtn = null;

    function openObsPopover(btn) {
        activeBtn = btn;
        popTA.value = btn.dataset.value ?? '';

        // Posición viewport-relative (position:fixed — NO usar scrollY)
        const rect      = btn.getBoundingClientRect();
        const popW      = 260;
        const popH      = 230; // altura estimada del popover
        const margin    = 8;

        // Horizontal: alinear con el botón, nunca salir de pantalla
        let left = rect.left;
        if (left + popW > window.innerWidth - margin) left = window.innerWidth - popW - margin;
        if (left < margin) left = margin;

        popover.style.display = 'block';
        popover.style.left    = left + 'px';

        // Vertical: preferir abajo; si no cabe, abrir arriba
        const spaceBelow = window.innerHeight - rect.bottom - margin;
        const spaceAbove = rect.top - margin;

        if (spaceBelow >= popH || spaceBelow >= spaceAbove) {
            popover.style.top    = (rect.bottom + 4) + 'px';
            popover.style.bottom = 'auto';
        } else {
            popover.style.top    = 'auto';
            popover.style.bottom = (window.innerHeight - rect.top + 4) + 'px';
        }

        popTA.focus();
    }

    function closeObsPopover() {
        popover.style.display = 'none';
        activeBtn = null;
    }

    function saveObs() {
        if (!activeBtn) return;
        const value = popTA.value.trim();
        saveRequirement(
            activeBtn.dataset.programId,
            activeBtn.dataset.inscriptionId,
            activeBtn.dataset.requirement,
            value
        ).then(data => {
            if (data.success) {
                activeBtn.dataset.value = value;
                // Actualizar visual del botón
                if (value) {
                    activeBtn.className = activeBtn.className
                        .replace('border-dashed border-gray-200 hover:border-gray-400', '')
                        .replace('border-amber-300 bg-amber-50 hover:bg-amber-100', '');
                    activeBtn.className += ' border-amber-300 bg-amber-50 hover:bg-amber-100';
                    activeBtn.title = value;
                    activeBtn.innerHTML = `<span class="inline-flex items-center gap-1 max-w-full">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400 flex-shrink-0"></span>
                        <span class="text-gray-600 text-xs truncate" style="max-width:4.5rem;">${value}</span>
                    </span>`;
                } else {
                    activeBtn.className = activeBtn.className
                        .replace('border-amber-300 bg-amber-50 hover:bg-amber-100', '')
                        .replace('border-dashed border-gray-200 hover:border-gray-400', '');
                    activeBtn.className += ' border-dashed border-gray-200 hover:border-gray-400';
                    activeBtn.title = 'Añadir observación';
                    activeBtn.innerHTML = '<span class="text-gray-300 text-xs">+ obs</span>';
                }
                closeObsPopover();
            }
        }).catch(() => {});
    }

    document.querySelectorAll('.obs-btn').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            if (popover.style.display === 'block' && activeBtn === btn) {
                closeObsPopover();
            } else {
                openObsPopover(btn);
            }
        });
    });

    popSave.addEventListener('click', saveObs);
    popCancel.addEventListener('click', closeObsPopover);

    popTA.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) { e.preventDefault(); saveObs(); }
        if (e.key === 'Escape') { closeObsPopover(); }
    });

    document.addEventListener('click', function (e) {
        if (popover.style.display === 'block' && !popover.contains(e.target)) {
            closeObsPopover();
        }
    });

    // ── Modal de documentos ───────────────────────────────────────────
    const docsModal     = document.getElementById('docs-modal');
    const docsModalName = document.getElementById('docs-modal-name');
    const docsModalBody = document.getElementById('docs-modal-body');
    const docsModalClose = document.getElementById('docs-modal-close');

    const docTypeLabels = {
        ci:                    'Cédula de Identidad',
        titulo:                'Título Académico',
        diploma:               'Diploma Académico',
        nacimiento:            'Certificado de Nacimiento',
        documentacion_completa:'Documentación Completa',
        compromiso:            'Carta de Compromiso',
        congelamiento:         'Carta de Congelamiento',
        recibo:                'Recibo',
        factura:               'Factura',
        comprobante_pago:      'Comprobante de Pago',
    };

    const docTypeIcons = {
        ci:                     '🪪',
        titulo:                 '🎓',
        diploma:                '📜',
        nacimiento:             '📋',
        documentacion_completa: '📁',
        compromiso:             '✍️',
        congelamiento:          '❄️',
        recibo:                 '🧾',
        factura:                '🧾',
        comprobante_pago:       '💳',
    };

    function fmtSize(bytes) {
        if (!bytes) return '';
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1048576) return (bytes / 1024).toFixed(0) + ' KB';
        return (bytes / 1048576).toFixed(1) + ' MB';
    }

    function openDocsModal(inscriptionId, name) {
        docsModalName.textContent = name;
        docsModalBody.innerHTML = '<div class="flex justify-center py-10"><svg class="w-8 h-8 text-indigo-400 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 22 6.477 22 12h-4z"/></svg></div>';
        docsModal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        fetch(`/academic/participants/${inscriptionId}/documents`, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
        })
        .then(r => r.json())
        .then(data => {
            const docs = data.documents ?? [];
            if (!docs.length) {
                docsModalBody.innerHTML = '<div class="text-center py-10 text-gray-400"><svg class="w-10 h-10 mx-auto mb-2 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg><p class="text-sm">Sin documentos subidos</p></div>';
                return;
            }

            // Agrupar por tipo
            const groups = {};
            docs.forEach(d => {
                const type = d.document_type ?? 'otro';
                if (!groups[type]) groups[type] = [];
                groups[type].push(d);
            });

            let html = '';
            for (const [type, items] of Object.entries(groups)) {
                const label = docTypeLabels[type] ?? type;
                const icon  = docTypeIcons[type]  ?? '📄';
                html += `<div class="mb-4">
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">${icon} ${label}</p>
                    <div class="space-y-2">`;
                items.forEach(d => {
                    const ext = (d.file_name ?? '').split('.').pop().toUpperCase();
                    const size = fmtSize(d.file_size);
                    const isImage = ['jpg','jpeg','png','gif','webp'].includes(ext.toLowerCase());
                    const isPdf   = ext === 'PDF';
                    html += `<div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg border border-gray-100 hover:border-indigo-200 transition-colors">
                        <div class="flex-shrink-0 w-9 h-9 rounded-lg flex items-center justify-center text-sm font-bold
                            ${isPdf ? 'bg-red-100 text-red-600' : isImage ? 'bg-green-100 text-green-600' : 'bg-blue-100 text-blue-600'}">
                            ${ext}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800 truncate">${d.file_name ?? '—'}</p>
                            <p class="text-xs text-gray-400">${size ? size + ' · ' : ''}${d.created_at ?? ''}</p>
                            ${d.description ? `<p class="text-xs text-gray-500 mt-0.5">${d.description}</p>` : ''}
                        </div>
                        ${d.google_drive_link ? `
                        <a href="${d.google_drive_link}" target="_blank" rel="noopener"
                           class="flex-shrink-0 inline-flex items-center gap-1 px-3 py-1.5 bg-indigo-600 text-white text-xs font-medium rounded-md hover:bg-indigo-700 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                            Ver
                        </a>` : ''}
                    </div>`;
                });
                html += '</div></div>';
            }
            docsModalBody.innerHTML = html;
        })
        .catch(() => {
            docsModalBody.innerHTML = '<p class="text-center text-sm text-red-500 py-8">Error al cargar los documentos.</p>';
        });
    }

    function closeDocsModal() {
        docsModal.classList.add('hidden');
        document.body.style.overflow = '';
    }

    document.querySelectorAll('.docs-btn').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            openDocsModal(this.dataset.inscriptionId, this.dataset.name);
        });
    });

    docsModalClose.addEventListener('click', closeDocsModal);
    docsModal.addEventListener('click', function (e) {
        if (e.target === docsModal) closeDocsModal();
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeDocsModal();
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
