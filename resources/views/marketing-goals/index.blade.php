@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="w-full px-4 sm:px-6 lg:px-8">

        <!-- Encabezado -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Metas de Marketing</h1>
            <p class="mt-1 text-sm text-gray-600">Seguimiento mensual de metas e inscripciones por asesor</p>
        </div>

        <!-- Filtros -->
        <div class="bg-white rounded-lg shadow p-4 mb-4">
            <form id="filterForm" method="GET" action="{{ route('marketing-goals.index') }}" class="flex flex-wrap items-end gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Mes</label>
                    <select name="month" onchange="document.getElementById('filterForm').submit()"
                        class="block px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        @foreach(['1'=>'Enero','2'=>'Febrero','3'=>'Marzo','4'=>'Abril','5'=>'Mayo','6'=>'Junio','7'=>'Julio','8'=>'Agosto','9'=>'Septiembre','10'=>'Octubre','11'=>'Noviembre','12'=>'Diciembre'] as $num => $nombre)
                            <option value="{{ $num }}" @selected($month == $num)>{{ $nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Año</label>
                    <select name="year" onchange="document.getElementById('filterForm').submit()"
                        class="block px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        @foreach(range(date('Y') - 1, date('Y') + 1) as $y)
                            <option value="{{ $y }}" @selected($year == $y)>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Equipo</label>
                    <select name="team_id" onchange="document.getElementById('filterForm').submit()"
                        class="block px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Todos los equipos</option>
                        @foreach($teams as $team)
                            <option value="{{ $team->id }}" @selected($teamId == $team->id)>{{ $team->name }}</option>
                        @endforeach
                    </select>
                </div>
                <a href="{{ route('marketing-goals.index') }}" class="px-3 py-2 bg-gray-200 text-gray-700 rounded-md text-sm hover:bg-gray-300">
                    Limpiar
                </a>
            </form>
        </div>

        <!-- Gestión de tipos de inscripción -->
        @can('marketing.manage_goals')
        @php
            $visibleCourseTypes = $courseTypes->filter(fn($ct) => !in_array($ct->id, $hiddenTypeIds));
        @endphp
        <div x-data="{ open: false }" class="bg-white rounded-lg shadow mb-4">
            <button @click="open = !open" type="button"
                class="w-full flex items-center justify-between px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50 rounded-lg">
                <span>Tipos de Inscripción
                    @if(count($hiddenTypeIds) > 0)
                        <span class="ml-2 text-xs font-normal text-orange-600">({{ count($hiddenTypeIds) }} oculto{{ count($hiddenTypeIds) > 1 ? 's' : '' }} este mes)</span>
                    @endif
                </span>
                <svg :class="{ 'rotate-180': open }" class="h-4 w-4 text-gray-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
            <div x-show="open" x-transition class="px-4 pb-4 border-t border-gray-100">
                <p class="text-xs text-gray-500 mt-3 mb-2">Puedes ocultar un tipo para este mes sin perder los datos registrados. Los tipos ocultos no aparecen en la tabla.</p>
                <div class="flex flex-wrap items-center gap-2">
                    @foreach($courseTypes as $ct)
                    @php $hidden = in_array($ct->id, $hiddenTypeIds); @endphp
                    <div class="flex items-center gap-1 px-3 py-1.5 rounded-full text-sm font-medium border
                        {{ $hidden ? 'bg-gray-100 text-gray-400 border-gray-200' : 'bg-blue-50 text-blue-800 border-blue-200' }}">
                        <span class="{{ $hidden ? 'line-through' : '' }}">{{ $ct->name }}</span>
                        <form method="POST" action="{{ route('marketing-course-types.toggle-month', $ct) }}">
                            @csrf
                            <input type="hidden" name="month" value="{{ $month }}">
                            <input type="hidden" name="year" value="{{ $year }}">
                            <button type="submit"
                                title="{{ $hidden ? 'Mostrar en este mes' : 'Ocultar en este mes' }}"
                                class="ml-1 {{ $hidden ? 'text-gray-400 hover:text-green-600' : 'text-blue-400 hover:text-orange-600' }}">
                                @if($hidden)
                                    {{-- Eye icon (show) --}}
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                @else
                                    {{-- Eye-off icon (hide) --}}
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                    </svg>
                                @endif
                            </button>
                        </form>
                    </div>
                    @endforeach

                    <form method="POST" action="{{ route('marketing-course-types.store') }}" class="flex items-center gap-2">
                        @csrf
                        <input type="text" name="name" placeholder="Nuevo tipo..." required
                            class="px-3 py-1.5 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 w-36">
                        <button type="submit" class="px-3 py-1.5 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700">
                            + Agregar
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endcan

        <!-- Formulario nueva meta -->
        @can('marketing.manage_goals')
        <div class="bg-white rounded-lg shadow p-4 mb-4">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Agregar Meta</h3>
            <form method="POST" action="{{ route('marketing-goals.store') }}" class="flex flex-wrap items-end gap-3">
                @csrf
                <input type="hidden" name="month" value="{{ $month }}">
                <input type="hidden" name="year" value="{{ $year }}">

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Asesor</label>
                    <select name="user_id" required
                        class="block px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Seleccionar asesor...</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Equipo</label>
                    <select name="team_id"
                        class="block px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Sin equipo</option>
                        @foreach($teams as $team)
                            <option value="{{ $team->id }}">{{ $team->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Meta mensual</label>
                    <input type="number" name="monthly_goal" min="0" required placeholder="0"
                        class="block w-28 px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>

                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700">
                    Agregar
                </button>
            </form>
        </div>
        @endcan

        <!-- Tabla -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            @if($goals->isEmpty())
                <div class="p-8 text-center text-gray-500 text-sm">
                    No hay metas registradas para este mes. Agregue una meta usando el formulario de arriba.
                </div>
            @else
            @php
                // Only show course types not hidden for this month
                $visibleTypes = $courseTypes->filter(fn($ct) => !in_array($ct->id, $hiddenTypeIds))->values();
                $colors = [
                    ['header' => '#3730a3', 'sub' => '#4338ca', 'row' => 'bg-indigo-50'],
                    ['header' => '#6b21a8', 'sub' => '#7c3aed', 'row' => 'bg-purple-50'],
                    ['header' => '#065f46', 'sub' => '#047857', 'row' => 'bg-emerald-50'],
                    ['header' => '#92400e', 'sub' => '#b45309', 'row' => 'bg-amber-50'],
                    ['header' => '#1e3a5f', 'sub' => '#1e4d8c', 'row' => 'bg-sky-50'],
                    ['header' => '#7f1d1d', 'sub' => '#991b1b', 'row' => 'bg-rose-50'],
                ];
            @endphp
            <div class="overflow-x-auto">
                <table class="min-w-full text-xs border-collapse">
                    <thead>
                        <tr class="bg-gray-800 text-white">
                            <th rowspan="2" class="px-3 py-2 text-left font-medium border border-gray-600 min-w-[160px]">Asesor</th>
                            <th rowspan="2" class="px-3 py-2 text-left font-medium border border-gray-600">Equipo</th>
                            <th rowspan="2" class="px-3 py-2 text-center font-medium border border-gray-600">Meta<br>Mes</th>
                            <th rowspan="2" class="px-3 py-2 text-center font-medium border border-gray-600">Deuda</th>
                            <th rowspan="2" class="px-3 py-2 text-center font-medium border border-gray-600">Meta<br>Total</th>
                            @foreach($visibleTypes as $idx => $ct)
                            @php $c = $colors[$idx % 6]; @endphp
                            <th colspan="3" class="px-3 py-2 text-center font-medium border border-gray-600"
                                style="background-color: {{ $c['header'] }}">
                                {{ $ct->name }}
                            </th>
                            @endforeach
                            <th rowspan="2" class="px-3 py-2 text-center font-medium border border-gray-600 bg-green-700">Total<br>Nacional</th>
                            <th rowspan="2" class="px-3 py-2 text-center font-medium border border-gray-600 bg-red-700">Dif.</th>
                            @can('marketing.manage_goals')
                            <th rowspan="2" class="px-3 py-2 text-center font-medium border border-gray-600 w-8"></th>
                            @endcan
                        </tr>
                        <tr class="bg-gray-700 text-white">
                            @foreach($visibleTypes as $idx => $ct)
                            @php $c = $colors[$idx % 6]; @endphp
                            <th class="px-2 py-1 text-center font-medium border border-gray-600" style="background-color: {{ $c['sub'] }}">Comp.</th>
                            <th class="px-2 py-1 text-center font-medium border border-gray-600" style="background-color: {{ $c['sub'] }}">Adel.</th>
                            <th class="px-2 py-1 text-center font-medium border border-gray-600" style="background-color: {{ $c['sub'] }}">Cpdo.</th>
                            @endforeach
                        </tr>
                    </thead>

                    @php
                        $totalMetaMes   = 0;
                        $totalDeuda     = 0;
                        $totalMetaTotal = 0;
                        $totalNacional  = 0;
                        $totalDif       = 0;
                        $totalByType    = [];
                        foreach ($visibleTypes as $ct) {
                            $totalByType[$ct->id] = ['completo' => 0, 'adelanto' => 0, 'completando' => 0];
                        }
                    @endphp

                    @foreach($goals as $goal)
                    @php
                        $totalMetaMes   += $goal->monthly_goal;
                        $totalDeuda     += $goal->debt_from_previous;
                        $totalMetaTotal += $goal->metaTotal();
                        $totalNacional  += $goal->nationalTotal();
                        $totalDif       += $goal->diferencia();
                        foreach ($visibleTypes as $ct) {
                            $totalByType[$ct->id]['completo']    += $goal->totalForType($ct->id, 'completo');
                            $totalByType[$ct->id]['adelanto']    += $goal->totalForType($ct->id, 'adelanto');
                            $totalByType[$ct->id]['completando'] += $goal->totalForType($ct->id, 'completando');
                        }
                    @endphp

                    {{-- Each goal in its own <tbody> with x-data for expand/collapse --}}
                    <tbody x-data="{ expanded: false }" class="divide-y divide-gray-100">

                        <tr class="hover:bg-gray-50 cursor-pointer" data-goal-id="{{ $goal->id }}">

                            <td class="px-3 py-2 border border-gray-200 font-medium text-gray-900"
                                @click="expanded = !expanded">
                                <div class="flex items-center gap-1">
                                    <svg :class="{ 'rotate-90': expanded }"
                                         class="h-3 w-3 text-gray-400 transition-transform flex-shrink-0"
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                    {{ $goal->user->name }}
                                </div>
                            </td>

                            <td class="px-3 py-2 border border-gray-200 text-gray-600">
                                {{ $goal->team?->name ?? '—' }}
                            </td>

                            <td class="px-3 py-2 border border-gray-200 text-center"
                                data-field="monthly_goal" data-goal-id="{{ $goal->id }}">
                                <span class="inline-block editable-goal-cell cursor-pointer hover:bg-yellow-50 rounded px-1"
                                      data-value="{{ $goal->monthly_goal }}">
                                    {{ $goal->monthly_goal }}
                                </span>
                            </td>

                            <td class="px-3 py-2 border border-gray-200 text-center text-orange-600 font-medium">
                                {{ $goal->debt_from_previous > 0 ? '+' . $goal->debt_from_previous : $goal->debt_from_previous }}
                            </td>

                            <td class="px-3 py-2 border border-gray-200 text-center font-semibold goal-meta-total"
                                data-goal-id="{{ $goal->id }}">
                                {{ $goal->metaTotal() }}
                            </td>

                            @foreach($visibleTypes as $idx => $ct)
                            @php $c = $colors[$idx % 6]; @endphp
                            <td class="px-3 py-2 border border-gray-200 text-center goal-type-completo {{ $c['row'] }}"
                                data-goal-id="{{ $goal->id }}" data-type-id="{{ $ct->id }}">
                                {{ $goal->totalForType($ct->id, 'completo') }}
                            </td>
                            <td class="px-3 py-2 border border-gray-200 text-center goal-type-adelanto {{ $c['row'] }}"
                                data-goal-id="{{ $goal->id }}" data-type-id="{{ $ct->id }}">
                                {{ $goal->totalForType($ct->id, 'adelanto') }}
                            </td>
                            <td class="px-3 py-2 border border-gray-200 text-center goal-type-completando {{ $c['row'] }}"
                                data-goal-id="{{ $goal->id }}" data-type-id="{{ $ct->id }}">
                                {{ $goal->totalForType($ct->id, 'completando') }}
                            </td>
                            @endforeach

                            <td class="px-3 py-2 border border-gray-200 text-center font-semibold bg-green-50 goal-nacional"
                                data-goal-id="{{ $goal->id }}">
                                {{ $goal->nationalTotal() }}
                            </td>

                            <td class="px-3 py-2 border border-gray-200 text-center font-semibold goal-diferencia
                                {{ $goal->diferencia() > 0 ? 'text-red-600 bg-red-50' : ($goal->diferencia() < 0 ? 'text-green-600 bg-green-50' : 'text-gray-500') }}"
                                data-goal-id="{{ $goal->id }}">
                                {{ $goal->diferencia() }}
                            </td>

                            @can('marketing.manage_goals')
                            <td class="px-3 py-2 border border-gray-200 text-center">
                                <form method="POST" action="{{ route('marketing-goals.destroy', $goal) }}"
                                      onsubmit="return confirm('¿Eliminar la meta de {{ $goal->user->name }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700" title="Eliminar">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </td>
                            @endcan
                        </tr>

                        {{-- Weekly detail rows (expandable, children of this tbody) --}}
                        @foreach($goal->weeks as $week)
                        <tr x-show="expanded" x-transition class="bg-blue-50/40 text-gray-700"
                            data-week-id="{{ $week->id }}" data-goal-id="{{ $goal->id }}">

                            <td class="px-3 py-2 border border-gray-200">
                                <div class="flex items-center gap-1 flex-wrap pl-5">
                                    <input type="date"
                                           class="text-xs border border-gray-300 rounded px-1 py-0.5 focus:outline-none focus:ring-1 focus:ring-blue-400"
                                           value="{{ $week->week_start?->format('Y-m-d') }}"
                                           data-week-id="{{ $week->id }}"
                                           data-field="week_start"
                                           onchange="updateWeekField(this)"
                                           onblur="updateWeekField(this)">
                                    <span class="text-gray-400">→</span>
                                    <input type="date"
                                           class="text-xs border border-gray-300 rounded px-1 py-0.5 focus:outline-none focus:ring-1 focus:ring-blue-400"
                                           value="{{ $week->week_end?->format('Y-m-d') }}"
                                           data-week-id="{{ $week->id }}"
                                           data-field="week_end"
                                           onchange="updateWeekField(this)"
                                           onblur="updateWeekField(this)">
                                </div>
                            </td>

                            <td class="px-2 py-2 border border-gray-200 text-center">
                                <div class="text-gray-400 text-xs mb-0.5">Reunión</div>
                                <input type="date"
                                       class="text-xs border border-gray-300 rounded px-1 py-0.5 focus:outline-none focus:ring-1 focus:ring-indigo-400"
                                       value="{{ $week->meeting_date?->format('Y-m-d') }}"
                                       data-week-id="{{ $week->id }}"
                                       data-field="meeting_date"
                                       onchange="updateWeekField(this)">
                            </td>

                            <td class="px-2 py-2 border border-gray-200 text-center bg-blue-100/60"
                                data-week-id="{{ $week->id }}" data-field="weekly_goal">
                                <div class="text-gray-400 text-xs mb-0.5">Meta Sem.</div>
                                <span class="inline-block editable-week-cell cursor-pointer hover:bg-yellow-50 rounded px-1 font-medium"
                                      data-value="{{ $week->weekly_goal }}">
                                    {{ $week->weekly_goal }}
                                </span>
                            </td>

                            <td class="border border-gray-200"></td>
                            <td class="border border-gray-200"></td>

                            @foreach($visibleTypes as $idx => $ct)
                            @php
                                $entry = $week->entries->firstWhere('course_type_id', $ct->id);
                                $c = $colors[$idx % 6];
                            @endphp
                            <td class="px-1 py-1 border border-gray-200 text-center {{ $c['row'] }}">
                                <input type="number" min="0"
                                    class="w-12 text-center text-xs border border-gray-300 rounded px-1 py-0.5 focus:outline-none focus:ring-1 focus:ring-blue-400"
                                    value="{{ $entry?->completo ?? 0 }}"
                                    data-week-id="{{ $week->id }}"
                                    data-course-type-id="{{ $ct->id }}"
                                    data-field="completo"
                                    onchange="updateEntryField(this)">
                            </td>
                            <td class="px-1 py-1 border border-gray-200 text-center {{ $c['row'] }}">
                                <input type="number" min="0"
                                    class="w-12 text-center text-xs border border-gray-300 rounded px-1 py-0.5 focus:outline-none focus:ring-1 focus:ring-blue-400"
                                    value="{{ $entry?->adelanto ?? 0 }}"
                                    data-week-id="{{ $week->id }}"
                                    data-course-type-id="{{ $ct->id }}"
                                    data-field="adelanto"
                                    onchange="updateEntryField(this)">
                            </td>
                            <td class="px-1 py-1 border border-gray-200 text-center {{ $c['row'] }}">
                                <input type="number" min="0"
                                    class="w-12 text-center text-xs border border-gray-300 rounded px-1 py-0.5 focus:outline-none focus:ring-1 focus:ring-blue-400"
                                    value="{{ $entry?->completando ?? 0 }}"
                                    data-week-id="{{ $week->id }}"
                                    data-course-type-id="{{ $ct->id }}"
                                    data-field="completando"
                                    onchange="updateEntryField(this)">
                            </td>
                            @endforeach

                            <td class="border border-gray-200"></td>
                            <td class="border border-gray-200"></td>
                            @can('marketing.manage_goals')
                            <td class="border border-gray-200"></td>
                            @endcan
                        </tr>
                        @endforeach

                    </tbody>
                    @endforeach

                    <tbody>
                        <tr class="bg-gray-800 text-white font-semibold text-xs">
                            <td colspan="2" class="px-3 py-2 border border-gray-600">TOTALES</td>
                            <td class="px-3 py-2 border border-gray-600 text-center">{{ $totalMetaMes }}</td>
                            <td class="px-3 py-2 border border-gray-600 text-center text-orange-300">{{ $totalDeuda > 0 ? '+' . $totalDeuda : $totalDeuda }}</td>
                            <td class="px-3 py-2 border border-gray-600 text-center">{{ $totalMetaTotal }}</td>
                            @foreach($visibleTypes as $ct)
                            <td class="px-3 py-2 border border-gray-600 text-center">{{ $totalByType[$ct->id]['completo'] }}</td>
                            <td class="px-3 py-2 border border-gray-600 text-center">{{ $totalByType[$ct->id]['adelanto'] }}</td>
                            <td class="px-3 py-2 border border-gray-600 text-center">{{ $totalByType[$ct->id]['completando'] }}</td>
                            @endforeach
                            <td class="px-3 py-2 border border-gray-600 text-center bg-green-900">{{ $totalNacional }}</td>
                            <td class="px-3 py-2 border border-gray-600 text-center {{ $totalDif > 0 ? 'text-red-300' : ($totalDif < 0 ? 'text-green-300' : '') }}">{{ $totalDif }}</td>
                            @can('marketing.manage_goals')
                            <td class="border border-gray-600"></td>
                            @endcan
                        </tr>
                    </tbody>
                </table>
            </div>
            @endif
        </div>

        <div class="mt-4 flex flex-wrap gap-4 text-xs text-gray-500">
            <span>Comp. = Completo &nbsp;|&nbsp; Adel. = Adelanto &nbsp;|&nbsp; Cpdo. = Completando</span>
            <span class="flex items-center gap-1"><span class="inline-block w-3 h-3 bg-green-100 border border-green-300 rounded"></span> Total Nacional = Completos + Adelantos (todos los tipos)</span>
            <span class="flex items-center gap-1"><span class="inline-block w-3 h-3 bg-red-100 border border-red-300 rounded"></span> Diferencia positiva = deuda pendiente</span>
            <span>Haga clic en la fila del asesor para ver/editar el detalle semanal.</span>
        </div>
    </div>
</div>

<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// ─── Edición inline: Meta Mensual ────────────────────────────────────────────
function attachGoalCellListener(span) {
    span.addEventListener('click', function onGoalCellClick() {
        const td      = span.closest('td');
        const goalId  = td.dataset.goalId;
        const current = parseInt(span.dataset.value) || 0;

        const input = document.createElement('input');
        input.type      = 'number';
        input.min       = '0';
        input.value     = current;
        input.className = 'w-16 text-center text-xs border border-yellow-400 rounded px-1 py-0.5 focus:outline-none focus:ring-1 focus:ring-yellow-400';

        span.replaceWith(input);
        input.focus();
        input.select();

        function save() {
            const newVal = parseInt(input.value) || 0;
            fetch(`/marketing-goals/${goalId}`, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({ monthly_goal: newVal }),
            })
            .then(r => { if (!r.ok) throw new Error(r.status); return r.json(); })
            .then(data => {
                const newSpan = document.createElement('span');
                newSpan.className    = 'inline-block editable-goal-cell cursor-pointer hover:bg-yellow-50 rounded px-1';
                newSpan.dataset.value = data.monthly_goal;
                newSpan.textContent  = data.monthly_goal;
                input.replaceWith(newSpan);
                attachGoalCellListener(newSpan);

                document.querySelectorAll(`.goal-meta-total[data-goal-id="${goalId}"]`).forEach(el => el.textContent = data.meta_total);
                updateDiferenciaCell(goalId, data.diferencia);
            })
            .catch(err => { console.error('Error guardando meta:', err); input.replaceWith(span); });
        }

        input.addEventListener('blur', save);
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter')  { e.preventDefault(); input.blur(); }
            if (e.key === 'Escape') { input.replaceWith(span); }
        });
    });
}
document.querySelectorAll('.editable-goal-cell').forEach(attachGoalCellListener);

// ─── Edición inline: Meta Semanal ────────────────────────────────────────────
function attachWeekCellListener(span) {
    span.addEventListener('click', function() {
        const td      = span.closest('td');
        const weekId  = td.dataset.weekId;
        const current = parseInt(span.dataset.value) || 0;

        const input = document.createElement('input');
        input.type      = 'number';
        input.min       = '0';
        input.value     = current;
        input.className = 'w-14 text-center text-xs border border-blue-400 rounded px-1 py-0.5 focus:outline-none focus:ring-1 focus:ring-blue-400';

        span.replaceWith(input);
        input.focus();
        input.select();

        function save() {
            const newVal = parseInt(input.value) || 0;
            patchWeek(weekId, 'weekly_goal', newVal, function(data) {
                const newSpan = document.createElement('span');
                newSpan.className    = 'inline-block editable-week-cell cursor-pointer hover:bg-yellow-50 rounded px-1 font-medium';
                newSpan.dataset.value = data.week.weekly_goal;
                newSpan.textContent  = data.week.weekly_goal;
                input.replaceWith(newSpan);
                attachWeekCellListener(newSpan);
            }, () => input.replaceWith(span));
        }

        input.addEventListener('blur', save);
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter')  { e.preventDefault(); input.blur(); }
            if (e.key === 'Escape') { input.replaceWith(span); }
        });
    });
}
document.querySelectorAll('.editable-week-cell').forEach(attachWeekCellListener);

