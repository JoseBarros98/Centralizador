<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-white leading-tight">
                {{ __('Solicitudes de Pago a Docentes') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <!-- Formulario de búsqueda avanzada -->
                    <div class="mb-6 bg-gray-50 p-4 rounded-lg">
                        <form method="GET" action="{{ route('payment_requests.index') }}">
                            <div class="flex flex-wrap gap-3 items-end">
                                <!-- Búsqueda por N° Planilla -->
                                <div class="flex-1 min-w-fit">
                                    <label for="payroll_number" class="block text-sm font-medium text-gray-700 mb-1">N° Planilla</label>
                                    <input type="text" name="payroll_number" id="payroll_number" value="{{ request('payroll_number') }}" 
                                           placeholder="Ej: PL-001" 
                                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                </div>

                                <!-- Búsqueda por Docente -->
                                <div class="flex-1 min-w-fit">
                                    <label for="teacher" class="block text-sm font-medium text-gray-700 mb-1">Docente</label>
                                    <input type="text" name="teacher" id="teacher" value="{{ request('teacher') }}" 
                                           placeholder="Nombre del docente" 
                                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                </div>

                                <!-- Búsqueda por Programa -->
                                <div class="flex-1 min-w-fit">
                                    <label for="program" class="block text-sm font-medium text-gray-700 mb-1">Programa</label>
                                    <input type="text" name="program" id="program" value="{{ request('program') }}" 
                                           placeholder="Nombre del programa" 
                                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                </div>

                                <!-- Búsqueda por Módulo -->
                                <div class="flex-1 min-w-fit">
                                    <label for="module" class="block text-sm font-medium text-gray-700 mb-1">Módulo</label>
                                    <input type="text" name="module" id="module" value="{{ request('module') }}" 
                                           placeholder="Nombre del módulo" 
                                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                </div>

                                <!-- Filtro por Estado -->
                                <div class="flex-1 min-w-fit">
                                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                                    <select name="status" id="status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                        <option value="">Todos</option>
                                        <option value="Pendiente" {{ request('status') == 'Pendiente' ? 'selected' : '' }}>Pendiente</option>
                                        <option value="Aprobado" {{ request('status') == 'Aprobado' ? 'selected' : '' }}>Aprobado</option>
                                        <option value="Rechazado" {{ request('status') == 'Rechazado' ? 'selected' : '' }}>Rechazado</option>
                                        <option value="Realizado" {{ request('status') == 'Realizado' ? 'selected' : '' }}>Realizado</option>
                                    </select>
                                </div>

                                <!-- Filtro por Año -->
                                <div class="flex-1 min-w-fit">
                                    <label for="year" class="block text-sm font-medium text-gray-700 mb-1">Año</label>
                                    @php
                                        $currentYear = now()->format('Y');
                                        $selectedYear = request('year', $currentYear);
                                    @endphp
                                    <select name="year" id="year" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                        @foreach($availableYears as $y)
                                            <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Filtro por Mes -->
                                <div class="flex-1 min-w-fit">
                                    <label for="month" class="block text-sm font-medium text-gray-700 mb-1">Mes</label>
                                    @php
                                        $currentMonth = now()->format('m');
                                        $selectedMonth = request('month', $currentMonth);
                                    @endphp
                                    <select name="month" id="month" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                        <option value="">Todos</option>
                                        @foreach([
                                            '01' => 'Enero',
                                            '02' => 'Febrero',
                                            '03' => 'Marzo',
                                            '04' => 'Abril',
                                            '05' => 'Mayo',
                                            '06' => 'Junio',
                                            '07' => 'Julio',
                                            '08' => 'Agosto',
                                            '09' => 'Septiembre',
                                            '10' => 'Octubre',
                                            '11' => 'Noviembre',
                                            '12' => 'Diciembre'
                                        ] as $num => $name)
                                            <option value="{{ $num }}" {{ $selectedMonth == $num ? 'selected' : '' }}>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Filtro por Tipo -->
                                <div class="flex-1 min-w-fit">
                                    <label for="request_type" class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                                    <select name="request_type" id="request_type" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                        <option value="">Todos</option>
                                        <option value="Modulo" {{ request('request_type') == 'Modulo' ? 'selected' : '' }}>Módulo</option>
                                        <option value="Tutoria" {{ request('request_type') == 'Tutoria' ? 'selected' : '' }}>Tutoría</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Botones de acción en una fila separada -->
                            <div class="flex gap-2 flex-wrap mt-4">
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150 whitespace-nowrap">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                    Buscar
                                </button>
                                <a href="{{ route('payment_requests.export', array_filter(request()->query())) }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150 whitespace-nowrap">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 16v-4m0 0V8m0 4h4m-4 0H8M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    Exportar Excel
                                </a>
                                @if(request()->hasAny(['payroll_number', 'teacher', 'program', 'module', 'status', 'request_type']))
                                    <a href="{{ route('payment_requests.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150 whitespace-nowrap">
                                        Limpiar Filtros
                                    </a>
                                @endif
                            </div>
                        </form>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-xs">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-3 py-2 text-center font-medium text-gray-500 uppercase tracking-wider">Opciones</th>
                                    <th scope="col" class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                    <th scope="col" class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">N°</th>
                                    <th scope="col" class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Sede</th>
                                    <th scope="col" class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Mes</th>
                                    <th scope="col" class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">N° Planilla</th>
                                    <th scope="col" class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Fecha Solicitud</th>
                                    <th scope="col" class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Cod. Contable</th>
                                    <th scope="col" class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Programa</th>
                                    <th scope="col" class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Módulo</th>
                                    <th scope="col" class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Fecha Inicio</th>
                                    <th scope="col" class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Fecha Fin</th>
                                    <th scope="col" class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Área</th>
                                    <th scope="col" class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">N° de Estudiantes</th>
                                    <th scope="col" class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Docente</th>
                                    <th scope="col" class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Carnet</th>
                                    <th scope="col" class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Factura</th>
                                    <th scope="col" class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">N° Factura</th>
                                    <th scope="col" class="px-3 py-2 text-right font-medium text-gray-500 uppercase tracking-wider">Importe Total</th>
                                    <th scope="col" class="px-3 py-2 text-right font-medium text-gray-500 uppercase tracking-wider">Base 70% (ESAM)</th>
                                    <th scope="col" class="px-3 py-2 text-right font-medium text-gray-500 uppercase tracking-wider">RET ESAM</th>
                                    <th scope="col" class="px-3 py-2 text-right font-medium text-gray-500 uppercase tracking-wider">Total Retención</th>
                                    <th scope="col" class="px-3 py-2 text-right font-medium text-gray-500 uppercase tracking-wider">Líquido Pagable</th>
                                    <th scope="col" class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Banco</th>
                                    <th scope="col" class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">N° Cuenta</th>
                                    <th scope="col" class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Observaciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($paymentRequests as $request)
                                    <tr>
                                        <td class="px-3 py-2 whitespace-nowrap text-center">
                                            <div class="flex justify-center gap-2">
                                                <a href="{{ route('payment_requests.show', $request->id) }}" class="inline-flex items-center justify-center text-blue-600 hover:text-blue-900" title="Ver">
                                                    <x-action-icons action="view" />
                                                </a>
                                                <a href="{{ route('payment_requests.edit', $request->id) }}" class="inline-flex items-center justify-center text-green-600 hover:text-green-900" title="Editar">
                                                    <x-action-icons action="edit" />
                                                </a>
                                                <form method="POST" action="{{ route('payment_requests.destroy', $request->id) }}" class="inline" onsubmit="return confirm('¿Está seguro?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-flex items-center justify-center text-red-600 hover:text-red-900" title="Eliminar">
                                                        <x-action-icons action="delete" />
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                        <td class="px-3 py-2 whitespace-nowrap">
                                            <span class="px-2 py-1 rounded-full text-xs font-semibold
                                                @if($request->status === 'Pendiente')
                                                    bg-yellow-100 text-yellow-800
                                                @elseif($request->status === 'Aprobado')
                                                    bg-green-100 text-green-800
                                                @elseif($request->status === 'Rechazado')
                                                    bg-red-100 text-red-800
                                                @elseif($request->status === 'Realizado')
                                                    bg-blue-100 text-blue-800
                                                @else
                                                    bg-gray-100 text-gray-800
                                                @endif
                                            ">
                                                {{ $request->status ?? '-' }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-2 whitespace-nowrap">{{ $loop->iteration }}</td>
                                        <td class="px-3 py-2 whitespace-nowrap">ESAM LATAM ALAS</td>
                                        <td class="px-3 py-2 whitespace-nowrap">
                                            @if($request->request_date)
                                                {{ $request->request_date->locale('es')->getTranslatedMonthName('long') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="px-3 py-2 whitespace-nowrap font-medium text-gray-900">{{ $request->payroll_number ?? '-' }}</td>
                                        <td class="px-3 py-2 whitespace-nowrap">{{ $request->request_date ? $request->request_date->format('d/m/Y') : '-' }}</td>
                                        <td class="px-3 py-2 whitespace-nowrap">{{ $request->module->program->accounting_code ?? '-' }}</td>
                                        <td class="px-3 py-2 whitespace-nowrap">{{ $request->module->program->name ?? '-' }}</td>
                                        <td class="px-3 py-2 whitespace-nowrap">{{ $request->module->name ?? '-' }}</td>
                                        <td class="px-3 py-2 whitespace-nowrap">{{ $request->module->start_date ? $request->module->start_date->format('d/m/Y') : '-' }}</td>
                                        <td class="px-3 py-2 whitespace-nowrap">{{ $request->module->finalization_date ? $request->module->finalization_date->format('d/m/Y') : '-' }}</td>
                                        <td class="px-3 py-2 whitespace-nowrap">
                                            @php
                                                $area = 'No disponible';
                                                try {
                                                    if ($request->module->program->postgraduate_id) {
                                                        $postgrad = \App\Models\External\ExternalPostgraduate::where('id_posgrado', $request->module->program->postgraduate_id)->first();
                                                        if ($postgrad && $postgrad->area_posgrado) {
                                                            $area = $postgrad->area_posgrado;
                                                        }
                                                    }
                                                } catch (\Exception $e) {
                                                    \Log::error("Error obteniendo área para programa {$request->module->program->code}: " . $e->getMessage());
                                                }
                                            @endphp
                                            {{ $area }}
                                        </td>
                                        <td class="px-3 py-2 whitespace-nowrap text-center">
                                            <input type="number" 
                                                   class="w-16 px-2 py-1 border rounded students-input" 
                                                   value="{{ $request->total_active_students ?? 0 }}"
                                                   data-payment-id="{{ $request->id }}"
                                                   min="0">
                                        </td>
                                        <td class="px-3 py-2 whitespace-nowrap">
                                            @if($request->request_type === 'Tutoria' && $request->tutoringTeacher)
                                                {{ $request->tutoringTeacher->full_name }}
                                            @else
                                                {{ $request->module->teacher->full_name ?? '-' }}
                                            @endif
                                        </td>
                                        <td class="px-3 py-2 whitespace-nowrap">
                                            @if($request->request_type === 'Tutoria' && $request->tutoringTeacher)
                                                {{ $request->tutoringTeacher->ci ?? '-' }}
                                            @else
                                                {{ $request->module->teacher->ci ?? '-' }}
                                            @endif
                                        </td>
                                        <td class="px-3 py-2 whitespace-nowrap">{{ $request->net_amount * 0.845 > 0 ? 'Sí' : '-' }}</td>
                                        <td class="px-3 py-2 whitespace-nowrap">{{ $request->invoice_number ?? '-' }}</td>
                                        <td class="px-3 py-2 whitespace-nowrap text-right font-medium">Bs. {{ number_format($request->total_amount, 2) }}</td>
                                        <td class="px-3 py-2 whitespace-nowrap text-right">
                                            @php
                                                $breakdown = $request->retention_breakdown;
                                                $esam70 = $request->total_amount * 0.70;
                                            @endphp
                                            Bs. {{ number_format($esam70, 2) }}
                                        </td>
                                        <td class="px-3 py-2 whitespace-nowrap text-right">
                                            Bs. {{ number_format($breakdown['esam'] ?? 0, 2) }}
                                        </td>
                                        <td class="px-3 py-2 whitespace-nowrap text-right">
                                            Bs. {{ number_format($request->retention_amount, 2) }}
                                        </td>
                                        <td class="px-3 py-2 whitespace-nowrap text-right font-bold text-green-600">Bs. {{ number_format($request->net_amount, 2) }}</td>
                                        <td class="px-3 py-2 whitespace-nowrap">
                                            @if($request->request_type === 'Tutoria' && $request->tutoringTeacher)
                                                {{ $request->tutoringTeacher->bank ?? '-' }}
                                            @else
                                                {{ $request->module->teacher->bank ?? '-' }}
                                            @endif
                                        </td>
                                        <td class="px-3 py-2 whitespace-nowrap">
                                            @if($request->request_type === 'Tutoria' && $request->tutoringTeacher)
                                                {{ $request->tutoringTeacher->account_number ?? '-' }}
                                            @else
                                                {{ $request->module->teacher->account_number ?? '-' }}
                                            @endif
                                        </td>
                                        <td class="px-3 py-2">{{ $request->observations ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="26" class="px-3 py-4 text-center text-gray-500">
                                            No hay solicitudes de pago registradas.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $paymentRequests->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.students-input').forEach(input => {
            let originalValue = input.value;

            input.addEventListener('blur', async function() {
                const paymentId = this.dataset.paymentId;
                const newValue = this.value;

                // Si el valor no cambió, no hacer nada
                if (newValue === originalValue) {
                    return;
                }

                try {
                    const response = await fetch(`/payment_requests/${paymentId}/update-students`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            students_count: newValue
                        })
                    });

                    if (response.ok) {
                        originalValue = newValue;
                        this.classList.remove('border-red-500', 'bg-red-100');
                        this.classList.add('border-green-500', 'bg-green-100');
                        setTimeout(() => {
                            this.classList.remove('border-green-500', 'bg-green-100');
                            this.classList.add('border-gray-300');
                        }, 1500);
                    } else {
                        this.value = originalValue;
                        this.classList.add('border-red-500', 'bg-red-100');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    this.value = originalValue;
                    this.classList.add('border-red-500', 'bg-red-100');
                }
            });
        });
    </script>
</x-app-layout>
