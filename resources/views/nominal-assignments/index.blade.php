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

            <!-- Filtros: programa + responsable + navegación mes -->
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
                    <input type="hidden" name="responsable_filter" value="{{ $responsableFilter }}">
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

                <!-- Filtro por Responsable -->
                @if($accountants->isNotEmpty())
                <div class="flex-shrink-0 min-w-[180px]">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Responsable</label>
                    <div class="relative">
                        <select onchange="applyResponsableFilter(this.value)"
                            class="w-full border border-gray-300 rounded-lg pl-8 pr-3 py-2 text-sm focus:ring-2 focus:ring-gray-800 focus:border-gray-800 appearance-none">
                            <option value="">Todos</option>
                            @foreach($accountants as $acc)
                            <option value="{{ $acc->id }}" {{ $responsableFilter == $acc->id ? 'selected' : '' }}>
                                {{ $acc->name }}
                            </option>
                            @endforeach
                        </select>
                        <svg class="absolute left-2.5 top-2.5 h-4 w-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    @if($responsableFilter)
                    <div class="mt-1.5">
                        <a href="{{ route('nominal-assignments.index', ['mes' => $mes, 'gestion' => $gestion, 'program_id' => $program?->id]) }}"
                           class="text-xs text-red-500 hover:text-red-700">× Quitar filtro</a>
                    </div>
                    @endif
                </div>
                @endif

                <!-- Navegación mes/año -->
                <div class="flex-shrink-0 flex items-center gap-2">
                    <a href="{{ route('nominal-assignments.index', ['program_id' => $program?->id, 'mes' => $prevMes, 'gestion' => $prevGestion, 'responsable_filter' => $responsableFilter]) }}"
                       class="p-2 text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </a>
                    <div class="text-center min-w-[130px]">
                        <div class="text-base font-bold text-gray-900">{{ $mesesNombres[$mes] }}</div>
                        <div class="text-sm text-gray-500">{{ $gestion }}</div>
                    </div>
                    <a href="{{ route('nominal-assignments.index', ['program_id' => $program?->id, 'mes' => $nextMes, 'gestion' => $nextGestion, 'responsable_filter' => $responsableFilter]) }}"
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

            @php
                $activeDetails           = $details->where('excluido', false);
                $excluidosCount          = $details->where('excluido', true)->count();
                // Totales por sección (solo inscritos activos)
                $totalAsig               = $activeDetails->sum('total_asignacion');
                $totalRetImporte         = $activeDetails->sum('cuotas_retrasadas_importe');
                $totalRetCobrado         = $activeDetails->sum('cuotas_retrasadas_cobrado');
                $totalRetSaldo           = max(0, $totalRetImporte - $totalRetCobrado);
                $totalVigImporte         = $activeDetails->sum('cuota_vigente_importe');
                $totalVigCobrado         = $activeDetails->sum('cuota_vigente_cobrado');
                $totalVigSaldo           = max(0, $totalVigImporte - $totalVigCobrado);
                $totalAdelantoImporte    = $activeDetails->sum('adelanto_importe');
                $totalMatImporte         = $activeDetails->sum('matricula_importe');
                $totalMatCobrado         = $activeDetails->sum('matricula_cobrado');
                $totalMatSaldo           = max(0, $totalMatImporte - $totalMatCobrado);
                $totalCerImporte         = $activeDetails->sum('certificacion_importe');
                $totalCerCobrado         = $activeDetails->sum('certificacion_cobrado');
                $totalCerSaldo           = max(0, $totalCerImporte - $totalCerCobrado);
            @endphp

            <!-- Info de la asignación + totales -->
            <div class="bg-white rounded-xl shadow-sm px-5 pt-4 pb-4 space-y-4">

                <!-- Fila superior: metadatos + responsable + acciones -->
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="text-sm text-gray-500">
                            <strong>{{ $details->count() }}</strong> participantes
                            @if($excluidosCount > 0)
                            <span class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-red-100 text-red-600">
                                {{ $excluidosCount }} excluido{{ $excluidosCount > 1 ? 's' : '' }}
                            </span>
                            @endif
                        </span>
                        <span class="text-xs text-gray-400">
                            Generado {{ $assignment->created_at->format('d/m/Y H:i') }}
                            por {{ $assignment->generator?->name ?? 'Sistema' }}
                        </span>

                        <!-- Responsable -->
                        @can('program_allocation.edit')
                        <div class="flex items-center gap-1.5 bg-gray-50 border border-gray-200 rounded-lg px-2.5 py-1">
                            <svg class="h-3.5 w-3.5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <span class="text-xs text-gray-500 whitespace-nowrap">Responsable:</span>
                            <select onchange="updateResponsable({{ $assignment->id }}, this.value)"
                                class="text-xs text-gray-800 font-medium bg-transparent border-0 focus:ring-0 cursor-pointer py-0 pl-0 pr-5 max-w-[160px]">
                                <option value="">Sin asignar</option>
                                @foreach($accountants as $acc)
                                <option value="{{ $acc->id }}" {{ $assignment->responsable_id == $acc->id ? 'selected' : '' }}>{{ $acc->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @elseif($assignment->responsable)
                        <div class="flex items-center gap-1.5 text-xs text-gray-600">
                            <svg class="h-3.5 w-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <span>{{ $assignment->responsable->name }}</span>
                        </div>
                        @endcan
                    </div>

                    <!-- Botones acción -->
                    @can('program_allocation.create')
                    <button id="refreshBtn" onclick="refreshAssignment({{ $assignment->id }})"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-indigo-600 bg-indigo-50 border border-indigo-200 rounded-lg hover:bg-indigo-100 transition" title="Actualizar asignación (añadir inscritos nuevos)">
                        <svg id="refreshIcon" class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Actualizar
                    </button>
                    @endcan
                </div>

                <!-- Totales desglosados -->
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 pt-3 border-t border-gray-100">

                    <!-- Total general -->
                    <div class="bg-gray-50 rounded-xl px-4 py-3">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Total Asignación</p>
                        <p class="text-lg font-bold text-gray-900" id="summaryTotalAsig">{{ number_format($totalAsig, 2) }}</p>
                    </div>

                    <!-- Cuota Retrasada -->
                    <div class="bg-red-50/60 rounded-xl px-4 py-3">
                        <p class="text-xs font-semibold text-red-600 uppercase tracking-wide mb-2">Retrasada</p>
                        <div class="space-y-1">
                            <div class="flex justify-between items-baseline">
                                <span class="text-xs text-gray-500">Importe</span>
                                <span class="text-xs font-semibold text-gray-800 font-mono" id="sumRetImporte">{{ number_format($totalRetImporte, 2) }}</span>
                            </div>
                            <div class="flex justify-between items-baseline">
                                <span class="text-xs text-gray-500">Cobrado</span>
                                <span class="text-xs font-semibold text-green-700 font-mono" id="sumRetCobrado">{{ number_format($totalRetCobrado, 2) }}</span>
                            </div>
                            <div class="flex justify-between items-baseline border-t border-red-100 pt-1">
                                <span class="text-xs text-gray-600 font-medium">Saldo</span>
                                <span class="text-xs font-bold text-red-600 font-mono" id="sumRetSaldo">{{ number_format($totalRetSaldo, 2) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Cuota Vigente -->
                    <div class="bg-amber-50/60 rounded-xl px-4 py-3">
                        <p class="text-xs font-semibold text-amber-600 uppercase tracking-wide mb-2">Vigente</p>
                        <div class="space-y-1">
                            <div class="flex justify-between items-baseline">
                                <span class="text-xs text-gray-500">Importe</span>
                                <span class="text-xs font-semibold text-gray-800 font-mono" id="sumVigImporte">{{ number_format($totalVigImporte, 2) }}</span>
                            </div>
                            <div class="flex justify-between items-baseline">
                                <span class="text-xs text-gray-500">Cobrado</span>
                                <span class="text-xs font-semibold text-green-700 font-mono" id="sumVigCobrado">{{ number_format($totalVigCobrado, 2) }}</span>
                            </div>
                            <div class="flex justify-between items-baseline border-t border-amber-100 pt-1">
                                <span class="text-xs text-gray-600 font-medium">Saldo</span>
                                <span class="text-xs font-bold text-amber-600 font-mono" id="sumVigSaldo">{{ number_format($totalVigSaldo, 2) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Adelanto -->
                    <div class="bg-emerald-50/60 rounded-xl px-4 py-3">
                        <p class="text-xs font-semibold text-emerald-600 uppercase tracking-wide mb-2">Adelanto</p>
                        <div class="space-y-1">
                            <div class="flex justify-between items-baseline">
                                <span class="text-xs text-gray-500">Total</span>
                                <span class="text-xs font-bold text-emerald-700 font-mono" id="sumAdelanto">{{ number_format($totalAdelantoImporte, 2) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Matrícula + Certificación (si tienen valores) -->
                    @if($totalMatImporte > 0 || $totalCerImporte > 0)
                    <div class="bg-indigo-50/60 rounded-xl px-4 py-3">
                        <p class="text-xs font-semibold text-indigo-600 uppercase tracking-wide mb-2">Mat. / Certif.</p>
                        <div class="space-y-1">
                            @if($totalMatImporte > 0)
                            <div class="flex justify-between items-baseline">
                                <span class="text-xs text-gray-500">Mat. Saldo</span>
                                <span class="text-xs font-semibold text-indigo-700 font-mono" id="sumMatSaldo">{{ number_format($totalMatSaldo, 2) }}</span>
                            </div>
                            @endif
                            @if($totalCerImporte > 0)
                            <div class="flex justify-between items-baseline">
                                <span class="text-xs text-gray-500">Certif. Saldo</span>
                                <span class="text-xs font-semibold text-purple-700 font-mono" id="sumCerSaldo">{{ number_format($totalCerSaldo, 2) }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Buscador de inscritos + toggle ocultos -->
            <div class="flex items-center gap-3">
                @php $ocultosCount = $details->where('oculto', true)->count(); @endphp
                @if($ocultosCount > 0)
                <button onclick="toggleShowHidden(this)"
                    id="showHiddenBtn"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 4.411m0 0L21 21"/>
                    </svg>
                    <span id="showHiddenLabel">Mostrar {{ $ocultosCount }} oculto{{ $ocultosCount > 1 ? 's' : '' }}</span>
                </button>
                @endif
                <div class="relative flex-1 max-w-xs">
                    <input type="text" id="participantSearch"
                        oninput="filterParticipants(this.value)"
                        placeholder="Buscar inscrito..."
                        class="w-full border border-gray-300 rounded-lg pl-8 pr-8 py-1.5 text-sm focus:ring-2 focus:ring-gray-800 focus:border-gray-800">
                    <svg class="absolute left-2.5 top-2 h-4 w-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <button onclick="clearParticipantSearch()" id="clearSearchBtn"
                        class="hidden absolute right-2 top-2 text-gray-400 hover:text-gray-600">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <span id="searchResultCount" class="text-xs text-gray-400 hidden"></span>
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
                            <tr class="transition-colors {{ $detail->excluido ? 'opacity-50' : 'hover:bg-gray-50' }} {{ $detail->oculto ? 'oculto-row' : '' }}"
                                data-detail-id="{{ $detail->id }}"
                                data-excluido="{{ $detail->excluido ? '1' : '0' }}"
                                data-oculto="{{ $detail->oculto ? '1' : '0' }}">

                                <!-- Participante (sticky) -->
                                <td class="px-3 py-2.5 sticky left-0 z-10 border-r border-gray-100 min-w-[210px] {{ $detail->excluido ? 'bg-red-50/60' : 'bg-white hover:bg-gray-50' }}">
                                    <div class="flex items-center gap-2">
                                        <!-- Nombre e info -->
                                        <div class="flex-1 min-w-0">
                                            <p class="font-semibold text-xs leading-tight participant-name {{ $detail->excluido ? 'text-gray-400 line-through' : 'text-gray-900' }}">
                                                {{ $detail->nombre_completo }}
                                            </p>
                                            <p class="text-gray-400 text-xs mt-0.5">{{ $detail->plan_pago_nombre ?: '—' }}</p>
                                            @if($detail->telefono)
                                            <p class="text-gray-400 text-xs">{{ $detail->telefono }}</p>
                                            @endif
                                            <span class="excluded-badge {{ $detail->excluido ? '' : 'hidden' }} inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-red-100 text-red-600 mt-1">
                                                Excluido
                                            </span>
                                        </div>
                                        <!-- Botones de acción (derecha) -->
                                        @can('program_allocation.edit')
                                        <div class="flex-shrink-0 flex flex-col gap-1 items-center">
                                            <!-- Excluir de totales -->
                                            <button onclick="toggleExcluded({{ $detail->id }}, this)"
                                                title="{{ $detail->excluido ? 'Reactivar inscrito' : 'Excluir de la asignación' }}"
                                                class="p-1 rounded hover:bg-gray-100 transition-colors exclude-btn {{ $detail->excluido ? 'text-red-400' : 'text-gray-300 hover:text-red-500' }}">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                                </svg>
                                            </button>
                                            <!-- Ocultar de la vista -->
                                            <button onclick="toggleHidden({{ $detail->id }}, this)"
                                                title="{{ $detail->oculto ? 'Mostrar en la vista' : 'Ocultar de la vista' }}"
                                                class="p-1 rounded hover:bg-gray-100 transition-colors hide-btn {{ $detail->oculto ? 'text-blue-400' : 'text-gray-300 hover:text-blue-400' }}">
                                                @if($detail->oculto)
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 4.411m0 0L21 21"/>
                                                </svg>
                                                @else
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                                @endif
                                            </button>
                                        </div>
                                        @endcan
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
                const url = new URL(window.location.href);
                url.searchParams.set('program_id', prog.id);
                url.searchParams.set('mes', mes);
                url.searchParams.set('gestion', gestion);
                // Preservar filtro de responsable
                const rf = document.querySelector('[name="responsable_filter"]')?.value;
                if (rf) url.searchParams.set('responsable_filter', rf);
                else url.searchParams.delete('responsable_filter');
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

    // ——— Actualizar asignación (añadir inscritos nuevos) ———
    async function refreshAssignment(assignmentId) {
        const btn  = document.getElementById('refreshBtn');
        const icon = document.getElementById('refreshIcon');
        if (btn) btn.disabled = true;
        if (icon) icon.classList.add('animate-spin');

        try {
            const res = await fetch(`/accounting/nominal-assignments/${assignmentId}/refresh`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            });

            if (!res.ok) {
                const errText = await res.text();
                showToast(`Error ${res.status}: ${res.statusText}`, true);
                return;
            }

            const data = await res.json();
            if (data.success) {
                showToast(data.message);
                if (data.added > 0) setTimeout(() => location.reload(), 1200);
            } else {
                showToast(data.message || 'Error al actualizar', true);
            }
        } catch (e) {
            showToast('Error de conexión al actualizar', true);
        } finally {
            if (btn) btn.disabled = false;
            if (icon) icon.classList.remove('animate-spin');
        }
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

    // ——— Filtro por responsable ———
    function applyResponsableFilter(userId) {
        const url = new URL(window.location.href);
        if (userId) url.searchParams.set('responsable_filter', userId);
        else url.searchParams.delete('responsable_filter');
        // Mantener programa y período actuales
        url.searchParams.set('mes', mes);
        url.searchParams.set('gestion', gestion);
        if (programId) url.searchParams.set('program_id', programId);
        window.location.href = url.toString();
    }

    // ——— Buscador de inscritos (client-side) ———
    function filterParticipants(query) {
        const rows       = document.querySelectorAll('#assignmentBody tr[data-detail-id]');
        const q          = query.toLowerCase().trim();
        const clearBtn   = document.getElementById('clearSearchBtn');
        const countEl    = document.getElementById('searchResultCount');
        let visible      = 0;

        rows.forEach(row => {
            const name   = row.querySelector('.participant-name')?.textContent?.toLowerCase() || '';
            const match  = !q || name.includes(q);
            row.style.display = match ? '' : 'none';
            if (match) visible++;
        });

        clearBtn.classList.toggle('hidden', !q);

        if (q) {
            countEl.textContent = `${visible} de ${rows.length}`;
            countEl.classList.remove('hidden');
        } else {
            countEl.classList.add('hidden');
        }
    }

    function clearParticipantSearch() {
        const input = document.getElementById('participantSearch');
        input.value = '';
        filterParticipants('');
        input.focus();
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

    // ——— Ocultar / mostrar inscrito en la vista ———
    let showingHidden = false;

    // Al cargar la página, ocultar las filas marcadas como ocultas
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('tr.oculto-row').forEach(r => r.style.display = 'none');
    });

    async function toggleHidden(detailId, btn) {
        const res = await fetch(`/accounting/nominal-assignments/detail/${detailId}/toggle-hidden`, {
            method: 'PATCH',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        });
        const data = await res.json();
        if (!data.success) { showToast('Error', true); return; }

        const row = document.querySelector(`tr[data-detail-id="${detailId}"]`);

        if (data.oculto) {
            row.setAttribute('data-oculto', '1');
            row.classList.add('oculto-row');
            // Solo ocultar si el modo "mostrar ocultos" NO está activo
            if (!showingHidden) row.style.display = 'none';
            btn.classList.add('text-blue-400', 'hover:text-blue-600');
            btn.classList.remove('text-gray-300', 'hover:text-blue-400');
            btn.title = 'Mostrar en la vista';
            // Actualizar icono a ojo-tachado
            btn.innerHTML = `<svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 4.411m0 0L21 21"/>
            </svg>`;
        } else {
            row.setAttribute('data-oculto', '0');
            row.classList.remove('oculto-row');
            row.style.display = '';
            btn.classList.remove('text-blue-400', 'hover:text-blue-600');
            btn.classList.add('text-gray-300', 'hover:text-blue-400');
            btn.title = 'Ocultar de la vista';
            btn.innerHTML = `<svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>`;
        }

        // Actualizar botón global y contador
        const label = document.getElementById('showHiddenLabel');
        const showBtn = document.getElementById('showHiddenBtn');
        if (label && showBtn) {
            const n = data.hidden_count;
            if (n > 0) {
                label.textContent = showingHidden
                    ? `Ocultar ${n} oculto${n > 1 ? 's' : ''}`
                    : `Mostrar ${n} oculto${n > 1 ? 's' : ''}`;
                showBtn.classList.remove('hidden');
            } else {
                showBtn.classList.add('hidden');
            }
        }

        showToast(data.oculto ? 'Inscrito oculto de la vista' : 'Inscrito visible');
    }

    function toggleShowHidden(btn) {
        showingHidden = !showingHidden;
        const rows  = document.querySelectorAll('tr.oculto-row');
        const label = document.getElementById('showHiddenLabel');
        const n     = rows.length;

        rows.forEach(r => r.style.display = showingHidden ? '' : 'none');

        if (label) {
            label.textContent = showingHidden
                ? `Ocultar ${n} oculto${n > 1 ? 's' : ''}`
                : `Mostrar ${n} oculto${n > 1 ? 's' : ''}`;
        }

        btn.classList.toggle('bg-blue-100', showingHidden);
        btn.classList.toggle('border-blue-400', showingHidden);
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
        if (data.totals) {
            const t = data.totals;
            const set = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = fmt(val); };
            set('summaryTotalAsig', t.asignacion);
            set('sumRetImporte',    t.ret_importe);
            set('sumRetCobrado',    t.ret_cobrado);
            set('sumRetSaldo',      t.ret_saldo);
            set('sumVigImporte',    t.vig_importe);
            set('sumVigCobrado',    t.vig_cobrado);
            set('sumVigSaldo',      t.vig_saldo);
            set('sumAdelanto',      t.adelanto);
            set('sumMatSaldo',      t.mat_saldo);
            set('sumCerSaldo',      t.cer_saldo);
        }

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
