<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">Cuotas por Participante</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">

            <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
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

            <div class="space-y-4" id="participantsList">
                @foreach($inscriptions as $inscription)
                @php
                    $inscQuotas   = $quotas->get($inscription->id, collect());
                    $discount     = $discounts->get($inscription->id);
                @endphp
                <div class="bg-white shadow-sm rounded-xl overflow-hidden"
                     data-inscription="{{ $inscription->id }}"
                     data-program="{{ $program->id }}">

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
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0" onclick="event.stopPropagation()">
                            @if($discount)
                            <span class="inline-flex items-center px-2 py-1 text-xs rounded-full bg-amber-100 text-amber-800 font-medium">
                                Descuento: {{ $discount->tipo === 'porcentaje' ? $discount->valor.'%' : number_format($discount->valor, 2) }}
                            </span>
                            @endif
                            <button onclick="openDiscountModal({{ $inscription->id }}, {{ $program->id }}, {{ json_encode($discount) }})"
                                class="text-xs px-3 py-1.5 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition">
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
                                        <th class="px-4 py-2"></th>
                                    </tr>
                                </thead>
                                <tbody class="quota-body-{{ $inscription->id }} divide-y divide-gray-100">
                                    @foreach($inscQuotas as $quota)
                                    <tr data-quota-id="{{ $quota->id }}">
                                        <td class="px-4 py-2 text-center text-gray-700">{{ $quota->numero_cuota }}</td>
                                        <td class="px-4 py-2 text-right font-mono text-gray-700">{{ number_format($quota->importe_base, 2) }}</td>
                                        <td class="px-4 py-2 text-right font-mono text-amber-600">{{ number_format($quota->descuento_aplicado, 2) }}</td>
                                        <td class="px-4 py-2 text-right font-mono font-semibold text-gray-900">{{ number_format($quota->importe_final, 2) }}</td>
                                        <td class="px-4 py-2 text-center text-gray-600">{{ $quota->fecha_vencimiento->format('d/m/Y') }}</td>
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

    <!-- Modal Generar desde Plan -->
    <div id="generateModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-base font-semibold text-gray-900">Generar Cuotas desde Plan</h3>
                <button onclick="document.getElementById('generateModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="px-6 py-4 space-y-4">
                <p class="text-sm text-gray-600">Participante: <span id="genParticipantName" class="font-medium text-gray-900"></span></p>
                <p class="text-xs text-amber-700 bg-amber-50 rounded-lg px-3 py-2">Esto eliminará las cuotas existentes de este participante y las regenerará.</p>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Plan de Pago <span class="text-red-500">*</span></label>
                    <select id="genPlanId" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-gray-800">
                        <option value="">Selecciona un plan...</option>
                        @foreach($paymentPlans as $plan)
                        <option value="{{ $plan->id }}" data-cuotas="{{ $plan->numero_cuotas }}" data-importe="{{ $plan->importe_base_cuota }}">
                            {{ $plan->name }} — {{ $plan->numero_cuotas }} cuota(s) × {{ number_format($plan->importe_base_cuota, 2) }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de inicio (1er cuota) <span class="text-red-500">*</span></label>
                    <input type="date" id="genFechaInicio" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-gray-800">
                </div>
                <div id="genError" class="hidden text-sm text-red-600 bg-red-50 rounded-lg px-3 py-2"></div>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 flex justify-end gap-3">
                <button onclick="document.getElementById('generateModal').classList.add('hidden')"
                    class="px-4 py-2 text-sm text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">Cancelar</button>
                <button onclick="generateQuotas()" id="genBtn"
                    class="px-4 py-2 text-sm bg-gray-800 text-white rounded-lg hover:bg-gray-700">Generar</button>
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
                        <select id="discTipo" class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-sm focus:ring-1 focus:ring-amber-500 focus:border-amber-500">
                            <option value="monto_fijo">Monto Fijo</option>
                            <option value="porcentaje">Porcentaje (%)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Valor</label>
                        <input type="number" id="discValor" min="0" step="0.01"
                            class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-sm focus:ring-1 focus:ring-amber-500 focus:border-amber-500" placeholder="0.00">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Descripción <span class="text-gray-400">(opcional)</span></label>
                    <input type="text" id="discDesc" class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-sm focus:ring-1 focus:ring-amber-500 focus:border-amber-500" placeholder="Ej: Beca parcial">
                </div>
            </div>
            <div class="px-4 py-3 border-t border-gray-200 flex justify-end gap-2">
                <button onclick="document.getElementById('discountModal').classList.add('hidden')"
                    class="px-3 py-1.5 text-xs text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">Cancelar</button>
                <button onclick="saveDiscount()"
                    class="px-3 py-1.5 text-xs bg-amber-600 text-white rounded-lg hover:bg-amber-700">Guardar</button>
            </div>
        </div>
    </div>

    <script>
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    let genInscriptionId = null, genProgramId = null;

    function toggleParticipant(id) {
        const body    = document.querySelector(`.participant-body-${id}`);
        const chevron = document.querySelector(`.chevron-${id}`);
        body.classList.toggle('hidden');
        chevron.style.transform = body.classList.contains('hidden') ? '' : 'rotate(180deg)';
    }

    function toggleAddQuota(inscId, progId) {
        document.querySelector(`.add-quota-form-${inscId}`).classList.toggle('hidden');
    }

    function calcFinal(inscId) {
        const base = parseFloat(document.querySelector(`.aq-base-${inscId}`).value) || 0;
        const desc = parseFloat(document.querySelector(`.aq-descuento-${inscId}`).value) || 0;
        document.querySelector(`.aq-final-${inscId}`).value = Math.max(0, base - desc).toFixed(2);
    }

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

    async function deleteQuota(id, btn) {
        if (!confirm('¿Eliminar esta cuota?')) return;
        const res  = await fetch(`/accounting/participant-quotas/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        });
        const data = await res.json();
        if (data.success) btn.closest('tr').remove();
    }

    function openGenerateModal(inscId, progId, name) {
        genInscriptionId = inscId;
        genProgramId     = progId;
        document.getElementById('genParticipantName').textContent = name;
        document.getElementById('genPlanId').value      = '';
        document.getElementById('genFechaInicio').value = '';
        document.getElementById('genError').classList.add('hidden');
        document.getElementById('generateModal').classList.remove('hidden');
    }

    async function generateQuotas() {
        const btn  = document.getElementById('genBtn');
        const errEl= document.getElementById('genError');
        btn.disabled = true;
        btn.textContent = 'Generando...';
        errEl.classList.add('hidden');

        const res = await fetch('/accounting/participant-quotas/generate-from-plan', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: JSON.stringify({
                inscription_id:  genInscriptionId,
                program_id:      genProgramId,
                payment_plan_id: document.getElementById('genPlanId').value,
                fecha_inicio:    document.getElementById('genFechaInicio').value,
            }),
        });
        const data = await res.json();

        if (data.success) {
            document.getElementById('generateModal').classList.add('hidden');
            location.reload();
        } else {
            errEl.textContent = data.message || Object.values(data.errors || {}).flat().join(', ') || 'Error al generar';
            errEl.classList.remove('hidden');
        }

        btn.disabled = false;
        btn.textContent = 'Generar';
    }

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
    </script>
</x-app-layout>
