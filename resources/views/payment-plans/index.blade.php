<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">Planes de Pago</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">

            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Planes de Pago</h1>
                    <p class="mt-1 text-sm text-gray-500">Configura los planes de pago base para los programas</p>
                </div>
                <button onclick="openCreateModal()"
                    class="inline-flex items-center px-4 py-2 bg-gray-800 text-white text-sm font-semibold rounded-lg hover:bg-gray-700 transition">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nuevo Plan
                </button>
            </div>

            <div class="bg-white shadow-sm rounded-xl overflow-hidden">
                <div class="overflow-x-auto">
                <table class="w-full text-sm" style="min-width: 900px;">
                    <thead>
                        <tr class="bg-gray-800 text-white text-xs">
                            <th class="px-4 py-3 text-left font-semibold text-white">Nombre</th>
                            <th class="px-4 py-3 text-left font-semibold text-white">Tipo</th>
                            <th class="px-4 py-3 text-right font-semibold text-white">N° Cuotas</th>
                            <th class="px-4 py-3 text-right font-semibold text-white">Importe / Cuota</th>
                            <th class="px-4 py-3 text-right font-semibold text-white">Matrícula</th>
                            <th class="px-4 py-3 text-right font-semibold text-white">Certificación</th>
                            <th class="px-4 py-3 text-left font-semibold text-white">Descripción</th>
                            <th class="px-4 py-3 text-center font-semibold text-white">Estado</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody id="plansTable" class="divide-y divide-gray-100">
                        @forelse($plans as $plan)
                        <tr data-id="{{ $plan->id }}">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $plan->name }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $plan->tipo === 'contado' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ $plan->tipo_label }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right text-gray-700">{{ $plan->numero_cuotas }}</td>
                            <td class="px-4 py-3 text-right font-mono text-gray-700">{{ number_format($plan->importe_base_cuota, 2) }}</td>
                            <td class="px-4 py-3 text-right font-mono {{ $plan->matricula > 0 ? 'text-indigo-700 font-semibold' : 'text-gray-400' }}">
                                {{ $plan->matricula > 0 ? number_format($plan->matricula, 2) : '—' }}
                            </td>
                            <td class="px-4 py-3 text-right font-mono {{ $plan->certificacion > 0 ? 'text-emerald-700 font-semibold' : 'text-gray-400' }}">
                                {{ $plan->certificacion > 0 ? number_format($plan->certificacion, 2) : '—' }}
                            </td>
                            <td class="px-4 py-3 text-gray-500">{{ $plan->descripcion ?? '—' }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $plan->activo ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-500' }}">
                                    {{ $plan->activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button onclick="openEditModal({{ $plan->id }}, {{ json_encode($plan) }})"
                                    class="text-gray-400 hover:text-gray-700 mr-2" title="Editar">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 112.828 2.828L11.828 15.828a2 2 0 01-1.414.586H9v-1.414A2 2 0 019.586 13z"/>
                                    </svg>
                                </button>
                                <button onclick="deletePlan({{ $plan->id }}, '{{ addslashes($plan->name) }}')"
                                    class="text-red-400 hover:text-red-600" title="Eliminar">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="px-4 py-10 text-center text-gray-400">
                                No hay planes de pago configurados. Crea el primero.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div id="planModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center sticky top-0 bg-white z-10">
                <h3 id="modalTitle" class="text-lg font-semibold text-gray-900">Nuevo Plan de Pago</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form id="planForm" class="px-6 py-4 space-y-4">
                <input type="hidden" id="planId">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre <span class="text-red-500">*</span></label>
                    <input type="text" id="planName" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-gray-800 focus:border-gray-800" placeholder="Ej: Plan a crédito 10 meses">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipo <span class="text-red-500">*</span></label>
                        <select id="planTipo" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-gray-800 focus:border-gray-800">
                            <option value="credito">Plan a Crédito</option>
                            <option value="contado">Plan Contado</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">N° Cuotas <span class="text-red-500">*</span></label>
                        <input type="number" id="planCuotas" min="1" max="120" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-gray-800 focus:border-gray-800" value="1">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Importe Base por Cuota <span class="text-red-500">*</span></label>
                    <input type="number" id="planImporte" min="0" step="0.01" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-gray-800 focus:border-gray-800" placeholder="0.00">
                </div>

                <!-- Matrícula y Certificación -->
                <div class="bg-gray-50 rounded-lg p-4 space-y-3">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Cargos adicionales</p>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Matrícula</label>
                            <input type="number" id="planMatricula" min="0" step="0.01" value="0"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="0.00">
                            <p class="text-xs text-gray-400 mt-1">Cargo único al inicio</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Certificación</label>
                            <input type="number" id="planCertificacion" min="0" step="0.01" value="0"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" placeholder="0.00">
                            <p class="text-xs text-gray-400 mt-1">Cargo único al finalizar</p>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                    <input type="text" id="planDesc" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-gray-800 focus:border-gray-800" placeholder="Opcional">
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="planActivo" checked class="rounded border-gray-300 text-gray-800">
                    <label for="planActivo" class="text-sm text-gray-700">Activo</label>
                </div>
                <div id="planError" class="hidden text-sm text-red-600 bg-red-50 rounded-lg px-3 py-2"></div>
            </form>
            <div class="px-6 py-4 border-t border-gray-200 flex justify-end gap-3 sticky bottom-0 bg-white">
                <button onclick="closeModal()" class="px-4 py-2 text-sm text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">Cancelar</button>
                <button onclick="savePlan()" id="saveBtn" class="px-4 py-2 text-sm bg-gray-800 text-white rounded-lg hover:bg-gray-700">Guardar</button>
            </div>
        </div>
    </div>

    <script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    let editingId = null;

    function openCreateModal() {
        editingId = null;
        document.getElementById('modalTitle').textContent = 'Nuevo Plan de Pago';
        document.getElementById('planId').value = '';
        document.getElementById('planName').value = '';
        document.getElementById('planTipo').value = 'credito';
        document.getElementById('planCuotas').value = 1;
        document.getElementById('planImporte').value = '';
        document.getElementById('planMatricula').value = '0';
        document.getElementById('planCertificacion').value = '0';
        document.getElementById('planDesc').value = '';
        document.getElementById('planActivo').checked = true;
        document.getElementById('planError').classList.add('hidden');
        document.getElementById('planModal').classList.remove('hidden');
    }

    function openEditModal(id, plan) {
        editingId = id;
        document.getElementById('modalTitle').textContent = 'Editar Plan de Pago';
        document.getElementById('planId').value = id;
        document.getElementById('planName').value = plan.name;
        document.getElementById('planTipo').value = plan.tipo;
        document.getElementById('planCuotas').value = plan.numero_cuotas;
        document.getElementById('planImporte').value = plan.importe_base_cuota;
        document.getElementById('planMatricula').value = plan.matricula || '0';
        document.getElementById('planCertificacion').value = plan.certificacion || '0';
        document.getElementById('planDesc').value = plan.descripcion || '';
        document.getElementById('planActivo').checked = plan.activo;
        document.getElementById('planError').classList.add('hidden');
        document.getElementById('planModal').classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('planModal').classList.add('hidden');
    }

    async function savePlan() {
        const btn = document.getElementById('saveBtn');
        const errEl = document.getElementById('planError');
        btn.disabled = true;
        btn.textContent = 'Guardando...';
        errEl.classList.add('hidden');

        const body = {
            name:               document.getElementById('planName').value.trim(),
            tipo:               document.getElementById('planTipo').value,
            numero_cuotas:      parseInt(document.getElementById('planCuotas').value),
            importe_base_cuota: parseFloat(document.getElementById('planImporte').value) || 0,
            matricula:          parseFloat(document.getElementById('planMatricula').value) || 0,
            certificacion:      parseFloat(document.getElementById('planCertificacion').value) || 0,
            descripcion:        document.getElementById('planDesc').value.trim() || null,
            activo:             document.getElementById('planActivo').checked,
        };

        const url    = editingId ? `/accounting/payment-plans/${editingId}` : '/accounting/payment-plans';
        const method = editingId ? 'PUT' : 'POST';

        try {
            const res = await fetch(url, {
                method,
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify(body),
            });
            const data = await res.json();
            if (data.success) {
                closeModal();
                location.reload();
            } else {
                showError(errEl, data.errors || data.message || 'Error al guardar');
            }
        } catch (e) {
            showError(errEl, 'Error de conexión');
        }

        btn.disabled = false;
        btn.textContent = 'Guardar';
    }

    async function deletePlan(id, name) {
        if (!confirm(`¿Eliminar el plan "${name}"?`)) return;
        const res  = await fetch(`/accounting/payment-plans/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        });
        const data = await res.json();
        if (data.success) location.reload();
    }

    function showError(el, msg) {
        el.textContent = typeof msg === 'object' ? Object.values(msg).flat().join(', ') : msg;
        el.classList.remove('hidden');
    }
    </script>
</x-app-layout>
