@extends('layouts.app')

@section('content')
<script>
window._expConfig = {
    grid:               @json((object)$grid),
    items:              @json($items),
    gestion:            {{ $gestion }},
    entities:           @json($entities->toArray()),
    entityAmountsGrid:  @json((object)$entityAmountsGrid),
    destroyUrl:         "{{ route('management-expenses.destroyItem') }}",
    renameUrl:          "{{ route('management-expenses.renameItem') }}",
    itemsUrl:           "{{ route('management-expenses.items') }}",
    entityStoreUrl:     "{{ route('management-expenses.entity.store') }}",
    entityRenameBase:   "{{ url('management-expenses/entity') }}",
    entityDestroyBase:  "{{ url('management-expenses/entity') }}",
    entityDetailBase:   "{{ url('management-expenses/entity') }}",
    entityAmountUrl:    "{{ route('management-expenses.entity.upsertAmount') }}",
    currentMonth:       {{ (int)date('n') }},
};
</script>

<div class="py-12" x-data="expenseGrid()">
    <div class="max-w-full px-4 sm:px-6 md:px-8">

        {{-- Header --}}
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Egresos por Gestión</h1>
                <p class="mt-1 text-sm text-gray-600">
                    Define las entidades, expande un mes y haz clic en una celda de entidad para registrar los egresos diarios.
                </p>
            </div>
            <form method="GET" action="{{ route('management-expenses.index') }}" class="flex items-center gap-2">
                <label for="gestion" class="text-sm font-medium text-gray-700">Gestión:</label>
                <input id="gestion" name="gestion" type="number" min="2000" max="2100"
                       value="{{ $gestion }}"
                       class="w-24 px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700">Cambiar</button>
            </form>
        </div>

        {{-- Controls bar --}}
        <div class="mb-4 flex flex-wrap items-center gap-3">

            {{-- Add item --}}
            <template x-if="!addingItem">
                <button @click="addingItem = true; $nextTick(() => $refs.newItemInput.focus())"
                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-green-600 text-white rounded-md text-sm font-medium hover:bg-green-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Agregar ítem
                </button>
            </template>
            <template x-if="addingItem">
                <div class="flex items-center gap-2">
                    <input x-ref="newItemInput" x-model="newItemName" type="text" placeholder="Nombre del ítem…"
                           @keydown.enter.prevent="addItem()" @keydown.escape.prevent="addingItem = false; newItemName = ''"
                           class="px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 w-64">
                    <button @click="addItem()" class="px-4 py-2 bg-green-600 text-white rounded-md text-sm font-medium hover:bg-green-700">Agregar</button>
                    <button @click="addingItem = false; newItemName = ''" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md text-sm font-medium hover:bg-gray-300">Cancelar</button>
                </div>
            </template>

            {{-- Import from previous year --}}
            <button @click="importFromYear(gestion - 1)" :disabled="importing"
                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-100 text-indigo-700 rounded-md text-sm font-medium hover:bg-indigo-200 disabled:opacity-60">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                <span x-text="importing ? 'Importando…' : 'Copiar ítems de ' + (gestion - 1)"></span>
            </button>
            <span x-show="importMsg" x-text="importMsg" class="text-sm text-indigo-700 font-medium"></span>

            {{-- Search --}}
            <div class="relative flex-1 min-w-[220px] max-w-md ml-auto">
                <input x-model="searchQuery" type="text" placeholder="Buscar ítem..."
                       style="padding-left:2.5rem;"
                       class="w-full pr-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                <svg style="position:absolute;left:0.75rem;top:50%;transform:translateY(-50%);width:1rem;height:1rem;color:#9ca3af;pointer-events:none;"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
        </div>

        {{-- Entity management bar --}}
        <div class="mb-4 flex flex-wrap items-center gap-2 p-3 bg-gray-50 rounded-lg border border-gray-200">
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Entidades:</span>

            <template x-for="ent in entities" :key="ent.id">
                <div class="inline-flex items-center gap-1 rounded-full text-xs font-medium transition-colors"
                     :class="renamingEntityId === ent.id
                         ? 'px-1.5 py-0.5 bg-blue-100 border border-blue-400'
                         : 'px-2.5 py-1 bg-blue-50 border border-blue-200 text-blue-800'">

                    {{-- Normal view --}}
                    <template x-if="renamingEntityId !== ent.id">
                        <div class="flex items-center gap-1.5">
                            <button @click="startRenameEntity(ent.id, ent.name)"
                                    class="hover:text-blue-600 transition-colors cursor-text"
                                    title="Clic para renombrar"
                                    x-text="ent.name"></button>
                            <button @click="deleteEntity(ent.id)"
                                    class="text-blue-300 hover:text-red-600 transition-colors leading-none"
                                    title="Eliminar entidad">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </template>

                    {{-- Rename input --}}
                    <template x-if="renamingEntityId === ent.id">
                        <div class="flex items-center gap-1">
                            <input :id="'rename-entity-' + ent.id"
                                   x-model="renameEntityValue"
                                   type="text"
                                   @keydown.enter.prevent="saveRenameEntity(ent.id)"
                                   @keydown.escape.prevent="cancelRenameEntity()"
                                   class="w-24 px-1.5 py-0.5 border border-blue-400 rounded-full text-xs focus:outline-none focus:ring-1 focus:ring-blue-400 bg-white">
                            <button @click="saveRenameEntity(ent.id)" :disabled="renamingEntity"
                                    class="text-blue-600 hover:text-blue-800 disabled:opacity-50" title="Guardar">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                            </button>
                            <button @click="cancelRenameEntity()" :disabled="renamingEntity"
                                    class="text-gray-400 hover:text-gray-600 disabled:opacity-50" title="Cancelar">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </template>
                </div>
            </template>

            <template x-if="!addingEntity">
                <button @click="addingEntity = true; $nextTick(() => $refs.newEntityInput.focus())"
                        class="inline-flex items-center gap-1 px-2.5 py-1 bg-blue-600 text-white rounded-full text-xs font-medium hover:bg-blue-700 transition-colors">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                    </svg>
                    Añadir entidad
                </button>
            </template>
            <template x-if="addingEntity">
                <div class="flex items-center gap-1.5">
                    <input x-ref="newEntityInput" x-model="newEntityName" type="text"
                           placeholder="Nombre (ej. ISPI)…"
                           @keydown.enter.prevent="addEntity()"
                           @keydown.escape.prevent="addingEntity = false; newEntityName = ''"
                           class="w-36 px-2 py-1 border border-blue-400 rounded-full text-xs focus:outline-none focus:ring-1 focus:ring-blue-400">
                    <button @click="addEntity()"
                            class="px-2.5 py-1 bg-blue-600 text-white rounded-full text-xs font-medium hover:bg-blue-700">
                        Añadir
                    </button>
                    <button @click="addingEntity = false; newEntityName = ''"
                            class="px-2.5 py-1 bg-gray-200 text-gray-700 rounded-full text-xs font-medium hover:bg-gray-300">
                        Cancelar
                    </button>
                </div>
            </template>

            <template x-if="entities.length === 0">
                <span class="text-xs text-gray-400 italic">Añade entidades para registrar egresos (ej. ISPI, LATAM).</span>
            </template>

            {{-- Entity filter --}}
            <template x-if="entities.length > 1">
                <div class="ml-auto flex items-center gap-2">
                    <span class="text-xs font-medium text-gray-500">Filtrar:</span>
                    <select x-model="entityFilter"
                            class="px-2.5 py-1 text-xs border rounded-full focus:outline-none focus:ring-1 transition-colors"
                            :class="entityFilter
                                ? 'border-blue-400 bg-blue-50 text-blue-800 font-semibold focus:ring-blue-400'
                                : 'border-gray-300 bg-white text-gray-700 focus:ring-blue-400'">
                        <option value="">Todas las entidades</option>
                        <template x-for="ent in entities" :key="ent.id">
                            <option :value="ent.id" x-text="ent.name"></option>
                        </template>
                    </select>
                    <button x-show="entityFilter" @click="entityFilter = ''"
                            class="text-blue-400 hover:text-red-500 transition-colors" title="Quitar filtro">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </template>
        </div>

        {{-- Grid table --}}
        <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm border-collapse">
                    <thead>
                        <tr class="bg-gray-800 text-white text-xs">
                            <th class="sticky left-0 z-20 bg-gray-800 px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider min-w-[200px] border-r border-gray-200">Ítem</th>

                            <template x-for="col in visibleColumns()" :key="col.key">
                                <th :class="{
                                        'px-2 py-3 text-center text-xs font-semibold uppercase tracking-wider min-w-[82px] select-none': true,
                                        'bg-red-50 text-red-700': col.type === 'month' && col.mes === currentMonth && !col.expanded,
                                        'bg-red-50 text-red-600': col.type === 'month' && col.mes === currentMonth && col.expanded,
                                        'bg-gray-50 text-white cursor-pointer hover:bg-gray-100': col.type === 'month',
                                        'bg-blue-50 text-blue-700 min-w-[90px]': col.type === 'entity',
                                    }">

                                    {{-- Month column header — click to expand --}}
                                    <template x-if="col.type === 'month'">
                                        <button @click="toggleMonth(col.mes)"
                                                class="w-full flex flex-col items-center gap-0.5 py-0.5"
                                                :title="col.expanded ? 'Contraer desglose' : 'Expandir para ver entidades'">
                                            <span class="flex items-center gap-1">
                                                <span x-text="col.short"></span>
                                                <svg x-show="!col.expanded" class="w-3 h-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                </svg>
                                                <svg x-show="col.expanded" class="w-3 h-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                                </svg>
                                            </span>
                                            <span x-show="col.expanded" class="text-[10px] font-normal text-gray-400 normal-case">Total</span>
                                        </button>
                                    </template>

                                    {{-- Entity column header --}}
                                    <template x-if="col.type === 'entity'">
                                        <div class="flex items-center justify-center gap-1">
                                            <span x-text="col.entityName" class="text-xs font-semibold text-blue-700 uppercase tracking-wider"></span>
                                            <button @click="deleteEntity(col.entityId)"
                                                    class="text-blue-300 hover:text-red-500 transition-colors flex-shrink-0"
                                                    title="Eliminar entidad">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </template>
                                </th>
                            </template>

                            <th class="px-4 py-3 text-right text-xs font-semibold text-white uppercase tracking-wider min-w-[110px] border-l border-gray-200">Total ítem</th>
                            <th class="px-3 py-3 min-w-[48px]"></th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">
                        <template x-if="items.length === 0">
                            <tr><td :colspan="2 + visibleColumns().length + 2" class="px-6 py-14 text-center text-sm text-gray-400">
                                No hay ítems para la gestión <span x-text="gestion"></span>.
                                <template x-if="entities.length === 0">
                                    <span> Añade entidades primero para poder registrar egresos.</span>
                                </template>
                            </td></tr>
                        </template>
                        <template x-if="items.length > 0 && filteredItems().length === 0">
                            <tr><td :colspan="2 + visibleColumns().length + 2" class="px-6 py-14 text-center text-sm text-gray-400">
                                No se encontraron ítems para "<span x-text="searchQuery"></span>".
                            </td></tr>
                        </template>

                        <template x-for="item in filteredItems()" :key="item">
                            <tr class="hover:bg-gray-50 transition-colors">

                                {{-- Item name column --}}
                                <td class="sticky left-0 z-10 bg-white px-4 py-2 font-medium text-gray-900 border-r border-gray-100">
                                    <template x-if="renamingItem !== item">
                                        <div style="display:flex;align-items:center;gap:6px;">
                                            <span x-text="item"></span>
                                            <button @click="startRename(item)" title="Renombrar"
                                                    style="flex-shrink:0;background:none;border:none;padding:2px;cursor:pointer;color:#9ca3af;"
                                                    onmouseover="this.style.color='#2563eb'" onmouseout="this.style.color='#9ca3af'">
                                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </template>
                                    <template x-if="renamingItem === item">
                                        <div class="flex items-center gap-1">
                                            <input :id="'rename-input-' + item" type="text" x-model="renameValue"
                                                   @keydown.enter.prevent="saveRename()" @keydown.escape.prevent="cancelRename()"
                                                   class="px-2 py-1 border border-blue-400 rounded text-sm focus:outline-none w-36">
                                            <button @click="saveRename()" :disabled="renaming" class="text-blue-600 hover:text-blue-800 disabled:opacity-50">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            </button>
                                            <button @click="cancelRename()" :disabled="renaming" class="text-gray-400 hover:text-gray-600 disabled:opacity-50">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>
                                    </template>
                                </td>

                                {{-- Dynamic month / entity columns --}}
                                <template x-for="col in visibleColumns()" :key="col.key">
                                    <td :class="{
                                            'px-1 py-1 text-center': col.type === 'month',
                                            'px-1 py-1 text-center bg-blue-50/40': col.type === 'entity',
                                        }">

                                        {{-- Month cell: read-only total, click to expand month --}}
                                        <template x-if="col.type === 'month'">
                                            <button @click="toggleMonth(col.mes)"
                                                    class="w-full min-h-[2.25rem] rounded px-1 py-1 text-xs transition-colors"
                                                    :title="col.expanded ? 'Contraer' : 'Expandir para ver entidades'"
                                                    :class="getCellAmount(item, col.mes) > 0
                                                        ? 'text-red-700 font-semibold hover:bg-red-50'
                                                        : 'text-gray-300 hover:bg-gray-50'">
                                                <span x-text="getCellAmount(item, col.mes) > 0 ? fmtAmt(getCellAmount(item, col.mes)) : '–'"></span>
                                            </button>
                                        </template>

                                        {{-- Entity cell: click opens calendar modal --}}
                                        <template x-if="col.type === 'entity'">
                                            <button @click="openEntityCalendar(col.entityId, item, col.mes)"
                                                    class="w-full min-h-[2.25rem] rounded px-1 py-1 text-xs transition-colors"
                                                    :class="getEntityAmount(col.entityId, item, col.mes) > 0
                                                        ? 'text-blue-700 font-semibold hover:bg-blue-100'
                                                        : 'text-gray-300 hover:bg-blue-50 hover:text-gray-400'">
                                                <span x-text="getEntityAmount(col.entityId, item, col.mes) > 0
                                                    ? fmtAmt(getEntityAmount(col.entityId, item, col.mes))
                                                    : '–'"></span>
                                            </button>
                                        </template>
                                    </td>
                                </template>

                                {{-- Item row total --}}
                                <td class="px-4 py-2 text-right font-bold text-red-700 border-l border-gray-100">
                                    Bs. <span x-text="fmtAmt(getItemTotal(item))"></span>
                                </td>

                                {{-- Delete button --}}
                                <td class="px-3 py-2 text-center">
                                    <button @click="deleteItem(item)" title="Eliminar ítem" class="text-gray-300 hover:text-red-600 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>

                    <tfoot class="border-t-2 border-gray-300 bg-gray-50">
                        <tr>
                            <td class="sticky left-0 z-10 bg-gray-50 px-4 py-3 text-xs font-bold text-gray-700 uppercase border-r border-gray-200">Total mes</td>

                            <template x-for="col in visibleColumns()" :key="col.key">
                                <td :class="{
                                        'px-2 py-3 text-center text-xs font-bold': true,
                                        'text-gray-800': col.type === 'month',
                                        'bg-red-50': col.type === 'month' && col.mes === currentMonth,
                                        'text-blue-700 bg-blue-50/60': col.type === 'entity',
                                    }">
                                    <template x-if="col.type === 'month'">
                                        <span>Bs. <span x-text="fmtAmt(getMonthTotal(col.mes))"></span></span>
                                    </template>
                                    <template x-if="col.type === 'entity'">
                                        <span x-text="fmtAmt(getEntityMonthTotal(col.entityId, col.mes))"></span>
                                    </template>
                                </td>
                            </template>

                            <td class="px-4 py-3 text-right text-sm font-bold text-red-800 border-l border-gray-200">
                                Bs. <span x-text="fmtAmt(getGrandTotal())"></span>
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

    </div>

    {{-- ══════════════════════════════════════════════════════════════ --}}
    {{-- MODAL CALENDARIO (usado para celdas de entidad)               --}}
    {{-- ══════════════════════════════════════════════════════════════ --}}
    <div x-show="calModal.open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="closeCalendar()"
         @keydown.escape.window="closeCalendar()"
         style="position:fixed;inset:0;z-index:50;background:rgba(0,0,0,0.5);">
        <div style="display:flex;align-items:center;justify-content:center;width:100%;height:100%;padding:1rem;">
        <div @click.stop
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             style="background:#fff;border-radius:1rem;box-shadow:0 25px 50px rgba(0,0,0,0.25);width:100%;max-width:540px;max-height:90vh;overflow-y:auto;">

            {{-- Modal header --}}
            <div style="display:flex;align-items:center;justify-content:space-between;padding:1rem 1.25rem;border-bottom:1px solid #e5e7eb;">
                <div>
                    <p style="font-size:0.7rem;font-weight:600;text-transform:uppercase;color:#9ca3af;letter-spacing:0.05em;">
                        Registro diario &mdash; <span x-text="calModal.entityName"></span>
                    </p>
                    <h2 style="font-size:1rem;font-weight:700;color:#111827;margin-top:2px;">
                        <span x-text="calModal.item"></span>
                        &mdash;
                        <span x-text="monthFull[(calModal.mes ?? 1) - 1]"></span>
                        <span x-text="gestion"></span>
                    </h2>
                </div>
                <button @click="closeCalendar()" style="color:#9ca3af;padding:4px;border-radius:6px;background:none;border:none;cursor:pointer;" onmouseover="this.style.color='#374151'" onmouseout="this.style.color='#9ca3af'">
                    <svg style="width:1.25rem;height:1.25rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Loading state --}}
            <div x-show="calModal.loading" style="display:flex;align-items:center;justify-content:center;padding:4rem 0;">
                <svg style="width:2rem;height:2rem;color:#3b82f6;animation:spin 1s linear infinite;" fill="none" viewBox="0 0 24 24">
                    <circle style="opacity:0.25;" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path style="opacity:0.75;" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 22 6.477 22 12h-4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                </svg>
            </div>

            {{-- Calendar body --}}
            <div x-show="!calModal.loading" style="padding:1.25rem;">

                {{-- Weekday headers --}}
                <div style="display:grid;grid-template-columns:repeat(7,1fr);gap:4px;margin-bottom:4px;">
                    <template x-for="d in ['Lun','Mar','Mié','Jue','Vie','Sáb','Dom']" :key="d">
                        <div style="text-align:center;font-size:0.65rem;font-weight:700;color:#9ca3af;padding:4px 0;text-transform:uppercase;letter-spacing:0.04em;" x-text="d"></div>
                    </template>
                </div>

                {{-- Calendar grid --}}
                <div style="display:grid;grid-template-columns:repeat(7,1fr);gap:4px;">
                    <template x-for="off in calModal.startOffset" :key="'o' + off">
                        <div></div>
                    </template>
                    <template x-for="day in calModal.daysInMonth" :key="day">
                        <button @click="selectDay(day)"
                                style="position:relative;display:flex;flex-direction:column;align-items:flex-start;justify-content:space-between;padding:5px 6px;min-height:54px;border-radius:8px;border:1.5px solid;cursor:pointer;transition:all 0.12s;width:100%;text-align:left;"
                                :style="calModal.selectedDay === day
                                    ? 'border-color:#3b82f6;background:#eff6ff;box-shadow:0 0 0 2px rgba(59,130,246,0.25);'
                                    : (getDayAmount(day) > 0
                                        ? 'border-color:#93c5fd;background:#f0f9ff;'
                                        : 'border-color:#f3f4f6;background:#fff;')">
                            <span style="font-size:0.75rem;font-weight:700;line-height:1;"
                                  :style="getDayAmount(day) > 0 ? 'color:#1d4ed8;' : 'color:#9ca3af;'"
                                  x-text="day"></span>
                            <span x-show="getDayAmount(day) > 0"
                                  style="font-size:0.65rem;font-weight:600;color:#1d4ed8;line-height:1;margin-top:auto;word-break:break-all;"
                                  x-text="fmtShort(getDayAmount(day))"></span>
                            <span x-show="getDayObs(day)"
                                  style="position:absolute;top:4px;right:4px;width:6px;height:6px;border-radius:50%;background:#f59e0b;border:1.5px solid #fff;"></span>
                        </button>
                    </template>
                </div>

                {{-- Edit panel --}}
                <div x-show="calModal.selectedDay !== null"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     id="day-edit-panel"
                     style="margin-top:1rem;padding:1rem;background:#eff6ff;border:1.5px solid #93c5fd;border-radius:10px;">

                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.75rem;">
                        <p style="font-size:0.875rem;font-weight:700;color:#111827;">
                            Día <span x-text="calModal.selectedDay"></span>
                            &ndash;
                            <span x-text="monthFull[(calModal.mes ?? 1) - 1]"></span>
                            <span x-text="gestion"></span>
                        </p>
                        <button @click="cancelDayEdit()"
                                style="background:none;border:none;cursor:pointer;color:#9ca3af;padding:2px;"
                                onmouseover="this.style.color='#374151'" onmouseout="this.style.color='#9ca3af'">
                            <svg style="width:1rem;height:1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <div style="display:flex;gap:0.5rem;flex-wrap:wrap;">
                        <div style="flex-shrink:0;">
                            <label style="display:block;font-size:0.7rem;color:#6b7280;margin-bottom:3px;font-weight:500;">Monto (Bs.)</label>
                            <input type="number" min="0" step="0.01"
                                   id="day-edit-amount"
                                   placeholder="0.00"
                                   @keydown.enter.prevent="saveDay(calModal.selectedDay)"
                                   @keydown.escape.prevent="cancelDayEdit()"
                                   style="width:7.5rem;padding:0.4rem 0.6rem;border:1px solid #d1d5db;border-radius:6px;font-size:0.8rem;outline:none;"
                                   onfocus="this.style.borderColor='#3b82f6';this.style.boxShadow='0 0 0 2px rgba(59,130,246,0.15)'"
                                   onblur="this.style.borderColor='#d1d5db';this.style.boxShadow='none'">
                        </div>
                        <div style="flex:1;min-width:140px;">
                            <label style="display:block;font-size:0.7rem;color:#6b7280;margin-bottom:3px;font-weight:500;">Observación (opcional)</label>
                            <input type="text"
                                   id="day-edit-obs"
                                   placeholder="Ej. pago proveedor…"
                                   maxlength="1000"
                                   @keydown.enter.prevent="saveDay(calModal.selectedDay)"
                                   @keydown.escape.prevent="cancelDayEdit()"
                                   style="width:100%;padding:0.4rem 0.6rem;border:1px solid #d1d5db;border-radius:6px;font-size:0.8rem;outline:none;"
                                   onfocus="this.style.borderColor='#3b82f6';this.style.boxShadow='0 0 0 2px rgba(59,130,246,0.15)'"
                                   onblur="this.style.borderColor='#d1d5db';this.style.boxShadow='none'">
                        </div>
                    </div>

                    <div style="display:flex;gap:0.5rem;margin-top:0.625rem;">
                        <button @click="saveDay(calModal.selectedDay)" :disabled="calModal.saving"
                                style="flex:1;padding:0.45rem 1rem;background:#2563eb;color:#fff;border:none;border-radius:6px;font-size:0.8rem;font-weight:600;cursor:pointer;transition:background 0.1s;"
                                onmouseover="if(!this.disabled)this.style.background='#1d4ed8'" onmouseout="this.style.background='#2563eb'">
                            <span x-text="calModal.saving ? 'Guardando…' : 'Guardar'"></span>
                        </button>
                        <button @click="cancelDayEdit()"
                                style="flex:1;padding:0.45rem 1rem;background:#e5e7eb;color:#374151;border:none;border-radius:6px;font-size:0.8rem;font-weight:600;cursor:pointer;transition:background 0.1s;"
                                onmouseover="this.style.background='#d1d5db'" onmouseout="this.style.background='#e5e7eb'">
                            Cancelar
                        </button>
                    </div>
                </div>

                {{-- Month total --}}
                <div style="display:flex;align-items:center;justify-content:space-between;margin-top:1rem;padding-top:0.75rem;border-top:1px solid #e5e7eb;">
                    <span style="font-size:0.875rem;color:#6b7280;">Total del mes (<span x-text="calModal.entityName" style="font-weight:600;"></span>)</span>
                    <span style="font-size:1.125rem;font-weight:700;color:#1d4ed8;">
                        Bs. <span x-text="fmtAmt(calModal.monthTotal)"></span>
                    </span>
                </div>

                {{-- Observations list --}}
                <template x-if="calModal.daysData && Object.values(calModal.daysData).some(d => d.obs)">
                    <div style="margin-top:0.75rem;padding-top:0.75rem;border-top:1px solid #f3f4f6;">
                        <p style="font-size:0.65rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:0.5rem;">Observaciones</p>
                        <template x-for="(dayData, dayKey) in calModal.daysData" :key="dayKey">
                            <template x-if="dayData.obs">
                                <div style="display:flex;gap:0.5rem;font-size:0.75rem;margin-bottom:4px;">
                                    <span style="font-weight:600;color:#374151;min-width:3rem;">Día <span x-text="dayKey"></span>:</span>
                                    <span style="color:#6b7280;" x-text="dayData.obs"></span>
                                </div>
                            </template>
                        </template>
                    </div>
                </template>

            </div>
        </div>
        </div>
    </div>

