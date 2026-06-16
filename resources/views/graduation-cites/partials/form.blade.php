@php
    $paymentTypes = [
        'inscripcion' => 'Inscripción',
        'matricula' => 'Matrícula',
        'colegiatura' => 'Colegiatura',
        'certificacion' => 'Certificación',
    ];

    // Cargar participantes con sus montos del pivot (edit) o de old() (re-submit tras validación)
    $oldAmounts = old('participant_amounts', []);

    if (isset($graduationCite) && $graduationCite->participants->isNotEmpty() && !old('participant_ids')) {
        $initialParticipants = $graduationCite->participants->map(function ($p) {
            return [
                'id'        => $p->id,
                'full_name' => $p->pivot->participant_full_name ?: $p->getFullName(),
                'ci'        => $p->ci,
                'program'   => $p->pivot->participant_program ?: ($p->programs->pluck('name')->filter()->implode(', ') ?: 'Sin programa'),
                'amount'    => (float) $p->pivot->amount,
            ];
        })->values();
    } else {
        $initialParticipantIds = old('participant_ids', []);
        $initialParticipants = empty($initialParticipantIds)
            ? collect()
            : \App\Models\Inscription::with('programs:id,name')
                ->whereIn('id', $initialParticipantIds)
                ->get()
                ->map(function ($p) use ($oldAmounts) {
                    return [
                        'id'        => $p->id,
                        'full_name' => $p->getFullName(),
                        'ci'        => $p->ci,
                        'program'   => $p->programs->pluck('name')->filter()->implode(', ') ?: 'Sin programa',
                        'amount'    => (float) ($oldAmounts[$p->id] ?? 0),
                    ];
                })
                ->values();
    }
@endphp

