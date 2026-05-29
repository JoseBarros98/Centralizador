<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">Asignación Nominal</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Cabecera -->
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Asignación Nominal</h1>
                    <p class="mt-1 text-sm text-gray-500">Seguimiento mensual de cuotas por participante</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('payment-plans.index') }}"
                       class="inline-flex items-center px-3 py-2 text-sm text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                        <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        Planes de Pago
                    </a>
                    @if($program)
                    <a href="{{ route('participant-quotas.index', $program->id) }}"
                       class="inline-flex items-center px-3 py-2 text-sm text-indigo-700 bg-indigo-50 border border-indigo-200 rounded-lg hover:bg-indigo-100 transition">
                        <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Configurar Cuotas
                    </a>
                    @endif
                </div>
            </div>

            <!-- Filtros: programa + navegación mes -->
            <div class="bg-white rounded-xl shadow-sm p-4 flex flex-col sm:flex-row sm:items-end gap-4"
                 x-data="programSearch({
                    programs: {{ json_encode($programs->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'status' => $p->status])) }},
                    selectedId: {{ $program?->id ?? 'null' }},
                    selectedName: {{ $program ? json_encode($program->name) : "''" }}
                 })">

                <!-- Buscador de programa -->
                <form method="GET" action="{{ route('nominal-assignments.index') }}" id="programForm" class="flex-1 min-w-0 relative" @submit.prevent>
                    <input type="hidden" name="mes" value="{{ $mes }}">
                    <input type="hidden" name="gestion" value="{{ $gestion }}">
                    <input type="hidden" name="program_id" :value="selectedId" id="programIdInput">

                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Programa</label>
                    <div class="relative">
                        <input
                            type="text"
                            x-model="search"
                            @input="filter()"
                            @focus="open = true"
                            @keydown.escape="open = false"
                            @keydown.enter.prevent="selectFirst()"
                            placeholder="Buscar programa..."
                            autocomplete="off"
                            class="w-full border border-gray-300 rounded-lg pl-9 pr-8 py-2 text-sm focus:ring-2 focus:ring-gray-800 focus:border-gray-800"
                        >
                        <svg class="absolute left-2.5 top-2.5 h-4 w-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <button x-show="selectedId" @click="clearSelection()" type="button"
                            class="absolute right-2.5 top-2 text-gray-400 hover:text-gray-600">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>

                        <!-- Dropdown resultados -->
                        <div x-show="open && filtered.length > 0"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 -translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             @click.outside="open = false"
                             class="absolute z-30 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-64 overflow-y-auto">
                            <template x-for="prog in filtered" :key="prog.id">
                                <button type="button"
                                    @click="selectProgram(prog)"
                                    :class="prog.id == selectedId ? 'bg-indigo-50' : 'hover:bg-gray-50'"
                                    class="w-full text-left px-4 py-2.5 border-b border-gray-50 last:border-0 transition">
                                    <span class="block text-sm font-medium text-gray-900" x-text="prog.name"></span>
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium mt-0.5"
                                          :class="prog.status === 'Inscripciones' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700'"
                                          x-text="prog.status"></span>
                                </button>
                            </template>
                        </div>

                        <!-- Sin resultados -->
                        <div x-show="open && search.length > 0 && filtered.length === 0"
                             @click.outside="open = false"
                             class="absolute z-30 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg px-4 py-3 text-sm text-gray-400">
                            Sin resultados para "<span x-text="search"></span>"
                        </div>
                    </div>

                    <!-- Badge del programa seleccionado -->
                    <div x-show="selectedId && !open" class="mt-1.5 text-xs text-indigo-600 font-medium" x-text="selectedName"></div>
                </form>

                <!-- Navegación mes/año -->
                <div class="flex-shrink-0 flex items-center gap-2">
                    <a href="{{ route('nominal-assignments.index', ['program_id' => $program?->id, 'mes' => $prevMes, 'gestion' => $prevGestion]) }}"
                       class="p-2 text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </a>
                    <div class="text-center min-w-[130px]">
                        <div class="text-base font-bold text-gray-900">{{ $mesesNombres[$mes] }}</div>
                        <div class="text-sm text-gray-500">{{ $gestion }}</div>
                    </div>
                    <a href="{{ route('nominal-assignments.index', ['program_id' => $program?->id, 'mes' => $nextMes, 'gestion' => $nextGestion]) }}"
                       class="p-2 text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>

            @if(!$program)
            <div class="bg-white rounded-xl shadow-sm p-12 text-center">
                <svg class="h-12 w-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <p class="text-gray-500 text-sm">Busca y selecciona un programa para ver la asignación nominal</p>
            </div>

            @elseif(!$assignment)
            <div class="bg-white rounded-xl shadow-sm p-12 text-center">
                <svg class="h-12 w-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-gray-900 font-semibold mb-1">No hay asignación para {{ $mesesNombres[$mes] }} {{ $gestion }}</p>
                <p class="text-gray-500 text-sm mb-6">Programa: <strong>{{ $program->name }}</strong></p>
                @can('program_allocation.create')
                <button onclick="generateAssignment()"
                    class="inline-flex items-center px-5 py-2.5 bg-gray-800 text-white text-sm font-semibold rounded-lg hover:bg-gray-700 transition">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Generar Asignación
                </button>
                @endcan
            </div>

            @else

            <!-- Info de la asignación -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white rounded-xl shadow-sm px-5 py-4">
                <div class="flex flex-wrap items-center gap-3">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
                        {{ $assignment->estado === 'finalizado' ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' }}">
                        {{ ucfirst($assignment->estado) }}
                    </span>
                    <span class="text-sm text-gray-500">
                        Generado por <strong>{{ $assignment->generator?->name ?? 'Sistema' }}</strong>
                        — {{ $assignment->created_at->format('d/m/Y H:i') }}
                        — {{ $details->count() }} participantes
                    </span>

                    <!-- Responsable de cobros -->
                    @can('program_allocation.edit')
                    <div class="flex items-center gap-1.5 bg-gray-50 border border-gray-200 rounded-lg px-3 py-1.5">
                        <svg class="h-3.5 w-3.5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span class="text-xs text-gray-500 whitespace-nowrap">Responsable:</span>
                        <select onchange="updateResponsable({{ $assignment->id }}, this.value)"
                            class="text-xs text-gray-800 font-medium bg-transparent border-0 focus:ring-0 cursor-pointer py-0 pl-0 pr-5 max-w-[160px]">
                            <option value="">Sin asignar</option>
                            @foreach($accountants as $acc)
                            <option value="{{ $acc->id }}" {{ $assignment->responsable_id == $acc->id ? 'selected' : '' }}>
                                {{ $acc->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    @else
                    @if($assignment->responsable)
                    <div class="flex items-center gap-1.5 text-sm text-gray-600">
                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span>{{ $assignment->responsable->name }}</span>
                    </div>
                    @endif
                    @endcan
                </div>
                <div class="flex items-center gap-4">
                    @php
                        $activeDetails  = $details->where('excluido', false);
                        $excluidosCount = $details->where('excluido', true)->count();
                        $totalAsig   = $activeDetails->sum('total_asignacion');
                        $totalCobRet = $activeDetails->sum('cuotas_retrasadas_cobrado');
                        $totalCobVig = $activeDetails->sum('cuota_vigente_cobrado');
                        $totalCobMat = $activeDetails->sum('matricula_cobrado');
                        $totalCobCer = $activeDetails->sum('certificacion_cobrado');
                    @endphp
                    @if($excluidosCount > 0)
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700">
                        {{ $excluidosCount }} excluido{{ $excluidosCount > 1 ? 's' : '' }}
                    </span>
                    @endif
                    <div class="text-right">
                        <p class="text-xs text-gray-500">Total Asignación</p>
                        <p class="text-sm font-bold text-gray-900" id="summaryTotalAsig">{{ number_format($totalAsig, 2) }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-gray-500">Total Cobrado</p>
                        <p class="text-sm font-bold text-green-700" id="summaryTotalCobrado">{{ number_format($totalCobRet + $totalCobVig + $totalCobMat + $totalCobCer, 2) }}</p>
                    </div>
                    @can('program_allocation.delete')
                    <button onclick="deleteAssignment({{ $assignment->id }})"
                        class="p-2 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="Eliminar asignación">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                    @endcan
                </div>
            </div>

            <!-- Tabla principal -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm border-collapse" style="min-width: 1400px;">
                        <thead>
                            <tr class="bg-gray-800 text-white text-xs">
                                <th class="px-4 py-2 text-left sticky left-0 bg-gray-800 z-10 border-r border-gray-700" rowspan="2">Participante</th>
                                <th class="px-3 py-2 text-right border-r border-gray-700" rowspan="2">Total<br>Asignación</th>
                                <th class="px-3 py-2 text-center border-r border-gray-700 bg-red-900/40" colspan="3">Cuota Retrasada</th>
                                <th class="px-3 py-2 text-center border-r border-gray-700 bg-amber-900/40" colspan="3">Cuota Vigente</th>
                                <th class="px-3 py-2 text-center border-r border-gray-700 bg-emerald-900/40" colspan="2">Adelanto</th>
                                <th class="px-3 py-2 text-center border-r border-gray-700 bg-indigo-900/40" colspan="2">Matrícula</th>
                                <th class="px-3 py-2 text-center border-r border-gray-700 bg-purple-900/40" colspan="2">Certificación</th>
                                <th class="px-3 py-2 text-left" rowspan="2">Observaciones</th>
                            </tr>
                            <tr class="bg-gray-700 text-gray-200 text-xs">
                                <th class="px-3 py-1.5 text-center border-r border-gray-600 bg-red-900/30">N° Cuota</th>
                                <th class="px-3 py-1.5 text-right border-r border-gray-600 bg-red-900/30">Cobrado</th>
                                <th class="px-3 py-1.5 text-right border-r border-gray-600 bg-red-900/30">Saldo</th>
                                <th class="px-3 py-1.5 text-center border-r border-gray-600 bg-amber-900/30">N° Cuota</th>
                                <th class="px-3 py-1.5 text-right border-r border-gray-600 bg-amber-900/30">Cobrado</th>
                                <th class="px-3 py-1.5 text-right border-r border-gray-600 bg-amber-900/30">Saldo</th>
                                <th class="px-3 py-1.5 text-center border-r border-gray-600 bg-emerald-900/30">N° Cuota</th>
                                <th class="px-3 py-1.5 text-right border-r border-gray-600 bg-emerald-900/30">Importe</th>
                                <th class="px-3 py-1.5 text-right border-r border-gray-600 bg-indigo-900/30">Cobrado</th>
                                <th class="px-3 py-1.5 text-right border-r border-gray-600 bg-indigo-900/30">Saldo</th>
                                <th class="px-3 py-1.5 text-right border-r border-gray-600 bg-purple-900/30">Cobrado</th>
                                <th class="px-3 py-1.5 text-right border-r border-gray-600 bg-purple-900/30">Saldo</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100" id="assignmentBody">
                            @forelse($details as $detail)
                            @php
                                $retSaldo  = max(0, (float)$detail->cuotas_retrasadas_importe - (float)$detail->cuotas_retrasadas_cobrado);
                                $vigSaldo  = max(0, (float)$detail->cuota_vigente_importe - (float)$detail->cuota_vigente_cobrado);
                                $matSaldo  = max(0, (float)$detail->matricula_importe - (float)$detail->matricula_cobrado);
                                $cerSaldo  = max(0, (float)$detail->certificacion_importe - (float)$detail->certificacion_cobrado);
                                $numRet    = $detail->cuotas_retrasadas_numeros;
                            @endphp
                            <tr class="transition-colors {{ $detail->excluido ? 'opacity-50' : 'hover:bg-gray-50' }}"
                                data-detail-id="{{ $detail->id }}"
                                data-excluido="{{ $detail->excluido ? '1' : '0' }}">

                                <!-- Participante (sticky) -->
                                <td class="px-4 py-3 sticky left-0 z-10 border-r border-gray-100 min-w-[190px] {{ $detail->excluido ? 'bg-red-50/60' : 'bg-white hover:bg-gray-50' }}">
                                    <div class="flex items-start gap-2">
                                        <!-- Botón excluir / reactivar -->
                                        @can('program_allocation.edit')
                                        <button onclick="toggleExcluded({{ $detail->id }}, this)"
                                            title="{{ $detail->excluido ? 'Reactivar inscrito' : 'Excluir de la asignación' }}"
                                            class="flex-shrink-0 mt-0.5 p-0.5 rounded transition-colors exclude-btn
                                                {{ $detail->excluido ? 'text-red-400 hover:text-green-600' : 'text-gray-300 hover:text-red-500' }}">
                                            @if($detail->excluido)
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                            </svg>
                                            @else
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                            </svg>
                                            @endif
                                        </button>
                                        @endcan
                                        <div class="flex-1 min-w-0">
                                            <p class="font-semibold text-xs leading-tight participant-name {{ $detail->excluido ? 'text-gray-400 line-through' : 'text-gray-900' }}">
                                                {{ $detail->nombre_completo }}
                                            </p>
                                            <p class="text-gray-400 text-xs mt-0.5">{{ $detail->plan_pago_nombre ?: '—' }}</p>
                                            @if($detail->telefono)
                                            <p class="text-gray-400 text-xs">{{ $detail->telefono }}</p>
                                            @endif
                                            @if($detail->excluido)
                                            <span class="excluded-badge inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-red-100 text-red-600 mt-1">
                                                Excluido
                                            </span>
                                            @else
                                            <span class="excluded-badge hidden inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-red-100 text-red-600 mt-1">
                                                Excluido
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <!-- Total Asignación -->
                                <td class="px-3 py-3 text-right border-r border-gray-100">
                                    <span class="total-asig-{{ $detail->id }} font-bold text-gray-900 text-xs">
                                        {{ number_format($detail->total_asignacion, 2) }}
                                    </span>
                                </td>

                                <!-- Cuota Retrasada: N° -->
                                <td class="px-3 py-3 text-center border-r border-gray-100 text-gray-600 text-xs bg-red-50/30">
                                    @if(!empty($numRet)) {{ implode(', ', $numRet) }}
                                    @else <span class="text-gray-300">—</span> @endif
                                </td>

                                <!-- Cuota Retrasada: Cobrado (editable) -->
                                <td class="px-3 py-3 text-right border-r border-gray-100 bg-red-50/30">
                                    @if(!empty($numRet))
                                    <span class="editable-cell cursor-pointer hover:bg-red-100 rounded px-1 py-0.5 transition font-mono text-xs ret-cobrado-display-{{ $detail->id }}"
                                          onclick="startEdit(this, 'cuotas_retrasadas_cobrado', {{ $detail->id }}, {{ (float)$detail->cuotas_retrasadas_cobrado }})">
                                        {{ number_format($detail->cuotas_retrasadas_cobrado, 2) }}
                                    </span>
                                    @else <span class="text-gray-300 text-xs">—</span> @endif
                                </td>

                                <!-- Cuota Retrasada: Saldo -->
                                <td class="px-3 py-3 text-right border-r border-gray-100 bg-red-50/30">
                                    <span class="ret-saldo-{{ $detail->id }} font-mono text-xs {{ $retSaldo > 0 ? 'text-red-600 font-semibold' : 'text-gray-400' }}">
                                        {{ number_format($retSaldo, 2) }}
                                    </span>
                                </td>

                                <!-- Cuota Vigente: N° -->
                                <td class="px-3 py-3 text-center border-r border-gray-100 text-gray-600 text-xs bg-amber-50/30">
                                    {{ $detail->cuota_vigente_numero ?? '—' }}
                                </td>

                                <!-- Cuota Vigente: Cobrado (editable) -->
                                <td class="px-3 py-3 text-right border-r border-gray-100 bg-amber-50/30">
                                    @if($detail->cuota_vigente_numero)
                                    <span class="editable-cell cursor-pointer hover:bg-amber-100 rounded px-1 py-0.5 transition font-mono text-xs vig-cobrado-display-{{ $detail->id }}"
                                          onclick="startEdit(this, 'cuota_vigente_cobrado', {{ $detail->id }}, {{ (float)$detail->cuota_vigente_cobrado }})">
                                        {{ number_format($detail->cuota_vigente_cobrado, 2) }}
                                    </span>
                                    @else <span class="text-gray-300 text-xs">—</span> @endif
                                </td>

                                <!-- Cuota Vigente: Saldo -->
                                <td class="px-3 py-3 text-right border-r border-gray-100 bg-amber-50/30">
                                    <span class="vig-saldo-{{ $detail->id }} font-mono text-xs {{ $vigSaldo > 0 ? 'text-amber-600 font-semibold' : 'text-gray-400' }}">
                                        {{ number_format($vigSaldo, 2) }}
                                    </span>
                                </td>

                                <!-- Adelanto: N° Cuota (editable) -->
                                <td class="px-3 py-3 text-center border-r border-gray-100 bg-emerald-50/30">
                                    <span class="editable-cell cursor-pointer hover:bg-emerald-100 rounded px-1 py-0.5 transition text-xs adel-num-display-{{ $detail->id }}"
                                          onclick="startEdit(this, 'adelanto_numero_cuota', {{ $detail->id }}, {{ $detail->adelanto_numero_cuota ?? 0 }})">
                                        {{ $detail->adelanto_numero_cuota ?? '—' }}
                                    </span>
                                </td>

                                <!-- Adelanto: Importe (editable) -->
                                <td class="px-3 py-3 text-right border-r border-gray-100 bg-emerald-50/30">
                                    <span class="editable-cell cursor-pointer hover:bg-emerald-100 rounded px-1 py-0.5 transition font-mono text-xs adel-imp-display-{{ $detail->id }}"
                                          onclick="startEdit(this, 'adelanto_importe', {{ $detail->id }}, {{ (float)$detail->adelanto_importe }})">
                                        {{ $detail->adelanto_importe > 0 ? number_format($detail->adelanto_importe, 2) : '—' }}
                                    </span>
                                </td>

                                <!-- Matrícula: Cobrado (editable) -->
                                <td class="px-3 py-3 text-right border-r border-gray-100 bg-indigo-50/30">
                                    @if($detail->matricula_importe > 0)
                                    <span class="editable-cell cursor-pointer hover:bg-indigo-100 rounded px-1 py-0.5 transition font-mono text-xs mat-cobrado-display-{{ $detail->id }}"
                                          onclick="startEdit(this, 'matricula_cobrado', {{ $detail->id }}, {{ (float)$detail->matricula_cobrado }})">
                                        {{ number_format($detail->matricula_cobrado, 2) }}
                                    </span>
                                    @else <span class="text-gray-300 text-xs">—</span> @endif
                                </td>

                                <!-- Matrícula: Saldo -->
                                <td class="px-3 py-3 text-right border-r border-gray-100 bg-indigo-50/30">
                                    <span class="mat-saldo-{{ $detail->id }} font-mono text-xs {{ $matSaldo > 0 ? 'text-indigo-600 font-semibold' : 'text-gray-400' }}">
                                        {{ $matSaldo > 0 ? number_format($matSaldo, 2) : '—' }}
                                    </span>
                                </td>

                                <!-- Certificación: Cobrado (editable) -->
                                <td class="px-3 py-3 text-right border-r border-gray-100 bg-purple-50/30">
                                    @if($detail->certificacion_importe > 0)
                                    <span class="editable-cell cursor-pointer hover:bg-purple-100 rounded px-1 py-0.5 transition font-mono text-xs cer-cobrado-display-{{ $detail->id }}"
                                          onclick="startEdit(this, 'certificacion_cobrado', {{ $detail->id }}, {{ (float)$detail->certificacion_cobrado }})">
                                        {{ number_format($detail->certificacion_cobrado, 2) }}
                                    </span>
                                    @else <span class="text-gray-300 text-xs">—</span> @endif
                                </td>

                                <!-- Certificación: Saldo -->
                                <td class="px-3 py-3 text-right border-r border-gray-100 bg-purple-50/30">
                                    <span class="cer-saldo-{{ $detail->id }} font-mono text-xs {{ $cerSaldo > 0 ? 'text-purple-600 font-semibold' : 'text-gray-400' }}">
                                        {{ $cerSaldo > 0 ? number_format($cerSaldo, 2) : '—' }}
                                    </span>
                                </td>

                                <!-- Observaciones (editable) -->
                                <td class="px-3 py-3 min-w-[150px]">
                                    <span class="editable-cell cursor-pointer hover:bg-gray-100 rounded px-1 py-0.5 transition text-xs text-gray-500 obs-display-{{ $detail->id }}"
                                          onclick="startEdit(this, 'observaciones', {{ $detail->id }}, {{ json_encode($detail->observaciones ?? '') }})">
                                        {{ $detail->observaciones ?: '—' }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="16" class="px-4 py-10 text-center text-gray-400">
                                    No hay participantes en esta asignación.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @endif

            <!-- Toast -->
            <div id="toast" class="hidden fixed bottom-5 right-5 text-sm px-4 py-3 rounded-xl shadow-lg z-50 bg-gray-900 text-white">
                <span id="toastMsg"></span>
            </div>
        </div>
    </div>

    <script>
    const csrf      = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const programId = {{ $program?->id ?? 'null' }};
    const mes       = {{ $mes }};
    const gestion   = {{ $gestion }};

    // ——— Buscador de programas (Alpine component) ———
    function programSearch(config) {
        return {
            search: config.selectedName || '',
            open: false,
            selectedId: config.selectedId || null,
            selectedName: config.selectedName || '',
            programs: config.programs,
            filtered: config.programs,

            init() {
                this.filtered = this.programs;
            },

            filter() {
                const q = this.search.toLowerCase().trim();
                this.filtered = q
                    ? this.programs.filter(p =>
                        p.name.toLowerCase().includes(q) ||
                        p.status.toLowerCase().includes(q))
                    : this.programs;
                this.open = true;
            },

            selectProgram(prog) {
                this.selectedId   = prog.id;
                this.selectedName = prog.name;
                this.search       = prog.name;
                this.open         = false;
                document.getElementById('programIdInput').value = prog.id;
                // Navegar a la URL con el programa seleccionado
                const url = new URL(window.location.href);
                url.searchParams.set('program_id', prog.id);
                url.searchParams.set('mes', mes);
                url.searchParams.set('gestion', gestion);
                window.location.href = url.toString();
            },

            selectFirst() {
                if (this.filtered.length > 0) this.selectProgram(this.filtered[0]);
            },

            clearSelection() {
                this.selectedId   = null;
                this.selectedName = '';
                this.search       = '';
                this.filtered     = this.programs;
                document.getElementById('programIdInput').value = '';
                const url = new URL(window.location.href);
                url.searchParams.delete('program_id');
                window.location.href = url.toString();
            },
        };
    }

    // ——— Generar asignación ———
    async function generateAssignment() {
        const btn = document.querySelector('[onclick="generateAssignment()"]');
        if (btn) { btn.disabled = true; btn.textContent = 'Generando...'; }

        const res  = await fetch('/accounting/nominal-assignments/generate', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: JSON.stringify({ program_id: programId, mes, gestion }),
        });
        const data = await res.json();

        if (data.success) {
            showToast(`Asignación generada — ${data.count} participantes`);
            setTimeout(() => location.reload(), 900);
        } else {
            alert(data.message || 'Error al generar');
            if (btn) { btn.disabled = false; btn.textContent = 'Generar Asignación'; }
        }
    }

    // ——— Eliminar asignación ———
    async function deleteAssignment(id) {
        if (!confirm('¿Eliminar esta asignación? Se perderán todos los datos registrados.')) return;
        const res  = await fetch(`/accounting/nominal-assignments/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        });
        const data = await res.json();
        if (data.success) location.reload();
    }

    // Campos cobrado: el usuario ingresa el INCREMENTO (nuevo pago), el servidor acumula
    const COBRADO_FIELDS = new Set([
        'cuotas_retrasadas_cobrado', 'cuota_vigente_cobrado',
        'matricula_cobrado', 'certificacion_cobrado'
    ]);

    // Mapa: campo cobrado → clave en la respuesta del servidor con el acumulado
    const COBRADO_SERVER_KEY = {
        'cuotas_retrasadas_cobrado': 'cuotas_retrasadas_cobrado',
        'cuota_vigente_cobrado':     'cuota_vigente_cobrado',
        'matricula_cobrado':         'matricula_cobrado',
        'certificacion_cobrado':     'certificacion_cobrado',
    };

    const fmt = (n) => {
        if (n === null || n === undefined) return '—';
        return parseFloat(n).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    };

    // ——— Edición inline ———
    let activeInput = null;

    function startEdit(el, field, detailId, currentVal) {
        if (activeInput) commitEdit(activeInput._field, activeInput._detailId);

        const isText     = field === 'observaciones';
        const isInt      = field === 'adelanto_numero_cuota';
        const isCobrado  = COBRADO_FIELDS.has(field);

        const input = document.createElement('input');
        input.type  = isText ? 'text' : 'number';
        if (!isText && !isInt) { input.step = '0.01'; input.min = '0'; }
        if (isInt)             { input.step = '1';    input.min = '1'; }

        // Cobrado: input vacío — el usuario ingresa solo el nuevo pago recibido
        // Otros campos: muestra el valor actual para editarlo directamente
        if (isCobrado) {
            input.value       = '';
            input.placeholder = currentVal > 0 ? `acum: ${fmt(currentVal)}` : '0.00';
        } else {
            input.value = (currentVal === 0 && !isText) ? '' : (currentVal || '');
        }

        input.className = 'w-full border-b border-indigo-400 bg-transparent text-xs focus:outline-none px-1 font-mono';
        input.style.minWidth = '70px';
        input._field    = field;
        input._detailId = detailId;
        input._origEl   = el;
        activeInput     = input;

        el.replaceWith(input);
        input.focus();

        input.addEventListener('blur',    () => commitEdit(field, detailId));
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter')  { e.preventDefault(); input.blur(); }
            if (e.key === 'Escape') { cancelEdit(input, el); }
        });
    }

    function cancelEdit(input, origEl) {
        activeInput = null;
        input.replaceWith(origEl);
    }

    async function commitEdit(field, detailId) {
        const input = activeInput;
        if (!input || input._detailId !== detailId) return;
        activeInput = null;

        const rawVal = input.value.trim();
        const isCobrado = COBRADO_FIELDS.has(field);

        // Si es cobrado y el campo está vacío, no enviar nada
        if (isCobrado && !rawVal) {
            input.replaceWith(input._origEl);
            return;
        }

        let val;
        if (field === 'observaciones')           val = rawVal || null;
        else if (field === 'adelanto_numero_cuota') val = rawVal ? parseInt(rawVal) : null;
        else                                     val = rawVal ? parseFloat(rawVal) : 0;

        const origEl   = input._origEl;
        const tempSpan = document.createElement('span');
        tempSpan.textContent = '...';
        tempSpan.className   = 'text-gray-400 text-xs';
        input.replaceWith(tempSpan);

        try {
            const res  = await fetch(`/accounting/nominal-assignments/detail/${detailId}`, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body: JSON.stringify({ [field]: val }),
            });
            const data = await res.json();

            if (data.success) {
                // Primero restaurar el elemento al DOM, luego actualizar su contenido
                tempSpan.replaceWith(origEl);
                updateRowDisplay(detailId, field, val, data);
                showToast('Guardado');
            } else {
                tempSpan.replaceWith(origEl);
                showToast('Error al guardar', true);
            }
        } catch (e) {
            tempSpan.replaceWith(origEl);
            showToast('Error de conexión', true);
        }
    }

    function updateRowDisplay(detailId, field, val, data) {
        // Actualizar saldos y total desde los valores devueltos por el servidor
        const updates = {
            ret_saldo:  [`.ret-saldo-${detailId}`,  data.cuotas_retrasadas_saldo, 'text-red-600'],
            vig_saldo:  [`.vig-saldo-${detailId}`,  data.cuota_vigente_saldo,     'text-amber-600'],
            mat_saldo:  [`.mat-saldo-${detailId}`,  data.matricula_saldo,         'text-indigo-600'],
            cer_saldo:  [`.cer-saldo-${detailId}`,  data.certificacion_saldo,     'text-purple-600'],
            total_asig: [`.total-asig-${detailId}`, data.total_asignacion,        ''],
        };

        for (const [, [selector, value]] of Object.entries(updates)) {
            const el = document.querySelector(selector);
            if (!el || value === undefined) continue;
            el.textContent = fmt(value);
            const isZero = parseFloat(value) === 0;
            if (selector.includes('total')) {
                el.className = `${selector.slice(1)} font-bold text-gray-900 text-xs`;
            } else {
                const colorMap = { ret: 'text-red-600', vig: 'text-amber-600', mat: 'text-indigo-600', cer: 'text-purple-600' };
                const prefix   = selector.match(/\.(ret|vig|mat|cer)/)?.[1];
                const color    = colorMap[prefix] || 'text-gray-700';
                el.className   = `${selector.slice(1)} font-mono text-xs ${isZero ? 'text-gray-400' : color + ' font-semibold'}`;
            }
        }

        const displayMap = {
            'cuotas_retrasadas_cobrado': `.ret-cobrado-display-${detailId}`,
            'cuota_vigente_cobrado':     `.vig-cobrado-display-${detailId}`,
            'adelanto_numero_cuota':     `.adel-num-display-${detailId}`,
            'adelanto_importe':          `.adel-imp-display-${detailId}`,
            'matricula_cobrado':         `.mat-cobrado-display-${detailId}`,
            'certificacion_cobrado':     `.cer-cobrado-display-${detailId}`,
            'observaciones':             `.obs-display-${detailId}`,
        };

        const displayEl = document.querySelector(displayMap[field]);
        if (!displayEl) return;

        if (field === 'observaciones') {
            displayEl.textContent = val || '—';
        } else if (field === 'adelanto_numero_cuota') {
            displayEl.textContent = val || '—';
        } else if (COBRADO_FIELDS.has(field)) {
            // Usar el valor ACUMULADO devuelto por el servidor (no el incremento ingresado)
            const accumulated = data[COBRADO_SERVER_KEY[field]];
            displayEl.textContent = accumulated > 0 ? fmt(accumulated) : '—';
        } else {
            displayEl.textContent = val > 0 ? fmt(val) : '—';
        }
    }

    // ——— Responsable de cobros ———
    async function updateResponsable(assignmentId, userId) {
        const res = await fetch(`/accounting/nominal-assignments/${assignmentId}/responsable`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: JSON.stringify({ responsable_id: userId || null }),
        });
        const data = await res.json();
        if (data.success) showToast(data.responsable_name ? `Responsable: ${data.responsable_name}` : 'Sin responsable asignado');
        else showToast('Error al actualizar responsable', true);
    }

    // ——— Excluir / reactivar inscrito ———
    async function toggleExcluded(detailId, btn) {
        const res = await fetch(`/accounting/nominal-assignments/detail/${detailId}/toggle-excluded`, {
            method: 'PATCH',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        });
        const data = await res.json();
        if (!data.success) { showToast('Error al cambiar estado', true); return; }

        const row      = document.querySelector(`tr[data-detail-id="${detailId}"]`);
        const nameEl   = row.querySelector('.participant-name');
        const badgeEl  = row.querySelector('.excluded-badge');
        const cell     = btn.closest('td');

        if (data.excluido) {
            row.classList.add('opacity-50');
            row.classList.remove('hover:bg-gray-50');
            nameEl?.classList.add('line-through', 'text-gray-400');
            nameEl?.classList.remove('text-gray-900');
            cell?.classList.add('bg-red-50/60');
            cell?.classList.remove('bg-white', 'hover:bg-gray-50');
            badgeEl?.classList.remove('hidden');
            btn.classList.add('text-red-400', 'hover:text-green-600');
            btn.classList.remove('text-gray-300', 'hover:text-red-500');
            btn.title = 'Reactivar inscrito';
        } else {
            row.classList.remove('opacity-50');
            row.classList.add('hover:bg-gray-50');
            nameEl?.classList.remove('line-through', 'text-gray-400');
            nameEl?.classList.add('text-gray-900');
            cell?.classList.remove('bg-red-50/60');
            cell?.classList.add('bg-white', 'hover:bg-gray-50');
            badgeEl?.classList.add('hidden');
            btn.classList.remove('text-red-400', 'hover:text-green-600');
            btn.classList.add('text-gray-300', 'hover:text-red-500');
            btn.title = 'Excluir de la asignación';
        }

        // Actualizar totales del encabezado
        const asigEl    = document.getElementById('summaryTotalAsig');
        const cobradoEl = document.getElementById('summaryTotalCobrado');
        if (asigEl)    asigEl.textContent    = fmt(data.program_total_asignacion);
        if (cobradoEl) cobradoEl.textContent = fmt(data.program_total_cobrado);

        showToast(data.excluido ? 'Inscrito excluido de la asignación' : 'Inscrito reactivado');
    }

    let toastTimer;
    function showToast(msg, isError = false) {
        const toast = document.getElementById('toast');
        document.getElementById('toastMsg').textContent = msg;
        toast.className = `fixed bottom-5 right-5 text-sm px-4 py-3 rounded-xl shadow-lg z-50 transition-all duration-300 ${isError ? 'bg-red-700 text-white' : 'bg-gray-900 text-white'}`;
        toast.classList.remove('hidden');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => toast.classList.add('hidden'), 2500);
    }
    </script>
</x-app-layout>
