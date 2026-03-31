@extends('layouts.app')

@section('content')
<script>
window._incConfig = {
    grid:       @json((object)$grid),
    items:      @json($items),
    gestion:    {{ $gestion }},
    upsertUrl:  "{{ route('management-incomes.cell') }}",
    destroyUrl: "{{ route('management-incomes.destroyItem') }}",
    itemsUrl:   "{{ route('management-incomes.items') }}"
};
</script>
<div class="py-12" x-data="incomeGrid()">
    <div class="max-w-full px-4 sm:px-6 md:px-8">

        {{-- Header --}}
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Ingresos por Gestion</h1>
                <p class="mt-1 text-sm text-gray-600">
                    Haz clic en una celda para registrar o editar el ingreso del mes.
                </p>
            </div>
            <form method="GET" action="{{ route('management-incomes.index') }}" class="flex items-center gap-2">
                <label for="gestion" class="text-sm font-medium text-gray-700">Gestion:</label>
                <input id="gestion" name="gestion" type="number" min="2000" max="2100"
                       value="{{ $gestion }}"
                       class="w-24 px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700">
                    Cambiar
                </button>
            </form>
        </div>

        {{-- Flash --}}
        @if (session('success'))
            <div class="mb-4 rounded-md bg-green-50 border border-green-200 p-4">
                <p class="text-sm text-green-800">{{ session('success') }}</p>
            </div>
        @endif

        {{-- Controls bar --}}
        <div class="mb-4 flex flex-wrap items-center gap-3">

            {{-- Add item --}}
            <template x-if="!addingItem">
                <button @click="addingItem = true; $nextTick(() => $refs.newItemInput.focus())"
                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-green-600 text-white rounded-md text-sm font-medium hover:bg-green-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Agregar item
                </button>
            </template>
            <template x-if="addingItem">
                <div class="flex items-center gap-2">
                    <input x-ref="newItemInput"
                           x-model="newItemName"
                           type="text"
                           placeholder="Nombre del item..."
                           @keydown.enter.prevent="addItem()"
                           @keydown.escape.prevent="addingItem = false; newItemName = ''"
                           class="px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 w-64">
                    <button @click="addItem()"
                            class="px-4 py-2 bg-green-600 text-white rounded-md text-sm font-medium hover:bg-green-700">Agregar</button>
                    <button @click="addingItem = false; newItemName = ''"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md text-sm font-medium hover:bg-gray-300">Cancelar</button>
                </div>
            </template>

            {{-- Import from previous year --}}
            <button @click="importFromYear(gestion - 1)"
                    :disabled="importing"
                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-100 text-indigo-700 rounded-md text-sm font-medium hover:bg-indigo-200 disabled:opacity-60">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                <span x-text="importing ? 'Importando...' : 'Copiar items de ' + (gestion - 1)"></span>
            </button>

            {{-- Search --}}
            <div class="relative flex-1 min-w-[220px] max-w-md ml-auto">
                <input x-model="searchQuery"
                       type="text"
                       placeholder="Buscar item..."
                       style="padding-left:2.5rem;"
                       class="w-full pr-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                <svg style="position:absolute;left:0.75rem;top:50%;transform:translateY(-50%);width:1rem;height:1rem;color:#9ca3af;pointer-events:none;"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>

            {{-- Import notification --}}
            <span x-show="importMsg" x-text="importMsg" class="text-sm text-indigo-700 font-medium"></span>
        </div>

        {{-- Grid table --}}
        <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b-2 border-gray-200">
                            <th class="sticky left-0 z-20 bg-gray-50 px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider min-w-[200px] border-r border-gray-200">
                                Item
                            </th>
                            @foreach(['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'] as $i => $short)
                            <th class="px-2 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider min-w-[80px]
                                       {{ (int)date('n') === $i + 1 ? 'bg-indigo-50 text-indigo-700' : '' }}">
                                {{ $short }}
                            </th>
                            @endforeach
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider min-w-[110px] border-l border-gray-200">
                                Total item
                            </th>
                            <th class="px-3 py-3 min-w-[48px]"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <template x-if="items.length === 0">
                            <tr>
                                <td colspan="15" class="px-6 py-14 text-center text-sm text-gray-400">
                                    No hay items para la gestion <span x-text="gestion"></span>.
                                    Haz clic en "Agregar item" o copia items de la gestion anterior.
                                </td>
                            </tr>
                        </template>

                        <template x-if="items.length > 0 && filteredItems().length === 0">
                            <tr>
                                <td colspan="15" class="px-6 py-14 text-center text-sm text-gray-400">
                                    No se encontraron items para la busqueda <span class="font-medium text-gray-500" x-text="searchQuery"></span>.
                                </td>
                            </tr>
                        </template>

                        <template x-for="item in filteredItems()" :key="item">
                            <tr class="hover:bg-gray-50 transition-colors"
                                :class="editingCell?.item === item ? 'bg-indigo-50' : ''">

                                <td class="sticky left-0 z-10 bg-white px-4 py-2 font-medium text-gray-900 border-r border-gray-100"
                                    :class="editingCell?.item === item ? '!bg-indigo-50' : ''">
                                    <span x-text="item"></span>
                                </td>

                                <template x-for="mes in [1,2,3,4,5,6,7,8,9,10,11,12]" :key="mes">
                                    <td class="px-1 py-1 text-center relative"
                                        @mouseenter="showTooltip($event, item, mes)"
                                        @mouseleave="hideTooltip()">
                                        <!-- Observation tooltip -->
                                        <div x-show="tooltip && tooltip.item === item && tooltip.mes === mes"
                                             :style="tooltip?.position === 'bottom'
                                                ? 'position:absolute;top:calc(100% + 8px);left:50%;transform:translateX(-50%);min-width:200px;max-width:280px;background:#1f2937;color:#f9fafb;font-size:11px;line-height:1.5;padding:9px 11px;border-radius:7px;z-index:100;pointer-events:none;box-shadow:0 4px 16px rgba(0,0,0,.3);'
                                                : 'position:absolute;bottom:calc(100% + 8px);left:50%;transform:translateX(-50%);min-width:200px;max-width:280px;background:#1f2937;color:#f9fafb;font-size:11px;line-height:1.5;padding:9px 11px;border-radius:7px;z-index:100;pointer-events:none;box-shadow:0 4px 16px rgba(0,0,0,.3);'">
                                            <div style="font-weight:700;font-size:10px;opacity:.6;text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px;">Observacion</div>
                                            <div x-text="grid[item]?.[mes]?.obs"></div>
                                            <template x-if="grid[item]?.[mes]?.user">
                                                <div style="margin-top:6px;padding-top:6px;border-top:1px solid rgba(255,255,255,.15);font-size:10px;opacity:.75;" x-text="'Anadido por: ' + (grid[item]?.[mes]?.user ?? '')"></div>
                                            </template>
                                            <div :style="tooltip?.position === 'bottom'
                                                ? 'position:absolute;top:-5px;left:50%;width:10px;height:10px;background:#1f2937;transform:translateX(-50%) rotate(45deg);'
                                                : 'position:absolute;bottom:-5px;left:50%;width:10px;height:10px;background:#1f2937;transform:translateX(-50%) rotate(45deg);'"></div>
                                        </div>
                                        <span
                                            x-show="!!(grid[item] && grid[item][mes] && String(grid[item][mes].obs ?? '').trim() !== '')"
                                            style="position:absolute;top:2px;right:2px;display:inline-block;width:10px;height:10px;border-radius:50%;background:#fbbf24;z-index:10;pointer-events:none;border:1.5px solid #fff;"
                                            title="Tiene observacion"></span>
                                        <button
                                            @click="openEdit(item, mes)"
                                            class="w-full min-h-[2.25rem] rounded px-1 py-1 text-xs transition-colors"
                                            :class="[
                                                ((editingCell?.item === item && editingCell?.mes === mes && draftAmount !== '') || getCellData(item, mes))
                                                    ? 'text-green-700 font-semibold hover:bg-green-50'
                                                    : 'text-gray-300 hover:bg-indigo-50 hover:text-gray-500',
                                                editingCell?.item === item && editingCell?.mes === mes
                                                    ? '!bg-indigo-200 ring-2 ring-inset ring-indigo-500 rounded'
                                                    : ''
                                            ]">
                                            <span x-text="(editingCell?.item === item && editingCell?.mes === mes && draftAmount !== '')
                                                ? fmtAmt(draftAmount)
                                                : (getCellData(item, mes) ? fmtAmt(getCellData(item, mes).amount) : '-')"></span>
                                        </button>
                                    </td>
                                </template>

                                <td class="px-4 py-2 text-right font-bold text-green-700 border-l border-gray-100">
                                    Bs. <span x-text="fmtAmt(getItemTotal(item))"></span>
                                </td>

                                <td class="px-3 py-2 text-center">
                                    <button @click="deleteItem(item)" title="Eliminar item"
                                            class="text-gray-300 hover:text-red-600 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>

                    <tfoot class="border-t-2 border-gray-300 bg-gray-50">
                        <tr>
                            <td class="sticky left-0 z-10 bg-gray-50 px-4 py-3 text-xs font-bold text-gray-700 uppercase border-r border-gray-200">
                                Total mes
                            </td>
                            @for ($m = 1; $m <= 12; $m++)
                            <td class="px-2 py-3 text-center text-xs font-bold text-gray-800 {{ (int)date('n') === $m ? 'bg-indigo-50' : '' }}">
                                Bs. <span x-text="fmtAmt(getMonthTotal({{ $m }}))"></span>
                            </td>
                            @endfor
                            <td class="px-4 py-3 text-right text-sm font-bold text-green-800 border-l border-gray-200">
                                Bs. <span x-text="fmtAmt(getGrandTotal())"></span>
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

    </div>{{-- /max-w --}}

    {{-- Inline edit panel --}}
    <div x-show="editingCell"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-4"
         class="mt-4 bg-white border-2 border-indigo-300 rounded-lg shadow-lg px-4 py-4">
        <div class="max-w-5xl">
            <div class="flex flex-col sm:flex-row items-start sm:items-end gap-4">
                <div class="flex-shrink-0">
                    <p class="text-xs font-semibold uppercase text-gray-400 mb-0.5">Editando celda</p>
                    <p class="text-sm font-bold text-gray-900">
                        <span x-text="editingCell?.item"></span>
                        &mdash;
                        <span x-text="monthFull[(editingCell?.mes ?? 1) - 1]"></span>
                        <span x-text="gestion"></span>
                    </p>
                </div>

                <div class="flex-shrink-0">
                    <label class="block text-xs text-gray-500 mb-1">Monto (Bs.)</label>
                    <input id="cell-amount-input"
                           type="number" min="0" step="0.01"
                           @input="draftAmount = $event.target.value"
                           @keydown.enter.prevent="saveCell()"
                           @keydown.escape.prevent="closeEdit()"
                           class="w-36 px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div class="flex-grow min-w-0">
                    <label class="block text-xs text-gray-500 mb-1">Observacion (opcional)</label>
                    <input id="cell-obs-input"
                           type="text"
                           placeholder="Escribe una observacion..."
                           @input="draftObs = $event.target.value"
                           @keydown.enter.prevent="saveCell()"
                           @keydown.escape.prevent="closeEdit()"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div class="flex-shrink-0 flex items-center gap-2 pb-0.5">
                    <button @click="saveCell()"
                            :disabled="saving"
                            class="inline-flex items-center gap-1.5 px-5 py-2 bg-indigo-600 text-white rounded-md text-sm font-medium hover:bg-indigo-700 disabled:opacity-60">
                        <template x-if="saving">
                            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor"
                                      d="M4 12a8 8 0 018-8V0C5.373 0 22 6.477 22 12h-4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                            </svg>
                        </template>
                        <span x-text="saving ? 'Guardando...' : 'Guardar'"></span>
                    </button>
                    <button @click="closeEdit()"
                            :disabled="saving"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md text-sm font-medium hover:bg-gray-300 disabled:opacity-60">
                        Cancelar
                    </button>
                </div>
            </div>
            <p class="text-xs text-gray-400 mt-2">
                <kbd class="bg-gray-100 border border-gray-300 rounded px-1 py-0.5">Enter</kbd> para guardar &middot;
                <kbd class="bg-gray-100 border border-gray-300 rounded px-1 py-0.5">Esc</kbd> para cancelar
            </p>
        </div>
    </div>

