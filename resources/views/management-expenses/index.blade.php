@extends('layouts.app')

@section('content')
<script>
window._expConfig = {
    grid:               @json((object)$grid),
    itemCategories:     @json((object)$itemCategories),
    items:              @json($items),
    gestion:            {{ $gestion }},
    entities:           @json($entities->toArray()),
    entityAmountsGrid:  @json((object)$entityAmountsGrid),
    destroyUrl:         "{{ route('management-expenses.destroyItem') }}",
    renameUrl:          "{{ route('management-expenses.renameItem') }}",
    moveCategoryUrl:    "{{ route('management-expenses.moveItemCategory') }}",
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
        <div style="margin-bottom:1.5rem;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:1rem;">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Egresos por Gestión</h1>
                <p class="mt-1 text-sm text-gray-600">
                    Define las entidades, expande un mes y haz clic en una celda de entidad para registrar los egresos diarios.
                </p>
            </div>
            <form method="GET" action="{{ route('management-expenses.index') }}" style="display:flex;align-items:center;gap:0.5rem;">
                <label for="gestion" style="font-size:0.875rem;font-weight:500;color:#374151;">Gestión</label>
                <input id="gestion" name="gestion" type="number" min="2000" max="2100"
                       value="{{ $gestion }}"
                       style="width:5rem;padding:0.375rem 0.75rem;border:1px solid #d1d5db;border-radius:0.375rem;font-size:0.875rem;outline:none;"
                       onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#d1d5db'">
                <button type="submit" style="padding:0.375rem 1rem;background:#2563eb;color:#fff;border:none;border-radius:0.375rem;font-size:0.875rem;font-weight:500;cursor:pointer;"
                        onmouseover="this.style.background='#1d4ed8'" onmouseout="this.style.background='#2563eb'">Aplicar</button>
            </form>
        </div>

        {{-- Entity management bar --}}
        <div class="mb-4 flex flex-wrap items-center gap-2 p-3 bg-gray-50 rounded-lg border border-gray-200">
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Entidades:</span>

            <template x-for="ent in entities" :key="ent.id">
                <div class="inline-flex items-center gap-1 rounded-full text-xs font-medium transition-colors"
                     :class="renamingEntityId === ent.id
                         ? 'px-1.5 py-0.5 bg-blue-100 border border-blue-400'
                         : 'px-2.5 py-1 bg-blue-50 border border-blue-200 text-blue-800'">
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

            {{-- Entity filter --}}
            <template x-if="entities.length > 1">
                <div class="ml-auto flex items-center gap-2">
                    <span class="text-xs font-medium text-gray-500">Filtrar:</span>
                    <div style="position:relative;display:inline-flex;align-items:center;">
                        <select x-model="entityFilter"
                                :style="'appearance:none;-webkit-appearance:none;padding:0.25rem 1.75rem 0.25rem 0.75rem;font-size:0.75rem;border-width:1px;border-style:solid;border-radius:9999px;outline:none;cursor:pointer;line-height:1.25;' + (entityFilter ? 'border-color:#60a5fa;background:#eff6ff;color:#1e40af;font-weight:600;' : 'border-color:#d1d5db;background:#fff;color:#374151;')">
                            <option value="">Todas las entidades</option>
                            <template x-for="ent in entities" :key="ent.id">
                                <option :value="ent.id" x-text="ent.name"></option>
                            </template>
                        </select>
                        <svg style="position:absolute;right:0.5rem;top:50%;transform:translateY(-50%);width:0.7rem;height:0.7rem;color:#9ca3af;pointer-events:none;"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                    <button x-show="entityFilter" @click="entityFilter = ''"
                            class="text-blue-400 hover:text-red-500 transition-colors" title="Quitar filtro">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </template>
        </div>

        {{-- ══════════════════════════════════════════════════════════════ --}}
        {{-- TABLA 1: GASTOS OPERATIVOS                                    --}}
        {{-- ══════════════════════════════════════════════════════════════ --}}
        <div class="mb-8">
            {{-- Section header --}}
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.75rem;flex-wrap:wrap;gap:0.5rem;">
                <button @click="showOperativo = !showOperativo"
                        style="display:flex;align-items:center;gap:0.75rem;background:none;border:none;cursor:pointer;padding:0;text-align:left;">
                    <div style="width:4px;height:1.75rem;background:#dc2626;border-radius:2px;flex-shrink:0;"></div>
                    <h2 style="font-size:1.125rem;font-weight:700;color:#111827;">Gastos Operativos</h2>
                    <span x-text="'(' + itemsByCategory('operativo').length + ' ítems)'"
                          style="font-size:0.75rem;color:#6b7280;font-weight:500;"></span>
                    <svg :style="'width:0.875rem;height:0.875rem;color:#9ca3af;transition:transform 0.2s;flex-shrink:0;transform:' + (showOperativo ? 'rotate(0deg)' : 'rotate(-90deg)')"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div style="display:flex;align-items:center;gap:0.75rem;flex-wrap:wrap;">
                    {{-- Search --}}
                    <div style="position:relative;width:220px;">
                        <svg style="position:absolute;left:0.6rem;top:50%;transform:translateY(-50%);width:0.875rem;height:0.875rem;color:#9ca3af;pointer-events:none;"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input x-model="searchOperativo" type="text" placeholder="Buscar ítem..."
                               style="width:100%;padding:0.4rem 0.75rem 0.4rem 2rem;border:1px solid #d1d5db;border-radius:0.375rem;font-size:0.8rem;outline:none;box-sizing:border-box;"
                               onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#d1d5db'">
                    </div>
                    {{-- Add item --}}
                    <template x-if="!addingItemCategory || addingItemCategory !== 'operativo'">
                        <button @click="addingItemCategory = 'operativo'; $nextTick(() => $refs.newItemInputOperativo.focus())"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-green-600 text-white rounded-md text-xs font-medium hover:bg-green-700">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Agregar ítem
                        </button>
                    </template>
                    <template x-if="addingItemCategory === 'operativo'">
                        <div class="flex items-center gap-2">
                            <input x-ref="newItemInputOperativo" x-model="newItemName" type="text" placeholder="Nombre del ítem…"
                                   @keydown.enter.prevent="addItem('operativo')" @keydown.escape.prevent="addingItemCategory = null; newItemName = ''"
                                   class="px-3 py-1.5 border border-gray-300 rounded-md text-xs focus:outline-none focus:ring-blue-500 focus:border-blue-500 w-52">
                            <button @click="addItem('operativo')" class="px-3 py-1.5 bg-green-600 text-white rounded-md text-xs font-medium hover:bg-green-700">Agregar</button>
                            <button @click="addingItemCategory = null; newItemName = ''" class="px-3 py-1.5 bg-gray-200 text-gray-700 rounded-md text-xs font-medium hover:bg-gray-300">Cancelar</button>
                        </div>
                    </template>
                    {{-- Import --}}
                    <button @click="importFromYear(gestion - 1, 'operativo')" :disabled="importing"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-100 text-indigo-700 rounded-md text-xs font-medium hover:bg-indigo-200 disabled:opacity-60">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        <span x-text="'Copiar de ' + (gestion - 1)"></span>
                    </button>
                </div>
            </div>

            <div x-show="showOperativo" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="bg-white rounded-lg shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm border-collapse">
                        <thead>
                            <tr style="background:#1f2937; color:white; font-size:0.75rem;">
                                <th style="position:sticky; left:0; z-index:20; background:#1f2937; padding:0.75rem 1rem; text-align:left; font-size:0.75rem; font-weight:600; color:white; text-transform:uppercase; letter-spacing:0.05em; min-width:200px; border-right:1px solid #374151;">Ítem</th>
                                <template x-for="col in visibleColumns()" :key="col.key">
                                    <th :style="col.type === 'entity'
                                            ? 'background:#1d4ed8; color:white; padding:0.75rem 0.5rem; text-align:center; font-size:0.75rem; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; min-width:90px;'
                                            : (col.mes === currentMonth
                                                ? 'background:#b91c1c; color:white; padding:0.75rem 0.5rem; text-align:center; font-size:0.75rem; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; min-width:82px; box-shadow:inset 0 0 0 2px #f87171; cursor:pointer;'
                                                : 'background:#1f2937; color:white; padding:0.75rem 0.5rem; text-align:center; font-size:0.75rem; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; min-width:82px; cursor:pointer;')">
                                        <template x-if="col.type === 'month'">
                                            <button @click="toggleMonth(col.mes)"
                                                    style="width:100%; display:flex; flex-direction:column; align-items:center; gap:2px; padding:2px 0; background:none; border:none; cursor:pointer; color:white;"
                                                    :title="col.expanded ? 'Contraer' : 'Expandir para ver entidades'">
                                                <span style="display:flex; align-items:center; gap:4px;">
                                                    <span x-text="col.short"></span>
                                                    <svg x-show="!col.expanded" style="width:0.75rem;height:0.75rem;opacity:0.5;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                    </svg>
                                                    <svg x-show="col.expanded" style="width:0.75rem;height:0.75rem;color:#93c5fd;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                                    </svg>
                                                </span>
                                                <span x-show="col.expanded" style="font-size:0.625rem; font-weight:400; color:#9ca3af; text-transform:none;">Total</span>
                                            </button>
                                        </template>
                                        <template x-if="col.type === 'entity'">
                                            <span x-text="col.entityName" style="font-size:0.75rem; font-weight:600; color:#bfdbfe; text-transform:uppercase; letter-spacing:0.05em;"></span>
                                        </template>
                                    </th>
                                </template>
                                <th style="padding:0.75rem 1rem; text-align:right; font-size:0.75rem; font-weight:600; color:white; text-transform:uppercase; letter-spacing:0.05em; min-width:110px; border-left:1px solid #374151;">Total ítem</th>
                                <th style="padding:0.75rem 0.75rem; min-width:80px; text-align:center; font-size:0.75rem; font-weight:600; color:#9ca3af; text-transform:uppercase; letter-spacing:0.05em;">Mover</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <template x-if="itemsByCategory('operativo').length === 0">
                                <tr><td :colspan="3 + visibleColumns().length" class="px-6 py-10 text-center text-sm text-gray-400">
                                    No hay ítems en Gastos Operativos para la gestión <span x-text="gestion"></span>.
                                </td></tr>
                            </template>
                            <template x-if="filteredItemsByCategory('operativo').length === 0 && itemsByCategory('operativo').length > 0">
                                <tr><td :colspan="3 + visibleColumns().length" class="px-6 py-6 text-center text-sm text-gray-400">
                                    No se encontraron ítems para "<span x-text="searchOperativo"></span>".
                                </td></tr>
                            </template>
                            <template x-for="item in filteredItemsByCategory('operativo')" :key="item">
                                <tr class="hover:bg-red-50/30 transition-colors">
                                    <td class="sticky left-0 z-10 bg-white px-4 py-2 font-medium text-gray-900 border-r border-gray-100">
                                        <template x-if="renamingItem !== item">
                                            <div style="display:flex;align-items:center;gap:6px;">
                                                <span x-text="item"></span>
                                                <button @click="startRename(item)" title="Renombrar"
                                                        style="flex-shrink:0;background:none;border:none;padding:2px;cursor:pointer;color:#9ca3af;"
                                                        onmouseover="this.style.color='#2563eb'" onmouseout="this.style.color='#9ca3af'">
                                                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                    <template x-for="col in visibleColumns()" :key="col.key">
                                        <td :class="{
                                                'px-1 py-1 text-center': col.type === 'month',
                                                'px-1 py-1 text-center bg-blue-50/40': col.type === 'entity',
                                            }">
                                            <template x-if="col.type === 'month'">
                                                <button @click="toggleMonth(col.mes)"
                                                        class="w-full min-h-[2.25rem] rounded px-1 py-1 text-xs transition-colors"
                                                        :class="getCellAmount(item, col.mes) > 0
                                                            ? 'text-red-700 font-semibold hover:bg-red-50'
                                                            : 'text-gray-300 hover:bg-gray-50'">
                                                    <span x-text="getCellAmount(item, col.mes) > 0 ? fmtAmt(getCellAmount(item, col.mes)) : '–'"></span>
                                                </button>
                                            </template>
                                            <template x-if="col.type === 'entity'">
                                                <button @click="openEntityCalendar(col.entityId, item, col.mes, 'operativo')"
                                                        class="w-full min-h-[2.25rem] rounded px-1 py-1 text-xs transition-colors"
                                                        :class="getEntityAmount(col.entityId, item, col.mes) > 0
                                                            ? 'text-blue-700 font-semibold hover:bg-blue-100'
                                                            : 'text-gray-300 hover:bg-blue-50 hover:text-gray-400'">
                                                    <span x-text="getEntityAmount(col.entityId, item, col.mes) > 0 ? fmtAmt(getEntityAmount(col.entityId, item, col.mes)) : '–'"></span>
                                                </button>
                                            </template>
                                        </td>
                                    </template>
                                    <td class="px-4 py-2 text-right font-bold text-red-700 border-l border-gray-100">
                                        Bs. <span x-text="fmtAmt(getItemTotal(item))"></span>
                                    </td>
                                    <td class="px-3 py-2 text-center">
                                        <div style="display:flex;align-items:center;justify-content:center;gap:4px;">
                                            <button @click="moveCategory(item, 'otro')" title="Mover a Otros Egresos"
                                                    style="background:none;border:1px solid #d1d5db;border-radius:4px;padding:3px 7px;font-size:0.65rem;cursor:pointer;color:#6b7280;white-space:nowrap;display:flex;align-items:center;gap:3px;"
                                                    onmouseover="this.style.borderColor='#f59e0b';this.style.color='#b45309';this.style.background='#fffbeb'"
                                                    onmouseout="this.style.borderColor='#d1d5db';this.style.color='#6b7280';this.style.background='none'">
                                                <svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                                Otros
                                            </button>
                                            <button @click="deleteItem(item)" title="Eliminar ítem"
                                                    style="background:none;border:none;padding:3px;cursor:pointer;color:#d1d5db;"
                                                    onmouseover="this.style.color='#dc2626'" onmouseout="this.style.color='#d1d5db'">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </div>
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
                                            <span>Bs. <span x-text="fmtAmt(getMonthTotalByCategory(col.mes, 'operativo'))"></span></span>
                                        </template>
                                        <template x-if="col.type === 'entity'">
                                            <span x-text="fmtAmt(getEntityMonthTotalByCategory(col.entityId, col.mes, 'operativo'))"></span>
                                        </template>
                                    </td>
                                </template>
                                <td class="px-4 py-3 text-right text-sm font-bold text-red-800 border-l border-gray-200">
                                    Bs. <span x-text="fmtAmt(getGrandTotalByCategory('operativo'))"></span>
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════════ --}}
        {{-- TABLA 2: OTROS EGRESOS                                        --}}
        {{-- ══════════════════════════════════════════════════════════════ --}}
        <div class="mb-8">
            {{-- Section header --}}
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.75rem;flex-wrap:wrap;gap:0.5rem;">
                <button @click="showOtro = !showOtro"
                        style="display:flex;align-items:center;gap:0.75rem;background:none;border:none;cursor:pointer;padding:0;text-align:left;">
                    <div style="width:4px;height:1.75rem;background:#f59e0b;border-radius:2px;flex-shrink:0;"></div>
                    <h2 style="font-size:1.125rem;font-weight:700;color:#111827;">Otros Egresos</h2>
                    <span x-text="'(' + itemsByCategory('otro').length + ' ítems)'"
                          style="font-size:0.75rem;color:#6b7280;font-weight:500;"></span>
                    <svg :style="'width:0.875rem;height:0.875rem;color:#9ca3af;transition:transform 0.2s;flex-shrink:0;transform:' + (showOtro ? 'rotate(0deg)' : 'rotate(-90deg)')"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div style="display:flex;align-items:center;gap:0.75rem;flex-wrap:wrap;">
                    {{-- Search --}}
                    <div style="position:relative;width:220px;">
                        <svg style="position:absolute;left:0.6rem;top:50%;transform:translateY(-50%);width:0.875rem;height:0.875rem;color:#9ca3af;pointer-events:none;"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input x-model="searchOtro" type="text" placeholder="Buscar ítem..."
                               style="width:100%;padding:0.4rem 0.75rem 0.4rem 2rem;border:1px solid #d1d5db;border-radius:0.375rem;font-size:0.8rem;outline:none;box-sizing:border-box;"
                               onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#d1d5db'">
                    </div>
                    {{-- Add item --}}
                    <template x-if="!addingItemCategory || addingItemCategory !== 'otro'">
                        <button @click="addingItemCategory = 'otro'; $nextTick(() => $refs.newItemInputOtro.focus())"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-green-600 text-white rounded-md text-xs font-medium hover:bg-green-700">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Agregar ítem
                        </button>
                    </template>
                    <template x-if="addingItemCategory === 'otro'">
                        <div class="flex items-center gap-2">
                            <input x-ref="newItemInputOtro" x-model="newItemName" type="text" placeholder="Nombre del ítem…"
                                   @keydown.enter.prevent="addItem('otro')" @keydown.escape.prevent="addingItemCategory = null; newItemName = ''"
                                   class="px-3 py-1.5 border border-gray-300 rounded-md text-xs focus:outline-none focus:ring-blue-500 focus:border-blue-500 w-52">
                            <button @click="addItem('otro')" class="px-3 py-1.5 bg-green-600 text-white rounded-md text-xs font-medium hover:bg-green-700">Agregar</button>
                            <button @click="addingItemCategory = null; newItemName = ''" class="px-3 py-1.5 bg-gray-200 text-gray-700 rounded-md text-xs font-medium hover:bg-gray-300">Cancelar</button>
                        </div>
                    </template>
                    {{-- Import --}}
                    <button @click="importFromYear(gestion - 1, 'otro')" :disabled="importing"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-100 text-indigo-700 rounded-md text-xs font-medium hover:bg-indigo-200 disabled:opacity-60">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        <span x-text="'Copiar de ' + (gestion - 1)"></span>
                    </button>
                </div>
            </div>

            <div x-show="showOtro" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="bg-white rounded-lg shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm border-collapse">
                        <thead>
                            <tr style="background:#374151; color:white; font-size:0.75rem;">
                                <th style="position:sticky; left:0; z-index:20; background:#374151; padding:0.75rem 1rem; text-align:left; font-size:0.75rem; font-weight:600; color:white; text-transform:uppercase; letter-spacing:0.05em; min-width:200px; border-right:1px solid #4b5563;">Ítem</th>
                                <template x-for="col in visibleColumns()" :key="col.key">
                                    <th :style="col.type === 'entity'
                                            ? 'background:#1d4ed8; color:white; padding:0.75rem 0.5rem; text-align:center; font-size:0.75rem; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; min-width:90px;'
                                            : (col.mes === currentMonth
                                                ? 'background:#d97706; color:white; padding:0.75rem 0.5rem; text-align:center; font-size:0.75rem; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; min-width:82px; box-shadow:inset 0 0 0 2px #fbbf24; cursor:pointer;'
                                                : 'background:#374151; color:white; padding:0.75rem 0.5rem; text-align:center; font-size:0.75rem; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; min-width:82px; cursor:pointer;')">
                                        <template x-if="col.type === 'month'">
                                            <button @click="toggleMonth(col.mes)"
                                                    style="width:100%; display:flex; flex-direction:column; align-items:center; gap:2px; padding:2px 0; background:none; border:none; cursor:pointer; color:white;">
                                                <span style="display:flex; align-items:center; gap:4px;">
                                                    <span x-text="col.short"></span>
                                                    <svg x-show="!col.expanded" style="width:0.75rem;height:0.75rem;opacity:0.5;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                    </svg>
                                                    <svg x-show="col.expanded" style="width:0.75rem;height:0.75rem;color:#93c5fd;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                                    </svg>
                                                </span>
                                                <span x-show="col.expanded" style="font-size:0.625rem; font-weight:400; color:#9ca3af; text-transform:none;">Total</span>
                                            </button>
                                        </template>
                                        <template x-if="col.type === 'entity'">
                                            <span x-text="col.entityName" style="font-size:0.75rem; font-weight:600; color:#bfdbfe; text-transform:uppercase; letter-spacing:0.05em;"></span>
                                        </template>
                                    </th>
                                </template>
                                <th style="padding:0.75rem 1rem; text-align:right; font-size:0.75rem; font-weight:600; color:white; text-transform:uppercase; letter-spacing:0.05em; min-width:110px; border-left:1px solid #4b5563;">Total ítem</th>
                                <th style="padding:0.75rem 0.75rem; min-width:80px; text-align:center; font-size:0.75rem; font-weight:600; color:#9ca3af; text-transform:uppercase; letter-spacing:0.05em;">Mover</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <template x-if="itemsByCategory('otro').length === 0">
                                <tr><td :colspan="3 + visibleColumns().length" class="px-6 py-10 text-center text-sm text-gray-400">
                                    No hay ítems en Otros Egresos para la gestión <span x-text="gestion"></span>.
                                </td></tr>
                            </template>
                            <template x-if="filteredItemsByCategory('otro').length === 0 && itemsByCategory('otro').length > 0">
                                <tr><td :colspan="3 + visibleColumns().length" class="px-6 py-6 text-center text-sm text-gray-400">
                                    No se encontraron ítems para "<span x-text="searchOtro"></span>".
                                </td></tr>
                            </template>
                            <template x-for="item in filteredItemsByCategory('otro')" :key="item">
                                <tr class="hover:bg-amber-50/30 transition-colors">
                                    <td class="sticky left-0 z-10 bg-white px-4 py-2 font-medium text-gray-900 border-r border-gray-100">
                                        <template x-if="renamingItem !== item">
                                            <div style="display:flex;align-items:center;gap:6px;">
                                                <span x-text="item"></span>
                                                <button @click="startRename(item)" title="Renombrar"
                                                        style="flex-shrink:0;background:none;border:none;padding:2px;cursor:pointer;color:#9ca3af;"
                                                        onmouseover="this.style.color='#2563eb'" onmouseout="this.style.color='#9ca3af'">
                                                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                    <template x-for="col in visibleColumns()" :key="col.key">
                                        <td :class="{
                                                'px-1 py-1 text-center': col.type === 'month',
                                                'px-1 py-1 text-center bg-blue-50/40': col.type === 'entity',
                                            }">
                                            <template x-if="col.type === 'month'">
                                                <button @click="toggleMonth(col.mes)"
                                                        class="w-full min-h-[2.25rem] rounded px-1 py-1 text-xs transition-colors"
                                                        :class="getCellAmount(item, col.mes) > 0
                                                            ? 'text-amber-700 font-semibold hover:bg-amber-50'
                                                            : 'text-gray-300 hover:bg-gray-50'">
                                                    <span x-text="getCellAmount(item, col.mes) > 0 ? fmtAmt(getCellAmount(item, col.mes)) : '–'"></span>
                                                </button>
                                            </template>
                                            <template x-if="col.type === 'entity'">
                                                <button @click="openEntityCalendar(col.entityId, item, col.mes, 'otro')"
                                                        class="w-full min-h-[2.25rem] rounded px-1 py-1 text-xs transition-colors"
                                                        :class="getEntityAmount(col.entityId, item, col.mes) > 0
                                                            ? 'text-blue-700 font-semibold hover:bg-blue-100'
                                                            : 'text-gray-300 hover:bg-blue-50 hover:text-gray-400'">
                                                    <span x-text="getEntityAmount(col.entityId, item, col.mes) > 0 ? fmtAmt(getEntityAmount(col.entityId, item, col.mes)) : '–'"></span>
                                                </button>
                                            </template>
                                        </td>
                                    </template>
                                    <td class="px-4 py-2 text-right font-bold text-amber-700 border-l border-gray-100">
                                        Bs. <span x-text="fmtAmt(getItemTotal(item))"></span>
                                    </td>
                                    <td class="px-3 py-2 text-center">
                                        <div style="display:flex;align-items:center;justify-content:center;gap:4px;">
                                            <button @click="moveCategory(item, 'operativo')" title="Mover a Gastos Operativos"
                                                    style="background:none;border:1px solid #d1d5db;border-radius:4px;padding:3px 7px;font-size:0.65rem;cursor:pointer;color:#6b7280;white-space:nowrap;display:flex;align-items:center;gap:3px;"
                                                    onmouseover="this.style.borderColor='#dc2626';this.style.color='#b91c1c';this.style.background='#fef2f2'"
                                                    onmouseout="this.style.borderColor='#d1d5db';this.style.color='#6b7280';this.style.background='none'">
                                                <svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                                Operativo
                                            </button>
                                            <button @click="deleteItem(item)" title="Eliminar ítem"
                                                    style="background:none;border:none;padding:3px;cursor:pointer;color:#d1d5db;"
                                                    onmouseover="this.style.color='#dc2626'" onmouseout="this.style.color='#d1d5db'">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </div>
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
                                            'bg-amber-50': col.type === 'month' && col.mes === currentMonth,
                                            'text-blue-700 bg-blue-50/60': col.type === 'entity',
                                        }">
                                        <template x-if="col.type === 'month'">
                                            <span>Bs. <span x-text="fmtAmt(getMonthTotalByCategory(col.mes, 'otro'))"></span></span>
                                        </template>
                                        <template x-if="col.type === 'entity'">
                                            <span x-text="fmtAmt(getEntityMonthTotalByCategory(col.entityId, col.mes, 'otro'))"></span>
                                        </template>
                                    </td>
                                </template>
                                <td class="px-4 py-3 text-right text-sm font-bold text-amber-800 border-l border-gray-200">
                                    Bs. <span x-text="fmtAmt(getGrandTotalByCategory('otro'))"></span>
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        {{-- Grand total bar --}}
        <div style="display:flex;align-items:center;justify-content:flex-end;padding:0.75rem 1.25rem;background:#1f2937;border-radius:0.5rem;margin-bottom:2rem;">
            <span style="font-size:0.875rem;color:#9ca3af;margin-right:1rem;">Total general gestión <span x-text="gestion"></span>:</span>
            <span style="font-size:1.25rem;font-weight:800;color:#f87171;">
                Bs. <span x-text="fmtAmt(getGrandTotalByCategory('operativo') + getGrandTotalByCategory('otro'))"></span>
            </span>
        </div>

    </div>

    {{-- ══════════════════════════════════════════════════════════════ --}}
    {{-- MODAL CALENDARIO                                               --}}
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

            <div x-show="calModal.loading" style="display:flex;align-items:center;justify-content:center;padding:4rem 0;">
                <svg style="width:2rem;height:2rem;color:#3b82f6;animation:spin 1s linear infinite;" fill="none" viewBox="0 0 24 24">
                    <circle style="opacity:0.25;" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path style="opacity:0.75;" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 22 6.477 22 12h-4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                </svg>
            </div>

            <div x-show="!calModal.loading" style="padding:1.25rem;">
                <div style="display:grid;grid-template-columns:repeat(7,1fr);gap:4px;margin-bottom:4px;">
                    <template x-for="d in ['Lun','Mar','Mié','Jue','Vie','Sáb','Dom']" :key="d">
                        <div style="text-align:center;font-size:0.65rem;font-weight:700;color:#9ca3af;padding:4px 0;text-transform:uppercase;letter-spacing:0.04em;" x-text="d"></div>
                    </template>
                </div>
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

                <div style="display:flex;align-items:center;justify-content:space-between;margin-top:1rem;padding-top:0.75rem;border-top:1px solid #e5e7eb;">
                    <span style="font-size:0.875rem;color:#6b7280;">Total del mes (<span x-text="calModal.entityName" style="font-weight:600;"></span>)</span>
                    <span style="font-size:1.125rem;font-weight:700;color:#1d4ed8;">
                        Bs. <span x-text="fmtAmt(calModal.monthTotal)"></span>
                    </span>
                </div>

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
        grid:           cfg.grid || {},
        itemCategories: cfg.itemCategories || {},
        items:          cfg.items || [],
        gestion:        cfg.gestion || new Date().getFullYear(),

        showOperativo: true,
        showOtro:      true,

        entities:          cfg.entities || [],
        entityAmountsGrid: cfg.entityAmountsGrid || {},
        expandedMonths:    [],
        addingEntity:      false,
        newEntityName:     '',
        renamingEntityId:  null,
        renameEntityValue: '',
        renamingEntity:    false,
        entityFilter:      '',

        monthShort: ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'],
        monthFull:  ['Enero','Febrero','Marzo','Abril','Mayo','Junio',
                     'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'],
        currentMonth: cfg.currentMonth || new Date().getMonth() + 1,

        searchOperativo: '',
        searchOtro:      '',
        newItemName:     '',
        addingItemCategory: null, // 'operativo' | 'otro' | null
        importing:       false,
        renamingItem:    null,
        renameValue:     '',
        renaming:        false,

        calModal: {
            open: false, loading: false, saving: false,
            item: null, mes: null, entityId: null, entityName: '',
            category: 'operativo',
            daysInMonth: [], startOffset: [], daysData: {},
            monthTotal: 0, selectedDay: null,
        },

        // ── Category helpers ──────────────────────────────────────────────
        itemsByCategory(cat) {
            return this.items.filter(i => (this.itemCategories[i] ?? 'operativo') === cat);
        },

        filteredItemsByCategory(cat) {
            const q = cat === 'operativo'
                ? this.searchOperativo.trim().toLocaleLowerCase()
                : this.searchOtro.trim().toLocaleLowerCase();
            return this.itemsByCategory(cat).filter(i => !q || i.toLocaleLowerCase().includes(q));
        },

        // ── Column layout ─────────────────────────────────────────────────
        visibleColumns() {
            const cols = [];
            for (let mes = 1; mes <= 12; mes++) {
                const expanded = this.expandedMonths.includes(mes);
                cols.push({ key: `m${mes}`, type: 'month', mes, short: this.monthShort[mes - 1], expanded });
                if (expanded) {
                    for (const ent of this.entities) {
                        cols.push({ key: `m${mes}-e${ent.id}`, type: 'entity', mes, entityId: ent.id, entityName: ent.name });
                    }
                }
            }
            return cols;
        },

        toggleMonth(mes) {
            this.expandedMonths = this.expandedMonths.includes(mes)
                ? this.expandedMonths.filter(m => m !== mes)
                : [...this.expandedMonths, mes];
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

        getMonthTotalByCategory(mes, cat) {
            return this.itemsByCategory(cat).reduce((t, item) => t + this.getCellAmount(item, mes), 0);
        },

        getEntityMonthTotalByCategory(entityId, mes, cat) {
            return this.itemsByCategory(cat).reduce((t, item) => t + this.getEntityAmount(entityId, item, mes), 0);
        },

        getGrandTotalByCategory(cat) {
            return this.itemsByCategory(cat).reduce((t, item) => t + this.getItemTotal(item), 0);
        },

        // ── Move item between categories ──────────────────────────────────
        async moveCategory(item, newCat) {
            const res = await fetch(cfg.moveCategoryUrl, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept':       'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ item, gestion: this.gestion, category: newCat }),
            });
            const data = await res.json();
            if (data.success) {
                this.itemCategories = { ...this.itemCategories, [item]: newCat };
            }
        },

        // ── Entity management ─────────────────────────────────────────────
        async addEntity() {
            const name = this.newEntityName.trim();
            if (!name) return;
            if (this.entities.some(e => e.name === name)) { alert('Ya existe una entidad con ese nombre.'); return; }
            const res = await fetch(cfg.entityStoreUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
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
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            });
            const data = await res.json();
            if (data.success) {
                this.entities = this.entities.filter(e => e.id !== entityId);
                if (String(this.entityFilter) === String(entityId)) this.entityFilter = '';
                const g = { ...this.entityAmountsGrid };
                delete g[entityId];
                this.entityAmountsGrid = g;
                this._rebuildGrid();
            }
        },

        startRenameEntity(entityId, currentName) {
            this.renamingEntityId  = entityId;
            this.renameEntityValue = currentName;
            this.$nextTick(() => { const el = document.getElementById('rename-entity-' + entityId); if (el) { el.focus(); el.select(); } });
        },

        cancelRenameEntity() { this.renamingEntityId = null; this.renameEntityValue = ''; },

        async saveRenameEntity(entityId) {
            const newName = this.renameEntityValue.trim();
            const ent = this.entities.find(e => e.id === entityId);
            if (!newName || newName === ent?.name) { this.cancelRenameEntity(); return; }
            this.renamingEntity = true;
            try {
                const res  = await fetch(cfg.entityRenameBase + '/' + entityId, {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({ name: newName }),
                });
                const data = await res.json();
                if (data.success) {
                    this.entities = this.entities.map(e => e.id === entityId ? { ...e, name: data.name } : e);
                    this.cancelRenameEntity();
                } else {
                    alert(data.message ?? 'No se pudo renombrar la entidad.');
                }
            } finally { this.renamingEntity = false; }
        },

        _rebuildGrid() {
            const g = {};
            for (const item of this.items) {
                g[item] = {};
                for (let m = 1; m <= 12; m++) {
                    let total = 0;
                    for (const ent of this.entities) total += this.getEntityAmount(ent.id, item, m);
                    if (total > 0) g[item][m] = { amount: total };
                }
            }
            this.grid = g;
        },

        // ── Calendar modal ────────────────────────────────────────────────
        async openEntityCalendar(entityId, item, mes, category) {
            const ent      = this.entities.find(e => e.id === entityId);
            const dim      = new Date(this.gestion, mes, 0).getDate();
            const firstDay = new Date(this.gestion, mes - 1, 1).getDay();
            const offset   = firstDay === 0 ? 6 : firstDay - 1;

            this.calModal = {
                open: true, loading: true, saving: false,
                item, mes, entityId, category: category || 'operativo',
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

        closeCalendar() { this.calModal.open = false; this.calModal.selectedDay = null; },
        getDayAmount(day) { return parseFloat(this.calModal.daysData[day]?.amount ?? 0); },
        getDayObs(day)    { return this.calModal.daysData[day]?.obs ?? ''; },

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

        cancelDayEdit() { this.calModal.selectedDay = null; },

        async saveDay(day) {
            if (this.calModal.saving || day === null) return;
            const amtEl  = document.getElementById('day-edit-amount');
            const obsEl  = document.getElementById('day-edit-obs');
            const amount = parseFloat(amtEl?.value) || 0;
            const obs    = obsEl?.value?.trim() ?? '';
            const { entityId, item, mes, category } = this.calModal;

            this.calModal.saving = true;
            try {
                const res = await fetch(cfg.entityAmountUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({ entity_id: entityId, item, mes, gestion: this.gestion, dia: day, amount, observation: obs || null, category }),
                });
                const data = await res.json();
                if (data.success) {
                    if (data.deleted || amount === 0) {
                        const updated = { ...this.calModal.daysData };
                        delete updated[day];
                        this.calModal.daysData = updated;
                    } else {
                        this.calModal.daysData = { ...this.calModal.daysData, [day]: { amount, obs } };
                    }
                    const newEntityTotal = data.month_total;
                    this.calModal.monthTotal = newEntityTotal;

                    const g = { ...this.entityAmountsGrid };
                    if (!g[entityId]) g[entityId] = {};
                    g[entityId] = { ...g[entityId], [item]: { ...(g[entityId][item] ?? {}), [mes]: newEntityTotal } };
                    if (newEntityTotal === 0 && g[entityId][item]) delete g[entityId][item][mes];
                    this.entityAmountsGrid = g;

                    let crossTotal = 0;
                    for (const ent of this.entities) crossTotal += this.getEntityAmount(ent.id, item, mes);
                    this.grid = { ...this.grid, [item]: { ...(this.grid[item] ?? {}), [mes]: crossTotal > 0 ? { amount: crossTotal } : undefined } };
                    this.calModal.selectedDay = null;
                }
            } finally { this.calModal.saving = false; }
        },

        // ── Item management ───────────────────────────────────────────────
        addItem(category) {
            const name = this.newItemName.trim();
            if (!name) return;
            if (this.items.includes(name)) { alert('Ya existe un ítem con ese nombre.'); return; }
            this.items = [...this.items, name].sort((a, b) => a.localeCompare(b, 'es'));
            this.grid  = { ...this.grid, [name]: {} };
            this.itemCategories = { ...this.itemCategories, [name]: category };
            this.newItemName       = '';
            this.addingItemCategory = null;
        },

        async deleteItem(item) {
            if (!confirm('Eliminar todos los registros de "' + item + '" para la gestión ' + this.gestion + '?')) return;
            await fetch(cfg.destroyUrl, {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ item, gestion: this.gestion }),
            });
            const newGrid = { ...this.grid };
            delete newGrid[item];
            this.grid  = newGrid;
            this.items = this.items.filter(i => i !== item);
            const cats = { ...this.itemCategories };
            delete cats[item];
            this.itemCategories = cats;

            const eg = { ...this.entityAmountsGrid };
            for (const eid of Object.keys(eg)) {
                if (eg[eid][item]) { eg[eid] = { ...eg[eid] }; delete eg[eid][item]; }
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
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({ old_item: oldItem, new_item: newItem, gestion: this.gestion }),
                });
                const data = await res.json();
                if (data.success) {
                    const newGrid = {};
                    for (const [k, v] of Object.entries(this.grid)) newGrid[k === oldItem ? newItem : k] = v;
                    this.grid  = newGrid;
                    this.items = this.items.map(i => i === oldItem ? newItem : i).sort((a, b) => a.localeCompare(b, 'es'));

                    const cats = { ...this.itemCategories };
                    cats[newItem] = cats[oldItem];
                    delete cats[oldItem];
                    this.itemCategories = cats;

                    const eg = {};
                    for (const [eid, entityData] of Object.entries(this.entityAmountsGrid)) {
                        eg[eid] = {};
                        for (const [k, v] of Object.entries(entityData)) eg[eid][k === oldItem ? newItem : k] = v;
                    }
                    this.entityAmountsGrid = eg;
                    this.cancelRename();
                }
            } finally { this.renaming = false; }
        },

        async importFromYear(fromGestion, category) {
            this.importing = true;
            try {
                const res  = await fetch(cfg.itemsUrl + '?gestion=' + fromGestion, { headers: { 'Accept': 'application/json' } });
                const data = await res.json();
                let added = 0;
                for (const itemName of (data.items ?? [])) {
                    if (!this.items.includes(itemName)) {
                        this.items = [...this.items, itemName].sort((a, b) => a.localeCompare(b, 'es'));
                        this.grid  = { ...this.grid, [itemName]: {} };
                        this.itemCategories = { ...this.itemCategories, [itemName]: category };
                        added++;
                    }
                }
                alert(added > 0
                    ? (added + ' ítem(s) importado(s) de ' + fromGestion + ' a ' + (category === 'operativo' ? 'Gastos Operativos' : 'Otros Egresos') + '.')
                    : ('Todos los ítems de ' + fromGestion + ' ya están presentes.'));
            } finally { this.importing = false; }
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
    };
}
</script>
@endsection
