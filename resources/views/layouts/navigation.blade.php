<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                    
                    <x-nav-link :href="route('inscriptions.index')" :active="request()->routeIs('inscriptions.*')">
                        {{ __('Inscripciones') }}
                    </x-nav-link>
                    
                    @can('program.view')
                    <x-nav-link :href="route('programs.index')" :active="request()->routeIs('programs.*')">
                        {{ __('Programas') }}
                    </x-nav-link>
                    @endcan
                    
                    @can('class.view')
                    <x-nav-link :href="route('calendar.index')" :active="request()->routeIs('calendar.*')">
                        {{ __('Calendario') }}
                    </x-nav-link>
                    @endcan
                    
                    @can('content.view')
                    <x-nav-link :href="route('content-pillars.index')" :active="request()->routeIs('content-pillars.*')">
                        {{ __('Pilares de Contenido') }}
                    </x-nav-link>
                    @endcan
                    
                    @can('payment_request.view')
                    <x-nav-link :href="route('payment_requests.index')" :active="request()->routeIs('payment_requests.*')">
                        {{ __('Solicitudes de Pago') }}
                    </x-nav-link>
                    @endcan
                    
                    @can('program_allocation.view')
                    <x-nav-link :href="route('program-allocation.index')" :active="request()->routeIs('program-allocation.*')">
                        {{ __('Asignación por Programa') }}
                    </x-nav-link>
                    @endcan
                    
                    @role('admin')
                    <div class="hidden sm:flex sm:items-center sm:ml-8">
                        <x-dropdown align="top" width="48">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                    <div>{{ __('Administración') }}</div>
                                    <div class="ml-1">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <x-dropdown-link :href="route('users.index')">
                                    <i class="fas fa-users mr-2"></i>{{ __('Usuarios') }}
                                </x-dropdown-link>
                                
                                @can('inscriptions.sync')
                                <x-dropdown-link :href="route('advisors.link.index')">
                                    <i class="fas fa-link mr-2"></i>{{ __('Vincular Asesores') }}
                                </x-dropdown-link>
                                @endcan
                                
                                <x-dropdown-link :href="route('admin.google-drive.setup')">
                                    <i class="fab fa-google-drive mr-2"></i>{{ __('Google Drive') }}
                                </x-dropdown-link>
                                
                                <x-dropdown-link :href="route('admin.sync.index')">
                                    <i class="fas fa-sync mr-2"></i>{{ __('Sincronización') }}
                                </x-dropdown-link>
                                
                                @can('system.view_logs')
                                <x-dropdown-link :href="route('logs.index')">
                                    <i class="fas fa-file-alt mr-2"></i>{{ __('Ver Logs') }}
                                </x-dropdown-link>
                                @endcan
                            </x-slot>
                        </x-dropdown>
                    </div>
                    @endrole
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ml-6">
                <!-- Notifications Dropdown -->
                <div class="relative mr-4" x-data="notifications()" x-init="fetchUnreadCount()")
                    <button @click="open = !open; if(open) fetchNotifications()" 
                            class="relative p-2 text-gray-500 hover:text-gray-700 focus:outline-none focus:text-gray-700 transition ease-in-out duration-150">
                        <i class="fas fa-bell text-lg"></i>
                        <span x-show="unreadCount > 0" 
                              x-text="unreadCount" 
                              class="notification-badge absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center"></span>
                    </button>

                    <div x-show="open" 
                         @click.away="open = false"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-80 bg-white rounded-md shadow-lg py-1 z-50 border border-gray-200">
                        
                        <div class="px-4 py-2 border-b border-gray-200 bg-gray-50">
                            <div class="flex justify-between items-center">
                                <h3 class="text-sm font-semibold text-gray-700">Notificaciones</h3>
                                <div class="flex space-x-2">
                                    <button @click="markAllAsRead()" class="text-xs text-blue-600 hover:text-blue-800">
                                        Marcar todas como leídas
                                    </button>
                                    <a href="{{ route('notifications.index') }}" class="text-xs text-gray-600 hover:text-gray-800">
                                        Ver todas
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="max-h-96 overflow-y-auto">
                            <div x-show="notifications.length === 0" class="px-4 py-6 text-center text-gray-500 text-sm">
                                No tienes notificaciones
                            </div>
                            
                            <template x-for="notification in notifications" :key="notification.id">
                                <div class="px-4 py-3 hover:bg-gray-50 border-b border-gray-100 cursor-pointer"
                                     @click="markAsReadAndRedirect(notification)">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0">
                                            <i :class="getNotificationIcon(notification.type)" class="text-sm"></i>
                                        </div>
                                        <div class="ml-3 flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 truncate" x-text="notification.title"></p>
                                            <p class="text-xs text-gray-600 mt-1 line-clamp-2" x-text="notification.message"></p>
                                            <p class="text-xs text-gray-400 mt-1" x-text="notification.created_at"></p>
                                        </div>
                                        <div class="flex-shrink-0 ml-2">
                                            <span x-show="notification.priority" 
                                                  :class="getPriorityClass(notification.priority)"
                                                  class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                                  x-text="notification.priority"></span>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ml-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Perfil') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Cerrar Sesión') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-mr-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            
            <x-responsive-nav-link :href="route('inscriptions.index')" :active="request()->routeIs('inscriptions.*')">
                {{ __('Inscripciones') }}
            </x-responsive-nav-link>
            
            @can('program.view')
            <x-responsive-nav-link :href="route('programs.index')" :active="request()->routeIs('programs.*')">
                {{ __('Programas') }}
            </x-responsive-nav-link>
            @endcan
            
            @can('class.view')
            <x-responsive-nav-link :href="route('calendar.index')" :active="request()->routeIs('calendar.*')">
                {{ __('Calendario') }}
            </x-responsive-nav-link>
            @endcan
            
            @can('content.view')
            <x-responsive-nav-link :href="route('content-pillars.index')" :active="request()->routeIs('content-pillars.*')">
                {{ __('Pilares de Contenido') }}
            </x-responsive-nav-link>
            @endcan
            
            @can('payment_request.view')
            <x-responsive-nav-link :href="route('payment_requests.index')" :active="request()->routeIs('payment_requests.*')">
                {{ __('Solicitudes de Pago') }}
            </x-responsive-nav-link>
            @endcan
            
            @can('program_allocation.view')
            <x-responsive-nav-link :href="route('program-allocation.index')" :active="request()->routeIs('program-allocation.*')">
                {{ __('Asignación por Programa') }}
            </x-responsive-nav-link>
            @endcan
            
            @role('admin')
            <!-- Sección de Administración -->
            <div class="pt-2 pb-2 border-t border-gray-200">
                <div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    {{ __('Administración') }}
                </div>
                
                <x-responsive-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')">
                    <i class="fas fa-users mr-2"></i>{{ __('Usuarios') }}
                </x-responsive-nav-link>
                
                @can('inscriptions.sync')
                <x-responsive-nav-link :href="route('advisors.link.index')" :active="request()->routeIs('advisors.*')">
                    <i class="fas fa-link mr-2"></i>{{ __('Vincular Asesores') }}
                </x-responsive-nav-link>
                @endcan
                
                <x-responsive-nav-link :href="route('admin.google-drive.setup')" :active="request()->routeIs('admin.google-drive.*')">
                    <i class="fab fa-google-drive mr-2"></i>{{ __('Google Drive') }}
                </x-responsive-nav-link>
                
                <x-responsive-nav-link :href="route('admin.sync.index')" :active="request()->routeIs('admin.sync.*')">
                    <i class="fas fa-sync mr-2"></i>{{ __('Sincronización') }}
                </x-responsive-nav-link>
                
                @can('system.view_logs')
                <x-responsive-nav-link :href="route('logs.index')" :active="request()->routeIs('logs.*')">
                    <i class="fas fa-file-alt mr-2"></i>{{ __('Ver Logs') }}
                </x-responsive-nav-link>
                @endcan
            </div>
            @endrole
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Perfil') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Cerrar Sesión') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
