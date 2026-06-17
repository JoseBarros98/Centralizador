<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">Cuotas por Participante</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">

            <div style="margin-bottom:1.5rem;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:1rem;">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Cuotas por Participante</h1>
                    <p class="mt-1 text-sm text-gray-500">
                        Programa: <span class="font-medium text-gray-700">{{ $program->name }}</span>
                    </p>
                </div>
                <a href="{{ route('nominal-assignments.index', ['program_id' => $program->id]) }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Volver a Asignación Nominal
                </a>
            </div>

            @if($inscriptions->isEmpty())
            <div class="bg-white rounded-xl shadow-sm p-10 text-center text-gray-400">
                No hay participantes activos en este programa (estados Inscripciones o Desarrollo).
            </div>
            @else

            <!-- Barra de herramientas -->
            <div style="margin-bottom:1rem;display:flex;flex-wrap:wrap;align-items:center;gap:0.75rem;">
                <div style="position:relative;flex:1;min-width:200px;max-width:360px;">
                    <svg style="position:absolute;left:10px;top:50%;transform:translateY(-50%);width:1rem;height:1rem;color:#9ca3af;pointer-events:none;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                    </svg>
                    <input type="text" id="participantSearch" placeholder="Buscar participante..."
                        oninput="filterParticipants(this.value)"
                        style="width:100%;padding:8px 12px 8px 34px;border:1px solid #d1d5db;border-radius:8px;font-size:0.875rem;color:#374151;outline:none;"
                        onfocus="this.style.borderColor='#6b7280'" onblur="this.style.borderColor='#d1d5db'">
                </div>
                <button onclick="openBulkModal()"
                    style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;background-color:#4f46e5;color:#fff;border:none;border-radius:8px;font-size:0.875rem;font-weight:500;cursor:pointer;">
                    <svg style="width:1rem;height:1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Generación Masiva
                </button>
            </div>
            <p id="participantCount" class="text-xs text-gray-400 mb-3"></p>

            <div class="space-y-4" id="participantsList">
                @foreach($inscriptions as $inscription)
                @php
                    $inscQuotas   = $quotas->get($inscription->id, collect());
                    $discount     = $discounts->get($inscription->id);
                @endphp
                <div class="bg-white shadow-sm rounded-xl overflow-hidden participant-card"
                     data-inscription="{{ $inscription->id }}"
                     data-program="{{ $program->id }}"
                     data-name="{{ strtolower($inscription->getFullName()) }}">

                    <!-- Cabecera del participante -->
                    <div class="px-5 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 cursor-pointer select-none"
                         onclick="toggleParticipant({{ $inscription->id }})">
                        <div class="flex items-center gap-3">
                            <svg class="h-5 w-5 text-gray-400 chevron-{{ $inscription->id }} transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                            <div>
                                <p class="font-semibold text-gray-900 text-sm">{{ $inscription->getFullName() }}</p>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    Plan: {{ $inscription->payment_plan ?? 'Sin plan' }} |
                                    Tel: {{ $inscription->phone ?? '—' }} |
                                    {{ $inscQuotas->count() }} cuota(s) configurada(s)
                                    @if($inscQuotas->where('pagada', true)->count() > 0)
                                    | <span style="color:#16a34a;font-weight:600;">{{ $inscQuotas->where('pagada', true)->count() }} pagada(s)</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0" onclick="event.stopPropagation()">
                            @if($discount)
                            <span class="inline-flex items-center px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800 font-medium">
                                Descuento: {{ $discount->tipo === 'porcentaje' ? $discount->valor.'%' : number_format($discount->valor, 2) }}
                            </span>
                            @endif
                            <button onclick="openDiscountModal({{ $inscription->id }}, {{ $program->id }}, {{ json_encode($discount) }})"
                                class="text-xs px-3 py-1.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                {{ $discount ? 'Editar Descuento' : 'Agregar Descuento' }}
                            </button>
                            <button onclick="openGenerateModal({{ $inscription->id }}, {{ $program->id }}, '{{ addslashes($inscription->getFullName()) }}')"
                                class="text-xs px-3 py-1.5 bg-gray-800 text-white rounded-lg hover:bg-gray-700 transition">
                                Generar desde Plan
                            </button>
                        </div>
                    </div>

                    <!-- Tabla de cuotas -->
                    <div class="participant-body-{{ $inscription->id }} hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="bg-gray-800 text-white text-xs">
                                        <th class="px-4 py-2 text-center text-xs font-semibold text-white">N° Cuota</th>
                                        <th class="px-4 py-2 text-right text-xs font-semibold text-white">Importe Base</th>
                                        <th class="px-4 py-2 text-right text-xs font-semibold text-white">Descuento</th>
                                        <th class="px-4 py-2 text-right text-xs font-semibold text-white">Importe Final</th>
                                        <th class="px-4 py-2 text-center text-xs font-semibold text-white">Fecha Venc.</th>
                                        <th class="px-4 py-2 text-center text-xs font-semibold text-white">Estado</th>
                                        <th class="px-4 py-2"></th>
                                    </tr>
                                </thead>
                                <tbody class="quota-body-{{ $inscription->id }} divide-y divide-gray-100">
                                    @foreach($inscQuotas as $quota)
                                    <tr data-quota-id="{{ $quota->id }}" style="{{ $quota->pagada ? 'background-color:#f0fdf4;' : '' }}">
                                        <td class="px-4 py-2 text-center" style="{{ $quota->pagada ? 'color:#15803d;font-weight:600;' : 'color:#374151;' }}">
                                            {{ $quota->numero_cuota }}
                                        </td>
                                        <td class="px-4 py-2 text-right font-mono" style="{{ $quota->pagada ? 'color:#86efac;text-decoration:line-through;' : 'color:#374151;' }}">
                                            {{ number_format($quota->importe_base, 2) }}
                                        </td>
                                        <td class="px-4 py-2 text-right font-mono" style="color:#d97706;">
                                            {{ number_format($quota->descuento_aplicado, 2) }}
                                        </td>
                                        <td class="px-4 py-2 text-right font-mono font-semibold" style="{{ $quota->pagada ? 'color:#16a34a;text-decoration:line-through;' : 'color:#111827;' }}">
                                            {{ number_format($quota->importe_final, 2) }}
                                        </td>
                                        <td class="px-4 py-2 text-center">
                                            <input type="date"
                                                value="{{ $quota->fecha_vencimiento->format('Y-m-d') }}"
                                                onchange="updateQuotaDate({{ $quota->id }}, this.value, this)"
                                                style="border:1px solid #e5e7eb;border-radius:6px;padding:3px 7px;font-size:0.8rem;color:#374151;background:transparent;cursor:pointer;"
                                                onfocus="this.style.borderColor='#6b7280'" onblur="this.style.borderColor='#e5e7eb'">
                                        </td>
                                        <td class="px-4 py-2 text-center">
                                            <button onclick="togglePagada({{ $quota->id }}, this)"
                                                title="{{ $quota->pagada ? 'Marcar como pendiente' : 'Marcar como pagada' }}"
                                                style="display:inline-flex;align-items:center;gap:4px;padding:2px 10px;border-radius:9999px;font-size:0.7rem;font-weight:600;border:none;cursor:pointer;transition:all 0.15s;{{ $quota->pagada ? 'background-color:#dcfce7;color:#15803d;' : 'background-color:#fef3c7;color:#b45309;' }}">
                                                @if($quota->pagada)
                                                <svg style="width:0.75rem;height:0.75rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                Pagada
                                                @else
                                                <svg style="width:0.75rem;height:0.75rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                Pendiente
                                                @endif
                                            </button>
                                        </td>
                                        <td class="px-4 py-2 text-right">
                                            <button onclick="deleteQuota({{ $quota->id }}, this)"
                                                class="text-red-400 hover:text-red-600">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if($inscQuotas->isEmpty())
                        <p class="px-5 py-4 text-sm text-gray-400 text-center quota-empty-{{ $inscription->id }}">
                            Sin cuotas configuradas. Usa "Generar desde Plan" para crear el calendario automáticamente.
                        </p>
                        @endif

                        <!-- Agregar cuota manual -->
                        <div class="px-5 py-3 border-t border-gray-100 bg-gray-50">
                            <button onclick="toggleAddQuota({{ $inscription->id }}, {{ $program->id }})"
                                class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">
                                + Agregar cuota manualmente
                            </button>
                            <div class="add-quota-form-{{ $inscription->id }} hidden mt-3 grid grid-cols-2 sm:grid-cols-5 gap-2">
                                <input type="number" placeholder="N° Cuota" min="1"
                                    class="border border-gray-300 rounded px-2 py-1.5 text-xs aq-numero-{{ $inscription->id }}">
                                <input type="number" placeholder="Importe Base" min="0" step="0.01"
                                    class="border border-gray-300 rounded px-2 py-1.5 text-xs aq-base-{{ $inscription->id }}"
                                    oninput="calcFinal({{ $inscription->id }})">
                                <input type="number" placeholder="Descuento" min="0" step="0.01" value="0"
                                    class="border border-gray-300 rounded px-2 py-1.5 text-xs aq-descuento-{{ $inscription->id }}"
                                    oninput="calcFinal({{ $inscription->id }})">
                                <input type="number" placeholder="Importe Final" min="0" step="0.01"
                                    class="border border-gray-300 rounded px-2 py-1.5 text-xs aq-final-{{ $inscription->id }}">
                                <input type="date"
                                    class="border border-gray-300 rounded px-2 py-1.5 text-xs aq-fecha-{{ $inscription->id }}">
                                <button onclick="saveManualQuota({{ $inscription->id }}, {{ $program->id }})"
                                    class="col-span-2 sm:col-span-1 px-3 py-1.5 bg-gray-800 text-white text-xs rounded hover:bg-gray-700">
                                    Guardar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    {{-- Datos de participantes para el modal masivo --}}
    <script>
    const allParticipants = @json($participantsJson);
    const programId = {{ $program->id }};
    </script>

    <!-- ══════════════════════════════════════════════════════════ -->
    <!-- Modal Generación Masiva — 3 pasos                         -->
    <!-- ══════════════════════════════════════════════════════════ -->
    <div id="bulkModal" class="hidden fixed inset-0 z-50 flex items-start justify-center bg-black/40" style="padding-top:3rem;">
        <div class="bg-white rounded-xl shadow-xl w-full mx-4" style="max-width:64rem;max-height:calc(100vh - 6rem);display:flex;flex-direction:column;">

            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center flex-shrink-0">
                <div>
                    <h3 class="text-base font-semibold text-gray-900">Generación Masiva de Cuotas</h3>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Paso <span id="bulkStepLabel">1</span> de 3
                    </p>
                </div>
                <button onclick="closeBulkModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- ── Paso 1: Plan + fecha ── -->
            <div id="bulkStep1" class="px-6 py-5 space-y-4 flex-shrink-0">
                <p class="text-xs text-amber-700 bg-amber-50 rounded-lg px-3 py-2">
                    Las cuotas existentes de los participantes seleccionados serán reemplazadas.
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Plan de Pago <span class="text-red-500">*</span></label>
                        <select id="bulkPlanId" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                            <option value="">Selecciona un plan...</option>
                            @foreach($paymentPlans as $plan)
                            <option value="{{ $plan->id }}">{{ $plan->name }} — {{ $plan->numero_cuotas }} cuota(s) × {{ number_format($plan->importe_base_cuota, 2) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de inicio (1ª cuota) <span class="text-red-500">*</span></label>
                        <input type="date" id="bulkFechaInicio" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
                <div id="bulkError1" class="hidden text-sm text-red-600 bg-red-50 rounded-lg px-3 py-2"></div>
            </div>

            <!-- ── Paso 2: Previsualizar cuotas ── -->
            <div id="bulkStep2" class="hidden flex-1 overflow-hidden flex flex-col">
                <div class="px-6 py-3 border-t border-gray-100 bg-gray-50 flex-shrink-0">
                    <p class="text-xs text-gray-600">Ajusta fechas y marca las cuotas ya pagadas. Estos ajustes se aplicarán a <strong>todos</strong> los participantes seleccionados.</p>
                </div>
                <div class="overflow-y-auto flex-1">
                    <table class="w-full text-sm">
                        <thead class="sticky top-0">
                            <tr style="background-color:#1f2937;">
                                <th class="px-3 py-2 text-center text-xs font-semibold text-white">N°</th>
                                <th class="px-3 py-2 text-right text-xs font-semibold text-white">Importe Base</th>
                                <th class="px-3 py-2 text-right text-xs font-semibold text-white">Descuento*</th>
                                <th class="px-3 py-2 text-right text-xs font-semibold text-white">Importe Final*</th>
                                <th class="px-3 py-2 text-center text-xs font-semibold text-white">Fecha Vencimiento</th>
                                <th class="px-3 py-2 text-center text-xs font-semibold text-white">Ya Pagada</th>
                            </tr>
                        </thead>
                        <tbody id="bulkPreviewBody" class="divide-y divide-gray-100"></tbody>
                    </table>
                </div>
                <div class="px-6 py-2 border-t border-gray-100 bg-gray-50 flex-shrink-0">
                    <p class="text-xs text-gray-400">* El descuento e importe final se recalculará por participante según su descuento individual configurado.</p>
                </div>
            </div>

            <!-- ── Paso 3: Selección de participantes ── -->
            <div id="bulkStep3" class="hidden flex-1 overflow-hidden flex flex-col">
                <div class="px-6 py-3 border-t border-gray-100 bg-gray-50 flex-shrink-0 flex items-center justify-between gap-3">
                    <p class="text-xs text-gray-600">Selecciona los participantes a los que aplicar el plan. Los sin cuotas están marcados por defecto.</p>
                    <div style="display:flex;gap:8px;flex-shrink:0;">
                        <button onclick="bulkSelectAll(true)" style="font-size:0.7rem;padding:4px 10px;border:1px solid #d1d5db;border-radius:6px;background:#fff;cursor:pointer;color:#374151;">Todos</button>
                        <button onclick="bulkSelectAll(false)" style="font-size:0.7rem;padding:4px 10px;border:1px solid #d1d5db;border-radius:6px;background:#fff;cursor:pointer;color:#374151;">Ninguno</button>
                        <button onclick="bulkSelectNone()" style="font-size:0.7rem;padding:4px 10px;border:1px solid #4f46e5;border-radius:6px;background:#eef2ff;cursor:pointer;color:#4338ca;">Sin cuotas</button>
                    </div>
                </div>
                <div class="px-4 py-2 border-b border-gray-100 flex-shrink-0" style="position:relative;">
                    <svg style="position:absolute;left:22px;top:50%;transform:translateY(-50%);width:0.875rem;height:0.875rem;color:#9ca3af;pointer-events:none;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                    </svg>
                    <input type="text" id="bulkSearch" placeholder="Buscar participante..." oninput="filterBulkList(this.value)"
                        style="width:100%;padding:6px 10px 6px 30px;border:1px solid #d1d5db;border-radius:6px;font-size:0.8rem;color:#374151;outline:none;">
                </div>
                <div class="overflow-y-auto flex-1" id="bulkParticipantList" style="max-height:320px;">
                </div>
                <div class="px-6 py-2 border-t border-gray-100 bg-gray-50 flex-shrink-0">
                    <p class="text-xs text-gray-500"><span id="bulkSelectedCount">0</span> participante(s) seleccionado(s)</p>
                </div>
                <div id="bulkError3" class="hidden mx-6 mb-2 text-sm text-red-600 bg-red-50 rounded-lg px-3 py-2"></div>
            </div>

            <!-- Footer -->
            <div class="px-6 py-4 border-t border-gray-200 flex justify-between items-center flex-shrink-0">
                <button id="bulkBackBtn" onclick="bulkBack()" class="hidden text-sm text-gray-600 hover:text-gray-800 flex items-center gap-1">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Volver
                </button>
                <div class="flex gap-3 ml-auto">
                    <button onclick="closeBulkModal()" class="px-4 py-2 text-sm text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">Cancelar</button>
                    <button onclick="bulkNext()" id="bulkNextBtn"
                        style="padding:8px 20px;background-color:#4f46e5;color:#fff;border:none;border-radius:8px;font-size:0.875rem;font-weight:500;cursor:pointer;">
                        Siguiente
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════ -->
    <!-- Modal Generar desde Plan — 2 pasos                        -->
    <!-- ══════════════════════════════════════════════════════════ -->
    <div id="generateModal" class="hidden fixed inset-0 z-50 flex items-start justify-center bg-black/40" style="padding-top:4rem;">
        <div class="bg-white rounded-xl shadow-xl w-full mx-4" style="max-width:56rem;max-height:calc(100vh - 8rem);display:flex;flex-direction:column;">

            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center flex-shrink-0">
                <div>
                    <h3 class="text-base font-semibold text-gray-900">Generar Cuotas desde Plan</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Participante: <span id="genParticipantName" class="font-medium text-gray-700"></span></p>
                </div>
                <button onclick="closeGenerateModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Paso 1 -->
            <div id="genStep1" class="px-6 py-5 space-y-4 flex-shrink-0">
                <p class="text-xs text-amber-700 bg-amber-50 rounded-lg px-3 py-2">Esto eliminará las cuotas existentes de este participante y las regenerará.</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Plan de Pago <span class="text-red-500">*</span></label>
                        <select id="genPlanId" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-gray-800">
                            <option value="">Selecciona un plan...</option>
                            @foreach($paymentPlans as $plan)
                            <option value="{{ $plan->id }}">{{ $plan->name }} — {{ $plan->numero_cuotas }} cuota(s) × {{ number_format($plan->importe_base_cuota, 2) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de inicio (1ª cuota) <span class="text-red-500">*</span></label>
                        <input type="date" id="genFechaInicio" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-gray-800">
                    </div>
                </div>
                <div id="genError" class="hidden text-sm text-red-600 bg-red-50 rounded-lg px-3 py-2"></div>
            </div>

            <!-- Paso 2: tabla preview (oculta hasta previsualizar) -->
            <div id="genStep2" class="hidden flex-1 overflow-hidden flex flex-col">
                <div class="px-6 py-3 border-t border-gray-100 bg-gray-50 flex-shrink-0">
                    <p class="text-xs text-gray-600">Revisa y ajusta las fechas. Marca <strong>Ya pagada</strong> en las cuotas que ya fueron cobradas — no entrarán en la asignación nominal.</p>
                </div>
                <div class="overflow-y-auto flex-1">
                    <table class="w-full text-sm">
                        <thead class="sticky top-0">
                            <tr style="background-color:#1f2937;">
                                <th class="px-3 py-2 text-center text-xs font-semibold text-white">N°</th>
                                <th class="px-3 py-2 text-right text-xs font-semibold text-white">Importe Base</th>
                                <th class="px-3 py-2 text-right text-xs font-semibold text-white">Descuento</th>
                                <th class="px-3 py-2 text-right text-xs font-semibold text-white">Importe Final</th>
                                <th class="px-3 py-2 text-center text-xs font-semibold text-white">Fecha Vencimiento</th>
                                <th class="px-3 py-2 text-center text-xs font-semibold text-white">Ya Pagada</th>
                            </tr>
                        </thead>
                        <tbody id="genPreviewBody" class="divide-y divide-gray-100">
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Footer -->
            <div class="px-6 py-4 border-t border-gray-200 flex justify-between items-center flex-shrink-0">
                <div id="genStep2BackArea" class="hidden">
                    <button onclick="backToStep1()" class="text-sm text-gray-600 hover:text-gray-800 flex items-center gap-1">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        Volver
                    </button>
                </div>
                <div class="flex gap-3 ml-auto">
                    <button onclick="closeGenerateModal()" class="px-4 py-2 text-sm text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">Cancelar</button>
                    <button onclick="handleGenerateBtn()" id="genBtn"
                        class="px-4 py-2 text-sm bg-gray-800 text-white rounded-lg hover:bg-gray-700">Previsualizar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Descuento -->
    <div id="discountModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-xs mx-4">
            <div class="px-4 py-3 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-sm font-semibold text-gray-900">Descuento</h3>
                <button onclick="document.getElementById('discountModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="px-4 py-3 space-y-3">
                <input type="hidden" id="discInscriptionId">
                <input type="hidden" id="discProgramId">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tipo</label>
                        <select id="discTipo" class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-sm">
                            <option value="monto_fijo">Monto Fijo</option>
                            <option value="porcentaje">Porcentaje (%)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Valor</label>
                        <input type="number" id="discValor" min="0" step="0.01"
                            class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-sm" placeholder="0.00">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Descripción <span class="text-gray-400">(opcional)</span></label>
                    <input type="text" id="discDesc" class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-sm" placeholder="Ej: Beca parcial">
                </div>
            </div>
            <div class="px-4 py-3 border-t border-gray-200 flex justify-end gap-2">
                <button onclick="document.getElementById('discountModal').classList.add('hidden')"
                    class="px-3 py-1.5 text-xs text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">Cancelar</button>
                <button onclick="saveDiscount()"
                    class="px-3 py-1.5 text-xs bg-blue-600 text-white rounded-lg hover:bg-blue-700">Guardar</button>
            </div>
        </div>
    </div>

    <script>
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    function filterParticipants(q) {
        const term  = q.toLowerCase().trim();
        const cards = document.querySelectorAll('.participant-card');
        let visible = 0;
        cards.forEach(card => {
            const match = !term || card.dataset.name.includes(term);
            card.style.display = match ? '' : 'none';
            if (match) visible++;
        });
        const countEl = document.getElementById('participantCount');
        if (countEl) countEl.textContent = term ? `${visible} resultado(s) de ${cards.length}` : '';
    }
    let genInscriptionId = null, genProgramId = null;
    let genStep = 1; // 1 = configurar, 2 = preview

    // ── Participante toggle ──────────────────────────────────────
    function toggleParticipant(id) {
        const body    = document.querySelector(`.participant-body-${id}`);
        const chevron = document.querySelector(`.chevron-${id}`);
        body.classList.toggle('hidden');
        chevron.style.transform = body.classList.contains('hidden') ? '' : 'rotate(180deg)';
    }

    function toggleAddQuota(inscId) {
        document.querySelector(`.add-quota-form-${inscId}`).classList.toggle('hidden');
    }

    function calcFinal(inscId) {
        const base = parseFloat(document.querySelector(`.aq-base-${inscId}`).value) || 0;
        const desc = parseFloat(document.querySelector(`.aq-descuento-${inscId}`).value) || 0;
        document.querySelector(`.aq-final-${inscId}`).value = Math.max(0, base - desc).toFixed(2);
    }

    // ── Cuota manual ─────────────────────────────────────────────
    async function saveManualQuota(inscId, progId) {
        const body = {
            inscription_id:     inscId,
            program_id:         progId,
            numero_cuota:       parseInt(document.querySelector(`.aq-numero-${inscId}`).value),
            importe_base:       parseFloat(document.querySelector(`.aq-base-${inscId}`).value),
            descuento_aplicado: parseFloat(document.querySelector(`.aq-descuento-${inscId}`).value) || 0,
            importe_final:      parseFloat(document.querySelector(`.aq-final-${inscId}`).value),
            fecha_vencimiento:  document.querySelector(`.aq-fecha-${inscId}`).value,
        };
        const res  = await fetch('/accounting/participant-quotas', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: JSON.stringify(body),
        });
        const data = await res.json();
        if (data.success) location.reload();
        else alert(data.message || 'Error al guardar');
    }

    // ── Eliminar cuota ───────────────────────────────────────────
    async function deleteQuota(id, btn) {
        if (!confirm('¿Eliminar esta cuota?')) return;
        const res  = await fetch(`/accounting/participant-quotas/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        });
        const data = await res.json();
        if (data.success) btn.closest('tr').remove();
    }

    // ── Editar fecha de cuota existente ──────────────────────────
    async function updateQuotaDate(id, value, input) {
        if (!value) return;
        input.style.borderColor = '#6b7280';
        const res  = await fetch(`/accounting/participant-quotas/${id}`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: JSON.stringify({ fecha_vencimiento: value }),
        });
        const data = await res.json();
        if (data.success) {
            input.style.borderColor = '#16a34a';
            setTimeout(() => input.style.borderColor = '#e5e7eb', 1200);
        } else {
            input.style.borderColor = '#dc2626';
            alert(data.message || 'Error al guardar');
        }
    }

    // ── Toggle pagada en tabla existente ─────────────────────────
    async function togglePagada(id, btn) {
        btn.disabled = true;
        const res  = await fetch(`/accounting/participant-quotas/${id}/toggle-pagada`, {
            method: 'PATCH',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        });
        const data = await res.json();
        if (data.success) {
            // Reload para reflejar el cambio de estilo en la fila completa
            location.reload();
        } else {
            btn.disabled = false;
            alert('Error al actualizar');
        }
    }

    // ── Modal Generar desde Plan ─────────────────────────────────
    function openGenerateModal(inscId, progId, name) {
        genInscriptionId = inscId;
        genProgramId     = progId;
        genStep          = 1;
        document.getElementById('genParticipantName').textContent = name;
        document.getElementById('genPlanId').value      = '';
        document.getElementById('genFechaInicio').value = '';
        document.getElementById('genError').classList.add('hidden');
        document.getElementById('genStep1').classList.remove('hidden');
        document.getElementById('genStep2').classList.add('hidden');
        document.getElementById('genStep2BackArea').classList.add('hidden');
        document.getElementById('genBtn').textContent = 'Previsualizar';
        document.getElementById('generateModal').classList.remove('hidden');
    }

    function closeGenerateModal() {
        document.getElementById('generateModal').classList.add('hidden');
    }

    function backToStep1() {
        genStep = 1;
        document.getElementById('genStep1').classList.remove('hidden');
        document.getElementById('genStep2').classList.add('hidden');
        document.getElementById('genStep2BackArea').classList.add('hidden');
        document.getElementById('genBtn').textContent = 'Previsualizar';
        document.getElementById('genError').classList.add('hidden');
    }

    async function handleGenerateBtn() {
        if (genStep === 1) {
            await previewQuotas();
        } else {
            await saveGeneratedQuotas();
        }
    }

    async function previewQuotas() {
        const btn   = document.getElementById('genBtn');
        const errEl = document.getElementById('genError');
        const planId     = document.getElementById('genPlanId').value;
        const fechaInicio = document.getElementById('genFechaInicio').value;

        if (!planId || !fechaInicio) {
            errEl.textContent = 'Selecciona un plan y una fecha de inicio.';
            errEl.classList.remove('hidden');
            return;
        }

        btn.disabled    = true;
        btn.textContent = 'Cargando...';
        errEl.classList.add('hidden');

        const res = await fetch('/accounting/participant-quotas/preview-from-plan', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: JSON.stringify({
                inscription_id:  genInscriptionId,
                program_id:      genProgramId,
                payment_plan_id: planId,
                fecha_inicio:    fechaInicio,
            }),
        });
        const data = await res.json();

        btn.disabled = false;

        if (!data.success) {
            errEl.textContent = data.message || Object.values(data.errors || {}).flat().join(', ') || 'Error';
            errEl.classList.remove('hidden');
            btn.textContent = 'Previsualizar';
            return;
        }

        // Renderizar tabla de preview
        const tbody = document.getElementById('genPreviewBody');
        tbody.innerHTML = '';
        data.quotas.forEach((q, idx) => {
            const row = document.createElement('tr');
            row.style.backgroundColor = '#fff';
            row.dataset.idx = idx;
            row.innerHTML = `
                <td style="padding:8px 12px;text-align:center;color:#374151;font-weight:600;">${q.numero_cuota}</td>
                <td style="padding:8px 12px;text-align:right;font-family:monospace;color:#374151;">${q.importe_base.toFixed(2)}</td>
                <td style="padding:8px 12px;text-align:right;font-family:monospace;color:#d97706;">${q.descuento_aplicado.toFixed(2)}</td>
                <td style="padding:8px 12px;text-align:right;font-family:monospace;font-weight:600;color:#111827;">${q.importe_final.toFixed(2)}</td>
                <td style="padding:8px 12px;text-align:center;">
                    <input type="date" value="${q.fecha_vencimiento}"
                        style="border:1px solid #d1d5db;border-radius:6px;padding:4px 8px;font-size:0.8rem;color:#374151;width:140px;"
                        onchange="updatePreviewRow(${idx}, 'fecha', this.value)">
                </td>
                <td style="padding:8px 12px;text-align:center;">
                    <label style="display:inline-flex;align-items:center;gap:6px;cursor:pointer;">
                        <input type="checkbox" onchange="updatePreviewRow(${idx}, 'pagada', this.checked)"
                            style="width:1rem;height:1rem;accent-color:#16a34a;cursor:pointer;">
                        <span style="font-size:0.75rem;color:#4b5563;">Ya pagada</span>
                    </label>
                </td>
            `;
            tbody.appendChild(row);
        });

        // Almacenar los datos en memoria para enviar al guardar
        window._genPreviewData = data.quotas;

        genStep = 2;
        document.getElementById('genStep1').classList.add('hidden');
        document.getElementById('genStep2').classList.remove('hidden');
        document.getElementById('genStep2BackArea').classList.remove('hidden');
        btn.textContent = 'Guardar cuotas';
    }

    function updatePreviewRow(idx, field, value) {
        if (field === 'fecha') {
            window._genPreviewData[idx].fecha_vencimiento = value;
            // Si está marcada como pagada, aplicar fondo verde
        } else if (field === 'pagada') {
            window._genPreviewData[idx].pagada = value;
            const row = document.querySelector(`#genPreviewBody tr[data-idx="${idx}"]`);
            if (row) row.style.backgroundColor = value ? '#f0fdf4' : '#fff';
        }
    }

    async function saveGeneratedQuotas() {
        const btn = document.getElementById('genBtn');
        btn.disabled    = true;
        btn.textContent = 'Guardando...';

        const res = await fetch('/accounting/participant-quotas/generate-from-plan', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: JSON.stringify({
                inscription_id:  genInscriptionId,
                program_id:      genProgramId,
                payment_plan_id: document.getElementById('genPlanId').value,
                quotas:          window._genPreviewData,
            }),
        });
        const data = await res.json();

        if (data.success) {
            closeGenerateModal();
            location.reload();
        } else {
            const errEl = document.getElementById('genError');
            errEl.textContent = data.message || Object.values(data.errors || {}).flat().join(', ') || 'Error al guardar';
            errEl.classList.remove('hidden');
            btn.disabled    = false;
            btn.textContent = 'Guardar cuotas';
            // Volver al paso 1 para ver el error
            backToStep1();
        }
    }

    // ── Modal Descuento ──────────────────────────────────────────
    function openDiscountModal(inscId, progId, discount) {
        document.getElementById('discInscriptionId').value = inscId;
        document.getElementById('discProgramId').value     = progId;
        document.getElementById('discTipo').value          = discount?.tipo || 'monto_fijo';
        document.getElementById('discValor').value         = discount?.valor || '';
        document.getElementById('discDesc').value          = discount?.descripcion || '';
        document.getElementById('discountModal').classList.remove('hidden');
    }

    async function saveDiscount() {
        const res  = await fetch('/accounting/participant-quotas/discount', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: JSON.stringify({
                inscription_id: document.getElementById('discInscriptionId').value,
                program_id:     document.getElementById('discProgramId').value,
                tipo:           document.getElementById('discTipo').value,
                valor:          parseFloat(document.getElementById('discValor').value),
                descripcion:    document.getElementById('discDesc').value || null,
            }),
        });
        const data = await res.json();
        if (data.success) { document.getElementById('discountModal').classList.add('hidden'); location.reload(); }
        else alert(data.message || 'Error al guardar descuento');
    }

    // ── Modal Generación Masiva ───────────────────────────────────
    let bulkStep = 1;
    let bulkPreviewData = [];

    function openBulkModal() {
        bulkStep = 1;
        document.getElementById('bulkPlanId').value      = '';
        document.getElementById('bulkFechaInicio').value = '';
        document.getElementById('bulkError1').classList.add('hidden');
        document.getElementById('bulkStep1').classList.remove('hidden');
        document.getElementById('bulkStep2').classList.add('hidden');
        document.getElementById('bulkStep3').classList.add('hidden');
        document.getElementById('bulkBackBtn').classList.add('hidden');
        document.getElementById('bulkNextBtn').textContent = 'Siguiente';
        document.getElementById('bulkStepLabel').textContent = '1';
        document.getElementById('bulkModal').classList.remove('hidden');
    }

    function closeBulkModal() {
        document.getElementById('bulkModal').classList.add('hidden');
    }

    function bulkBack() {
        if (bulkStep === 2) {
            bulkStep = 1;
            document.getElementById('bulkStep1').classList.remove('hidden');
            document.getElementById('bulkStep2').classList.add('hidden');
            document.getElementById('bulkBackBtn').classList.add('hidden');
            document.getElementById('bulkNextBtn').textContent = 'Siguiente';
            document.getElementById('bulkStepLabel').textContent = '1';
        } else if (bulkStep === 3) {
            bulkStep = 2;
            document.getElementById('bulkStep2').classList.remove('hidden');
            document.getElementById('bulkStep3').classList.add('hidden');
            document.getElementById('bulkNextBtn').textContent = 'Siguiente';
            document.getElementById('bulkStepLabel').textContent = '2';
        }
    }

    async function bulkNext() {
        if (bulkStep === 1) {
            await bulkGoToStep2();
        } else if (bulkStep === 2) {
            bulkGoToStep3();
        } else if (bulkStep === 3) {
            await bulkSave();
        }
    }

    async function bulkGoToStep2() {
        const errEl = document.getElementById('bulkError1');
        const planId = document.getElementById('bulkPlanId').value;
        const fecha  = document.getElementById('bulkFechaInicio').value;

        if (!planId || !fecha) {
            errEl.textContent = 'Selecciona un plan y una fecha de inicio.';
            errEl.classList.remove('hidden');
            return;
        }
        errEl.classList.add('hidden');

        const btn = document.getElementById('bulkNextBtn');
        btn.disabled    = true;
        btn.textContent = 'Cargando...';

        // Usamos el preview individual con inscription_id ficticio (solo necesitamos las cuotas base)
        const firstParticipant = allParticipants[0];
        const res = await fetch('/accounting/participant-quotas/preview-from-plan', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: JSON.stringify({
                inscription_id:  firstParticipant.id,
                program_id:      programId,
                payment_plan_id: planId,
                fecha_inicio:    fecha,
            }),
        });
        const data = await res.json();

        btn.disabled = false;

        if (!data.success) {
            errEl.textContent = data.message || 'Error al previsualizar';
            errEl.classList.remove('hidden');
            btn.textContent = 'Siguiente';
            return;
        }

        bulkPreviewData = data.quotas;

        const tbody = document.getElementById('bulkPreviewBody');
        tbody.innerHTML = '';
        bulkPreviewData.forEach((q, idx) => {
            const row = document.createElement('tr');
            row.dataset.idx = idx;
            row.style.backgroundColor = '#fff';
            row.innerHTML = `
                <td style="padding:8px 12px;text-align:center;color:#374151;font-weight:600;">${q.numero_cuota}</td>
                <td style="padding:8px 12px;text-align:right;font-family:monospace;color:#374151;">${q.importe_base.toFixed(2)}</td>
                <td style="padding:8px 12px;text-align:right;font-family:monospace;color:#d97706;">—</td>
                <td style="padding:8px 12px;text-align:right;font-family:monospace;font-weight:600;color:#111827;">${q.importe_final.toFixed(2)}</td>
                <td style="padding:8px 12px;text-align:center;">
                    <input type="date" value="${q.fecha_vencimiento}"
                        style="border:1px solid #d1d5db;border-radius:6px;padding:4px 8px;font-size:0.8rem;color:#374151;width:140px;"
                        onchange="updateBulkRow(${idx}, 'fecha', this.value)">
                </td>
                <td style="padding:8px 12px;text-align:center;">
                    <label style="display:inline-flex;align-items:center;gap:6px;cursor:pointer;">
                        <input type="checkbox" onchange="updateBulkRow(${idx}, 'pagada', this.checked)"
                            style="width:1rem;height:1rem;accent-color:#16a34a;cursor:pointer;">
                        <span style="font-size:0.75rem;color:#4b5563;">Ya pagada</span>
                    </label>
                </td>
            `;
            tbody.appendChild(row);
        });

        bulkStep = 2;
        document.getElementById('bulkStep1').classList.add('hidden');
        document.getElementById('bulkStep2').classList.remove('hidden');
        document.getElementById('bulkBackBtn').classList.remove('hidden');
        btn.textContent = 'Siguiente';
        document.getElementById('bulkStepLabel').textContent = '2';
    }

    function updateBulkRow(idx, field, value) {
        if (field === 'fecha') {
            bulkPreviewData[idx].fecha_vencimiento = value;
        } else {
            bulkPreviewData[idx].pagada = value;
            const row = document.querySelector(`#bulkPreviewBody tr[data-idx="${idx}"]`);
            if (row) row.style.backgroundColor = value ? '#f0fdf4' : '#fff';
        }
    }

    function bulkGoToStep3() {
        // Renderizar lista de participantes
        renderBulkParticipantList(allParticipants);
        bulkStep = 3;
        document.getElementById('bulkStep2').classList.add('hidden');
        document.getElementById('bulkStep3').classList.remove('hidden');
        document.getElementById('bulkNextBtn').textContent = 'Generar cuotas';
        document.getElementById('bulkStepLabel').textContent = '3';
        updateBulkSelectedCount();
    }

    function renderBulkParticipantList(participants) {
        const container = document.getElementById('bulkParticipantList');
        container.innerHTML = '';
        participants.forEach(p => {
            const checked  = !p.has_quotas; // por defecto: solo sin cuotas
            const item = document.createElement('label');
            item.style.cssText = 'display:flex;align-items:center;gap:10px;padding:10px 24px;cursor:pointer;border-bottom:1px solid #f3f4f6;';
            item.dataset.name = p.name.toLowerCase();
            item.innerHTML = `
                <input type="checkbox" value="${p.id}" ${checked ? 'checked' : ''}
                    onchange="updateBulkSelectedCount()"
                    style="width:1rem;height:1rem;accent-color:#4f46e5;cursor:pointer;flex-shrink:0;">
                <span style="flex:1;font-size:0.875rem;color:#111827;">${p.name}</span>
                ${p.has_quotas
                    ? '<span style="font-size:0.7rem;padding:2px 8px;background:#fef3c7;color:#b45309;border-radius:9999px;">Tiene cuotas</span>'
                    : '<span style="font-size:0.7rem;padding:2px 8px;background:#f3f4f6;color:#6b7280;border-radius:9999px;">Sin cuotas</span>'
                }
            `;
            container.appendChild(item);
        });
        updateBulkSelectedCount();
    }

    function filterBulkList(q) {
        const term = q.toLowerCase().trim();
        document.querySelectorAll('#bulkParticipantList label').forEach(label => {
            label.style.display = (!term || label.dataset.name.includes(term)) ? '' : 'none';
        });
    }

    function bulkSelectAll(checked) {
        document.querySelectorAll('#bulkParticipantList input[type="checkbox"]').forEach(cb => cb.checked = checked);
        updateBulkSelectedCount();
    }

    function bulkSelectNone() {
        // Seleccionar solo los sin cuotas
        document.querySelectorAll('#bulkParticipantList input[type="checkbox"]').forEach(cb => {
            const p = allParticipants.find(p => p.id == cb.value);
            cb.checked = p ? !p.has_quotas : false;
        });
        updateBulkSelectedCount();
    }

    function updateBulkSelectedCount() {
        const count = document.querySelectorAll('#bulkParticipantList input[type="checkbox"]:checked').length;
        const el = document.getElementById('bulkSelectedCount');
        if (el) el.textContent = count;
    }

    async function bulkSave() {
        const errEl = document.getElementById('bulkError3');
        errEl.classList.add('hidden');

        const selectedIds = Array.from(
            document.querySelectorAll('#bulkParticipantList input[type="checkbox"]:checked')
        ).map(cb => parseInt(cb.value));

        if (selectedIds.length === 0) {
            errEl.textContent = 'Selecciona al menos un participante.';
            errEl.classList.remove('hidden');
            return;
        }

        const btn = document.getElementById('bulkNextBtn');
        btn.disabled    = true;
        btn.textContent = 'Generando...';

        const res = await fetch('/accounting/participant-quotas/generate-bulk', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: JSON.stringify({
                program_id:      programId,
                payment_plan_id: document.getElementById('bulkPlanId').value,
                inscription_ids: selectedIds,
                quotas:          bulkPreviewData,
            }),
        });
        const data = await res.json();

        btn.disabled = false;

        if (data.success) {
            closeBulkModal();
            location.reload();
        } else {
            errEl.textContent = data.message || Object.values(data.errors || {}).flat().join(', ') || 'Error al generar';
            errEl.classList.remove('hidden');
            btn.textContent = 'Generar cuotas';
        }
    }
    </script>
</x-app-layout>
