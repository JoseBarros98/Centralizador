<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'ESAM LATAM') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Estilos adicionales -->
    @stack('styles')
    <style>
        [x-cloak] { display: none !important; }
        /* Icon-only sidebar */
        .nav-icon-only > div > button { justify-content: center; padding-left: 0.25rem; padding-right: 0.25rem; }
        .nav-icon-only > div > button > svg:first-child { margin-right: 0 !important; }
        .nav-icon-only > div > button > svg:last-child { display: none !important; }
        .nav-icon-only > div > div { display: none !important; }
        .nav-icon-only .nav-label { display: none !important; }
        /* Navbar header slot */
        .navbar-title h2 { font-size: 1rem; font-weight: 600; color: #1f2937; line-height: 1.25rem; }
        .navbar-title h3 { font-size: 0.875rem; font-weight: 600; color: #374151; }
    </style>
</head>
<body class="font-sans antialiased bg-gray-50">
    <div x-data="{
            sidebarOpen: false,
            sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true',
            toggleSidebar() {
                this.sidebarCollapsed = !this.sidebarCollapsed;
                localStorage.setItem('sidebarCollapsed', this.sidebarCollapsed);
            }
        }" class="min-h-screen flex flex-col">
        <!-- Mobile menu button -->
        <div class="md:hidden bg-white shadow-sm py-3 px-4 flex items-center justify-between">
            <button @click="sidebarOpen = true" class="text-gray-500 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500">
                <span class="sr-only">Abrir menú</span>
                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
            <div class="flex-1 min-w-0 mx-3 navbar-title">
                @if(isset($header))
                    {{ $header }}
                @else
                    <span class="text-base font-semibold text-gray-800 truncate">ESAM LATAM</span>
                @endif
            </div>
            <a href="{{ route('profile.edit') }}" class="flex-shrink-0">
                @php
                    $mbParts = explode(' ', trim(Auth::user()->name));
                    $mbInitials = strtoupper(substr($mbParts[0], 0, 1));
                    if (count($mbParts) > 1) { $mbInitials .= strtoupper(substr(end($mbParts), 0, 1)); }
                @endphp
                <div class="h-8 w-8 rounded-full bg-indigo-600 flex items-center justify-center text-white text-xs font-bold select-none">
                    {{ $mbInitials }}
                </div>
            </a>
        </div>

        <div class="flex flex-1 overflow-hidden">
            <!-- Sidebar for desktop -->
            <div class="hidden md:block flex-shrink-0 transition-all duration-300"
                 :style="sidebarCollapsed ? 'width:4rem' : 'width:16rem'">
                <div class="flex flex-col fixed inset-y-0 z-20 overflow-hidden transition-all duration-300"
                     :class="sidebarCollapsed ? 'w-16' : 'w-64'">
                    <div class="flex flex-col h-full bg-gray-900">
                        <!-- Logo + Toggle -->
                        <div class="flex-shrink-0 flex items-center border-b border-gray-700/60 transition-all duration-300"
                             :class="sidebarCollapsed ? 'justify-center py-4 px-2' : 'justify-between py-5 px-4'">
                            <div class="h-12 overflow-hidden" x-show="!sidebarCollapsed">
                                <img src="{{ asset('images/ESAM LATAM BLANCO.png') }}" alt="Logo" class="h-full w-auto object-contain">
                            </div>
                            <button @click="toggleSidebar()"
                                    class="flex-shrink-0 p-1.5 rounded-lg text-gray-400 hover:text-white hover:bg-gray-700 transition-colors focus:outline-none"
                                    :title="sidebarCollapsed ? 'Expandir menú' : 'Colapsar menú'">
                                <svg class="h-5 w-5 transition-transform duration-300" :class="sidebarCollapsed ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                                </svg>
                            </button>
                        </div>
                        <!-- Navegación (scrollable) -->
                        <div class="flex-1 overflow-y-auto py-3">
                            <nav :class="sidebarCollapsed ? 'nav-icon-only px-1' : 'px-2'" class="space-y-0.5">
                                
                                <!-- Dashboards - Desplegable -->
                                @if(auth()->user()->can('dashboard.marketing') || auth()->user()->can('dashboard.academic') || auth()->user()->can('dashboard.accounting') || auth()->user()->can('dashboard.design'))
                                <div x-data="{ open: {{ request()->routeIs('dashboard*') ? 'true' : 'false' }} }">
                                    <button @click="open = !open" title="Dashboards" class="group w-full flex items-center px-2 py-2 text-sm font-medium rounded-md text-white hover:bg-gray-800 focus:outline-none">
                                        <svg class="mr-3 h-5 w-5 text-gray-400 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v15a1 1 0 0 0 1 1h15M8 16l2.5-5.5 3 3L17.273 7 20 9.667"/>
                                        </svg>
                                        <span class="nav-label">Dashboards</span>
                                        <svg :class="{ 'rotate-180': open }" class="ml-auto h-5 w-5 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                    
                                    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" class="mt-1 space-y-1">
                                        <!-- Dashboard Principal -->
                                        @can('dashboard.marketing')
                                        <a href="{{ route('dashboard') }}" class="group flex items-center pl-8 pr-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('dashboard') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                            <svg class="mr-3 h-5 w-5 {{ request()->routeIs('dashboard') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-white' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6.025A7.5 7.5 0 1 0 17.975 14H10V6.025Z"/>
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 3c-.169 0-.334.014-.5.025V11h7.975c.011-.166.025-.331.025-.5A7.5 7.5 0 0 0 13.5 3Z"/>
                                            </svg>
                                            Marketing
                                        </a>
                                        @endcan

                                        <!-- Dashboard Académico -->
                                        @can('dashboard.academic')
                                        <a href="{{ route('dashboard.academic') }}" class="group flex items-center pl-8 pr-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('dashboard.academic') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                            <svg class="mr-3 h-5 w-5 {{ request()->routeIs('dashboard.academic') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                                            </svg>
                                            Académico
                                        </a>
                                        @endcan

                                        <!-- Dashboard Contable -->
                                        @can('dashboard.accounting')
                                        <a href="{{ route('dashboard.accounting') }}" class="group flex items-center pl-8 pr-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('dashboard.accounting') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                            <svg class="mr-3 h-5 w-5 {{ request()->routeIs('dashboard.accounting') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                            </svg>
                                            Cobranzas
                                        </a>

                                        <a href="{{ route('dashboard.income-expense') }}" class="group flex items-center pl-8 pr-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('dashboard.income-expense') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                            <svg class="mr-3 h-5 w-5 {{ request()->routeIs('dashboard.income-expense') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 17l6-6 4 4 8-8" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 7h7v7" />
                                            </svg>
                                            Ingresos/Egresos
                                        </a>
                                        @endcan

                                        <!-- Dashboard Solicitudes de Arte -->
                                        @can('dashboard.design')
                                        <a href="{{ route('art_requests.dashboard') }}" class="group flex items-center pl-8 pr-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('art_requests.dashboard') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                            <svg class="mr-3 h-5 w-5 {{ request()->routeIs('art_requests.dashboard') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 7h.01m3.486 1.513h.01m-6.978 0h.01M6.99 12H7m9 4h2.706a1.957 1.957 0 0 0 1.883-1.325A9 9 0 1 0 3.043 12.89 9.1 9.1 0 0 0 8.2 20.1a8.62 8.62 0 0 0 3.769.9 2.013 2.013 0 0 0 2.03-2v-.857A2.036 2.036 0 0 1 16 16Z"/>
                                            </svg>
                                            Diseño
                                        </a>
                                        @endcan

                                    </div>
                                </div>
                                @endif

                                <!-- Marketing - Desplegable -->
                                @if(auth()->user()->can('inscription.view') || auth()->user()->can('inscription.view_own') || auth()->user()->can('marketing.manage_teams') || auth()->user()->can('marketing.manage_goals') || auth()->user()->can('marketing_contact.view') || auth()->user()->can('marketing_contact.view_own') || auth()->user()->can('marketing_contact.view_team'))
                                <div x-data="{ open: {{ request()->routeIs('inscriptions.*') || request()->routeIs('marketing-teams.*') || request()->routeIs('marketing-contacts.*') || request()->routeIs('marketing-goals.*') ? 'true' : 'false' }} }">
                                    <button @click="open = !open" title="Marketing" class="group w-full flex items-center px-2 py-2 text-sm font-medium rounded-md text-white hover:bg-gray-800 focus:outline-none">
                                        <svg class="mr-3 h-5 w-5 text-gray-400 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.891 15.107 15.11 8.89m-5.183-.52h.01m3.089 7.254h.01M14.08 3.902a2.849 2.849 0 0 0 2.176.902 2.845 2.845 0 0 1 2.94 2.94 2.849 2.849 0 0 0 .901 2.176 2.847 2.847 0 0 1 0 4.16 2.848 2.848 0 0 0-.901 2.175 2.843 2.843 0 0 1-2.94 2.94 2.848 2.848 0 0 0-2.176.902 2.847 2.847 0 0 1-4.16 0 2.85 2.85 0 0 0-2.176-.902 2.845 2.845 0 0 1-2.94-2.94 2.848 2.848 0 0 0-.901-2.176 2.848 2.848 0 0 1 0-4.16 2.849 2.849 0 0 0 .901-2.176 2.845 2.845 0 0 1 2.941-2.94 2.849 2.849 0 0 0 2.176-.901 2.847 2.847 0 0 1 4.159 0Z"/>
                                        </svg>
                                        <span class="nav-label">Marketing</span>
                                        <svg :class="{ 'rotate-180': open }" class="ml-auto h-5 w-5 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>

                                    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" class="mt-1 space-y-1">
                                        <!-- Inscripciones -->
                                        @if(auth()->user()->can('inscription.view') || auth()->user()->can('inscription.view_own'))
                                        <a href="{{ route('inscriptions.index') }}" class="group flex items-center pl-8 pr-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('inscriptions.*') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                            <svg class="mr-3 h-5 w-5 {{ request()->routeIs('inscriptions.*') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-white' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                            </svg>
                                            Inscripciones
                                        </a>
                                        @endif

                                        <!-- Contactos -->
                                        @if(auth()->user()->can('marketing_contact.view') || auth()->user()->can('marketing_contact.view_own') || auth()->user()->can('marketing_contact.view_team'))
                                        <a href="{{ route('marketing-contacts.index') }}" class="group flex items-center pl-8 pr-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('marketing-contacts.*') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                            <svg class="mr-3 h-5 w-5 {{ request()->routeIs('marketing-contacts.*') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                            Contactos
                                        </a>
                                        @endif

                                        <!-- Metas -->
                                        @can('marketing.manage_goals')
                                        <a href="{{ route('marketing-goals.index') }}" class="group flex items-center pl-8 pr-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('marketing-goals.*') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                            <svg class="mr-3 h-5 w-5 {{ request()->routeIs('marketing-goals.*') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                            </svg>
                                            Metas
                                        </a>
                                        @endcan

                                        @can('marketing_program.view')
                                        <a href="{{ route('marketing-programs.index') }}" class="group flex items-center pl-8 pr-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('marketing-programs.*') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                            <svg class="mr-3 h-5 w-5 {{ request()->routeIs('marketing-programs.*') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                            </svg>
                                            Oferta Académica
                                        </a>
                                        @endcan

                                        <!-- Equipos -->
                                        @can('marketing.manage_teams')
                                        <a href="{{ route('marketing-teams.index') }}" class="group flex items-center pl-8 pr-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('marketing-teams.*') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                            <svg class="mr-3 h-5 w-5 {{ request()->routeIs('marketing-teams.*') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                            </svg>
                                            Equipos
                                        </a>
                                        @endcan
                                    </div>
                                </div>
                                @endif

                                <!-- Académico - Desplegable -->
                                @if(auth()->user()->can('program.view') || auth()->user()->can('calendar.view') || auth()->user()->can('teacher.view') || auth()->user()->can('participants.view'))
                                <div x-data="{ open: {{ request()->routeIs('programs.*') || request()->routeIs('calendar.*') || request()->routeIs('teachers.*') || request()->routeIs('academic.participants.*') ? 'true' : 'false' }} }">
                                    <button @click="open = !open" title="Académico" class="group w-full flex items-center px-2 py-2 text-sm font-medium rounded-md text-white hover:bg-gray-800 focus:outline-none">
                                        <svg class="mr-3 h-5 w-5 text-gray-400 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                                        </svg>
                                        <span class="nav-label">Académico</span>
                                        <svg :class="{ 'rotate-180': open }" class="ml-auto h-5 w-5 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>

                                    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" class="mt-1 space-y-1">
                                        <!-- Programas -->
                                        @can('program.view')
                                        <a href="{{ route('programs.index') }}" class="group flex items-center pl-8 pr-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('programs.*') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                            <svg class="mr-3 h-5 w-5 {{ request()->routeIs('programs.*') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-white' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                            </svg>
                                            Programas
                                        </a>
                                        @endcan

                                        <!-- Participantes -->
                                        @can('participants.view')
                                        <a href="{{ route('academic.participants.index') }}" class="group flex items-center pl-8 pr-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('academic.participants.*') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                            <svg class="mr-3 h-5 w-5 {{ request()->routeIs('academic.participants.*') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            Participantes
                                        </a>
                                        @endcan

                                        <!-- Calendario -->
                                        @can('calendar.view')
                                        <a href="{{ route('calendar.index') }}" class="group flex items-center pl-8 pr-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('calendar.*') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                            <svg class="mr-3 h-5 w-5 {{ request()->routeIs('calendar.*') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-white' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            Calendario
                                        </a>
                                        @endcan

                                        <!-- Docentes -->
                                        @can('teacher.view')
                                        <a href="{{ route('teachers.index') }}" class="group flex items-center pl-8 pr-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('teachers.*') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                            <svg class="mr-3 h-5 w-5 {{ request()->routeIs('teachers.*') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2a1 1 0 001 1h14a1 1 0 001-1v-2c0-2.66-5.33-4-8-4z" />
                                            </svg>
                                            Docentes
                                        </a>
                                        @endcan

                                        <!-- Universidades -->
                                        @role(['admin'])
                                        <a href="{{ route('universities.index') }}" class="group flex items-center pl-8 pr-2 py-2 text-base font-medium rounded-md {{ request()->routeIs('universities.*') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                            <svg class="mr-4 h-5 w-5 {{ request()->routeIs('universities.*') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 4h12M6 4v16M6 4H5m13 0v16m0-16h1m-1 16H6m12 0h1M6 20H5M9 7h1v1H9V7Zm5 0h1v1h-1V7Zm-5 4h1v1H9v-1Zm5 0h1v1h-1v-1Zm-3 4h2a1 1 0 0 1 1 1v4h-4v-4a1 1 0 0 1 1-1Z"/>
                                            </svg>
                                            Universidades
                                        </a>
                                        @endrole

                                        <!-- Profesiones -->
                                        @role(['admin'])
                                        <a href="{{ route('professions.index') }}" class="group flex items-center pl-8 pr-2 py-2 text-base font-medium rounded-md {{ request()->routeIs('professions.*') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                            <svg class="mr-4 h-5 w-5 {{ request()->routeIs('professions.*') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M9 8h10M9 12h10M9 16h10M4.99 8H5m-.02 4h.01m0 4H5"/>
                                            </svg>
                                            Profesiones
                                        </a>
                                        @endrole
                                    </div>
                                </div>
                                @endif

                                <!-- Diseño - Desplegable -->
                                @if(auth()->user()->can('content_pillar.view') || auth()->user()->can('type_of_art.view') || auth()->user()->can('content.view') || auth()->user()->can('content.view_own'))
                                <div x-data="{ open: {{ request()->routeIs('content-pillars.*') || request()->routeIs('type-of-arts.*') || request()->routeIs('type_of_arts.*') || request()->routeIs('art-requests.*') ? 'true' : 'false' }} }">
                                    <button @click="open = !open" title="Diseño" class="group w-full flex items-center px-2 py-2 text-sm font-medium rounded-md text-white hover:bg-gray-800 focus:outline-none">
                                        <svg class="mr-3 h-5 w-5 text-gray-400 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 7h.01m3.486 1.513h.01m-6.978 0h.01M6.99 12H7m9 4h2.706a1.957 1.957 0 0 0 1.883-1.325A9 9 0 1 0 3.043 12.89 9.1 9.1 0 0 0 8.2 20.1a8.62 8.62 0 0 0 3.769.9 2.013 2.013 0 0 0 2.03-2v-.857A2.036 2.036 0 0 1 16 16Z"/>
                                        </svg>
                                        <span class="nav-label">Diseño</span>
                                        <svg :class="{ 'rotate-180': open }" class="ml-auto h-5 w-5 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                    
                                    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" class="mt-1 space-y-1">
                                        <!-- Pilar de Contenido -->
                                        @can('content_pillar.view')
                                        <a href="{{ route('content-pillars.index') }}" class="group flex items-center pl-8 pr-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('content-pillars.*') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                            <svg class="mr-3 h-5 w-5 {{ request()->routeIs('content-pillars.*') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            Pilar de Contenido
                                        </a>
                                        @endcan

                                        <!-- Tipos de Artes -->
                                        @can('type_of_art.view')
                                        <a href="{{ route('type_of_arts.index') }}" class="group flex items-center pl-8 pr-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('type-of-arts.*') || request()->routeIs('type_of_arts.*') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                            <svg class="mr-3 h-5 w-5 {{ request()->routeIs('type-of-arts.*') || request()->routeIs('type_of_arts.*') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                                            </svg>
                                            Tipos de Artes
                                        </a>
                                        @endcan

                                        <!-- Solicitud de Artes -->
                                        @if(auth()->user()->can('content.view') || auth()->user()->can('content.view_own'))
                                        <a href="{{ route('art_requests.index') }}" class="group flex items-center pl-8 pr-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('art-requests.*') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                            <svg class="mr-3 h-5 w-5 {{ request()->routeIs('art-requests.*') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            Solicitud de Artes
                                        </a>
                                        @endif
                                    </div>
                                </div>
                                @endif

                                <!-- Contabilidad - Desplegable -->
                                @if(auth()->user()->can('payment_request.view') || auth()->user()->can('graduation_cite.view') || auth()->user()->can('program_allocation.view') || auth()->user()->can('nominal_assignment.view') || auth()->user()->can('management_income.view') || auth()->user()->can('management_expense.view'))
                                <div x-data="{ open: {{ request()->routeIs('payment_requests.*') || request()->routeIs('graduation-cites.*') || request()->routeIs('program-allocation.*') || request()->routeIs('management-incomes.*') || request()->routeIs('management-expenses.*') || request()->routeIs('nominal-assignments.*') || request()->routeIs('payment-plans.*') || request()->routeIs('participant-quotas.*') ? 'true' : 'false' }} }">
                                    <button @click="open = !open" title="Contabilidad" class="group w-full flex items-center px-2 py-2 text-sm font-medium rounded-md text-white hover:bg-gray-800 focus:outline-none">
                                        <svg class="mr-3 h-5 w-5 text-gray-400 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M6 14h2m3 0h5M3 7v10a1 1 0 0 0 1 1h16a1 1 0 0 0 1-1V7a1 1 0 0 0-1-1H4a1 1 0 0 0-1 1Z"/>
                                        </svg>
                                        <span class="nav-label">Contabilidad</span>
                                        <svg :class="{ 'rotate-180': open }" class="ml-auto h-5 w-5 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>

                                    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" class="mt-1 space-y-1">
                                        <!-- Solicitud de pago a docente -->
                                        @can('payment_request.view')
                                        <a href="{{ route('payment_requests.index') }}" class="group flex items-center pl-8 pr-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('payment_requests.*') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                            <svg class="mr-3 h-5 w-5 {{ request()->routeIs('payment_requests.*') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-white' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M8 7V6a1 1 0 0 1 1-1h11a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1h-1M3 18v-7a1 1 0 0 1 1-1h11a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1Zm8-3.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z"/>
                                            </svg>
                                            Solicitud de Pago a Docente
                                        </a>
                                        @endcan

                                        @can('graduation_cite.view')
                                        <a href="{{ route('graduation-cites.index') }}" class="group flex items-center pl-8 pr-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('graduation-cites.*') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                            <svg class="mr-3 h-5 w-5 {{ request()->routeIs('graduation-cites.*') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6M7 4h10a2 2 0 012 2v12a2 2 0 01-2 2H7a2 2 0 01-2-2V6a2 2 0 012-2z" />
                                            </svg>
                                            Titulación
                                        </a>
                                        @endcan

                                        <!-- Asignación Nominal -->
                                        @can('nominal_assignment.view')
                                        <a href="{{ route('nominal-assignments.index') }}" class="group flex items-center pl-8 pr-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('nominal-assignments.*') || request()->routeIs('payment-plans.*') || request()->routeIs('participant-quotas.*') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                            <svg class="mr-3 h-5 w-5 {{ request()->routeIs('nominal-assignments.*') || request()->routeIs('payment-plans.*') || request()->routeIs('participant-quotas.*') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                            </svg>
                                            Asignación Nominal
                                        </a>
                                        @endcan

                                        <!-- Asignación por Programa -->
                                        @can('program_allocation.view')
                                        <a href="{{ route('program-allocation.index') }}" class="group flex items-center pl-8 pr-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('program-allocation.*') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                            <svg class="mr-3 h-5 w-5 {{ request()->routeIs('program-allocation.*') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                            </svg>
                                            Asignación por Programa
                                        </a>
                                        @endcan

                                        @can('management_income.view')
                                        <a href="{{ route('management-incomes.index') }}" class="group flex items-center pl-8 pr-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('management-incomes.*') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                            <svg class="mr-3 h-5 w-5 {{ request()->routeIs('management-incomes.*') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Ingresos por Gestión
                                        </a>
                                        @endcan

                                        @can('management_expense.view')
                                        <a href="{{ route('management-expenses.index') }}" class="group flex items-center pl-8 pr-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('management-expenses.*') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                            <svg class="mr-3 h-5 w-5 {{ request()->routeIs('management-expenses.*') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Egresos por Gestión
                                        </a>
                                        @endcan

                                    </div>
                                </div>
                                @endif
                                
                                @role('admin')
                                <!-- Administración - Desplegable -->
                                <div x-data="{ open: {{ request()->routeIs('users.*') || request()->routeIs('roles.*') || request()->routeIs('advisors.*') || request()->routeIs('admin.google-drive.*') || request()->routeIs('admin.google-calendar.*') || request()->routeIs('admin.sync.*') || request()->routeIs('logs.*') ? 'true' : 'false' }} }">
                                    <button @click="open = !open" title="Administración" class="group w-full flex items-center px-2 py-2 text-sm font-medium rounded-md text-white hover:bg-gray-800 focus:outline-none">
                                        <svg class="mr-3 h-5 w-5 text-gray-400 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <span class="nav-label">Administración</span>
                                        <svg :class="{ 'rotate-180': open }" class="ml-auto h-5 w-5 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>

                                    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" class="mt-1 space-y-1">
                                        <!-- Usuarios -->
                                        <a href="{{ route('users.index') }}" class="group flex items-center pl-8 pr-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('users.*') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                            <svg class="mr-3 h-5 w-5 {{ request()->routeIs('users.*') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                            </svg>
                                            Usuarios
                                        </a>

                                        <!-- Roles -->
                                        <a href="{{ route('roles.index') }}" class="group flex items-center pl-8 pr-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('roles.*') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                            <svg class="mr-3 h-5 w-5 {{ request()->routeIs('roles.*') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                            </svg>
                                            Roles
                                        </a>

                                        <!-- Vincular Asesores -->
                                        @can('inscriptions.sync')
                                        <a href="{{ route('advisors.link.index') }}" class="group flex items-center pl-8 pr-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('advisors.*') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                            <svg class="mr-3 h-5 w-5 {{ request()->routeIs('advisors.*') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                            </svg>
                                            Vincular Asesores
                                        </a>
                                        @endcan

                                        <!-- Google Drive -->
                                        <a href="{{ route('admin.google-drive.setup') }}" class="group flex items-center pl-8 pr-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.google-drive.*') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                            <svg class="mr-3 h-5 w-5 {{ request()->routeIs('admin.google-drive.*') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
                                            </svg>
                                            Google Drive
                                        </a>

                                        <!-- Google Calendar & Meet -->
                                        <a href="{{ route('admin.google-calendar.setup') }}" class="group flex items-center pl-8 pr-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.google-calendar.*') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                            <svg class="mr-3 h-5 w-5 {{ request()->routeIs('admin.google-calendar.*') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            Google Calendar & Meet
                                        </a>

                                        <!-- Sincronización -->
                                        <a href="{{ route('admin.sync.index') }}" class="group flex items-center pl-8 pr-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.sync.*') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                            <svg class="mr-3 h-5 w-5 {{ request()->routeIs('admin.sync.*') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                            </svg>
                                            Sincronización
                                        </a>

                                        <!-- Ver Logs -->
                                        @can('system.view_logs')
                                        <a href="{{ route('logs.index') }}" class="group flex items-center pl-8 pr-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('logs.*') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                            <svg class="mr-3 h-5 w-5 {{ request()->routeIs('logs.*') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            Ver Logs
                                        </a>
                                        @endcan

                                        <!-- Backup -->
                                        @can('system.backup')
                                        <a href="{{ route('backup.index') }}" class="group flex items-center pl-8 pr-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('backup.*') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                            <svg class="mr-3 h-5 w-5 {{ request()->routeIs('backup.*') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                            </svg>
                                            Backup
                                        </a>
                                        @endcan
                                    </div>
                                </div>
                                @endrole
                            </nav>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mobile sidebar -->
            <div x-show="sidebarOpen" class="fixed inset-0 flex z-50 md:hidden" x-description="Off-canvas menu for mobile, show/hide based on off-canvas menu state." x-ref="dialog" aria-modal="true">
                <div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-linear duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-600 bg-opacity-75" x-description="Off-canvas menu overlay, show/hide based on off-canvas menu state." @click="sidebarOpen = false" aria-hidden="true"></div>
                
                <div x-show="sidebarOpen" x-transition:enter="transition ease-in-out duration-300 transform" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in-out duration-300 transform" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full" class="relative flex-1 flex flex-col max-w-xs w-full bg-gray-900" x-description="Off-canvas menu, show/hide based on off-canvas menu state.">
                    <div x-show="sidebarOpen" x-transition:enter="ease-in-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in-out duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="absolute top-0 right-0 -mr-12 pt-2" x-description="Close button, show/hide based on off-canvas menu state.">
                        <button @click="sidebarOpen = false" class="ml-1 flex items-center justify-center h-10 w-10 rounded-full focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white">
                            <span class="sr-only">Cerrar sidebar</span>
                            <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Logo mobile -->
                    <div class="flex-shrink-0 flex items-center px-4 py-5 border-b border-gray-700/60">
                        <div class="h-10 overflow-hidden">
                            <img src="{{ asset('images/ESAM LATAM BLANCO.png') }}" alt="Logo" class="h-full w-auto object-contain">
                        </div>
                    </div>
                    <!-- Nav mobile (scrollable) -->
                    <div class="flex-1 overflow-y-auto py-3">
                        <nav class="px-2 space-y-0.5">
                            <!-- Dashboards - Desplegable -->
                            @if(auth()->user()->can('dashboard.marketing') || auth()->user()->can('dashboard.academic') || auth()->user()->can('dashboard.accounting') || auth()->user()->can('dashboard.design'))
                            <div x-data="{ open: {{ request()->routeIs('dashboard*') ? 'true' : 'false' }} }">
                                <button @click="open = !open" class="group w-full flex items-center px-2 py-2 text-base font-medium rounded-md text-white hover:bg-gray-800 focus:outline-none">
                                    <svg class="mr-3 h-5 w-5 text-gray-400 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v15a1 1 0 0 0 1 1h15M8 16l2.5-5.5 3 3L17.273 7 20 9.667"/>
                                    </svg>
                                    Dashboards
                                    <svg :class="{ 'rotate-180': open }" class="ml-auto h-5 w-5 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                
                                <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" class="mt-1 space-y-1">
                                    <!-- Dashboard Principal -->
                                    @can('dashboard.marketing')
                                    <a href="{{ route('dashboard') }}" class="group flex items-center pl-8 pr-2 py-2 text-base font-medium rounded-md {{ request()->routeIs('dashboard') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                        <svg class="mr-4 h-5 w-5 {{ request()->routeIs('dashboard') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-white' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6.025A7.5 7.5 0 1 0 17.975 14H10V6.025Z"/>
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 3c-.169 0-.334.014-.5.025V11h7.975c.011-.166.025-.331.025-.5A7.5 7.5 0 0 0 13.5 3Z"/>
                                        </svg>
                                        Marketing
                                    </a>
                                    @endcan

                                    <!-- Dashboard Académico -->
                                    @can('dashboard.academic')
                                    <a href="{{ route('dashboard.academic') }}" class="group flex items-center pl-8 pr-2 py-2 text-base font-medium rounded-md {{ request()->routeIs('dashboard.academic') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                        <svg class="mr-4 h-5 w-5 {{ request()->routeIs('dashboard.academic') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                                        </svg>
                                        Académico
                                    </a>
                                    @endcan

                                    <!-- Dashboard Contable -->
                                    @can('dashboard.accounting')
                                    <a href="{{ route('dashboard.accounting') }}" class="group flex items-center pl-8 pr-2 py-2 text-base font-medium rounded-md {{ request()->routeIs('dashboard.accounting') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                        <svg class="mr-4 h-5 w-5 {{ request()->routeIs('dashboard.accounting') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                        </svg>

                                    </a>

                                    <a href="{{ route('dashboard.income-expense') }}" class="group flex items-center pl-8 pr-2 py-2 text-base font-medium rounded-md {{ request()->routeIs('dashboard.income-expense') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                        <svg class="mr-4 h-5 w-5 {{ request()->routeIs('dashboard.income-expense') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 17l6-6 4 4 8-8" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 7h7v7" />
                                        </svg>
                                        Ingresos/Egresos
                                    </a>
                                    @endcan

                                    <!-- Dashboard Solicitudes de Arte -->
                                    @can('dashboard.design')
                                    <a href="{{ route('art_requests.dashboard') }}" class="group flex items-center pl-8 pr-2 py-2 text-base font-medium rounded-md {{ request()->routeIs('art_requests.dashboard') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                        <svg class="mr-4 h-5 w-5 {{ request()->routeIs('art_requests.dashboard') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.5a2 2 0 00-1 .267V5a2 2 0 012-2h4a2 2 0 012 2v12a2 2 0 01-2 2z" />
                                        </svg>
                                        Diseño
                                    </a>
                                    @endcan
                                </div>
                            </div>
                            @endif

                            <!-- Marketing - Desplegable -->
                            @if(auth()->user()->can('inscription.view') || auth()->user()->can('inscription.view_own') || auth()->user()->can('marketing.manage_teams') || auth()->user()->can('marketing.manage_goals') || auth()->user()->can('marketing_contact.view') || auth()->user()->can('marketing_contact.view_own') || auth()->user()->can('marketing_contact.view_team'))
                            <div x-data="{ open: {{ request()->routeIs('inscriptions.*') || request()->routeIs('marketing-teams.*') || request()->routeIs('marketing-contacts.*') || request()->routeIs('marketing-goals.*') ? 'true' : 'false' }} }">
                                <button @click="open = !open" class="group w-full flex items-center px-2 py-2 text-base font-medium rounded-md text-white hover:bg-gray-800 focus:outline-none">
                                    <svg class="mr-3 h-5 w-5 text-gray-400 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.891 15.107 15.11 8.89m-5.183-.52h.01m3.089 7.254h.01M14.08 3.902a2.849 2.849 0 0 0 2.176.902 2.845 2.845 0 0 1 2.94 2.94 2.849 2.849 0 0 0 .901 2.176 2.847 2.847 0 0 1 0 4.16 2.848 2.848 0 0 0-.901 2.175 2.843 2.843 0 0 1-2.94 2.94 2.848 2.848 0 0 0-2.176.902 2.847 2.847 0 0 1-4.16 0 2.85 2.85 0 0 0-2.176-.902 2.845 2.845 0 0 1-2.94-2.94 2.848 2.848 0 0 0-.901-2.176 2.848 2.848 0 0 1 0-4.16 2.849 2.849 0 0 0 .901-2.176 2.845 2.845 0 0 1 2.941-2.94 2.849 2.849 0 0 0 2.176-.901 2.847 2.847 0 0 1 4.159 0Z"/>
                                    </svg>
                                    Marketing
                                    <svg :class="{ 'rotate-180': open }" class="ml-auto h-5 w-5 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                
                                <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" class="mt-1 space-y-1">
                                    <!-- Inscripciones -->
                                    @if(auth()->user()->can('inscription.view') || auth()->user()->can('inscription.view_own'))
                                    <a href="{{ route('inscriptions.index') }}" class="group flex items-center pl-8 pr-2 py-2 text-base font-medium rounded-md {{ request()->routeIs('inscriptions.*') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                        <svg class="mr-4 h-5 w-5 {{ request()->routeIs('inscriptions.*') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-white' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                        Inscripciones
                                    </a>
                                    @endif

                                    <!-- Contactos -->
                                    @if(auth()->user()->can('marketing_contact.view') || auth()->user()->can('marketing_contact.view_own') || auth()->user()->can('marketing_contact.view_team'))
                                    <a href="{{ route('marketing-contacts.index') }}" class="group flex items-center pl-8 pr-2 py-2 text-base font-medium rounded-md {{ request()->routeIs('marketing-contacts.*') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                        <svg class="mr-4 h-5 w-5 {{ request()->routeIs('marketing-contacts.*') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        Contactos
                                    </a>
                                    @endif

                                    <!-- Metas -->
                                    @can('marketing.manage_goals')
                                    <a href="{{ route('marketing-goals.index') }}" class="group flex items-center pl-8 pr-2 py-2 text-base font-medium rounded-md {{ request()->routeIs('marketing-goals.*') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                        <svg class="mr-4 h-5 w-5 {{ request()->routeIs('marketing-goals.*') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                        </svg>
                                        Metas
                                    </a>
                                    @endcan

                                    @can('marketing_program.view')
                                    <a href="{{ route('marketing-programs.index') }}" class="group flex items-center pl-8 pr-2 py-2 text-base font-medium rounded-md {{ request()->routeIs('marketing-programs.*') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                        <svg class="mr-4 h-5 w-5 {{ request()->routeIs('marketing-programs.*') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                        Oferta Académica
                                    </a>
                                    @endcan

                                    <!-- Equipos -->
                                    @can('marketing.manage_teams')
                                    <a href="{{ route('marketing-teams.index') }}" class="group flex items-center pl-8 pr-2 py-2 text-base font-medium rounded-md {{ request()->routeIs('marketing-teams.*') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                        <svg class="mr-4 h-5 w-5 {{ request()->routeIs('marketing-teams.*') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                        Equipos
                                    </a>
                                    @endcan
                                </div>
                            </div>
                            @endif

                            <!-- Académico - Desplegable -->
                            @if(auth()->user()->can('program.view') || auth()->user()->can('calendar.view') || auth()->user()->can('teacher.view'))
                            <div x-data="{ open: {{ request()->routeIs('programs.*') || request()->routeIs('calendar.*') || request()->routeIs('teachers.*') ? 'true' : 'false' }} }">
                                <button @click="open = !open" class="group w-full flex items-center px-2 py-2 text-base font-medium rounded-md text-white hover:bg-gray-800 focus:outline-none">
                                    <svg class="mr-3 h-5 w-5 text-gray-400 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                                    </svg>
                                    Académico
                                    <svg :class="{ 'rotate-180': open }" class="ml-auto h-5 w-5 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                
                                <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" class="mt-1 space-y-1">
                                    <!-- Programas -->
                                    @can('program.view')
                                    <a href="{{ route('programs.index') }}" class="group flex items-center pl-8 pr-2 py-2 text-base font-medium rounded-md {{ request()->routeIs('programs.*') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                        <svg class="mr-4 h-5 w-5 {{ request()->routeIs('programs.*') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-white' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                        </svg>
                                        Programas
                                    </a>
                                    @endcan

                                    <!-- Calendario -->
                                    @can('calendar.view')
                                    <a href="{{ route('calendar.index') }}" class="group flex items-center pl-8 pr-2 py-2 text-base font-medium rounded-md {{ request()->routeIs('calendar.*') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                        <svg class="mr-4 h-5 w-5 {{ request()->routeIs('calendar.*') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-white' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        Calendario
                                    </a>
                                    @endcan

                                    <!-- Docentes -->
                                    @can('teacher.view')
                                    <a href="{{ route('teachers.index') }}" class="group flex items-center pl-8 pr-2 py-2 text-base font-medium rounded-md {{ request()->routeIs('teachers.*') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                        <svg class="mr-4 h-5 w-5 {{ request()->routeIs('teachers.*') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2a1 1 0 001 1h14a1 1 0 001-1v-2c0-2.66-5.33-4-8-4z" />
                                        </svg>
                                        Docentes
                                    </a>
                                    @endcan

                                    <!-- Universidades -->
                                    @role(['admin'])
                                    <a href="{{ route('universities.index') }}" class="group flex items-center pl-8 pr-2 py-2 text-base font-medium rounded-md {{ request()->routeIs('universities.*') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                        <svg class="mr-4 h-5 w-5 {{ request()->routeIs('universities.*') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 4h12M6 4v16M6 4H5m13 0v16m0-16h1m-1 16H6m12 0h1M6 20H5M9 7h1v1H9V7Zm5 0h1v1h-1V7Zm-5 4h1v1H9v-1Zm5 0h1v1h-1v-1Zm-3 4h2a1 1 0 0 1 1 1v4h-4v-4a1 1 0 0 1 1-1Z"/>
                                        </svg>
                                        Universidades
                                    </a>
                                    @endrole

                                    <!-- Profesiones -->
                                        @role(['admin'])
                                        <a href="{{ route('professions.index') }}" class="group flex items-center pl-8 pr-2 py-2 text-base font-medium rounded-md {{ request()->routeIs('professions.*') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                            <svg class="mr-4 h-5 w-5 {{ request()->routeIs('professions.*') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M9 8h10M9 12h10M9 16h10M4.99 8H5m-.02 4h.01m0 4H5"/>
                                            </svg>
                                            Profesiones
                                        </a>
                                        @endrole
                                </div>
                            </div>
                            @endif

                            <!-- Diseño - Desplegable -->
                            @if(auth()->user()->can('content_pillar.view') || auth()->user()->can('type_of_art.view') || auth()->user()->can('content.view') || auth()->user()->can('content.view_own'))
                            <div x-data="{ open: {{ request()->routeIs('content-pillars.*') || request()->routeIs('type-of-arts.*') || request()->routeIs('type_of_arts.*') || request()->routeIs('art-requests.*') ? 'true' : 'false' }} }">
                                <button @click="open = !open" class="group w-full flex items-center px-2 py-2 text-base font-medium rounded-md text-white hover:bg-gray-800 focus:outline-none">
                                    <svg class="mr-3 h-5 w-5 text-gray-400 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 7h.01m3.486 1.513h.01m-6.978 0h.01M6.99 12H7m9 4h2.706a1.957 1.957 0 0 0 1.883-1.325A9 9 0 1 0 3.043 12.89 9.1 9.1 0 0 0 8.2 20.1a8.62 8.62 0 0 0 3.769.9 2.013 2.013 0 0 0 2.03-2v-.857A2.036 2.036 0 0 1 16 16Z"/>
                                    </svg>
                                    Diseño
                                    <svg :class="{ 'rotate-180': open }" class="ml-auto h-5 w-5 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                
                                <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" class="mt-1 space-y-1">
                                    <!-- Pilar de Contenido -->
                                    @can('content_pillar.view')
                                    <a href="{{ route('content-pillars.index') }}" class="group flex items-center pl-8 pr-2 py-2 text-base font-medium rounded-md {{ request()->routeIs('content-pillars.*') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                        <svg class="mr-4 h-5 w-5 {{ request()->routeIs('content-pillars.*') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        Pilar de Contenido
                                    </a>
                                    @endcan

                                    <!-- Tipos de Artes -->
                                    @can('type_of_art.view')
                                    <a href="{{ route('type_of_arts.index') }}" class="group flex items-center pl-8 pr-2 py-2 text-base font-medium rounded-md {{ request()->routeIs('type-of-arts.*') || request()->routeIs('type_of_arts.*') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                        <svg class="mr-4 h-5 w-5 {{ request()->routeIs('type-of-arts.*') || request()->routeIs('type_of_arts.*') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                                        </svg>
                                        Tipos de Artes
                                    </a>
                                    @endcan

                                    <!-- Solicitud de Artes -->
                                    @if(auth()->user()->can('content.view') || auth()->user()->can('content.view_own'))
                                    <a href="{{ route('art_requests.index') }}" class="group flex items-center pl-8 pr-2 py-2 text-base font-medium rounded-md {{ request()->routeIs('art-requests.*') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                        <svg class="mr-4 h-5 w-5 {{ request()->routeIs('art-requests.*') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        Solicitud de Artes
                                    </a>
                                    @endif
                                </div>
                            </div>
                            @endif

                            <!-- Contabilidad - Desplegable -->
                                @if(auth()->user()->can('payment_request.view') || auth()->user()->can('graduation_cite.view') || auth()->user()->can('program_allocation.view') || auth()->user()->can('nominal_assignment.view') || auth()->user()->can('management_income.view') || auth()->user()->can('management_expense.view'))
                                <div x-data="{ open: {{ request()->routeIs('payment_requests.*') || request()->routeIs('graduation-cites.*') || request()->routeIs('program-allocation.*') || request()->routeIs('management-incomes.*') || request()->routeIs('management-expenses.*') || request()->routeIs('nominal-assignments.*') || request()->routeIs('payment-plans.*') || request()->routeIs('participant-quotas.*') ? 'true' : 'false' }} }">
                                    <button @click="open = !open" class="group w-full flex items-center px-2 py-2 text-sm font-medium rounded-md text-white hover:bg-gray-800 focus:outline-none">
                                        <svg class="mr-3 h-5 w-5 text-gray-400 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M6 14h2m3 0h5M3 7v10a1 1 0 0 0 1 1h16a1 1 0 0 0 1-1V7a1 1 0 0 0-1-1H4a1 1 0 0 0-1 1Z"/>
                                        </svg>
                                        Contabilidad
                                        <svg :class="{ 'rotate-180': open }" class="ml-auto h-5 w-5 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                    
                                    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" class="mt-1 space-y-1">
                                        <!-- Solicitud de pago a docente -->
                                        @can('payment_request.view')
                                        <a href="{{ route('payment_requests.index') }}" class="group flex items-center pl-8 pr-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('payment_requests.*') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                            <svg class="mr-3 h-5 w-5 {{ request()->routeIs('payment_requests.*') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-white' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M8 7V6a1 1 0 0 1 1-1h11a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1h-1M3 18v-7a1 1 0 0 1 1-1h11a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1Zm8-3.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z"/>
                                            </svg>
                                            Solicitud de Pago a Docente
                                        </a>
                                        @endcan

                                        @can('graduation_cite.view')
                                        <a href="{{ route('graduation-cites.index') }}" class="group flex items-center pl-8 pr-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('graduation-cites.*') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                            <svg class="mr-3 h-5 w-5 {{ request()->routeIs('graduation-cites.*') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6M7 4h10a2 2 0 012 2v12a2 2 0 01-2 2H7a2 2 0 01-2-2V6a2 2 0 012-2z" />
                                            </svg>
                                            Titulación
                                        </a>
                                        @endcan

                                        <!-- Asignación Nominal -->
                                        @can('nominal_assignment.view')
                                        <a href="{{ route('nominal-assignments.index') }}" class="group flex items-center pl-8 pr-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('nominal-assignments.*') || request()->routeIs('payment-plans.*') || request()->routeIs('participant-quotas.*') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                            <svg class="mr-3 h-5 w-5 {{ request()->routeIs('nominal-assignments.*') || request()->routeIs('payment-plans.*') || request()->routeIs('participant-quotas.*') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                            </svg>
                                            Asignación Nominal
                                        </a>
                                        @endcan

                                        <!-- Asignación por Programa -->
                                        @can('program_allocation.view')
                                        <a href="{{ route('program-allocation.index') }}" class="group flex items-center pl-8 pr-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('program-allocation.*') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                            <svg class="mr-3 h-5 w-5 {{ request()->routeIs('program-allocation.*') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                            </svg>
                                            Asignación por Programa
                                        </a>
                                        @endcan

                                        @can('management_income.view')
                                        <a href="{{ route('management-incomes.index') }}" class="group flex items-center pl-8 pr-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('management-incomes.*') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                            <svg class="mr-3 h-5 w-5 {{ request()->routeIs('management-incomes.*') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Ingresos por Gestión
                                        </a>
                                        @endcan

                                        @can('management_expense.view')
                                        <a href="{{ route('management-expenses.index') }}" class="group flex items-center pl-8 pr-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('management-expenses.*') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                            <svg class="mr-3 h-5 w-5 {{ request()->routeIs('management-expenses.*') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Egresos por Gestión
                                        </a>
                                        @endcan

                                    </div>
                                </div>
                                @endif

                            @role('admin')
                            <!-- Administración - Desplegable -->
                            <div x-data="{ open: {{ request()->routeIs('users.*') || request()->routeIs('roles.*') || request()->routeIs('advisors.*') || request()->routeIs('admin.google-drive.*') || request()->routeIs('admin.sync.*') || request()->routeIs('logs.*') ? 'true' : 'false' }} }">
                                <button @click="open = !open" class="group w-full flex items-center px-2 py-2 text-base font-medium rounded-md text-white hover:bg-gray-800 focus:outline-none">
                                    <svg class="mr-3 h-5 w-5 text-gray-400 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    Administración
                                    <svg :class="{ 'rotate-180': open }" class="ml-auto h-5 w-5 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                
                                <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" class="mt-1 space-y-1">
                                    <!-- Usuarios -->
                                    <a href="{{ route('users.index') }}" class="group flex items-center pl-8 pr-2 py-2 text-base font-medium rounded-md {{ request()->routeIs('users.*') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                        <svg class="mr-4 h-5 w-5 {{ request()->routeIs('users.*') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                        </svg>
                                        Usuarios
                                    </a>

                                    <!-- Roles -->
                                    <a href="{{ route('roles.index') }}" class="group flex items-center pl-8 pr-2 py-2 text-base font-medium rounded-md {{ request()->routeIs('roles.*') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                        <svg class="mr-4 h-5 w-5 {{ request()->routeIs('roles.*') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                        </svg>
                                        Roles
                                    </a>

                                    <!-- Vincular Asesores -->
                                    @can('inscriptions.sync')
                                    <a href="{{ route('advisors.link.index') }}" class="group flex items-center pl-8 pr-2 py-2 text-base font-medium rounded-md {{ request()->routeIs('advisors.*') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                        <svg class="mr-4 h-5 w-5 {{ request()->routeIs('advisors.*') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                        </svg>
                                        Vincular Asesores
                                    </a>
                                    @endcan

                                    <!-- Google Drive -->
                                    <a href="{{ route('admin.google-drive.setup') }}" class="group flex items-center pl-8 pr-2 py-2 text-base font-medium rounded-md {{ request()->routeIs('admin.google-drive.*') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                        <svg class="mr-4 h-5 w-5 {{ request()->routeIs('admin.google-drive.*') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
                                        </svg>
                                        Google Drive
                                    </a>

                                    <!-- Sincronización -->
                                    <a href="{{ route('admin.sync.index') }}" class="group flex items-center pl-8 pr-2 py-2 text-base font-medium rounded-md {{ request()->routeIs('admin.sync.*') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                        <svg class="mr-4 h-5 w-5 {{ request()->routeIs('admin.sync.*') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                        Sincronización
                                    </a>

                                    <!-- Ver Logs -->
                                    @can('system.view_logs')
                                    <a href="{{ route('logs.index') }}" class="group flex items-center pl-8 pr-2 py-2 text-base font-medium rounded-md {{ request()->routeIs('logs.*') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                        <svg class="mr-4 h-5 w-5 {{ request()->routeIs('logs.*') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        Ver Logs
                                    </a>
                                    @endcan

                                    <!-- Backup -->
                                    @can('system.backup')
                                    <a href="{{ route('backup.index') }}" class="group flex items-center pl-8 pr-2 py-2 text-base font-medium rounded-md {{ request()->routeIs('backup.*') ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                        <svg class="mr-4 h-5 w-5 {{ request()->routeIs('backup.*') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                        </svg>
                                        Backup
                                    </a>
                                    @endcan
                                </div>
                            </div>
                            @endrole
                        </nav>
                    </div>
                    <!-- Usuario mobile (fondo) -->
                    <div class="flex-shrink-0 border-t border-gray-700/60 p-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center min-w-0 flex-1">
                                <div class="flex-shrink-0 h-8 w-8 rounded-full bg-indigo-600 flex items-center justify-center text-white text-xs font-bold select-none">
                                    @php
                                        $mParts = explode(' ', trim(Auth::user()->name));
                                        $mInitials = strtoupper(substr($mParts[0], 0, 1));
                                        if (count($mParts) > 1) { $mInitials .= strtoupper(substr(end($mParts), 0, 1)); }
                                    @endphp
                                    {{ $mInitials }}
                                </div>
                                <div class="ml-2.5 min-w-0 flex-1">
                                    <p class="text-sm font-medium text-white truncate leading-tight">{{ Auth::user()->name }}</p>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="text-xs text-gray-400 hover:text-white transition-colors duration-150">
                                            Cerrar sesión
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex-shrink-0 w-14" aria-hidden="true">
                    <!-- Dummy element to force sidebar to shrink to fit close icon -->
                </div>
            </div>

            <!-- Main content -->
            <div class="flex flex-col w-0 flex-1 overflow-hidden">
                <!-- Top Navbar (desktop) -->
                <div class="hidden md:flex bg-white border-b border-gray-200 px-6 h-14 flex-shrink-0 items-center justify-between z-10 shadow-sm">
                    <div class="navbar-title flex items-center min-w-0">
                        @if(isset($header))
                            {{ $header }}
                        @else
                            <span class="font-semibold text-gray-800 text-sm">ESAM LATAM</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-1">
                        <!-- Notificaciones -->
                        <div x-data="notificationDropdown()" x-init="init()" class="relative">
                            <button @click="toggle()"
                                    class="relative p-2 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors">
                                <span class="sr-only">Ver notificaciones</span>
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5.365V3m0 2.365a5.338 5.338 0 0 1 5.133 5.368v1.8c0 2.386 1.867 2.982 1.867 4.175 0 .593 0 1.193-.538 1.193H5.538c-.538 0-.538-.6-.538-1.193 0-1.193 1.867-1.789 1.867-4.175v-1.8A5.338 5.338 0 0 1 12 5.365Zm-8.134 5.368a8.458 8.458 0 0 1 2.252-5.714m14.016 5.714a8.458 8.458 0 0 0-2.252-5.714M8.54 17.901a3.48 3.48 0 0 0 6.92 0H8.54Z"/>
                                </svg>
                                <span x-show="unreadCount > 0"
                                      x-text="unreadCount"
                                      x-cloak
                                      class="absolute -top-0.5 -right-0.5 inline-flex items-center justify-center h-4 w-4 rounded-full bg-red-500 text-xs font-bold text-white shadow">
                                </span>
                            </button>
                            <!-- Dropdown notificaciones -->
                            <div x-show="open"
                                 x-cloak
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 translate-y-1"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100 translate-y-0"
                                 x-transition:leave-end="opacity-0 translate-y-1"
                                 @click.away="open = false"
                                 @keydown.escape.window="open = false"
                                 class="absolute right-0 top-full mt-2 w-80 bg-white rounded-xl shadow-xl border border-gray-200 z-50 overflow-hidden">
                                <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                                    <h3 class="text-sm font-semibold text-gray-800">Notificaciones</h3>
                                    <span x-show="unreadCount > 0" x-text="unreadCount + ' sin leer'" class="text-xs bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full font-medium"></span>
                                </div>
                                <div class="max-h-72 overflow-y-auto divide-y divide-gray-50">
                                    <template x-for="notification in notifications" :key="notification.id">
                                        <div class="px-4 py-3 hover:bg-gray-50 cursor-pointer transition-colors"
                                             @click="navigateToNotification(notification)"
                                             :class="{ 'bg-indigo-50/60': !notification.read_at }">
                                            <div class="flex items-start gap-3">
                                                <div class="flex-shrink-0 h-7 w-7 rounded-full bg-indigo-100 flex items-center justify-center mt-0.5">
                                                    <svg class="h-3.5 w-3.5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                                    </svg>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm font-medium text-gray-800 leading-tight" x-text="notification.title || notification.data?.title || 'Sin título'"></p>
                                                    <p class="text-xs text-gray-500 mt-0.5 line-clamp-2" x-text="notification.message || notification.data?.message || ''"></p>
                                                    <p class="text-xs text-gray-400 mt-1" x-text="notification.created_at"></p>
                                                </div>
                                                <div x-show="!notification.read_at" class="flex-shrink-0 mt-1.5">
                                                    <div class="h-2 w-2 bg-indigo-500 rounded-full"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                    <div x-show="notifications.length === 0" class="px-4 py-8 text-center">
                                        <svg class="h-8 w-8 text-gray-300 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                        </svg>
                                        <p class="text-sm text-gray-400">Sin notificaciones</p>
                                    </div>
                                </div>
                                <div class="px-4 py-2.5 border-t border-gray-100">
                                    <a href="{{ route('notifications.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700 transition-colors">Ver todas →</a>
                                </div>
                            </div>
                        </div>

                        <!-- Menú de usuario -->
                        <div x-data="{ userOpen: false }" class="relative">
                            <button @click="userOpen = !userOpen"
                                    class="flex items-center gap-2 pl-2 pr-3 py-1.5 rounded-lg hover:bg-gray-100 transition-colors focus:outline-none">
                                @php
                                    $navParts = explode(' ', trim(Auth::user()->name));
                                    $navInitials = strtoupper(substr($navParts[0], 0, 1));
                                    if (count($navParts) > 1) { $navInitials .= strtoupper(substr(end($navParts), 0, 1)); }
                                @endphp
                                <div class="h-7 w-7 rounded-full bg-indigo-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                    {{ $navInitials }}
                                </div>
                                <span class="text-sm font-medium text-gray-700">{{ Auth::user()->name }}</span>
                                <svg class="h-4 w-4 text-gray-400 transition-transform duration-200" :class="userOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div x-show="userOpen"
                                 x-cloak
                                 @click.away="userOpen = false"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95"
                                 class="absolute right-0 top-full mt-2 w-56 bg-white rounded-xl shadow-lg border border-gray-200 py-1 z-50 overflow-hidden">
                                <div class="px-4 py-3 border-b border-gray-100">
                                    <p class="text-sm font-semibold text-gray-800 truncate">{{ Auth::user()->name }}</p>
                                    <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</p>
                                </div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                        </svg>
                                        Cerrar sesión
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Page content -->
                <main class="flex-1 relative overflow-y-auto focus:outline-none min-h-screen">
                    <div class="pt-4 pb-8">
                        <div class="w-full px-4 sm:px-6 lg:px-8">
                            @if (session('success'))
                                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                                    <p>{{ session('success') }}</p>
                                </div>
                            @endif

                            @if (session('error'))
                                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                                    <p>{{ session('error') }}</p>
                                </div>
                            @endif

                            @isset($slot)
                                {{ $slot }}
                            @else
                                @yield('content')
                            @endisset
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // Toggle user menu
        document.getElementById('user-menu-button')?.addEventListener('click', function() {
            document.getElementById('user-menu').classList.toggle('hidden');
        });

        // Close user menu when clicking outside
        document.addEventListener('click', function(event) {
            const userMenu = document.getElementById('user-menu');
            const userMenuButton = document.getElementById('user-menu-button');
            if (userMenu && userMenuButton && !userMenuButton.contains(event.target) && !userMenu.contains(event.target)) {
                userMenu.classList.add('hidden');
            }
        });

        // Mobile user menu
        document.getElementById('user-menu-button-mobile')?.addEventListener('click', function() {
            document.getElementById('user-menu-mobile').classList.toggle('hidden');
        });

        // Close mobile user menu when clicking outside
        document.addEventListener('click', function(event) {
            const userMenuMobile = document.getElementById('user-menu-mobile');
            const userMenuButtonMobile = document.getElementById('user-menu-button-mobile');
            if (userMenuMobile && userMenuButtonMobile && !userMenuButtonMobile.contains(event.target) && !userMenuMobile.contains(event.target)) {
                userMenuMobile.classList.add('hidden');
            }
        });

        // Close notification dropdowns when clicking on navigation links
        document.addEventListener('click', function(event) {
            if (event.target.tagName === 'A' && event.target.href) {
                // Trigger Alpine.js to close notification dropdowns
                const notificationComponents = document.querySelectorAll('[x-data*="notifications"]');
                notificationComponents.forEach(component => {
                    if (component._x_dataStack && component._x_dataStack[0].open) {
                        component._x_dataStack[0].open = false;
                    }
                });
            }
        });
        
        // Sonido de notificación (Web Audio API, sin archivos externos)
        function playNotifSound() {
            try {
                const ac = new (window.AudioContext || window.webkitAudioContext)();
                [[880, 0], [1100, 0.16]].forEach(([freq, delay]) => {
                    const osc = ac.createOscillator(), gain = ac.createGain();
                    osc.connect(gain); gain.connect(ac.destination);
                    osc.type = 'sine'; osc.frequency.value = freq;
                    const t = ac.currentTime + delay;
                    gain.gain.setValueAtTime(0, t);
                    gain.gain.linearRampToValueAtTime(0.22, t + 0.01);
                    gain.gain.exponentialRampToValueAtTime(0.001, t + 0.45);
                    osc.start(t); osc.stop(t + 0.45);
                });
            } catch (e) {}
        }

        // Notificación del navegador (OS)
        function showBrowserNotification(title, body, url) {
            if (typeof Notification === 'undefined' || Notification.permission !== 'granted') return;
            try {
                const n = new Notification(title, { body, icon: '/favicon.ico', tag: 'esam-notif' });
                n.onclick = () => { window.focus(); if (url && url !== '#') window.location.href = url; n.close(); };
                setTimeout(() => n.close(), 6000);
            } catch (e) {}
        }

        // Exponer globalmente para el show page
        window.playNotifSound       = playNotifSound;
        window.showBrowserNotification = showBrowserNotification;

        // Función para el dropdown de notificaciones
        function notificationDropdown() {
    return {
        open: false,
        notifications: [],
        unreadCount: 0,
        initialized: false,

        init() {
            if (typeof Notification !== 'undefined' && Notification.permission === 'default') {
                Notification.requestPermission();
            }
            this.fetchNotifications();
            setInterval(() => this.fetchNotifications(), 30000);
        },

        toggle() {
            this.open = !this.open;
            if (this.open) {
                this.fetchNotifications();
            }
        },

        async fetchNotifications() {
            try {
                const previousCount = this.unreadCount;
                const [notificationsResponse, countResponse] = await Promise.all([
                    fetch('/notifications/unread'),
                    fetch('/notifications/count')
                ]);

                if (notificationsResponse.ok && countResponse.ok) {
                    const notificationsData = await notificationsResponse.json();
                    const countData = await countResponse.json();

                    let notifications = notificationsData.notifications || notificationsData;
                    if (!Array.isArray(notifications)) notifications = [];

                    const newCount = countData.unread_count || countData.count || 0;

                    // Sonido + notificación OS cuando llega algo nuevo
                    if (this.initialized && newCount > previousCount) {
                        playNotifSound();
                        const latest = notifications[0];
                        if (latest) showBrowserNotification(latest.title, latest.message, latest.url);
                    }

                    this.notifications = notifications;
                    this.unreadCount   = newCount;
                    this.initialized   = true;
                }
            } catch (error) {
                console.error('Error fetching notifications:', error);
            }
        },
        
        async markAsRead(notificationId) {
            try {
                const response = await fetch(`/notifications/${notificationId}/read`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json'
                    }
                });
                
                if (response.ok) {
                    await this.fetchNotifications();
                }
            } catch (error) {
                console.error('Error marking notification as read:', error);
            }
        },
        
        navigateToNotification(notification) {
            // Manejar diferentes estructuras de notificación
            const url = notification.url || notification.data?.url;
            
            if (url && url !== '#') {
                this.markAsRead(notification.id);
                window.location.href = url;
            } else {
                // Si no hay URL, solo marcar como leído
                this.markAsRead(notification.id);
                this.open = false;
            }
        }
        }
    }
    </script>
    
    @stack('scripts')
</body>
</html>