// ─── Fechas de semana ─────────────────────────────────────────────────────────
function updateWeekField(el) {
    // Avoid double-firing from both onchange and onblur
    if (el._saving) return;
    el._saving = true;
    setTimeout(() => { el._saving = false; }, 500);

    const weekId = el.dataset.weekId;
    const field  = el.dataset.field;
    const value  = el.value;

    patchWeek(weekId, field, value, null, function() {
        console.error('Error guardando campo de semana:', field);
    });
}

function patchWeek(weekId, field, value, onSuccess, onError) {
    fetch(`/marketing-goal-weeks/${weekId}`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify({ field, value }),
    })
    .then(r => { if (!r.ok) throw new Error(r.status); return r.json(); })
    .then(data => { if (onSuccess) onSuccess(data); })
    .catch(err => { console.error('patchWeek error:', err); if (onError) onError(); });
}

// ─── Entry: completo / adelanto / completando ─────────────────────────────────
function updateEntryField(el) {
    const weekId       = el.dataset.weekId;
    const courseTypeId = el.dataset.courseTypeId;
    const field        = el.dataset.field;
    const value        = parseInt(el.value) || 0;

    fetch(`/marketing-goal-week-entries/${weekId}/${courseTypeId}`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify({ field, value }),
    })
    .then(r => { if (!r.ok) throw new Error(r.status); return r.json(); })
    .then(data => {
        const goalId = data.goal.id;

        Object.entries(data.goal.type_totals).forEach(([typeId, totals]) => {
            document.querySelectorAll(`.goal-type-completo[data-goal-id="${goalId}"][data-type-id="${typeId}"]`)
                .forEach(el => el.textContent = totals.completo);
            document.querySelectorAll(`.goal-type-adelanto[data-goal-id="${goalId}"][data-type-id="${typeId}"]`)
                .forEach(el => el.textContent = totals.adelanto);
            document.querySelectorAll(`.goal-type-completando[data-goal-id="${goalId}"][data-type-id="${typeId}"]`)
                .forEach(el => el.textContent = totals.completando);
        });

        document.querySelectorAll(`.goal-nacional[data-goal-id="${goalId}"]`)
            .forEach(el => el.textContent = data.goal.national_total);
        updateDiferenciaCell(goalId, data.goal.diferencia);
    })
    .catch(err => console.error('Error actualizando entry:', err));
}

// ─── Diferencia ───────────────────────────────────────────────────────────────
function updateDiferenciaCell(goalId, diferencia) {
    document.querySelectorAll(`.goal-diferencia[data-goal-id="${goalId}"]`).forEach(function(el) {
        el.textContent = diferencia;
        el.classList.remove('text-red-600', 'bg-red-50', 'text-green-600', 'bg-green-50', 'text-gray-500');
        if (diferencia > 0) {
            el.classList.add('text-red-600', 'bg-red-50');
        } else if (diferencia < 0) {
            el.classList.add('text-green-600', 'bg-green-50');
        } else {
            el.classList.add('text-gray-500');
        }
    });
}
</script>
@endsection
