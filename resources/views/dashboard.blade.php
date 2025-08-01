@section('header-title', 'Dashboard')
@can('dashboard.view')
    

<x-app-layout>
    <div class="mb-6">
        <div class="flex flex-col md:flex-row justify-between items-center">
            <div class="w-full md:w-auto mb-4 md:mb-0">
                <h1 class="text-2xl font-semibold text-gray-800">Dashboard</h1>
                <p class="text-gray-600">{{ $viewType === 'monthly' ? 'Vista mensual - ' . $nombreMes . ' ' . $year : 'Vista anual - ' . $year }}</p>
            </div>
            <div class="w-full md:w-auto">
                <form method="GET" action="{{ route('dashboard') }}" class="flex flex-wrap gap-2">
                    <select id="view_type" name="view_type" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" onchange="toggleMonthField()">
                        <option value="monthly" {{ $viewType === 'monthly' ? 'selected' : '' }}>Mensual</option>
                        <option value="yearly" {{ $viewType === 'yearly' ? 'selected' : '' }}>Anual</option>
                    </select>
                    
                    <div id="month-field" style="{{ $viewType === 'yearly' ? 'display: none;' : '' }}">
                        <select id="month" name="month" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            @for ($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" {{ isset($month) && $month == $i ? 'selected' : '' }}>
                                    {{ ucfirst(\Carbon\Carbon::createFromDate(null, $i, 1)->translatedFormat('F')) }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    
                    <select id="year" name="year" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        @for ($i = date('Y'); $i >= date('Y') - 5; $i--)
                            <option value="{{ $i }}" {{ $year == $i ? 'selected' : '' }}>
                                {{ $i }}
                            </option>
                        @endfor
                    </select>
                    
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:border-indigo-700 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                        Filtrar
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Tarjetas de métricas -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <!-- Total Inscritos -->
        <div class="bg-white rounded-lg shadow p-6 flex justify-between items-center">
            <div>
                <h2 class="text-3xl font-bold text-indigo-900">{{ $stats['total'] }}</h2>
                <p class="text-gray-600">Total Inscritos</p>
                <div class="mt-2 flex items-center">
                    @if($stats['percentage_change_total'] > 0)
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd" />
                        </svg>
                        <span class="text-green-500 text-sm ml-1">+{{ number_format($stats['percentage_change_total'], 1) }}%</span>
                    @elseif($stats['percentage_change_total'] < 0)
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M12 13a1 1 0 100 2h5a1 1 0 001-1V9a1 1 0 10-2 0v2.586l-4.293-4.293a1 1 0 00-1.414 0L8 9.586 3.707 5.293a1 1 0 00-1.414 1.414l5 5a1 1 0 001.414 0L11 9.414 14.586 13H12z" clip-rule="evenodd" />
                        </svg>
                        <span class="text-red-500 text-sm ml-1">{{ number_format($stats['percentage_change_total'], 1) }}%</span>
                    @else
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5 10a1 1 0 011-1h8a1 1 0 110 2H6a1 1 0 01-1-1z" clip-rule="evenodd" />
                        </svg>
                        <span class="text-gray-500 text-sm ml-1">0%</span>
                    @endif
                    <span class="text-gray-500 text-xs ml-2">
                        vs {{ $viewType === 'monthly' ? $nombreMesAnterior : $previousYear }}
                    </span>
                </div>
            </div>
            <div class="bg-yellow-400 p-3 rounded-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" />
                </svg>
            </div>
        </div>
        
        <!-- Total Pagado con comparación -->
        <div class="bg-white rounded-lg shadow p-6 flex justify-between items-center">
            <div>
                <h2 class="text-3xl font-bold text-indigo-900">{{ number_format($stats['total_paid'], 2) }} Bs</h2>
                <p class="text-gray-600">Total Pagado</p>
                <div class="mt-2 flex items-center">
                    @if($stats['percentage_change_total_paid'] > 0)
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd" />
                        </svg>
                        <span class="text-green-500 text-sm ml-1">+{{ number_format($stats['percentage_change_total_paid'], 1) }}%</span>
                    @elseif($stats['percentage_change_total_paid'] < 0)
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M12 13a1 1 0 100 2h5a1 1 0 001-1V9a1 1 0 10-2 0v2.586l-4.293-4.293a1 1 0 00-1.414 0L8 9.586 3.707 5.293a1 1 0 00-1.414 1.414l5 5a1 1 0 001.414 0L11 9.414 14.586 13H12z" clip-rule="evenodd" />
                        </svg>
                        <span class="text-red-500 text-sm ml-1">{{ number_format($stats['percentage_change_total_paid'], 1) }}%</span>
                    @else
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5 10a1 1 0 011-1h8a1 1 0 110 2H6a1 1 0 01-1-1z" clip-rule="evenodd" />
                        </svg>
                        <span class="text-gray-500 text-sm ml-1">0%</span>
                    @endif
                    <span class="text-gray-500 text-xs ml-2">
                        vs {{ $viewType === 'monthly' ? $nombreMesAnterior : $previousYear }}
                    </span>
                </div>
            </div>
            <div class="bg-cyan-400 p-3 rounded-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z" />
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd" />
                </svg>
            </div>
        </div>
        
        <!-- Total sin adelantos (Completo + Completando) -->
        <div class="bg-white rounded-lg shadow p-6 flex justify-between items-center">
            <div>
                <h2 class="text-3xl font-bold text-indigo-900">{{ $stats['total_sin_adelantos'] }}</h2>
                <p class="text-gray-600">Inscripciones Completas</p>
                <div class="mt-2 flex items-center">
                    @if($stats['percentage_change_total_sin_adelantos'] > 0)
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd" />
                        </svg>
                        <span class="text-green-500 text-sm ml-1">+{{ number_format($stats['percentage_change_total_sin_adelantos'], 1) }}%</span>
                    @elseif($stats['percentage_change_total_sin_adelantos'] < 0)
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M12 13a1 1 0 100 2h5a1 1 0 001-1V9a1 1 0 10-2 0v2.586l-4.293-4.293a1 1 0 00-1.414 0L8 9.586 3.707 5.293a1 1 0 00-1.414 1.414l5 5a1 1 0 001.414 0L11 9.414 14.586 13H12z" clip-rule="evenodd" />
                        </svg>
                        <span class="text-red-500 text-sm ml-1">{{ number_format($stats['percentage_change_total_sin_adelantos'], 1) }}%</span>
                    @else
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5 10a1 1 0 011-1h8a1 1 0 110 2H6a1 1 0 01-1-1z" clip-rule="evenodd" />
                        </svg>
                        <span class="text-gray-500 text-sm ml-1">0%</span>
                    @endif
                    <span class="text-gray-500 text-xs ml-2">
                        vs {{ $viewType === 'monthly' ? $nombreMesAnterior : $previousYear }}
                    </span>
                </div>
                <div class="flex items-center mt-1">
                    <div class="w-3 h-3 rounded-full bg-green-500 mr-2"></div>
                    <span class="text-xs text-gray-600">Completos: {{ $stats['completo'] }}</span>
                </div>
                <div class="flex items-center mt-1">
                    <div class="w-3 h-3 rounded-full bg-yellow-500 mr-2"></div>
                    <span class="text-xs text-gray-600">Completando: {{ $stats['completando'] }}</span>
                </div>
            </div>
            <div class="bg-green-500 p-3 rounded-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
            </div>
        </div>
        
        <!-- Adelantos -->
        <div class="bg-white rounded-lg shadow p-6 flex justify-between items-center">
            <div>
                <h2 class="text-3xl font-bold text-indigo-900">{{ $stats['adelanto'] }}</h2>
                <p class="text-gray-600">Adelantos</p>
                <div class="mt-2 flex items-center">
                    @if($stats['percentage_change_adelanto'] > 0)
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd" />
                        </svg>
                        <span class="text-green-500 text-sm ml-1">+{{ number_format($stats['percentage_change_adelanto'], 1) }}%</span>
                    @elseif($stats['percentage_change_adelanto'] < 0)
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M12 13a1 1 0 100 2h5a1 1 0 001-1V9a1 1 0 10-2 0v2.586l-4.293-4.293a1 1 0 00-1.414 0L8 9.586 3.707 5.293a1 1 0 00-1.414 1.414l5 5a1 1 0 001.414 0L11 9.414 14.586 13H12z" clip-rule="evenodd" />
                        </svg>
                        <span class="text-red-500 text-sm ml-1">{{ number_format($stats['percentage_change_adelanto'], 1) }}%</span>
                    @else
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5 10a1 1 0 011-1h8a1 1 0 110 2H6a1 1 0 01-1-1z" clip-rule="evenodd" />
                        </svg>
                        <span class="text-gray-500 text-sm ml-1">0%</span>
                    @endif
                    <span class="text-gray-500 text-xs ml-2">
                        vs {{ $viewType === 'monthly' ? $nombreMesAnterior : $previousYear }}
                    </span>
                </div>
                <div class="mt-2">
                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                        @php
                            $porcentajeAdelantos = $stats['total'] > 0 ? ($stats['adelanto'] / $stats['total']) * 100 : 0;
                        @endphp
                        <div class="bg-blue-500 h-2.5 rounded-full" style="width: {{ $porcentajeAdelantos }}%"></div>
                    </div>
                    <span class="text-xs text-gray-600">{{ number_format($porcentajeAdelantos, 1) }}% del total</span>
                </div>
            </div>
            <div class="bg-blue-500 p-3 rounded-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                </svg>
            </div>
        </div>
    </div>
    
    <!-- Nuevos gráficos: Inscripciones por creador y por programa -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Inscripciones por creador -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Inscripciones por Asesor</h3>
                    <div class="text-sm text-gray-500">{{ $viewType === 'monthly' ? 'Mensual' : 'Anual' }}</div>
                </div>
                <div class="h-80">
                    <canvas id="creatorChart"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Inscripciones por programa -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Inscripciones por Programa</h3>
                    <div class="text-sm text-gray-500">{{ $viewType === 'monthly' ? 'Mensual' : 'Anual' }}</div>
                </div>
                <div class="h-80">
                    <canvas id="programChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Gráficos secundarios -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Inscripciones por estado -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Inscripciones por Estado</h3>
                    <div class="text-sm text-gray-500">{{ $viewType === 'monthly' ? 'Mensual' : 'Anual' }}</div>
                </div>
                <div class="h-64">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Inscripciones por plan de pago -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Inscripciones por Plan de Pago</h3>
                    <div class="text-sm text-gray-500">{{ $viewType === 'monthly' ? 'Mensual' : 'Anual' }}</div>
                </div>
                <div class="h-64">
                    <canvas id="paymentPlanChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Gráficos por residencia y profesión -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Inscripciones por residencia -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Inscripciones por Residencia</h3>
                    <div class="text-sm text-gray-500">{{ $viewType === 'monthly' ? 'Mensual' : 'Anual' }}</div>
                </div>
                <div class="h-64">
                    <canvas id="residenceChart"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Inscripciones por profesión -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Inscripciones por Profesión</h3>
                    <div class="text-sm text-gray-500">{{ $viewType === 'monthly' ? 'Mensual' : 'Anual' }}</div>
                </div>
                <div class="h-64">
                    <canvas id="professionChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    @if($viewType === 'yearly')
    <!-- Gráfico de evolución mensual (solo para vista anual) -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Evolución Mensual de Inscripciones</h3>
                <div class="text-sm text-gray-500">{{ $year }}</div>
            </div>
            <div class="h-80">
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Gráfico de total pagado por mes (solo para vista anual) -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Total Pagado por Mes</h3>
                <div class="text-sm text-gray-500">{{ $year }}</div>
            </div>
            <div class="h-64">
                <canvas id="monthlyPaymentChart"></canvas>
            </div>
        </div>
    </div>
    @endif
    
    <!-- Últimas Inscripciones -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Últimas Inscripciones</h3>
                <a href="{{ route('inscriptions.index') }}" class="text-indigo-600 hover:text-indigo-800 text-sm">Ver todas</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Programa</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Pagado</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($latestInscriptions as $inscription)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $inscription->code }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $inscription->first_name }} {{ $inscription->paternal_surname }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $inscription->program->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($inscription->status == 'Completo')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Completo
                                        </span>
                                    @elseif($inscription->status == 'Completando')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            Completando
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                            Adelanto
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($inscription->total_paid, 2) }} Bs</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $inscription->inscription_date->format('d/m/Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('inscriptions.show', $inscription) }}" class="text-indigo-600 hover:text-indigo-900">Ver</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No hay inscripciones para mostrar</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    @push('scripts')
    <script>
        // Función para mostrar/ocultar el campo de mes según el tipo de vista
        function toggleMonthField() {
            const viewType = document.getElementById('view_type').value;
            const monthField = document.getElementById('month-field');
            
            if (viewType === 'yearly') {
                monthField.style.display = 'none';
            } else {
                monthField.style.display = 'block';
            }
        }
        
        // Función para generar colores dinámicos
        function generateColors(count) {
            const colors = [];
            for (let i = 0; i < count; i++) {
                const hue = (i * 137) % 360; // Distribuir los colores uniformemente
                colors.push(`hsla(${hue}, 70%, 60%, 0.7)`);
            }
            return colors;
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Colores para los gráficos
            const colors = {
                'Completo': 'rgba(34, 197, 94, 0.7)',
                'Completando': 'rgba(234, 179, 8, 0.7)',
                'Adelanto': 'rgba(59, 130, 246, 0.7)',
                'contado': 'rgba(59, 130, 246, 0.7)', // Azul
                'credito': 'rgba(236, 72, 153, 0.7)' // Rosa
            };
            
            // Nuevo gráfico: Inscripciones por creador
            const creatorChartCtx = document.getElementById('creatorChart').getContext('2d');
            const advisorLabels = JSON.parse('{!! $chartData["advisorLabels"] !!}');
            const advisorDatasets = JSON.parse('{!! $chartData["advisorDatasets"] !!}');
            
            new Chart(creatorChartCtx, {
                type: 'bar',
                data: {
                    labels: advisorLabels,
                    datasets: [
                        {
                            label: 'Completo',
                            data: advisorDatasets['Completo'],
                            backgroundColor: colors['Completo'],
                            stack: 'Stack 0',
                        },
                        {
                            label: 'Completando',
                            data: advisorDatasets['Completando'],
                            backgroundColor: colors['Completando'],
                            stack: 'Stack 0',
                        },
                        {
                            label: 'Adelanto',
                            data: advisorDatasets['Adelanto'],
                            backgroundColor: colors['Adelanto'],
                            stack: 'Stack 0',
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    scales: {
                        x: {
                            stacked: true,
                            beginAtZero: true,
                            grid: {
                                drawBorder: false
                            }
                        },
                        y: {
                            stacked: true,
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
            
            // Nuevo gráfico: Inscripciones por programa
            const programChartCtx = document.getElementById('programChart').getContext('2d');
            const programLabels = JSON.parse('{!! $chartData["programLabels"] !!}');
            const programDatasets = JSON.parse('{!! $chartData["programDatasets"] !!}');

            new Chart(programChartCtx, {
                type: 'bar',
                data: {
                    labels: programLabels,
                    datasets: [
                        {
                            label: 'Completo',
                            data: programDatasets['Completo'],
                            backgroundColor: colors['Completo'],
                            stack: 'Stack 0',
                        },
                        {
                            label: 'Completando',
                            data: programDatasets['Completando'],
                            backgroundColor: colors['Completando'],
                            stack: 'Stack 0',
                        },
                        {
                            label: 'Adelanto',
                            data: programDatasets['Adelanto'],
                            backgroundColor: colors['Adelanto'],
                            stack: 'Stack 0',
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    scales: {
                        x: {
                            stacked: true,
                            beginAtZero: true,
                            grid: {
                                drawBorder: false
                            }
                        },
                        y: {
                            stacked: true,
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
            
            // 1. Gráfico de inscripciones por estado
            const statusCtx = document.getElementById('statusChart').getContext('2d');
            const statusData = JSON.parse('{!! $chartData["statusData"] !!}');
            
            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(statusData),
                    datasets: [{
                        data: Object.values(statusData),
                        backgroundColor: Object.keys(statusData).map(status => colors[status]),
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                        }
                    }
                }
            });
            
            // 2. Gráfico de inscripciones por plan de pago
            const paymentPlanCtx = document.getElementById('paymentPlanChart').getContext('2d');
            const paymentPlanData = JSON.parse('{!! $chartData["paymentPlanData"] !!}');

            // Crear un array de etiquetas y colores que correspondan correctamente a las claves
            const paymentLabels = [];
            const paymentColors = [];

            Object.keys(paymentPlanData).forEach(plan => {
                if (plan === 'Contado') {
                    paymentLabels.push('Contado');
                    paymentColors.push('rgba(59, 130, 246, 0.7)'); // Azul
                } else if (plan === 'Crédito') {
                    paymentLabels.push('Crédito');
                    paymentColors.push('rgba(236, 72, 153, 0.7)'); // Rosa
                } else {
                    paymentLabels.push(plan);
                    paymentColors.push('rgba(156, 163, 175, 0.7)'); // Gris para otros casos
                }
            });

            new Chart(paymentPlanCtx, {
                type: 'pie',
                data: {
                    labels: paymentLabels,
                    datasets: [{
                        data: Object.values(paymentPlanData),
                        backgroundColor: paymentColors,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                        }
                    }
                }
            });
            
            // 5. Gráfico de inscripciones por residencia
            const residenceCtx = document.getElementById('residenceChart').getContext('2d');
            const residenceData = JSON.parse('{!! $chartData["residenceData"] !!}');
            
            // Generar colores dinámicos para cada residencia
            const residenceColors = generateColors(Object.keys(residenceData).length);
            
            new Chart(residenceCtx, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(residenceData),
                    datasets: [{
                        data: Object.values(residenceData),
                        backgroundColor: residenceColors,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                boxWidth: 15
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.raw || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = Math.round((value / total) * 100);
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
            
            // 6. Gráfico de inscripciones por profesión
            const professionCtx = document.getElementById('professionChart').getContext('2d');
            const professionData = JSON.parse('{!! $chartData["professionData"] !!}');
            
            // Generar colores dinámicos para cada profesión
            const professionColors = generateColors(Object.keys(professionData).length);
            
            new Chart(professionCtx, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(professionData),
                    datasets: [{
                        data: Object.values(professionData),
                        backgroundColor: professionColors,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                boxWidth: 15
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.raw || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = Math.round((value / total) * 100);
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
            
            @if($viewType === 'yearly')
                // Gráfico de inscripciones mensuales (solo para vista anual)
                const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
                let monthlyLabels = JSON.parse('{!! $chartData["monthlyLabels"] !!}');
                const monthlyDatasets = JSON.parse('{!! $chartData["monthlyDatasets"] !!}');
                
                new Chart(monthlyCtx, {
                    type: 'line',
                    data: {
                        labels: monthlyLabels,
                        datasets: [
                            {
                                label: 'Total',
                                data: monthlyDatasets['total'],
                                borderColor: 'rgba(75, 85, 99, 0.7)',
                                backgroundColor: 'rgba(75, 85, 99, 0.1)',
                                borderWidth: 2,
                                fill: false,
                                tension: 0.1
                            },
                            {
                                label: 'Completo',
                                data: monthlyDatasets['completo'],
                                borderColor: colors['Completo'],
                                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                                borderWidth: 2,
                                fill: false,
                                tension: 0.1
                            },
                            {
                                label: 'Completando',
                                data: monthlyDatasets['completando'],
                                borderColor: colors['Completando'],
                                backgroundColor: 'rgba(234, 179, 8, 0.1)',
                                borderWidth: 2,
                                fill: false,
                                tension: 0.1
                            },
                            {
                                label: 'Adelanto',
                                data: monthlyDatasets['adelanto'],
                                borderColor: colors['Adelanto'],
                                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                borderWidth: 2,
                                fill: false,
                                tension: 0.1
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
                
                // Gráfico de total pagado por mes (solo para vista anual)
                const monthlyPaymentCtx = document.getElementById('monthlyPaymentChart').getContext('2d');
                
                new Chart(monthlyPaymentCtx, {
                    type: 'line',
                    data: {
                        labels: monthlyLabels,
                        datasets: [
                            {
                                label: 'Total Pagado (Bs)',
                                data: monthlyDatasets['total_paid'],
                                borderColor: 'rgba(147, 51, 234, 0.7)', // Color morado
                                backgroundColor: 'rgba(147, 51, 234, 0.1)',
                                borderWidth: 3,
                                fill: true,
                                tension: 0.4,
                                pointRadius: 6,
                                pointBackgroundColor: 'rgba(147, 51, 234, 0.8)',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return value.toLocaleString('es-BO') + ' Bs';
                                    }
                                }
                            }
                        },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        let label = context.dataset.label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        if (context.parsed.y !== null) {
                                            label += new Intl.NumberFormat('es-BO', {
                                                style: 'currency',
                                                currency: 'BOB'
                                            }).format(context.parsed.y);
                                        }
                                        return label;
                                    }
                                }
                            }
                        }
                    }
                });
            @endif
        });
    </script>
    @endpush
</x-app-layout>
@endcan