</div>{{-- /x-data --}}

<script>
function expenseGrid() {
    const cfg = window._expConfig || {};
    return {
        grid:    cfg.grid || {},
        items:   cfg.items || [],
        gestion: cfg.gestion || new Date().getFullYear(),

        // Entity support
        entities:          cfg.entities || [],
        entityAmountsGrid: cfg.entityAmountsGrid || {},
        expandedMonths:    [],
        addingEntity:      false,
        newEntityName:     '',
        renamingEntityId:  null,
        renameEntityValue: '',
        renamingEntity:    false,
        entityFilter:      '',  // '' = all, entity id (number/string) = specific entity

        monthShort: ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'],
        monthFull:  ['Enero','Febrero','Marzo','Abril','Mayo','Junio',
                     'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'],
        currentMonth: cfg.currentMonth || new Date().getMonth() + 1,

        searchQuery:  '',
        newItemName:  '',
        addingItem:   false,
        importing:    false,
        importMsg:    '',
        renamingItem: null,
        renameValue:  '',
        renaming:     false,

        calModal: {
            open:        false,
            loading:     false,
            saving:      false,
            item:        null,
            mes:         null,
            entityId:    null,
            entityName:  '',
            daysInMonth: [],
            startOffset: [],
            daysData:    {},
            monthTotal:  0,
            selectedDay: null,
        },

        // ── Column layout ─────────────────────────────────────────────────
        visibleColumns() {
            const cols = [];
            for (let mes = 1; mes <= 12; mes++) {
                const expanded = this.expandedMonths.includes(mes);
                cols.push({
                    key: `m${mes}`,
                    type: 'month',
                    mes,
                    short: this.monthShort[mes - 1],
                    expanded,
                });
                if (expanded) {
                    for (const ent of this.entities) {
                        cols.push({
                            key: `m${mes}-e${ent.id}`,
                            type: 'entity',
                            mes,
                            entityId: ent.id,
                            entityName: ent.name,
                        });
                    }
                }
            }
            return cols;
        },

        toggleMonth(mes) {
            if (this.expandedMonths.includes(mes)) {
                this.expandedMonths = this.expandedMonths.filter(m => m !== mes);
            } else {
                this.expandedMonths = [...this.expandedMonths, mes];
            }
        },

        // ── Cell values ───────────────────────────────────────────────────
        getCellAmount(item, mes) {
            if (this.entityFilter) {
                return parseFloat(this.entityAmountsGrid[this.entityFilter]?.[item]?.[mes] ?? 0);
            }
            return parseFloat(this.grid[item]?.[mes]?.amount ?? 0);
        },

        getEntityAmount(entityId, item, mes) {
            return parseFloat(this.entityAmountsGrid[entityId]?.[item]?.[mes] ?? 0);
        },

        getItemTotal(item) {
            let t = 0;
            for (let m = 1; m <= 12; m++) t += this.getCellAmount(item, m);
            return t;
        },

        getMonthTotal(mes) {
            return this.filteredItems().reduce((t, item) => t + this.getCellAmount(item, mes), 0);
        },

        getEntityMonthTotal(entityId, mes) {
            return this.filteredItems().reduce((t, item) => t + this.getEntityAmount(entityId, item, mes), 0);
        },

        getGrandTotal() {
            return this.filteredItems().reduce((t, item) => t + this.getItemTotal(item), 0);
        },

        filteredItems() {
            const q = this.searchQuery.trim().toLocaleLowerCase();
            return q ? this.items.filter(i => i.toLocaleLowerCase().includes(q)) : this.items;
        },

        fmtAmt(value) {
            return new Intl.NumberFormat('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(parseFloat(value) || 0);
        },

        fmtShort(n) {
            n = parseFloat(n) || 0;
            if (n === 0) return '';
            if (n >= 10000) return Math.round(n / 1000) + 'k';
            if (n >= 1000)  return (n / 1000).toFixed(1).replace(/\.0$/, '') + 'k';
            return Number.isInteger(n) ? n.toString() : n.toFixed(0);
        },

        // ── Entity management ─────────────────────────────────────────────
        async addEntity() {
            const name = this.newEntityName.trim();
            if (!name) return;
            if (this.entities.some(e => e.name === name)) {
                alert('Ya existe una entidad con ese nombre.');
                return;
            }

            const res = await fetch(cfg.entityStoreUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept':       'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ gestion: this.gestion, name }),
            });
            const data = await res.json();
            if (data.success) {
                this.entities = [...this.entities, data.entity];
                this.newEntityName = '';
                this.addingEntity  = false;
            }
        },

        async deleteEntity(entityId) {
            if (!confirm('¿Eliminar esta entidad? Se perderán todos los montos registrados para la gestión ' + this.gestion + '.')) return;

            const res = await fetch(cfg.entityDestroyBase + '/' + entityId, {
                method: 'DELETE',
                headers: {
                    'Accept':       'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
            });
            const data = await res.json();
            if (data.success) {
                this.entities = this.entities.filter(e => e.id !== entityId);
                if (String(this.entityFilter) === String(entityId)) this.entityFilter = '';
                const g = { ...this.entityAmountsGrid };
                delete g[entityId];
                this.entityAmountsGrid = g;

                // Recalculate main grid
                this._rebuildGrid();
            }
        },

        startRenameEntity(entityId, currentName) {
            this.renamingEntityId  = entityId;
            this.renameEntityValue = currentName;
            this.$nextTick(() => {
                const el = document.getElementById('rename-entity-' + entityId);
                if (el) { el.focus(); el.select(); }
            });
        },

        cancelRenameEntity() {
            this.renamingEntityId  = null;
            this.renameEntityValue = '';
        },

        async saveRenameEntity(entityId) {
            const newName = this.renameEntityValue.trim();
            const ent     = this.entities.find(e => e.id === entityId);
            if (!newName || newName === ent?.name) { this.cancelRenameEntity(); return; }

            this.renamingEntity = true;
            try {
                const res  = await fetch(cfg.entityRenameBase + '/' + entityId, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept':       'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ name: newName }),
                });
                const data = await res.json();
                if (data.success) {
                    this.entities = this.entities.map(e => e.id === entityId ? { ...e, name: data.name } : e);
                    this.cancelRenameEntity();
                } else {
                    alert(data.message ?? 'No se pudo renombrar la entidad.');
                }
            } finally {
                this.renamingEntity = false;
            }
        },

        _rebuildGrid() {
            const g = {};
            for (const item of this.items) {
                g[item] = {};
                for (let m = 1; m <= 12; m++) {
                    let total = 0;
                    for (const ent of this.entities) {
                        total += this.getEntityAmount(ent.id, item, m);
                    }
                    if (total > 0) g[item][m] = { amount: total };
                }
            }
            this.grid = g;
        },

        // ── Calendar modal (entity-based) ─────────────────────────────────
        async openEntityCalendar(entityId, item, mes) {
            const ent      = this.entities.find(e => e.id === entityId);
            const dim      = new Date(this.gestion, mes, 0).getDate();
            const firstDay = new Date(this.gestion, mes - 1, 1).getDay();
            const offset   = firstDay === 0 ? 6 : firstDay - 1;

            this.calModal = {
                open:        true,
                loading:     true,
                saving:      false,
                item,
                mes,
                entityId,
                entityName:  ent ? ent.name : '',
                daysInMonth: Array.from({ length: dim },    (_, i) => i + 1),
                startOffset: Array.from({ length: offset }, (_, i) => i),
                daysData:    {},
                monthTotal:  this.getEntityAmount(entityId, item, mes),
                selectedDay: null,
            };

            try {
                const url = cfg.entityDetailBase + '/' + entityId + '/month-detail'
                          + '?item=' + encodeURIComponent(item)
                          + '&mes=' + mes
                          + '&gestion=' + this.gestion;

                const res  = await fetch(url, { headers: { 'Accept': 'application/json' } });
                const data = await res.json();

                const daysData = {};
                let total = 0;
                for (const r of (data.records ?? [])) {
                    daysData[r.dia] = { amount: r.amount, obs: r.obs };
                    total += r.amount;
                }
                this.calModal.daysData   = daysData;
                this.calModal.monthTotal = total;
            } finally {
                this.calModal.loading = false;
            }
        },

        closeCalendar() {
            this.calModal.open       = false;
            this.calModal.selectedDay = null;
        },

        getDayAmount(day) {
            return parseFloat(this.calModal.daysData[day]?.amount ?? 0);
        },

        getDayObs(day) {
            return this.calModal.daysData[day]?.obs ?? '';
        },

        selectDay(day) {
            this.calModal.selectedDay = day;
            const existing = this.calModal.daysData[day];
            this.$nextTick(() => {
                const amtEl = document.getElementById('day-edit-amount');
                const obsEl = document.getElementById('day-edit-obs');
                if (amtEl) { amtEl.value = existing ? existing.amount : ''; amtEl.focus(); amtEl.select(); }
                if (obsEl) obsEl.value = existing ? (existing.obs ?? '') : '';
                document.getElementById('day-edit-panel')?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            });
        },

        cancelDayEdit() {
            this.calModal.selectedDay = null;
        },

        async saveDay(day) {
            if (this.calModal.saving || day === null) return;

            const amtEl  = document.getElementById('day-edit-amount');
            const obsEl  = document.getElementById('day-edit-obs');
            const amount = parseFloat(amtEl?.value) || 0;
            const obs    = obsEl?.value?.trim() ?? '';
            const { entityId, item, mes } = this.calModal;

            this.calModal.saving = true;
            try {
                const res = await fetch(cfg.entityAmountUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept':       'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        entity_id:   entityId,
                        item, mes,
                        gestion:     this.gestion,
                        dia:         day,
                        amount,
                        observation: obs || null,
                    }),
                });
                const data = await res.json();

                if (data.success) {
                    if (data.deleted || amount === 0) {
                        const updated = { ...this.calModal.daysData };
                        delete updated[day];
                        this.calModal.daysData = updated;
                    } else {
                        this.calModal.daysData = {
                            ...this.calModal.daysData,
                            [day]: { amount, obs },
                        };
                    }

                    const newEntityTotal = data.month_total;
                    this.calModal.monthTotal = newEntityTotal;

                    // Update entityAmountsGrid for this entity/item/mes
                    const g = { ...this.entityAmountsGrid };
                    if (!g[entityId]) g[entityId] = {};
                    g[entityId] = {
                        ...g[entityId],
                        [item]: { ...(g[entityId][item] ?? {}), [mes]: newEntityTotal },
                    };
                    if (newEntityTotal === 0 && g[entityId][item]) {
                        delete g[entityId][item][mes];
                    }
                    this.entityAmountsGrid = g;

                    // Recalculate the main grid cell (sum across all entities)
                    let crossTotal = 0;
                    for (const ent of this.entities) {
                        crossTotal += this.getEntityAmount(ent.id, item, mes);
                    }
                    this.grid = {
                        ...this.grid,
                        [item]: {
                            ...(this.grid[item] ?? {}),
                            [mes]: crossTotal > 0 ? { amount: crossTotal } : undefined,
                        },
                    };

                    this.calModal.selectedDay = null;
                }
            } finally {
                this.calModal.saving = false;
            }
        },

        // ── Item management ───────────────────────────────────────────────
        addItem() {
            const name = this.newItemName.trim();
            if (!name) return;
            if (this.items.includes(name)) { alert('Ya existe un ítem con ese nombre.'); return; }
            this.items = [...this.items, name].sort((a, b) => a.localeCompare(b, 'es'));
            this.grid  = { ...this.grid, [name]: {} };
            this.newItemName = '';
            this.addingItem  = false;
        },

        async deleteItem(item) {
            if (!confirm('Eliminar todos los registros de "' + item + '" para la gestión ' + this.gestion + '?')) return;
            await fetch(cfg.destroyUrl, {
                method:  'DELETE',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body:    JSON.stringify({ item, gestion: this.gestion }),
            });
            const newGrid = { ...this.grid };
            delete newGrid[item];
            this.grid  = newGrid;
            this.items = this.items.filter(i => i !== item);

            // Remove from entityAmountsGrid
            const eg = { ...this.entityAmountsGrid };
            for (const eid of Object.keys(eg)) {
                if (eg[eid][item]) {
                    eg[eid] = { ...eg[eid] };
                    delete eg[eid][item];
                }
            }
            this.entityAmountsGrid = eg;
        },

        startRename(item) {
            this.renamingItem = item;
            this.renameValue  = item;
            this.$nextTick(() => { const el = document.getElementById('rename-input-' + item); if (el) { el.focus(); el.select(); } });
        },

        cancelRename() { this.renamingItem = null; this.renameValue = ''; },

        async saveRename() {
            const oldItem = this.renamingItem;
            const newItem = this.renameValue.trim();
            if (!newItem || newItem === oldItem) { this.cancelRename(); return; }
            if (this.items.includes(newItem)) { alert('Ya existe un ítem con ese nombre.'); return; }
            this.renaming = true;
            try {
                const res  = await fetch(cfg.renameUrl, {
                    method:  'PATCH',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body:    JSON.stringify({ old_item: oldItem, new_item: newItem, gestion: this.gestion }),
                });
                const data = await res.json();
                if (data.success) {
                    // Update grid keys
                    const newGrid = {};
                    for (const [k, v] of Object.entries(this.grid)) newGrid[k === oldItem ? newItem : k] = v;
                    this.grid  = newGrid;
                    this.items = this.items.map(i => i === oldItem ? newItem : i).sort((a, b) => a.localeCompare(b, 'es'));

                    // Update entityAmountsGrid keys
                    const eg = {};
                    for (const [eid, entityData] of Object.entries(this.entityAmountsGrid)) {
                        eg[eid] = {};
                        for (const [k, v] of Object.entries(entityData)) {
                            eg[eid][k === oldItem ? newItem : k] = v;
                        }
                    }
                    this.entityAmountsGrid = eg;

                    this.cancelRename();
                }
            } finally { this.renaming = false; }
        },

        async importFromYear(fromGestion) {
            this.importing = true;
            this.importMsg = '';
            try {
                const res  = await fetch(cfg.itemsUrl + '?gestion=' + fromGestion, { headers: { 'Accept': 'application/json' } });
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
                    ? (added + ' ítem(s) importado(s) de ' + fromGestion + '.')
                    : ('Todos los ítems de ' + fromGestion + ' ya están presentes.');
                setTimeout(() => { this.importMsg = ''; }, 4000);
            } finally { this.importing = false; }
        },
    };
}
</script>
@endsection
