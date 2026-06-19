@extends('layouts.app')

@section('content')
<div>
    <div class="w-full sm:px-6 lg:px-8">

        {{-- Header + Filtros --}}
        <div style="margin-bottom:1.5rem;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:0.75rem;">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Dashboard Ingresos vs Egresos</h2>
                <p class="mt-1 text-gray-600">Comparativa anual de ingresos y egresos por gestión</p>
            </div>

            <form method="GET" action="{{ route('dashboard.income-expense') }}"
                  style="display:flex;flex-wrap:wrap;align-items:center;gap:0.5rem;">

                {{-- Gestión --}}
                <div style="position:relative;display:inline-flex;align-items:center;">
                    <select name="year" onchange="this.form.submit()"
                            style="appearance:none;-webkit-appearance:none;padding:0.35rem 1.75rem 0.35rem 0.65rem;font-size:0.8rem;font-weight:600;border:1px solid #d1d5db;border-radius:0.375rem;outline:none;cursor:pointer;background:#fff;color:#374151;"
                            onfocus="this.style.borderColor='#6366f1'" onblur="this.style.borderColor='#d1d5db'">
                        @foreach($availableYears as $ay)
                            <option value="{{ $ay }}" @selected((int)$year===(int)$ay)>{{ $ay }}</option>
                        @endforeach
                    </select>
                    <svg style="position:absolute;right:0.45rem;top:50%;transform:translateY(-50%);width:0.65rem;height:0.65rem;color:#9ca3af;pointer-events:none;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                </div>

                @if($incomeEntities->isNotEmpty())
                <div style="position:relative;display:inline-flex;align-items:center;">
                    <select name="income_entity" onchange="this.form.submit()"
                            style="appearance:none;-webkit-appearance:none;padding:0.35rem 1.75rem 0.35rem 0.65rem;font-size:0.8rem;border:1px solid {{ $incomeEntity ? '#059669' : '#d1d5db' }};border-radius:0.375rem;outline:none;cursor:pointer;background:{{ $incomeEntity ? '#ecfdf5' : '#fff' }};color:{{ $incomeEntity ? '#065f46' : '#6b7280' }};font-weight:{{ $incomeEntity ? '600' : '400' }};"
                            onfocus="this.style.borderColor='#059669'" onblur="this.style.borderColor='{{ $incomeEntity ? '#059669' : '#d1d5db' }}'">
                        <option value="">Entidad ingresos (Todas)</option>
                        @foreach($incomeEntities as $entity)
                            <option value="{{ $entity->id }}" @selected($incomeEntity == $entity->id)>{{ $entity->name }}</option>
                        @endforeach
                    </select>
                    <svg style="position:absolute;right:0.45rem;top:50%;transform:translateY(-50%);width:0.65rem;height:0.65rem;color:#9ca3af;pointer-events:none;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                </div>
                @endif

                @if($expenseEntities->isNotEmpty())
                <div style="position:relative;display:inline-flex;align-items:center;">
                    <select name="expense_entity" onchange="this.form.submit()"
                            style="appearance:none;-webkit-appearance:none;padding:0.35rem 1.75rem 0.35rem 0.65rem;font-size:0.8rem;border:1px solid {{ $expenseEntity ? '#dc2626' : '#d1d5db' }};border-radius:0.375rem;outline:none;cursor:pointer;background:{{ $expenseEntity ? '#fff1f2' : '#fff' }};color:{{ $expenseEntity ? '#7f1d1d' : '#6b7280' }};font-weight:{{ $expenseEntity ? '600' : '400' }};"
                            onfocus="this.style.borderColor='#dc2626'" onblur="this.style.borderColor='{{ $expenseEntity ? '#dc2626' : '#d1d5db' }}'">
                        <option value="">Entidad egresos (Todas)</option>
                        @foreach($expenseEntities as $entity)
                            <option value="{{ $entity->id }}" @selected($expenseEntity == $entity->id)>{{ $entity->name }}</option>
                        @endforeach
                    </select>
                    <svg style="position:absolute;right:0.45rem;top:50%;transform:translateY(-50%);width:0.65rem;height:0.65rem;color:#9ca3af;pointer-events:none;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                </div>
                @endif

                @if($incomeEntity || $expenseEntity)
                <a href="{{ route('dashboard.income-expense', ['year' => $year]) }}"
                   style="padding:0.35rem 0.75rem;background:#f3f4f6;color:#6b7280;border:1px solid #d1d5db;border-radius:0.375rem;font-size:0.8rem;font-weight:500;text-decoration:none;display:inline-flex;align-items:center;gap:0.3rem;">
                    <svg style="width:0.75rem;height:0.75rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                    Limpiar
                </a>
                @endif
            </form>
        </div>

        {{-- Cards resumen (5) --}}
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(155px,1fr));gap:1rem;margin-bottom:1.5rem;">
            <div class="bg-white rounded-lg shadow p-4" style="border-left:4px solid #10b981;">
                <p style="font-size:0.75rem;color:#6b7280;">Total Ingresos</p>
                <p style="margin-top:0.4rem;font-size:1.4rem;font-weight:800;color:#065f46;">Bs. {{ number_format($totalIncome, 2) }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4" style="border-left:4px solid #f43f5e;">
                <p style="font-size:0.75rem;color:#6b7280;">Total Egresos</p>
                <p style="margin-top:0.4rem;font-size:1.4rem;font-weight:800;color:#be123c;">Bs. {{ number_format($totalExpense, 2) }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4" style="border-left:4px solid {{ $balance >= 0 ? '#6366f1' : '#f59e0b' }};">
                <p style="font-size:0.75rem;color:#6b7280;">Balance</p>
                <p style="margin-top:0.4rem;font-size:1.4rem;font-weight:800;color:{{ $balance >= 0 ? '#4338ca' : '#b45309' }};">Bs. {{ number_format($balance, 2) }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4" style="border-left:4px solid #dc2626;">
                <p style="font-size:0.75rem;color:#6b7280;">Egr. Operativos</p>
                <p style="margin-top:0.4rem;font-size:1.4rem;font-weight:800;color:#991b1b;">Bs. {{ number_format($expenseByCategory['operativo'] ?? 0, 2) }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4" style="border-left:4px solid #f59e0b;">
                <p style="font-size:0.75rem;color:#6b7280;">Otros Egresos</p>
                <p style="margin-top:0.4rem;font-size:1.4rem;font-weight:800;color:#92400e;">Bs. {{ number_format($expenseByCategory['otro'] ?? 0, 2) }}</p>
            </div>
        </div>

        {{-- Gráfico principal: Comparativa Mensual --}}
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Comparativa Mensual</h3>
            <canvas id="incomeExpenseChart"></canvas>
        </div>

        {{-- Fila 2: Tabs Acumulado/Proyección | Gráfico de Diferencias --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

            {{-- Tabs: Acumulado Anual / Proyección --}}
            <div class="bg-white rounded-lg shadow p-6">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;gap:0.5rem;flex-wrap:wrap;">
                    <h3 class="text-lg font-semibold text-gray-900" id="accProjTitle">Acumulado Anual</h3>
                    <div style="display:flex;border:1px solid #e5e7eb;border-radius:0.375rem;overflow:hidden;font-size:0.8rem;">
                        <button id="tab-acc" onclick="switchAccProjTab('acc')"
                                style="padding:0.3rem 0.8rem;font-weight:600;background:#4f46e5;color:#fff;border:none;cursor:pointer;">Acumulado</button>
                        <button id="tab-proj" onclick="switchAccProjTab('proj')"
                                style="padding:0.3rem 0.8rem;font-weight:500;background:#fff;color:#6b7280;border:none;cursor:pointer;">Proyección</button>
                    </div>
                </div>
                <div style="position:relative;">
                    <div id="panel-acc">
                        <canvas id="accumulatedChart"></canvas>
                    </div>
                    <div id="panel-proj" style="display:none;">
                        <canvas id="annualProjectionChart"></canvas>
                    </div>
                </div>
            </div>

            {{-- Nuevo: Diferencias Mensuales --}}
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Diferencias Mensuales</h3>
                <canvas id="monthlyDifferenceChart"></canvas>
            </div>
        </div>

        {{-- Fila 3: Egresos por Categoría --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow p-6" style="display:flex;flex-direction:column;">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Egresos por Categoría</h3>
                <div style="flex:1;display:flex;align-items:center;justify-content:center;gap:3rem;">
                    <div style="width:200px;height:200px;flex-shrink:0;">
                        <canvas id="expenseCategoryDonut" width="200" height="200"></canvas>
                    </div>
                    <div id="expenseCategoryLegend"></div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Egresos Mensuales por Categoría</h3>
                <canvas id="expenseCategoryMonthly"></canvas>
            </div>
        </div>

        {{-- Fila 4: Top Ítems --}}
        <div class="bg-white rounded-lg shadow p-6">
            <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;margin-bottom:1rem;gap:0.75rem;">
                <h3 class="text-lg font-semibold text-gray-900" id="topItemsTitle">Top Ítems — Ingresos</h3>
                <div style="display:flex;border:1px solid #e5e7eb;border-radius:0.375rem;overflow:hidden;font-size:0.8rem;">
                    <button id="tab-top-income" onclick="switchTopItemsTab('income')"
                            style="padding:0.3rem 0.8rem;font-weight:600;background:#4f46e5;color:#fff;border:none;cursor:pointer;">Ingresos</button>
                    <button id="tab-top-expense" onclick="switchTopItemsTab('expense')"
                            style="padding:0.3rem 0.8rem;font-weight:500;background:#fff;color:#6b7280;border:none;cursor:pointer;">Egresos</button>
                </div>
            </div>
            <div style="position:relative;height:260px;">
                <div id="panel-top-income" style="position:absolute;inset:0;">
                    <canvas id="topIncomeChart" style="width:100%;height:100%;"></canvas>
                </div>
                <div id="panel-top-expense" style="position:absolute;inset:0;opacity:0;pointer-events:none;">
                    <canvas id="topExpenseChart" style="width:100%;height:100%;"></canvas>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    const chartData = @json($chartData);

    function bsTick(value) {
        return 'Bs. ' + Number(value).toLocaleString();
    }

    function renderEmptyState(canvasEl, message) {
        canvasEl.style.display = 'none';
        const div = document.createElement('div');
        div.style.cssText = 'display:flex;flex-direction:column;align-items:center;justify-content:center;padding:3rem 0;color:#9ca3af;';
        div.innerHTML = `<svg style="width:3rem;height:3rem;margin-bottom:0.75rem;opacity:0.4;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg><p style="font-size:0.875rem;">${message}</p>`;
        canvasEl.parentNode.appendChild(div);
    }

    const moneyScale = {
        beginAtZero: true,
        ticks: { callback: v => bsTick(v) }
    };

    const moneyTooltip = {
        callbacks: {
            label: ctx => ctx.dataset.label + ': Bs. ' + Number(ctx.parsed.y).toLocaleString()
        }
    };

    // ── Comparativa Mensual ──────────────────────────────────────────────────
    const incomeExpenseCanvas = document.getElementById('incomeExpenseChart');
    const hasMainData = (chartData.incomeSeries||[]).some(v=>v>0)||(chartData.expenseSeries||[]).some(v=>v>0);
    if (!hasMainData) {
        renderEmptyState(incomeExpenseCanvas, 'Sin datos para el período seleccionado');
    } else {
        new Chart(incomeExpenseCanvas, {
            type: 'bar',
            data: {
                labels: chartData.months,
                datasets: [
                    { type:'bar',  label:'Ingresos', data:chartData.incomeSeries,  backgroundColor:'rgba(16,185,129,0.45)', borderColor:'rgb(16,185,129)',  borderWidth:1, borderRadius:4 },
                    { type:'line', label:'Egresos',  data:chartData.expenseSeries, borderColor:'rgb(244,63,94)', backgroundColor:'rgba(244,63,94,0.12)', borderWidth:3, tension:0.3, fill:true, pointRadius:3, pointHoverRadius:5 }
                ]
            },
            options: { responsive:true, aspectRatio:2.5, plugins:{ legend:{display:true}, tooltip:moneyTooltip }, scales:{ y:moneyScale } }
        });
    }

    // ── Acumulado Anual ──────────────────────────────────────────────────────
    const accCanvas = document.getElementById('accumulatedChart');
    const hasAccData = (chartData.incomeAccumulated||[]).some(v=>v>0)||(chartData.expenseAccumulated||[]).some(v=>v>0);
    if (!hasAccData) {
        renderEmptyState(accCanvas, 'Sin datos acumulados para el período');
    } else {
        new Chart(accCanvas, {
            type: 'line',
            data: {
                labels: chartData.months,
                datasets: [
                    { label:'Ingresos Acumulados', data:chartData.incomeAccumulated,  borderColor:'rgb(16,185,129)', backgroundColor:'rgba(16,185,129,0.12)', borderWidth:3, fill:false, tension:0.25, pointRadius:2 },
                    { label:'Egresos Acumulados',  data:chartData.expenseAccumulated, borderColor:'rgb(220,38,38)',  backgroundColor:'rgba(220,38,38,0.12)',  borderWidth:3, fill:false, tension:0.25, pointRadius:2 }
                ]
            },
            options: { responsive:true, aspectRatio:2, plugins:{ legend:{display:true}, tooltip:moneyTooltip }, scales:{ y:moneyScale } }
        });
    }

    // ── Proyección de Cierre Anual ───────────────────────────────────────────
    const projCanvas = document.getElementById('annualProjectionChart');
    if (!chartData.lastMonthWithData || chartData.lastMonthWithData < 1) {
        renderEmptyState(projCanvas, 'Sin datos suficientes para proyectar');
    } else {
        new Chart(projCanvas, {
            type: 'line',
            data: {
                labels: chartData.months,
                datasets: [
                    { label:'Proyección Ingresos', data:chartData.incomeProjectionSeries,  borderColor:'rgb(5,150,105)',  backgroundColor:'rgba(5,150,105,0.1)',  borderWidth:3, tension:0.25, fill:false, pointRadius:3, spanGaps:false },
                    { label:'Proyección Egresos',  data:chartData.expenseProjectionSeries, borderColor:'rgb(225,29,72)',  backgroundColor:'rgba(225,29,72,0.1)',  borderWidth:3, tension:0.25, fill:false, pointRadius:3, spanGaps:false }
                ]
            },
            options: {
                responsive:true, aspectRatio:2,
                plugins: {
                    legend:{display:true},
                    tooltip:{ callbacks:{ label: ctx => ctx.dataset.label+': Bs. '+Number(ctx.parsed.y).toLocaleString(), footer: () => 'Estimación hasta '+chartData.months[chartData.lastMonthWithData-1] } }
                },
                scales:{ y:moneyScale }
            }
        });
    }

    // ── Tabs Acumulado / Proyección ──────────────────────────────────────────
    function switchAccProjTab(tab) {
        const titles = { acc:'Acumulado Anual', proj:'Proyección de Cierre Anual' };
        document.getElementById('accProjTitle').textContent = titles[tab];
        document.getElementById('panel-acc').style.display  = tab === 'acc'  ? 'block' : 'none';
        document.getElementById('panel-proj').style.display = tab === 'proj' ? 'block' : 'none';
        document.getElementById('tab-acc').style.background  = tab === 'acc'  ? '#4f46e5' : '#fff';
        document.getElementById('tab-acc').style.color       = tab === 'acc'  ? '#fff'    : '#6b7280';
        document.getElementById('tab-proj').style.background = tab === 'proj' ? '#4f46e5' : '#fff';
        document.getElementById('tab-proj').style.color      = tab === 'proj' ? '#fff'    : '#6b7280';
    }

    // ── Diferencias Mensuales (nuevo gráfico combinado) ──────────────────────
    const categoryMonthly = @json([
        'operativo' => array_values($categoryMonthlyData['operativo']),
        'otro'      => array_values($categoryMonthlyData['otro']),
    ]);

    const balanceSeries  = chartData.balanceSeries;
    const catDiffSeries  = categoryMonthly.operativo.map((v, i) => v - categoryMonthly.otro[i]);

    const diffCanvas = document.getElementById('monthlyDifferenceChart');
    const hasDiffData = balanceSeries.some(v => v !== 0) || catDiffSeries.some(v => v !== 0);

    if (!hasDiffData) {
        renderEmptyState(diffCanvas, 'Sin diferencias registradas para el período');
    } else {
        new Chart(diffCanvas, {
            type: 'bar',
            data: {
                labels: chartData.months,
                datasets: [
                    {
                        label: 'Balance (Ingr − Egr)',
                        data: balanceSeries,
                        backgroundColor: balanceSeries.map(v => v >= 0 ? 'rgba(59,130,246,0.55)' : 'rgba(245,158,11,0.55)'),
                        borderColor:     balanceSeries.map(v => v >= 0 ? 'rgb(59,130,246)'        : 'rgb(245,158,11)'),
                        borderWidth: 1,
                        borderRadius: 4,
                        order: 1,
                    },
                    {
                        label: 'Dif. Op − Otro',
                        data: catDiffSeries,
                        type: 'line',
                        borderColor: 'rgb(168,85,247)',
                        backgroundColor: 'rgba(168,85,247,0.1)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: false,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        order: 0,
                    }
                ]
            },
            options: {
                responsive: true,
                aspectRatio: 2,
                plugins: {
                    legend: { display: true, position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } },
                    tooltip: { callbacks: { label: ctx => ' ' + ctx.dataset.label + ': Bs. ' + Number(ctx.parsed.y).toLocaleString() } }
                },
                scales: {
                    y: { ticks: { callback: v => bsTick(v) } }
                }
            }
        });
    }

    // ── Egresos por Categoría (dona) ─────────────────────────────────────────
    const expenseCategoryData = @json([
        'operativo' => (float)($expenseByCategory['operativo'] ?? 0),
        'otro'      => (float)($expenseByCategory['otro'] ?? 0),
    ]);

    const donutCanvas = document.getElementById('expenseCategoryDonut');
    const totalCat = expenseCategoryData.operativo + expenseCategoryData.otro;

    if (totalCat === 0) {
        renderEmptyState(donutCanvas, 'Sin datos de categorías');
    } else {
        const catMeta = [
            { label:'Gastos Operativos', value:expenseCategoryData.operativo, bg:'rgba(239,68,68,0.85)',  border:'rgb(220,38,38)',  dot:'rgb(220,38,38)'  },
            { label:'Otros Egresos',     value:expenseCategoryData.otro,      bg:'rgba(245,158,11,0.85)', border:'rgb(217,119,6)',  dot:'rgb(217,119,6)'  },
        ].filter(c => c.value > 0);

        new Chart(donutCanvas, {
            type: 'doughnut',
            data: {
                labels: catMeta.map(c => c.label),
                datasets: [{ data:catMeta.map(c=>c.value), backgroundColor:catMeta.map(c=>c.bg), borderColor:catMeta.map(c=>c.border), borderWidth:2, hoverOffset:6 }]
            },
            options: {
                responsive: false, cutout: '68%',
                plugins: { legend:{display:false}, tooltip:{ callbacks:{ label: ctx => ' Bs. '+ctx.parsed.toLocaleString('es-BO',{minimumFractionDigits:2,maximumFractionDigits:2}) } } }
            }
        });

        const legend = document.getElementById('expenseCategoryLegend');
        const allCats = [
            { label:'Gastos Operativos', value:expenseCategoryData.operativo, dot:'rgb(220,38,38)' },
            { label:'Otros Egresos',     value:expenseCategoryData.otro,      dot:'rgb(217,119,6)' },
        ];
        legend.innerHTML = allCats.map(c => {
            const pct = totalCat > 0 ? ((c.value / totalCat) * 100).toFixed(1) : '0.0';
            return `<div style="display:flex;align-items:flex-start;gap:0.6rem;margin-bottom:1rem;">
                <span style="width:11px;height:11px;border-radius:50%;background:${c.dot};flex-shrink:0;margin-top:4px;"></span>
                <div>
                    <div style="font-size:0.8rem;font-weight:600;color:#374151;">${c.label}</div>
                    <div style="font-size:0.78rem;color:#374151;font-weight:500;">Bs. ${c.value.toLocaleString('es-BO',{minimumFractionDigits:2,maximumFractionDigits:2})}</div>
                    <div style="font-size:0.72rem;color:#9ca3af;">${pct}% del total</div>
                </div>
            </div>`;
        }).join('');
    }

    // ── Egresos Mensuales por Categoría (barras apiladas) ────────────────────
    const catMonthlyCanvas = document.getElementById('expenseCategoryMonthly');
    const hasCatData = categoryMonthly.operativo.some(v=>v>0)||categoryMonthly.otro.some(v=>v>0);
    if (!hasCatData) {
        renderEmptyState(catMonthlyCanvas, 'Sin datos de categorías por mes');
    } else {
        new Chart(catMonthlyCanvas, {
            type: 'bar',
            data: {
                labels: chartData.months,
                datasets: [
                    { label:'Gastos Operativos', data:categoryMonthly.operativo, backgroundColor:'rgba(239,68,68,0.7)',  borderColor:'rgb(220,38,38)', borderWidth:1, borderRadius:3 },
                    { label:'Otros Egresos',     data:categoryMonthly.otro,      backgroundColor:'rgba(245,158,11,0.7)', borderColor:'rgb(217,119,6)', borderWidth:1, borderRadius:3 }
                ]
            },
            options: {
                responsive: true,
                plugins: { legend:{ position:'bottom', labels:{ boxWidth:12, font:{size:11} } }, tooltip:{ callbacks:{ label: ctx => ' '+ctx.dataset.label+': Bs. '+ctx.parsed.y.toLocaleString('es-BO',{minimumFractionDigits:2,maximumFractionDigits:2}) } } },
                scales: { x:{ stacked:true }, y:{ stacked:true, ...moneyScale } }
            }
        });
    }

    // ── Top Ítems ────────────────────────────────────────────────────────────
    const topIncomeData  = @json($topIncomeItems);
    const topExpenseData = @json($topExpenseItems);

    function stripLeadingNumbers(str) {
        return str.replace(/^[\d\s\.\-_\)\(]+/, '').trim();
    }

    function switchTopItemsTab(tab) {
        const titles = { income:'Top Ítems — Ingresos', expense:'Top Ítems — Egresos' };
        document.getElementById('topItemsTitle').textContent = titles[tab];
        ['income','expense'].forEach(t => {
            const panel = document.getElementById('panel-top-'+t);
            const btn   = document.getElementById('tab-top-'+t);
            const active = t === tab;
            panel.style.opacity       = active ? '1' : '0';
            panel.style.pointerEvents = active ? 'auto' : 'none';
            btn.style.background = active ? '#4f46e5' : '#fff';
            btn.style.color      = active ? '#fff'    : '#6b7280';
            btn.style.fontWeight = active ? '600'     : '500';
        });
    }

    function buildTopChart(canvasId, itemsObj, color, borderColor, stripNumbers) {
        const canvas = document.getElementById(canvasId);
        const rawLabels = Object.keys(itemsObj);
        const labels = stripNumbers ? rawLabels.map(stripLeadingNumbers) : rawLabels;
        const values = Object.values(itemsObj).map(Number);
        if (!labels.length) { renderEmptyState(canvas, 'Sin datos para el período seleccionado'); return; }
        new Chart(canvas, {
            type: 'bar',
            data: { labels, datasets: [{ label:'Bs.', data:values, backgroundColor:color, borderColor:borderColor, borderWidth:1, borderRadius:4 }] },
            options: {
                indexAxis: 'y', responsive:true, maintainAspectRatio:false,
                plugins: { legend:{display:false}, tooltip:{ callbacks:{ label: ctx => 'Bs. '+Number(ctx.parsed.x).toLocaleString() } } },
                scales: {
                    x: { ticks:{ callback: v => bsTick(v) } },
                    y: { ticks:{ font:{size:11}, callback: function(val,idx) { const l=this.getLabelForValue(idx); return l.length>24?l.slice(0,22)+'…':l; } } }
                }
            }
        });
    }

    buildTopChart('topIncomeChart',  topIncomeData,  'rgba(16,185,129,0.55)', 'rgb(16,185,129)', false);
    buildTopChart('topExpenseChart', topExpenseData, 'rgba(244,63,94,0.55)',  'rgb(244,63,94)',  true);
</script>
@endsection
