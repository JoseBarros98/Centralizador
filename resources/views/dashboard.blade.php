@section('header-title', 'Dashboard')
@can('dashboard.view')
    

<x-app-layout>
    <div class="mb-6">
        <div class="flex flex-col md:flex-row justify-between items-center">
            <div class="w-full md:w-auto mb-4 md:mb-0">
                <h1 class="text-2xl font-semibold text-gray-800 ">Dashboard</h1>
                <p class="text-gray-600 ">{{ $viewType === 'monthly' ? 'Vista Mensual - ' . strtoupper($nombreMes) . ' ' . $year : 'Vista Anual - ' . $year }}</p>
            </div>
            <div class="w-full md:w-auto">
                <form method="GET" action="{{ route('dashboard') }}" class="flex flex-wrap gap-2">
                    {{-- <select id="view_type" name="view_type" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" onchange="toggleMonthField()">
                        <option value="monthly" {{ $viewType === 'monthly' ? 'selected' : '' }}>Mensual</option>
                        <option value="yearly" {{ $viewType === 'yearly' ? 'selected' : '' }}>Anual</option>
                    </select> --}}
                    
                    <div id="month-field">
                        <select id="month" name="month" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="all" {{ (isset($month) && (string) $month === 'all') || $viewType === 'yearly' ? 'selected' : '' }}>
                                TODOS LOS MESES (ANUAL)
                            </option>
                            @for ($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" {{ isset($month) && $month == $i ? 'selected' : '' }}>
                                    {{ strtoupper(\Carbon\Carbon::createFromDate(null, $i, 1)->translatedFormat('F')) }}
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
                    
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white  tracking-widest hover:bg-indigo-700 focus:outline-none focus:border-indigo-700 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                        FILTRAR
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
                <h2 class="text-3xl font-bold text-indigo-900">{{ number_format($stats['total']) }}</h2>
                <p class="text-gray-600 ">Total Inscritos</p>
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
                    <span class="text-gray-500 text-xs ml-2 ">
                        vs {{ $viewType === 'monthly' ? strtoupper($nombreMesAnterior) : $previousYear }}
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
                <h2 class="text-3xl font-bold text-indigo-900">{{ number_format($stats['total_paid'], 2) }} BS</h2>
                <p class="text-gray-600 ">Total Pagado</p>
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
                    <span class="text-gray-500 text-xs ml-2 ">
                        vs {{ $viewType === 'monthly' ? strtoupper($nombreMesAnterior) : $previousYear }}
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
                <h2 class="text-3xl font-bold text-indigo-900">{{ number_format($stats['total_sin_adelantos']) }}</h2>
                <p class="text-gray-600 ">Inscripciones Completas</p>
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
                    <span class="text-gray-500 text-xs ml-2 ">
                        vs {{ $viewType === 'monthly' ? strtoupper($nombreMesAnterior) : $previousYear }}
                    </span>
                </div>
                <div class="flex items-center mt-1">
                    <div class="w-3 h-3 rounded-full bg-green-500 mr-2"></div>
                    <span class="text-xs text-gray-600 ">Completos: {{ number_format($stats['completo']) }}</span>
                </div>
                <div class="flex items-center mt-1">
                    <div class="w-3 h-3 rounded-full bg-yellow-500 mr-2"></div>
                    <span class="text-xs text-gray-600 ">Completando: {{ number_format($stats['completando']) }}</span>
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
                <h2 class="text-3xl font-bold text-indigo-900">{{ number_format($stats['adelanto']) }}</h2>
                <p class="text-gray-600 ">Adelantos</p>
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
                    <span class="text-gray-500 text-xs ml-2 ">
                        vs {{ $viewType === 'monthly' ? strtoupper($nombreMesAnterior) : $previousYear }}
                    </span>
                </div>
                <div class="mt-2">
                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                        @php
                            $porcentajeAdelantos = $stats['total'] > 0 ? ($stats['adelanto'] / $stats['total']) * 100 : 0;
                        @endphp
                        <div class="bg-blue-500 h-2.5 rounded-full" style="width: {{ $porcentajeAdelantos }}%"></div>
                    </div>
                    <span class="text-xs text-gray-600 ">{{ number_format($porcentajeAdelantos, 1) }}% DEL TOTAL</span>
                </div>
            </div>
            <div class="bg-blue-500 p-3 rounded-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Evolucion progresiva de inscripciones (ancho completo) -->
    <div class="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition-shadow duration-300 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Evolucion Progresiva de Inscripciones</h3>
        </div>
        <div class="h-80 relative">
            <canvas id="progressiveChart" class="w-full h-full"></canvas>
        </div>
    </div>

    @if($viewType === 'yearly')
        <!-- Gráficos de evolución anual debajo de evolución progresiva -->
        <div class="space-y-6 mb-6">
            <div class="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition-shadow duration-300">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Evolución mensual de inscripciones</h3>
                <div class="h-80 relative">
                    <canvas id="monthlyChart" class="w-full h-full"></canvas>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition-shadow duration-300">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Evolución mensual de pagos</h3>
                <div class="h-80 relative">
                    <canvas id="monthlyPaymentChart" class="w-full h-full"></canvas>
                </div>
            </div>
        </div>
    @endif
    
    <!-- Gráficos: Inscripciones por residencia, género y plan de pagos -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Inscripciones por residencia -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">
                Inscripciones por Departamento
            </h3>
            <div id="departmentChartContainer" class="relative" style="min-height: 400px;">
                <canvas id="departamentosChart"></canvas>
            </div>
        </div>

        <!-- Columna derecha con género y plan de pago -->
        <div class="space-y-6">
            <!-- Inscripciones por género -->
            {{-- <div class="bg-white rounded-lg shadow p-4 h-48"> --}}
            <div class="bg-white rounded-lg shadow p-4 h-60">
                <h3 class="text-sm font-medium text-gray-900 mb-3">Inscripciones por Género</h3>
                <div class="w-full flex flex-row justify-center items-start gap-4 h-40">
                    <!-- Masculino -->
                    <div class="flex flex-col items-center flex-1">
                        <div class="flex flex-col items-center mb-2">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center mb-1 bg-blue-500 shadow-lg">
                                <!-- Icono Flaticon hombre -->
                                <img src="https://cdn-icons-png.flaticon.com/512/2922/2922540.png" alt="Hombre" class="w-6 h-6" style="filter: brightness(0) invert(1);">
                            </div>
                            <div class="text-base font-bold text-blue-700 mb-1" id="male-count">0</div>
                            <div class="text-xs font-semibold text-blue-500 tracking-wide">MASCULINO</div>
                        </div>
                        <div class="flex flex-col space-y-1 text-xs w-full">
                            <div class="flex items-center bg-green-50 px-1 py-1 rounded-md">
                                <div class="w-2 h-2 rounded-full bg-green-500 mr-1"></div>
                                <span id="male-completo" class="font-medium text-green-700 text-xs">0</span>
                                <span class="ml-1 text-xs text-gray-500">Completo</span>
                            </div>
                            <div class="flex items-center bg-yellow-50 px-1 py-1 rounded-md">
                                <div class="w-2 h-2 rounded-full bg-yellow-500 mr-1"></div>
                                <span id="male-completando" class="font-medium text-yellow-700 text-xs">0</span>
                                <span class="ml-1 text-xs text-gray-500">Completando</span>
                            </div>
                            <div class="flex items-center bg-blue-50 px-1 py-1 rounded-md">
                                <div class="w-2 h-2 rounded-full bg-blue-500 mr-1"></div>
                                <span id="male-adelanto" class="font-medium text-blue-700 text-xs">0</span>
                                <span class="ml-1 text-xs text-gray-500">Adelanto</span>
                            </div>
                        </div>
                    </div>
                    <!-- Femenino -->
                    <div class="flex flex-col items-center flex-1">
                        <div class="flex flex-col items-center mb-2">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center mb-1 bg-pink-500 shadow-lg">
                                <!-- Icono Flaticon mujer -->
                                <img src="https://cdn-icons-png.flaticon.com/512/2922/2922579.png" alt="Mujer" class="w-6 h-6" style="filter: brightness(0) invert(1);">
                            </div>
                            <div class="text-base font-bold text-pink-700 mb-1" id="female-count">0</div>
                            <div class="text-xs font-semibold text-pink-500 tracking-wide">FEMENINO</div>
                        </div>
                        <div class="flex flex-col space-y-1 text-xs w-full">
                            <div class="flex items-center bg-green-50 px-1 py-1 rounded-md">
                                <div class="w-2 h-2 rounded-full bg-green-500 mr-1"></div>
                                <span id="female-completo" class="font-medium text-green-700 text-xs">0</span>
                                <span class="ml-1 text-xs text-gray-500">Completo</span>
                            </div>
                            <div class="flex items-center bg-yellow-50 px-1 py-1 rounded-md">
                                <div class="w-2 h-2 rounded-full bg-yellow-500 mr-1"></div>
                                <span id="female-completando" class="font-medium text-yellow-700 text-xs">0</span>
                                <span class="ml-1 text-xs text-gray-500">Completando</span>
                            </div>
                            <div class="flex items-center bg-blue-50 px-1 py-1 rounded-md">
                                <div class="w-2 h-2 rounded-full bg-blue-500 mr-1"></div>
                                <span id="female-adelanto" class="font-medium text-blue-700 text-xs">0</span>
                                <span class="ml-1 text-xs text-gray-500">Adelanto</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Plan de pago -->
            <div class="bg-white rounded-lg shadow p-4 h-75">
                <h3 class="text-sm font-medium text-gray-900 mb-3">Plan de pago</h3>
                <div class="h-32 relative">
                    <canvas id="paymentPlanBarChart" class="w-full h-full"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Grid de gráficos: Asesor y Programa -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Inscripciones por asesor -->
        <div class="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition-shadow duration-300">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Inscripciones por Asesor</h3>   
            </div>
            <div id="advisorChartContainer" class="relative" style="min-height: 400px;">
                <canvas id="creatorChart" class="w-full h-full"></canvas>
            </div>
        </div>
        
        <!-- Inscripciones por programa -->
        <div class="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition-shadow duration-300">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Inscripciones por Programa</h3>
            </div>
            <div id="programChartContainer" class="relative" style="min-height: 400px;">
                <canvas id="programChart" class="w-full h-full"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Gráficos adicionales -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Inscripciones por estado -->
        <div class="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition-shadow duration-300">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Inscripciones por Estado</h3>
                
            </div>
            <div class="h-56 relative">
                <canvas id="statusChart" class="w-full h-full"></canvas>
            </div>
        </div>
        
        <!-- Inscripciones por medio de pago -->
        <div class="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition-shadow duration-300">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Inscripciones por Medio de Pago</h3>
            </div>
            <div class="h-56 relative">
                <canvas id="paymentMethodChart" class="w-full h-full"></canvas>
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
            
            // Nuevo gráfico: Inscripciones por creador (Asesor)
            const creatorChartCtx = document.getElementById('creatorChart').getContext('2d');
            const advisorLabels = JSON.parse('{!! $chartData["advisorLabels"] !!}');
            const advisorDatasets = JSON.parse('{!! $chartData["advisorDatasets"] !!}');

            // Ajustar altura del contenedor según la cantidad de asesores
            const advisorChartContainer = document.getElementById('advisorChartContainer');
            const advisorMinHeight = 400;
            const advisorHeightPerItem = 40; // pixels por cada asesor
            const advisorCalculatedHeight = Math.max(advisorMinHeight, advisorLabels.length * advisorHeightPerItem);
            advisorChartContainer.style.height = advisorCalculatedHeight + 'px';
            
            new Chart(creatorChartCtx, {
                type: 'bar',
                data: {
                    labels: advisorLabels,
                    datasets: [
                        {
                            label: 'COMPLETO',
                            data: advisorDatasets['Completo'],
                            backgroundColor: colors['Completo'],
                            borderColor: 'rgba(34, 197, 94, 1)',
                            borderWidth: 1,
                            stack: 'Stack 0',
                        },
                        {
                            label: 'COMPLETANDO',
                            data: advisorDatasets['Completando'],
                            backgroundColor: colors['Completando'],
                            borderColor: 'rgba(234, 179, 8, 1)',
                            borderWidth: 1,
                            stack: 'Stack 0',
                        },
                        {
                            label: 'ADELANTO',
                            data: advisorDatasets['Adelanto'],
                            backgroundColor: colors['Adelanto'],
                            borderColor: 'rgba(59, 130, 246, 1)',
                            borderWidth: 1,
                            stack: 'Stack 0',
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                boxWidth: 15,
                                padding: 15,
                                font: {
                                    size: 12,
                                    weight: 'bold'
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            borderColor: 'rgba(255, 255, 255, 0.1)',
                            borderWidth: 1
                        }
                    },
                    scales: {
                        x: {
                            stacked: true,
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)',
                                drawBorder: false
                            },
                            ticks: {
                                font: {
                                    size: 11
                                }
                            }
                        },
                        y: {
                            stacked: true,
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    size: 11
                                },
                                maxRotation: 0
                            }
                        }
                    }
                }
            });
            
            // Nuevo gráfico: Inscripciones por programa
            const programChartCtx = document.getElementById('programChart').getContext('2d');
            const programLabels = JSON.parse('{!! $chartData["programLabels"] !!}');
            const programDatasets = JSON.parse('{!! $chartData["programDatasets"] !!}');

            // Función para truncar texto largo
            function truncateLabel(label, maxLength = 35) {
                if (label.length > maxLength) {
                    return label.substring(0, maxLength) + '...';
                }
                return label;
            }

            // Crear labels truncadas para visualización
            const truncatedProgramLabels = programLabels.map(label => truncateLabel(label));

            // Ajustar altura del contenedor según la cantidad de programas
            const programChartContainer = document.getElementById('programChartContainer');
            const minHeight = 400;
            const heightPerItem = 50; // pixels por cada programa
            const calculatedHeight = Math.max(minHeight, programLabels.length * heightPerItem);
            programChartContainer.style.height = calculatedHeight + 'px';

            new Chart(programChartCtx, {
                type: 'bar',
                data: {
                    labels: truncatedProgramLabels,
                    datasets: [
                        {
                            label: 'COMPLETO',
                            data: programDatasets['Completo'],
                            backgroundColor: colors['Completo'],
                            borderColor: 'rgba(34, 197, 94, 1)',
                            borderWidth: 1,
                            stack: 'Stack 0',
                        },
                        {
                            label: 'COMPLETANDO',
                            data: programDatasets['Completando'],
                            backgroundColor: colors['Completando'],
                            borderColor: 'rgba(234, 179, 8, 1)',
                            borderWidth: 1,
                            stack: 'Stack 0',
                        },
                        {
                            label: 'ADELANTO',
                            data: programDatasets['Adelanto'],
                            backgroundColor: colors['Adelanto'],
                            borderColor: 'rgba(59, 130, 246, 1)',
                            borderWidth: 1,
                            stack: 'Stack 0',
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                boxWidth: 15,
                                padding: 15,
                                font: {
                                    size: 12,
                                    weight: 'bold'
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            borderColor: 'rgba(255, 255, 255, 0.1)',
                            borderWidth: 1,
                            callbacks: {
                                // Mostrar el nombre completo en el tooltip
                                title: function(context) {
                                    const index = context[0].dataIndex;
                                    return programLabels[index];
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            stacked: true,
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)',
                                drawBorder: false
                            },
                            ticks: {
                                font: {
                                    size: 11
                                }
                            }
                        },
                        y: {
                            stacked: true,
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    size: 10
                                },
                                maxRotation: 0,
                                autoSkip: false,
                                padding: 5
                            }
                        }
                    }
                }
            });
            
            // 1. Gráfico de inscripciones por estado
            const statusCtx = document.getElementById('statusChart').getContext('2d');
            const statusData = JSON.parse('{!! $chartData["statusData"] !!}');
            
            // Mapear los nombres de estado a nombres amigables
            const statusMapping = {
                'Completo': 'COMPLETO',
                'Completando': 'COMPLETANDO',
                'Adelanto': 'ADELANTO'
            };

            // Crear arrays de etiquetas, datos y colores para estados
            const statusLabels = [];
            const statusValues = [];
            const statusColors = [
                'rgba(34, 197, 94, 0.8)',   // Verde - Completo
                'rgba(234, 179, 8, 0.8)',   // Amarillo - Completando
                'rgba(59, 130, 246, 0.8)'   // Azul - Adelanto
            ];

            Object.entries(statusData).forEach(([status, count]) => {
                const friendlyName = statusMapping[status] || status.toUpperCase();
                statusLabels.push(friendlyName);
                statusValues.push(count);
            });

            // Asegurar que tenemos los colores correctos para cada estado
            const finalStatusColors = statusLabels.map(label => {
                if (label === 'COMPLETO') return 'rgba(34, 197, 94, 0.8)';
                if (label === 'COMPLETANDO') return 'rgba(234, 179, 8, 0.8)';
                if (label === 'ADELANTO') return 'rgba(59, 130, 246, 0.8)';
                return 'rgba(156, 163, 175, 0.8)'; // Gris para otros
            });
            
            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: statusLabels,
                    datasets: [{
                        data: statusValues,
                        backgroundColor: finalStatusColors,
                        borderColor: finalStatusColors.map(color => color.replace('0.8', '1')),
                        borderWidth: 2,
                        hoverOffset: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                padding: 15,
                                font: {
                                    size: 11,
                                    weight: 'bold'
                                },
                                usePointStyle: true,
                                pointStyle: 'circle'
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            borderColor: 'rgba(255, 255, 255, 0.1)',
                            borderWidth: 1,
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = total > 0 ? ((context.parsed / total) * 100).toFixed(1) : '0.0';
                                    return `${context.label}: ${context.parsed} (${percentage}%)`;
                                }
                            }
                        }
                    },
                    cutout: '40%'
                }
            });
            
            // 2. Gráfico de inscripciones por medio de pago
            const paymentMethodCtx = document.getElementById('paymentMethodChart').getContext('2d');
            
            // Obtener datos reales del controlador
            const paymentMethodData = JSON.parse('{!! $chartData["paymentMethodData"] !!}');

            // Mapear los nombres técnicos a nombres amigables
            const methodMapping = {
                'QR': 'QR/DIGITAL',
                'efectivo': 'EFECTIVO',
                'deposito': 'DEPÓSITO',
                'transferencia': 'TRANSFERENCIA',
                'tarjeta_credito': 'T. CRÉDITO',
                'tarjeta_debito': 'T. DÉBITO'
            };

            // Crear arrays de etiquetas, datos y colores
            const methodLabels = [];
            const methodValues = [];
            const methodColors = [
                'rgba(34, 197, 94, 0.8)',   // Verde - Efectivo
                'rgba(59, 130, 246, 0.8)',  // Azul - Transferencia/Depósito
                'rgba(147, 51, 234, 0.8)',  // Púrpura - QR/Digital
                'rgba(239, 68, 68, 0.8)',   // Rojo - Tarjeta Crédito
                'rgba(245, 158, 11, 0.8)',  // Amarillo - Tarjeta Débito
                'rgba(236, 72, 153, 0.8)'   // Rosa - Otros
            ];

            let colorIndex = 0;
            Object.entries(paymentMethodData).forEach(([method, count]) => {
                const rawMethod = (method || '').trim();

                // Omitir metodos vacios para evitar segmentos sin etiqueta
                if (rawMethod === '') {
                    return;
                }

                const normalizedMethod = rawMethod.toLowerCase();

                // Usar el mapeo si existe, sino usar el nombre original en mayúsculas
                const friendlyName = methodMapping[rawMethod] || methodMapping[normalizedMethod] || rawMethod.toUpperCase();
                methodLabels.push(friendlyName);
                methodValues.push(count);
                colorIndex++;
            });

            // Asegurar que tenemos suficientes colores
            const finalColors = methodColors.slice(0, methodLabels.length);
            while (finalColors.length < methodLabels.length) {
                finalColors.push(`hsla(${Math.random() * 360}, 70%, 60%, 0.8)`);
            }

            new Chart(paymentMethodCtx, {
                type: 'doughnut',
                data: {
                    labels: methodLabels,
                    datasets: [{
                        data: methodValues,
                        backgroundColor: finalColors,
                        borderColor: finalColors.map(color => color.replace('0.8', '1')),
                        borderWidth: 2,
                        hoverOffset: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                padding: 15,
                                font: {
                                    size: 11,
                                    weight: 'bold'
                                },
                                usePointStyle: true,
                                pointStyle: 'circle'
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            borderColor: 'rgba(255, 255, 255, 0.1)',
                            borderWidth: 1,
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = total > 0 ? ((context.parsed / total) * 100).toFixed(1) : '0.0';
                                    return `${context.label}: ${context.parsed} (${percentage}%)`;
                                }
                            }
                        }
                    },
                    cutout: '40%'
                }
            });
            
            // 5. Inscripciones por residencia
            const residenceData = JSON.parse('{!! $chartData["residenceData"] !!}');
            
            // Datos detallados por estado para cada departamento (datos reales del controlador)
            const residenceDetailData = @json($chartData['residenceDetailData']);
            
            const labels = [];
            const completos = [];
            const completando = [];
            const adelantos = [];

            // recorrer los departamentos
            Object.keys(residenceData).forEach(dept => {
                const departmentName = (dept || '').trim();

                // Evitar filas vacias en el grafico por departamentos sin nombre
                if (departmentName === '') {
                    return;
                }

                const detail = residenceDetailData[dept] || {
                    completo: 0,
                    completando: 0,
                    adelanto: 0
                };

                labels.push(departmentName);
                completos.push(detail.completo);
                completando.push(detail.completando);
                adelantos.push(detail.adelanto);

            });

            const ctx = document.getElementById('departamentosChart');

            // Ajustar altura del contenedor segun la cantidad de departamentos
            const departmentChartContainer = document.getElementById('departmentChartContainer');
            const departmentMinHeight = 400;
            const departmentHeightPerItem = 40; // pixels por cada departamento
            const departmentCalculatedHeight = Math.max(departmentMinHeight, labels.length * departmentHeightPerItem);
            departmentChartContainer.style.height = departmentCalculatedHeight + 'px';

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Completos',
                            data: completos,
                            backgroundColor: '#22c55e'
                        },
                        {
                            label: 'Completando',
                            data: completando,
                            backgroundColor: '#eab308'
                        },
                        {
                            label: 'Adelantos',
                            data: adelantos,
                            backgroundColor: '#3b82f6'
                        }
                    ]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top'
                        }
                    },
                    scales: {
                        x: {
                            stacked: true,
                            beginAtZero: true
                        },
                        y: {
                            stacked: true
                        }
                    }
                }
            });
            
            // 6. Gráfico interactivo de inscripciones por género
            const genderData = @json($chartData['genderData'] ?? '{}');
            const genderDetailData = @json($chartData['genderDetailData'] ?? []);
            
            // Convertir genderData de string JSON a objeto si es necesario
            let genderDataObj = {};
            try {
                genderDataObj = typeof genderData === 'string' ? JSON.parse(genderData) : genderData;
            } catch (e) {
                console.log('Error parsing genderData:', e);
                genderDataObj = {};
            }
            
            // Actualizar datos de género
            Object.entries(genderDataObj).forEach(([genero, cantidad]) => {
                if (genero === 'Masculino' || genero === 'masculino' || genero === 'M') {
                    document.getElementById('male-count').textContent = cantidad;
                    const detalle = genderDetailData[genero] || { completo: 0, completando: 0, adelanto: 0 };
                    document.getElementById('male-completo').textContent = detalle.completo;
                    document.getElementById('male-completando').textContent = detalle.completando;
                    document.getElementById('male-adelanto').textContent = detalle.adelanto;
                } else if (genero === 'Femenino' || genero === 'femenino' || genero === 'F') {
                    document.getElementById('female-count').textContent = cantidad;
                    const detalle = genderDetailData[genero] || { completo: 0, completando: 0, adelanto: 0 };
                    document.getElementById('female-completo').textContent = detalle.completo;
                    document.getElementById('female-completando').textContent = detalle.completando;
                    document.getElementById('female-adelanto').textContent = detalle.adelanto;
                }
            });
            
            // 6.1. Gráfico de barras de plan de pago
            const paymentPlanBarCtx = document.getElementById('paymentPlanBarChart').getContext('2d');
            const paymentPlanBarData = JSON.parse('{!! $chartData["paymentPlanData"] !!}');
            
            // Preparar datos para el gráfico de barras - Unificado
            const planData = {};
            
            Object.entries(paymentPlanBarData).forEach(([plan, cantidad]) => {
                let label = '';
                let color = '';
                
                if (plan === 'PLAN CONTADO' || plan === 'PLAN CONTADO GESTIÓN 2026') {
                    label = 'PLAN CONTADO';
                    color = 'rgba(59, 130, 246, 0.8)'; // Azul
                } else if (plan === 'PLAN CRÉDITO' || plan === 'PLAN A CRÉDITO GESTIÓN 2026') {
                    label = 'PLAN CRÉDITO';
                    color = 'rgba(236, 72, 153, 0.8)'; // Rosa
                } else {
                    label = plan.toUpperCase();
                    color = 'rgba(156, 163, 175, 0.8)'; // Gris
                }
                
                // Si la etiqueta ya existe, sumar cantidad; si no, crear nueva
                if (planData[label]) {
                    planData[label].value += cantidad;
                } else {
                    planData[label] = { value: cantidad, color: color };
                }
            });
            
            // Convertir objeto a arrays para Chart.js
            const planLabels = Object.keys(planData);
            const planValues = Object.values(planData).map(item => item.value);
            const planColors = Object.values(planData).map(item => item.color);
            
            new Chart(paymentPlanBarCtx, {
                type: 'bar',
                data: {
                    labels: planLabels,
                    datasets: [{
                        data: planValues,
                        backgroundColor: planColors,
                        borderColor: planColors.map(color => color.replace('0.8', '1')),
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                display: true,
                                color: 'rgba(0, 0, 0, 0.1)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
            
            // 7. Grafico progresivo de inscripciones (mensual: dias, anual: meses)
            const progressiveCtx = document.getElementById('progressiveChart').getContext('2d');
            const progressiveLabels = JSON.parse('{!! $chartData["progressiveLabels"] !!}');
            const progressiveValues = JSON.parse('{!! $chartData["progressiveData"] !!}');
            const progressiveGranularity = '{!! $chartData["progressiveGranularity"] !!}';

            new Chart(progressiveCtx, {
                type: 'line',
                data: {
                    labels: progressiveLabels,
                    datasets: [{
                        label: progressiveGranularity === 'day' ? 'Acumulado por dia' : 'Acumulado por mes',
                        data: progressiveValues,
                        borderColor: 'rgba(37, 99, 235, 0.9)',
                        backgroundColor: 'rgba(37, 99, 235, 0.15)',
                        borderWidth: 3,
                        pointRadius: 2,
                        pointHoverRadius: 5,
                        fill: true,
                        tension: 0.25
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            borderColor: 'rgba(255, 255, 255, 0.1)',
                            borderWidth: 1,
                            callbacks: {
                                label: function(context) {
                                    return `Inscripciones acumuladas: ${context.parsed.y}`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                maxTicksLimit: progressiveGranularity === 'day' ? 10 : 12
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
                                label: 'TOTAL',
                                data: monthlyDatasets['total'],
                                borderColor: 'rgba(75, 85, 99, 0.7)',
                                backgroundColor: 'rgba(75, 85, 99, 0.1)',
                                borderWidth: 2,
                                fill: false,
                                tension: 0.1
                            },
                            {
                                label: 'COMPLETO',
                                data: monthlyDatasets['completo'],
                                borderColor: colors['Completo'],
                                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                                borderWidth: 2,
                                fill: false,
                                tension: 0.1
                            },
                            {
                                label: 'COMPLETANDO',
                                data: monthlyDatasets['completando'],
                                borderColor: colors['Completando'],
                                backgroundColor: 'rgba(234, 179, 8, 0.1)',
                                borderWidth: 2,
                                fill: false,
                                tension: 0.1
                            },
                            {
                                label: 'ADELANTO',
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
                                label: 'TOTAL PAGADO (BS)',
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