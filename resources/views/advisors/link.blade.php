<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Vinculación de Asesores') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Estadísticas -->
            <div class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Total Inscripciones</p>
                                <p class="text-2xl font-semibold text-gray-900" id="stat-total">{{ $stats['total'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Vinculadas</p>
                                <p class="text-2xl font-semibold text-green-600" id="stat-linked">{{ $stats['linked'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Sin Vincular</p>
                                <p class="text-2xl font-semibold text-yellow-600" id="stat-unlinked">{{ $stats['unlinked'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Porcentaje</p>
                                <p class="text-2xl font-semibold text-indigo-600" id="stat-percentage">{{ $stats['percentage_linked'] }}%</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botón de Auto-vinculación -->
            <div class="mb-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Vinculación Automática</h3>
                            <p class="mt-1 text-sm text-gray-600">Vincula automáticamente asesores externos con usuarios del sistema basándose en similitud de nombres (>80%)</p>
                        </div>
                        <button onclick="autoLinkAdvisors()" 
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            Vincular Automáticamente
                        </button>
                    </div>
                </div>
            </div>

            <!-- Lista de Asesores Sin Vincular -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="mb-4 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                        <h3 class="text-lg font-medium text-gray-900">Asesores Externos Sin Vincular</h3>

                        <form method="GET" action="{{ route('advisors.link.index') }}" class="flex w-full flex-col gap-2 md:w-auto md:flex-row md:items-end">
                            <div>
                                <label for="search" class="block text-xs font-medium uppercase tracking-wider text-gray-500">Buscar</label>
                                <input
                                    id="search"
                                    name="search"
                                    type="text"
                                    value="{{ $search ?? '' }}"
                                    placeholder="Nombre o ID externo"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 md:w-64"
                                >
                            </div>
                            <div class="flex gap-2">
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring ring-indigo-300 transition ease-in-out duration-150">
                                    Buscar
                                </button>
                                <a href="{{ route('advisors.link.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-300 focus:outline-none focus:ring ring-gray-300 transition ease-in-out duration-150">
                                    Limpiar
                                </a>
                            </div>
                        </form>
                    </div>
                    
                    @if($unlinkedAdvisors->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            ID Externo
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Nombre Asesor
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Inscripciones
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Usuario Sugerido
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Vincular con
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Acción
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200" id="advisors-table">
                                    @foreach($unlinkedAdvisors as $advisor)
                                        <tr id="advisor-row-{{ $advisor['external_id'] }}">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $advisor['external_id'] }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $advisor['name'] }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                    {{ $advisor['inscriptions_count'] }} inscripciones
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                @if($advisor['suggested_user'])
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                        <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                        {{ $advisor['suggested_user']->name }}
                                                    </span>
                                                @else
                                                    <span class="text-gray-400">Sin sugerencia</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <select id="unlinked-user-select-{{ $advisor['external_id'] }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                                    <option value="">Seleccionar usuario...</option>
                                                    @foreach($users as $user)
                                                        <option value="{{ $user->id }}" {{ ($advisor['suggested_user'] && $advisor['suggested_user']->id == $user->id) ? 'selected' : '' }}>
                                                            {{ $user->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <button onclick="linkAdvisor('{{ $advisor['external_id'] }}', 'unlinked')" 
                                                        class="inline-flex items-center px-3 py-1 border border-transparent text-xs leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:border-indigo-700 focus:ring ring-indigo-300 transition ease-in-out duration-150">
                                                    <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                                    </svg>
                                                    Vincular
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            {{ $unlinkedAdvisors->onEachSide(1)->links() }}
                        </div>
                    @else
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Todos los asesores están vinculados</h3>
                            <p class="mt-1 text-sm text-gray-500">No hay asesores externos pendientes de vinculación.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Lista de Asesores Vinculados (cambio de usuario) -->
            <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Asesores Externos Vinculados</h3>

                    @if($linkedAdvisors->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID Externo</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre Asesor</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Inscripciones</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario Actual</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cambiar a</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acción</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200" id="linked-advisors-table">
                                    @foreach($linkedAdvisors as $advisor)
                                        <tr id="linked-advisor-row-{{ $advisor['external_id'] }}">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $advisor['external_id'] }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $advisor['name'] }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-indigo-100 text-indigo-800">
                                                    {{ $advisor['inscriptions_count'] }} inscripciones
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $advisor['current_user']->name ?? 'Sin usuario' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <select id="linked-user-select-{{ $advisor['external_id'] }}" class="block w-64 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" style="min-width: 16rem; height: 42px;">
                                                    <option value="">Seleccionar usuario...</option>
                                                    @foreach($users as $user)
                                                        <option value="{{ $user->id }}" {{ ((int) $advisor['user_id'] === (int) $user->id) ? 'selected' : '' }}>
                                                            {{ $user->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <button onclick="linkAdvisor('{{ $advisor['external_id'] }}', 'linked')"
                                                        class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm leading-5 font-semibold rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:border-indigo-700 focus:ring ring-indigo-300 transition ease-in-out duration-150" style="min-width: 170px; height: 42px;">
                                                    <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                                    </svg>
                                                    Cambiar usuario
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            {{ $linkedAdvisors->onEachSide(1)->links() }}
                        </div>
                    @else
                        <div class="text-center py-8">
                            <p class="text-sm text-gray-500">No hay asesores vinculados para mostrar.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Vincular asesor individual o cambiar usuario vinculado
        function linkAdvisor(externalAdvisorId, mode = 'unlinked') {
            const selectPrefix = mode === 'linked' ? 'linked-user-select' : 'unlinked-user-select';
            const userId = document.getElementById(`${selectPrefix}-${externalAdvisorId}`).value;
            
            if (!userId) {
                alert('Por favor seleccione un usuario');
                return;
            }

            const confirmMessage = mode === 'linked'
                ? '¿Está seguro de cambiar el usuario vinculado para este asesor?'
                : '¿Está seguro de vincular este asesor?';

            if (!confirm(confirmMessage)) {
                return;
            }

            // Mostrar loading
            const button = event.target.closest('button');
            const originalHtml = button.innerHTML;
            button.disabled = true;
            button.innerHTML = mode === 'linked'
                ? '<svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Cambiando...'
                : '<svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Vinculando...';

            fetch('{{ route("advisors.link.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    external_advisor_id: externalAdvisorId,
                    user_id: userId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (mode === 'unlinked') {
                        // Remover la fila de la tabla de no vinculados
                        document.getElementById(`advisor-row-${externalAdvisorId}`).remove();
                    }
                    
                    // Actualizar estadísticas
                    updateStats();
                    
                    // Mostrar mensaje de éxito
                    showNotification('success', data.message);
                    
                    // Recargar para refrescar ambas tablas y asignaciones actuales
                    const tableBody = document.getElementById('advisors-table');
                    if (mode === 'unlinked' && tableBody.children.length === 0) {
                        location.reload();
                    } else if (mode === 'linked') {
                        setTimeout(() => location.reload(), 500);
                    }
                } else {
                    showNotification('error', data.message);
                    button.disabled = false;
                    button.innerHTML = originalHtml;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('error', 'Error al vincular asesor');
                button.disabled = false;
                button.innerHTML = originalHtml;
            });
        }

        // Auto-vincular todos los asesores
        function autoLinkAdvisors() {
            if (!confirm('¿Desea vincular automáticamente todos los asesores basándose en similitud de nombres?\n\nEsto vinculará asesores con coincidencia >80%.')) {
                return;
            }

            // Mostrar loading
            const button = event.target;
            const originalHtml = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<svg class="animate-spin h-5 w-5 mr-2 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Procesando...';

            fetch('{{ route("advisors.link.auto") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('success', `${data.message}\nVinculadas: ${data.linked} | Errores: ${data.errors}`);
                    
                    // Recargar la página para mostrar resultados
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    showNotification('error', data.message);
                    button.disabled = false;
                    button.innerHTML = originalHtml;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('error', 'Error en auto-vinculación');
                button.disabled = false;
                button.innerHTML = originalHtml;
            });
        }

        // Actualizar estadísticas
        function updateStats() {
            fetch('{{ route("advisors.link.stats") }}')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('stat-total').textContent = data.total;
                    document.getElementById('stat-linked').textContent = data.linked;
                    document.getElementById('stat-unlinked').textContent = data.unlinked;
                    document.getElementById('stat-percentage').textContent = data.percentage_linked + '%';
                })
                .catch(error => console.error('Error:', error));
        }

        // Mostrar notificación
        function showNotification(type, message) {
            const bgColor = type === 'success' ? 'bg-green-500' : 'bg-red-500';
            
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 ${bgColor} text-white px-6 py-3 rounded-lg shadow-lg z-50 transition-opacity duration-300`;
            notification.textContent = message;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.opacity = '0';
                setTimeout(() => {
                    notification.remove();
                }, 300);
            }, 3000);
        }
    </script>
    @endpush
</x-app-layout>