<form method="POST" action="{{ $action }}" x-data="graduationCiteForm({ initialParticipants: @js($initialParticipants), initialAmountPerParticipant: @js((float) old('amount_per_participant', $graduationCite->amount_per_participant ?? 0)) })" class="space-y-8" x-ref="form">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-6">
        <div>
            <x-label for="cite_number" value="CITE" />
            <x-input id="cite_number" class="block mt-1 w-full" type="text" name="cite_number" :value="old('cite_number', $graduationCite->cite_number ?? '')" required />
            @error('cite_number')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <x-label for="cite_date" value="Fecha del CITE" />
            <x-input id="cite_date" class="block mt-1 w-full" type="date" name="cite_date" :value="old('cite_date', isset($graduationCite) && $graduationCite->cite_date ? $graduationCite->cite_date->format('Y-m-d') : now()->format('Y-m-d'))" required />
            @error('cite_date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <x-label for="payment_type" :value="__('Concepto a Pagar')" />
            <select id="payment_type" name="payment_type" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                <option value="">Selecciona un concepto</option>
                @foreach($paymentTypes as $value => $label)
                    <option value="{{ $value }}" {{ old('payment_type', $graduationCite->payment_type ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            @error('payment_type')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <x-label for="amount_per_participant" :value="__('Monto por defecto (Bs)')" />
            <x-input id="amount_per_participant" x-model="amountPerParticipant" @input="normalizeAmount" class="block mt-1 w-full" type="number" step="0.01" min="0" name="amount_per_participant" :value="old('amount_per_participant', $graduationCite->amount_per_participant ?? 0)" />
            <p class="text-xs text-gray-500 mt-1">Se aplica al agregar nuevos participantes.</p>
            @error('amount_per_participant')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <x-label for="total_amount_preview" :value="__('Monto Total (Bs)')" />
            <input id="total_amount_preview" type="text" :value="formattedTotalAmount" readonly class="block mt-1 w-full rounded-md border-gray-300 bg-gray-100 shadow-sm text-gray-700">
            <p class="text-xs text-gray-500 mt-1">Suma de los montos individuales.</p>
        </div>
    </div>

    <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 space-y-4">
        <div>
            <h3 class="text-lg font-semibold text-gray-900">Participantes del CITE</h3>
            <p class="text-sm text-gray-600">Busca por nombre completo o CI para agregar participantes. Al seleccionarlos se cargan automáticamente sus datos.</p>
        </div>

        <div>
            <label for="participant-search" class="block text-sm font-medium text-gray-700">Buscar participante</label>
            <div class="relative mt-1">
                <input id="participant-search" type="text" x-model="search" @input.debounce.300ms="searchParticipants" placeholder="Escribe nombre o CI" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <div x-show="loading" class="absolute inset-y-0 right-3 flex items-center text-sm text-gray-500">Buscando...</div>
            </div>
            <p x-show="errorMessage" x-text="errorMessage" class="text-red-500 text-xs mt-1"></p>
            @error('participant_ids')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror

            <div x-show="results.length > 0" class="mt-3 border border-gray-200 rounded-lg bg-white shadow-sm max-h-72 overflow-y-auto">
                <template x-for="result in results" :key="result.id">
                    <button type="button" @click="addParticipant(result)" class="w-full text-left px-4 py-3 hover:bg-indigo-50 border-b border-gray-100 last:border-b-0">
                        <div class="font-medium text-gray-900" x-text="result.full_name"></div>
                        <div class="text-sm text-gray-600">
                            <span x-text="result.ci || 'Sin CI'"></span>
                            <span class="mx-2">|</span>
                            <span x-text="result.program"></span>
                        </div>
                    </button>
                </template>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 border border-gray-200 rounded-lg overflow-hidden">
                <thead>
                    <tr class="bg-gray-800 text-white text-xs">
                        <th class="px-4 py-3 text-left font-medium text-white uppercase tracking-wider">Nombre Completo</th>
                        <th class="px-4 py-3 text-left font-medium text-white uppercase tracking-wider">CI</th>
                        <th class="px-4 py-3 text-left font-medium text-white uppercase tracking-wider">Programa</th>
                        <th class="px-4 py-3 text-left font-medium text-white uppercase tracking-wider w-32">Monto (Bs)</th>
                        <th class="px-4 py-3 text-right font-medium text-white uppercase tracking-wider">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <template x-if="selectedParticipants.length === 0">
                        <tr>
                            <td colspan="5" class="px-4 py-4 text-sm text-center text-gray-500">No hay participantes seleccionados.</td>
                        </tr>
                    </template>
                    <template x-for="participant in selectedParticipants" :key="participant.id">
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-900" x-text="participant.full_name"></td>
                            <td class="px-4 py-3 text-sm text-gray-700" x-text="participant.ci || 'Sin CI'"></td>
                            <td class="px-4 py-3 text-sm text-gray-700" x-text="participant.program"></td>
                            <td class="px-4 py-3">
                                <input type="hidden" name="participant_ids[]" :value="participant.id">
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    :name="`participant_amounts[${participant.id}]`"
                                    x-model="participant.amount"
                                    @input="participant.amount = parseFloat($event.target.value) || 0"
                                    class="w-28 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                >
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button type="button" @click="removeParticipant(participant.id)" class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-red-700 bg-red-50 rounded-md hover:bg-red-100">Quitar</button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <div>
        <x-label for="observations" :value="__('Observaciones')" />
        <textarea id="observations" name="observations" rows="4" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('observations', $graduationCite->observations ?? '') }}</textarea>
        @error('observations')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>

    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('graduation-cites.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
            Cancelar
        </a>
        <x-button>{{ $submitLabel }}</x-button>
    </div>
</form>

<script>
    function graduationCiteForm({ initialParticipants, initialAmountPerParticipant }) {
        return {
            search: '',
            results: [],
            selectedParticipants: (initialParticipants ?? []).map(p => ({ ...p, amount: parseFloat(p.amount) || 0 })),
            amountPerParticipant: initialAmountPerParticipant ?? 0,
            loading: false,
            errorMessage: '',
            get totalAmount() {
                return this.selectedParticipants.reduce((sum, p) => sum + (parseFloat(p.amount) || 0), 0);
            },
            get formattedTotalAmount() {
                return this.totalAmount.toFixed(2);
            },
            normalizeAmount() {
                if (this.amountPerParticipant === '' || this.amountPerParticipant === null) {
                    this.amountPerParticipant = 0;
                }
            },
            async searchParticipants() {
                const query = this.search.trim();
                this.errorMessage = '';
                if (query.length < 2) {
                    this.results = [];
                    return;
                }
                this.loading = true;
                try {
                    const response = await fetch(`{{ route('graduation-cites.participants.search') }}?q=${encodeURIComponent(query)}`, {
                        headers: { 'Accept': 'application/json' },
                    });
                    if (!response.ok) throw new Error('No se pudo buscar participantes.');
                    const data = await response.json();
                    const selectedIds = new Set(this.selectedParticipants.map(p => p.id));
                    this.results = data.filter(p => !selectedIds.has(p.id));
                } catch (error) {
                    this.results = [];
                    this.errorMessage = error.message;
                } finally {
                    this.loading = false;
                }
            },
            addParticipant(participant) {
                if (this.selectedParticipants.some(item => item.id === participant.id)) return;
                this.selectedParticipants.push({
                    ...participant,
                    amount: parseFloat(this.amountPerParticipant) || 0,
                });
                this.search = '';
                this.results = [];
            },
            removeParticipant(participantId) {
                this.selectedParticipants = this.selectedParticipants.filter(p => p.id !== participantId);
            },
        };
    }
</script>