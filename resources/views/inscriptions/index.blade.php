<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Inscripciones') }}
        </h2>
    </x-slot>

    <div >
        <div class="w-full sm:px-6 lg:px-8">
            @php
                $currentUser = auth()->user();
                $isTeamLeader = $currentUser && $currentUser->leadsActiveMarketingTeam();
            @endphp

            <!-- Filtros -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="GET" action="{{ route('inscriptions.index') }}" class="space-y-4">
                        <!-- Campo de búsqueda y botón Sincronizar -->
                        <div class="flex flex-col md:flex-row md:items-end md:justify-between w-full gap-4">
                            <div class="w-full md:w-1/2">
                                <x-label for="search" :value="__('Buscar')" />
                                <div class="mt-1 flex rounded-md shadow-sm">
                                    <input type="text" name="search" id="search" value="{{ request('search') }}" 
                                        class="flex-1 rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                                        placeholder="Buscar por código, nombre, apellidos o CI">
                                    <button type="submit" class="ml-2 inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                        Buscar
                                    </button>
                                </div>
                            </div>

                            <div class="w-full md:w-auto">
                                <button
                                    type="submit"
                                    form="sync-inscriptions-form"
                                    class="w-full md:w-auto inline-flex items-center justify-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700"
                                    onclick="return confirm('Se sincronizarán las inscripciones y se vincularán asesores automáticamente. ¿Deseas continuar?')"
                                >
                                    Sincronizar Inscripciones
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3">
                            <div>
                                <x-label for="date_from" :value="__('Desde')" />
                                <input type="date" id="date_from" name="date_from" value="{{ request('date_from') }}" 
                                    class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full text-sm">
                            </div>
                            
                            <div>
                                <x-label for="date_to" :value="__('Hasta')" />
                                <input type="date" id="date_to" name="date_to" value="{{ request('date_to') }}" 
                                    class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full text-sm">
                            </div>
                            
                            <div>
                                <x-label for="status" :value="__('Estado')" />
                                <select id="status" name="status" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full text-sm">
                                    <option value="">Todos</option>
                                    <option value="Completo" {{ request('local_payment_status') == 'Completo' ? 'selected' : '' }}>Completo</option>
                                    <option value="Completando" {{ request('local_payment_status') == 'Completando' ? 'selected' : '' }}>Completando</option>
                                    <option value="Adelanto" {{ request('local_payment_status') == 'Adelanto' ? 'selected' : '' }}>Adelanto</option>
                                </select>
                            </div>
                            
                            <div>
                                <x-label for="program_id" :value="__('Programa')" />
                                <select id="program_id" name="program_id" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full text-sm">
                                    <option value="">Todos</option>
                                    @foreach($programs as $id => $name)
                                        <option value="{{ $id }}" {{ request('program_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div>
                                <x-label for="created_by" :value="__('Asesor')" />
                                <select id="created_by" name="created_by" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full text-sm">
                                    <option value="">Todos</option>
                                    @foreach($creators as $id => $name)
                                        <option value="{{ $id }}" {{ request('created_by') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <div class="flex justify-end">
                            <x-button>
                                {{ __('Filtrar') }}
                            </x-button>
                        </div>
                    </form>

                    <form id="sync-inscriptions-form" method="POST" action="{{ route('inscriptions.sync') }}" class="hidden">
                        @csrf
                    </form>
                    
                    <script>
                        // Validar que la fecha inicial sea menor a la final
                        const dateFromInput = document.getElementById('date_from');
                        const dateToInput = document.getElementById('date_to');
                        
                        function validateDates() {
                            if (dateFromInput.value && dateToInput.value) {
                                if (new Date(dateFromInput.value) > new Date(dateToInput.value)) {
                                    dateToInput.value = dateFromInput.value;
                                }
                            }
                        }
                        
                        dateFromInput.addEventListener('change', validateDates);
                        dateToInput.addEventListener('change', () => {
                            if (dateFromInput.value && dateToInput.value) {
                                if (new Date(dateToInput.value) < new Date(dateFromInput.value)) {
                                    dateFromInput.value = dateToInput.value;
                                }
                            }
                        });
                    </script>
                </div>
            </div>
            
            <!-- Estadísticas -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    {{-- <h3 class="text-lg font-medium text-gray-900 mb-4">{{ $statsTitle ?? 'Estadísticas' }}</h3> --}}
                    <div class="gap-2" style="display:flex; flex-wrap:nowrap; gap:0.5rem; align-items:stretch;">
                        <div class="bg-gray-100 p-3 rounded-lg min-w-0" style="flex:1 1 0; min-width:0;">
                            <p class="text-xs md:text-sm text-gray-600 leading-tight">Inscritos Latam</p>
                            <p class="text-xl md:text-2xl font-bold">{{ $stats['inscritos_latam'] }}</p>
                        </div>
                        <div class="bg-orange-100 p-3 rounded-lg min-w-0" style="flex:1 1 0; min-width:0;">
                            <p class="text-xs md:text-sm text-gray-600 leading-tight">Otra sede</p>
                            <p class="text-xl md:text-2xl font-bold">{{ $stats['otra_sede'] }}</p>
                        </div>
                        <div class="bg-green-100 p-3 rounded-lg min-w-0" style="flex:1 1 0; min-width:0;">
                            <p class="text-xs md:text-sm text-gray-600 leading-tight">Completos</p>
                            <p class="text-xl md:text-2xl font-bold">{{ $stats['completo'] }}</p>
                        </div>
                        <div class="bg-yellow-100 p-3 rounded-lg min-w-0" style="flex:1 1 0; min-width:0;">
                            <p class="text-xs md:text-sm text-gray-600 leading-tight">Completando</p>
                            <p class="text-xl md:text-2xl font-bold">{{ $stats['completando'] }}</p>
                        </div>
                        <div class="bg-blue-100 p-3 rounded-lg min-w-0" style="flex:1 1 0; min-width:0;">
                            <p class="text-xs md:text-sm text-gray-600 leading-tight">Adelantos</p>
                            <p class="text-xl md:text-2xl font-bold">{{ $stats['adelanto'] }}</p>
                        </div>
                        <div class="bg-purple-100 p-3 rounded-lg min-w-0" style="flex:1 1 0; min-width:0;">
                            <p class="text-xs md:text-sm text-gray-600 leading-tight">Total Pagado (Bs)</p>
                            <p class="text-xl md:text-2xl font-bold">{{ number_format($stats['total_paid'], 2) }}</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Tabla de inscripciones -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100">
                            <thead>
                                <tr class="bg-gray-800 text-white text-xs">
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Acciones</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Fecha</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Estudiante</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Asesor</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Estado</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Estado Inscripción</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Total Pagado</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">N° Recibo</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Verificado</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($inscriptions as $inscription)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-3">
                                                <a href="{{ route('inscriptions.show', $inscription) }}" 
                                                   class="text-indigo-600 hover:text-indigo-900"
                                                   title="Ver detalles">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                    <span class="sr-only">Ver</span>
                                                </a>
                                                
                                                @php
                                                    $canEditInscription = $currentUser
                                                        && (
                                                            $currentUser->can('inscription.edit')
                                                            || ($currentUser->can('inscription.edit_own') && $currentUser->id === $inscription->created_by)
                                                            || ($isTeamLeader && optional($inscription->creator)->email === 'sistema.externo@centtest.local')
                                                        );
                                                @endphp

                                                @if($canEditInscription)
                                                        <a href="{{ route('inscriptions.edit', $inscription) }}" 
                                                        class="text-yellow-600 hover:text-yellow-900"
                                                        title="Editar inscripción">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                            </svg>
                                                            <span class="sr-only">Editar</span>
                                                        </a>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $inscription->inscription_date->format('d/m/Y') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <div class="font-semibold">{{ $inscription->getFullName() }}</div>
                                            <div class="text-xs text-gray-500">CI: {{ $inscription->ci }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $inscription->getAdvisorName() }}
                                            @if($inscription->external_advisor_name && $inscription->creator->email === 'sistema.externo@centtest.local')
                                                <span class="ml-1 text-xs text-gray-400" title="Asesor externo sin cuenta en el sistema">(Externo)</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @php
                                                $paymentStatus = $inscription->display_payment_status ?? ($inscription->local_payment_status ?? $inscription->status);
                                            @endphp
                                            @if($paymentStatus == 'Completo')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    Completo
                                                </span>
                                            @elseif($paymentStatus == 'Completando')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                    Completando
                                                </span>
                                            @elseif($paymentStatus == 'Adelanto')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                    Adelanto
                                                </span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                    Pendiente
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($inscription->external_inscription_status == 'Inscrito')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    Inscrito
                                                </span>
                                            @elseif($inscription->external_inscription_status == 'Preinscrito')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                    Preinscrito
                                                </span>
                                            @elseif($inscription->external_inscription_status == 'Retirado')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                    Retirado
                                                </span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                    Pendiente
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($inscription->display_total_paid, 2) }} Bs</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $inscription->receipt_number ?? '—' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm {{ $currentUser->hasPermissionTo('inscription.verify') ? 'verified-cell cursor-pointer' : '' }}"
                                            data-inscription="{{ $inscription->id }}"
                                            data-original="{{ $inscription->is_verified ?? '' }}">
                                            @if($currentUser->hasPermissionTo('inscription.verify'))
                                                <span class="verified-display">
                                                    @if($inscription->is_verified)
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">{{ $inscription->is_verified }}</span>
                                                    @else
                                                        <span class="text-gray-400 text-xs">—</span>
                                                    @endif
                                                </span>
                                                <input
                                                    type="text"
                                                    class="verified-inline-input hidden rounded border-gray-300 text-xs focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 w-32"
                                                    value="{{ $inscription->is_verified ?? '' }}"
                                                    placeholder="Ingrese valor..."
                                                >
                                            @elseif($inscription->is_verified)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">{{ $inscription->is_verified }}</span>
                                            @else
                                                <span class="text-gray-400 text-xs">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No hay inscripciones para mostrar</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        {{ $inscriptions->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        function showNotification(type, message) {
            const el = document.createElement('div');
            el.className = `fixed top-4 right-4 z-50 p-4 rounded-md shadow-lg text-white ${type === 'success' ? 'bg-green-500' : 'bg-red-500'}`;
            el.textContent = message;
            document.body.appendChild(el);
            setTimeout(() => el.remove(), 3000);
        }

        function renderBadge(display, value) {
            if (value) {
                display.innerHTML = `<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">${value}</span>`;
            } else {
                display.innerHTML = `<span class="text-gray-400 text-xs">—</span>`;
            }
        }

        document.querySelectorAll('.verified-cell').forEach(cell => {
            const display = cell.querySelector('.verified-display');
            const input = cell.querySelector('.verified-inline-input');

            cell.addEventListener('click', function(e) {
                if (!input.classList.contains('hidden')) return;
                display.classList.add('hidden');
                input.classList.remove('hidden');
                input.focus();
                input.select();
            });

            input.addEventListener('blur', function() {
                const inscriptionId = cell.dataset.inscription;
                const value = this.value.trim();
                const original = cell.dataset.original;

                if (value === original) {
                    input.classList.add('hidden');
                    display.classList.remove('hidden');
                    return;
                }

                const formData = new FormData();
                formData.append('is_verified', value);
                formData.append('_method', 'PATCH');

                fetch(`/inscriptions/${inscriptionId}/verify`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    body: formData
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        cell.dataset.original = value;
                        renderBadge(display, value);
                        showNotification('success', 'Verificado actualizado');
                    } else {
                        this.value = original;
                        showNotification('error', data.message || 'Error al actualizar');
                    }
                    input.classList.add('hidden');
                    display.classList.remove('hidden');
                })
                .catch(() => {
                    this.value = original;
                    input.classList.add('hidden');
                    display.classList.remove('hidden');
                    showNotification('error', 'Error de conexión');
                });
            });

            input.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') { e.preventDefault(); this.blur(); }
                if (e.key === 'Escape') {
                    this.value = cell.dataset.original;
                    this.blur();
                }
            });

            input.addEventListener('click', e => e.stopPropagation());
        });
    </script>
</x-app-layout>