</div>{{-- /x-data --}}

<script>
function incomeGrid() {
    const cfg = window._incConfig || {};
    return {
        grid:     cfg.grid || {},
        items:    cfg.items || [],
        gestion:  cfg.gestion || new Date().getFullYear(),

        monthFull: ['Enero','Febrero','Marzo','Abril','Mayo','Junio',
                    'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'],

        /* -- editing state -- */
        editingCell: null,
        draftAmount: '',
        draftObs:    '',
        saving:      false,

        /* -- tooltip -- */
        tooltip: null,

        /* -- add item -- */
        searchQuery: '',
        newItemName: '',
        addingItem:  false,

        /* -- import -- */
        importing:  false,
        importMsg:  '',

        /* -- helpers -- */
        getCellData(item, mes) {
            return this.grid[item]?.[mes] ?? null;
        },

        hasObservation(item, mes) {
            const cell = this.getCellData(item, mes);
            return !!(cell && String(cell.obs ?? '').trim() !== '');
        },

        getTooltipPosition(event) {
            const tdEl = event?.currentTarget;
            if (!tdEl) return 'top';

            let clipTop = 8;
            let clipBottom = window.innerHeight - 8;

            let parent = tdEl.parentElement;
            while (parent) {
                const style = window.getComputedStyle(parent);
                const overflowY = style.overflowY;
                const overflow = style.overflow;
                if (/(auto|hidden|scroll|clip)/.test(overflowY) || /(auto|hidden|scroll|clip)/.test(overflow)) {
                    const r = parent.getBoundingClientRect();
                    clipTop = Math.max(clipTop, r.top + 8);
                    clipBottom = Math.min(clipBottom, r.bottom - 8);
                }
                parent = parent.parentElement;
            }

            const rect = tdEl.getBoundingClientRect();
            const estimatedTooltipHeight = 108;
            const spaceAbove = rect.top - clipTop;
            const spaceBelow = clipBottom - rect.bottom;

            if (spaceAbove < estimatedTooltipHeight && spaceBelow > spaceAbove) {
                return 'bottom';
            }

            return 'top';
        },

        showTooltip(event, item, mes) {
            const cell = this.grid[item]?.[mes];
            if (!cell || !String(cell.obs ?? '').trim()) return;

            const position = this.getTooltipPosition(event);
            this.tooltip = { item, mes, position };
        },

        hideTooltip() {
            this.tooltip = null;
        },

        filteredItems() {
            const query = this.searchQuery.trim().toLocaleLowerCase();
            if (!query) return this.items;

            return this.items.filter(item => item.toLocaleLowerCase().includes(query));
        },

        getItemTotal(item) {
            let t = 0;
            for (let m = 1; m <= 12; m++) t += parseFloat(this.grid[item]?.[m]?.amount ?? 0);
            return t;
        },

        getMonthTotal(mes) {
            return this.filteredItems().reduce((t, item) => t + parseFloat(this.grid[item]?.[mes]?.amount ?? 0), 0);
        },

        getGrandTotal() {
            return this.filteredItems().reduce((t, item) => t + this.getItemTotal(item), 0);
        },

        fmtAmt(value) {
            return new Intl.NumberFormat('es-BO', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            }).format(parseFloat(value) || 0);
        },

        /* -- editing -- */
        openEdit(item, mes) {
            const cell = this.getCellData(item, mes);
            this.editingCell = { item, mes };
            this.draftAmount = cell ? String(cell.amount) : '';
            this.draftObs = cell ? (cell.obs ?? '') : '';
            this.$nextTick(() => {
                const amountEl = document.getElementById('cell-amount-input');
                const obsEl    = document.getElementById('cell-obs-input');
                if (amountEl) { amountEl.value = this.draftAmount; amountEl.focus(); amountEl.select(); }
                if (obsEl)    { obsEl.value    = this.draftObs; }
            });
        },

        closeEdit() {
            this.editingCell = null;
            this.draftAmount = '';
            this.draftObs = '';
        },

        async saveCell() {
            if (!this.editingCell || this.saving) return;
            const { item, mes } = this.editingCell;
            const amount = parseFloat(document.getElementById('cell-amount-input')?.value) || 0;
            const obs    = document.getElementById('cell-obs-input')?.value ?? '';

            this.saving = true;
            try {
                const res = await fetch(cfg.upsertUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type':  'application/json',
                        'Accept':        'application/json',
                        'X-CSRF-TOKEN':  document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        item, mes, gestion: this.gestion,
                        income_amount: amount,
                        observation:   obs || null,
                    }),
                });
                const data = await res.json();
                if (data.success) {
                    this.grid = {
                        ...this.grid,
                        [item]: {
                            ...this.grid[item],
                            [mes]: { id: data.id, amount, obs: (obs ?? '').trim(), user: data.user ?? '' },
                        },
                    };
                    this.closeEdit();
                }
            } finally {
                this.saving = false;
            }
        },

        /* delete item row */
        async deleteItem(item) {
            if (!confirm('Eliminar todos los registros de "' + item + '" para la gestion ' + this.gestion + '? Esta accion no se puede deshacer.')) return;

            await fetch(cfg.destroyUrl, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept':       'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ item, gestion: this.gestion }),
            });

            const newGrid = { ...this.grid };
            delete newGrid[item];
            this.grid  = newGrid;
            this.items = this.items.filter(i => i !== item);
            if (this.editingCell?.item === item) this.closeEdit();
        },

        /* add item row (local only until a cell is saved) */
        addItem() {
            const name = this.newItemName.trim();
            if (!name) return;
            if (this.items.includes(name)) {
                alert('Ya existe un item con ese nombre.');
                return;
            }
            this.items = [...this.items, name].sort((a, b) => a.localeCompare(b, 'es'));
            this.grid  = { ...this.grid, [name]: {} };
            this.newItemName = '';
            this.addingItem  = false;
        },

        /* import item names from another year */
        async importFromYear(fromGestion) {
            this.importing = true;
            this.importMsg = '';
            try {
                const res  = await fetch(cfg.itemsUrl + '?gestion=' + fromGestion, {
                    headers: { 'Accept': 'application/json' },
                });
                const data = await res.json();
                let added = 0;
                for (const itemName of (data.items ?? [])) {
                    if (!this.items.includes(itemName)) {
                        this.items = [...this.items, itemName].sort((a, b) => a.localeCompare(b, 'es'));
                        this.grid  = { ...this.grid, [itemName]: {} };
                        added++;
                    }
                }
                this.importMsg = added > 0
                    ? (added + ' item(s) importado(s) de ' + fromGestion + '.')
                    : ('Todos los items de ' + fromGestion + ' ya estan presentes.');
                setTimeout(() => { this.importMsg = ''; }, 4000);
            } finally {
                this.importing = false;
            }
        },
    };
}
</script>
@endsection