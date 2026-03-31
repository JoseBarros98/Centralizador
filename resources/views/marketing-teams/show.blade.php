<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-white leading-tight">
                {{ $team->name }}
            </h2>
            <div class="flex space-x-2">
                @can('marketing.edit')
                <a href="{{ route('marketing-teams.edit', $team) }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 focus:bg-yellow-700 active:bg-yellow-900 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Editar
                </a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div>
        @php
            $canManageMembers = auth()->check() && (auth()->user()->hasRole('admin') || (int) auth()->id() === (int) $team->leader_id);
        @endphp

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Breadcrumb -->
            <div class="mb-6">
                <nav class="flex" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a href="{{ route('marketing-teams.index') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                                <svg class="w-3 h-3 mr-2.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="m19.707 9.293-2-2-7-7a1 1 0 0 0-1.414 0l-7 7-2 2a1 1 0 0 0 1.414 1.414L2 10.414V18a2 2 0 0 0 2 2h3a1 1 0 0 0 1-1v-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v4a1 1 0 0 0 1 1h3a2 2 0 0 0 2-2v-7.586l.293.293a1 1 0 0 0 1.414-1.414Z"/>
                                </svg>
                                Equipos de Marketing
                            </a>
                        </li>
                        <li aria-current="page">
                            <div class="flex items-center">
                                <svg class="w-3 h-3 text-gray-400 mx-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                                <span class="text-sm font-medium text-gray-500">{{ $team->name }}</span>
                            </div>
                        </li>
                    </ol>
                </nav>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Información del Equipo -->
                <div class="lg:col-span-1">
                    <x-card>
                        <x-slot name="header">
                            <h3 class="text-lg font-medium text-gray-900">Información del Equipo</h3>
                        </x-slot>

                        <div class="space-y-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Nombre</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $team->name }}</dd>
                            </div>

                            @if($team->description)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Descripción</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $team->description }}</dd>
                            </div>
                            @endif

                            <div>
                                <dt class="text-sm font-medium text-gray-500">Líder</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    <div class="flex items-center">
                                        <div class="h-8 w-8 bg-indigo-500 rounded-full flex items-center justify-center text-white text-xs font-medium mr-2">
                                            {{ substr($team->leader->name, 0, 2) }}
                                        </div>
                                        <div>
                                            <div class="font-medium">{{ $team->leader->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $team->leader->email }}</div>
                                        </div>
                                    </div>
                                </dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-gray-500">Estado</dt>
                                <dd class="mt-1">
                                    @if($team->active)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Activo
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            Inactivo
                                        </span>
                                    @endif
                                </dd>
                            </div>

                            <div class="border-t border-gray-200 pt-4 grid grid-cols-2 gap-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Creado</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ $team->created_at->format('d/m/Y H:i') }}
                                    </dd>
                                </div>

                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Última actualización</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ $team->updated_at->format('d/m/Y H:i') }}
                                    </dd>
                                </div>
                            </div>
                        </div>
                    </x-card>
                </div>

                <!-- Miembros del Equipo -->
                <div class="lg:col-span-2">
                    <x-card>
                        <x-slot name="header">
                            <div class="flex justify-between items-center">
                                <h3 class="text-lg font-medium text-gray-900">Miembros del Equipo ({{ $teamMembers->count() }})</h3>
                                @if($canManageMembers)
                                <button type="button" onclick="openAddMemberModal()" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    Agregar Miembro
                                </button>
                                @endif
                            </div>
                        </x-slot>

                        @if($teamMembers->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Miembro</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha de Ingreso</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                            @if($canManageMembers)
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($teamMembers as $member)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="h-8 w-8 bg-indigo-500 rounded-full flex items-center justify-center text-white text-xs font-medium mr-3">
                                                        {{ substr($member->name, 0, 2) }}
                                                    </div>
                                                    <div>
                                                        <div class="text-sm font-medium text-gray-900">{{ $member->name }}</div>
                                                        <div class="text-sm text-gray-500">{{ $member->email }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $member->pivot->joined_at ? \Carbon\Carbon::parse($member->pivot->joined_at)->format('d/m/Y') : 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($member->pivot->active)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        Activo
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                        Inactivo
                                                    </span>
                                                @endif
                                            </td>
                                            @if($canManageMembers)
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                @if($member->pivot->active)
                                                @if($canManageMembers)
                                                    <form method="POST" action="{{ route('marketing-teams.deactivate-member', [$team, $member]) }}" class="inline" onsubmit="return confirm('¿Estás seguro de dar de baja a este miembro del equipo?')">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="inline-flex h-5 w-5 items-center justify-center text-red-600 hover:text-red-900" title="Dar de baja">
                                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 12H8"></path>
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z"></path>
                                                            </svg>
                                                        </button>
                                                    </form>
                                                @endif
                                                @else
                                                    <span class="text-gray-500">Inactivo</span>
                                                @endif
                                            </td>
                                            @endif
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-12">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No hay miembros</h3>
                                <p class="mt-1 text-sm text-gray-500">No hay miembros en este equipo aún.</p>
                                @if($canManageMembers)
                                <div class="mt-6">
                                    <button type="button" onclick="openAddMemberModal()" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                        Agregar primer miembro
                                    </button>
                                </div>
                                @endif
                            </div>
                        @endif
                    </x-card>
                </div>
            </div>
        </div>

        {{-- Tabla de inscripciones de los miembros del equipo --}}
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-1 gap-6">
                <div class="lg:col-span-1">
                    <x-card>
                        <x-slot name="header">
                            <div class="flex flex-col gap-4">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-lg font-medium text-gray-900">Inscripciones de los Miembros</h3>
                                    <span class="text-sm text-gray-500">Total: {{ $teamInscriptions->total() }}</span>
                                </div>

                                <form method="GET" action="{{ route('marketing-teams.show', $team) }}" class="grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
                                    <div>
                                        <label for="year" class="block text-xs font-medium text-gray-500 uppercase tracking-wider">Gestión</label>
                                        <select id="year" name="year" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                            <option value="all" {{ (string) $selectedYear === 'all' ? 'selected' : '' }}>Todas</option>
                                            @foreach($availableYears as $year)
                                                <option value="{{ $year }}" {{ (string) $selectedYear === (string) $year ? 'selected' : '' }}>
                                                    {{ $year }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label for="month" class="block text-xs font-medium text-gray-500 uppercase tracking-wider">Mes</label>
                                        <select id="month" name="month" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                            <option value="all" {{ $selectedMonth === 'all' ? 'selected' : '' }}>Todos</option>
                                            @for ($i = 1; $i <= 12; $i++)
                                                <option value="{{ $i }}" {{ (int) $selectedMonth === $i ? 'selected' : '' }}>
                                                    {{ ucfirst(\Carbon\Carbon::createFromDate(null, $i, 1)->locale('es')->translatedFormat('F')) }}
                                                </option>
                                            @endfor
                                        </select>
                                    </div>

                                    <div>
                                        <label for="status" class="block text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</label>
                                        <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                            <option value="">Todos</option>
                                            <option value="Completo" {{ $selectedStatus === 'Completo' ? 'selected' : '' }}>Completo</option>
                                            <option value="Completando" {{ $selectedStatus === 'Completando' ? 'selected' : '' }}>Completando</option>
                                            <option value="Adelanto" {{ $selectedStatus === 'Adelanto' ? 'selected' : '' }}>Adelanto</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label for="advisor_id" class="block text-xs font-medium text-gray-500 uppercase tracking-wider">Asesor</label>
                                        <select id="advisor_id" name="advisor_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                            <option value="">Todos</option>
                                            @foreach($teamAdvisors as $advisor)
                                                <option value="{{ $advisor->id }}" {{ (string) $selectedAdvisor === (string) $advisor->id ? 'selected' : '' }}>
                                                    {{ $advisor->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="flex gap-2">
                                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                                            Filtrar
                                        </button>
                                        <a href="{{ route('marketing-teams.show', $team) }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400">
                                            Limpiar
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </x-slot>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        {{-- <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th> --}}
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre Completo</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Teléfono</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Asesor</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Programa</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Documentación</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Plan de Pago</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Medio de Pago</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Matricula</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">1ra Cuota</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Pagado</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse ($teamInscriptions as $inscription)
                                        <tr class="hover:bg-gray-50">
                                            {{-- <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <a href="{{ route('inscriptions.show', $inscription) }}" class="inline-flex h-5 w-5 items-center justify-center text-indigo-600 hover:text-indigo-900" title="Ver inscripción">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                    </svg>
                                                </a>
                                            </td> --}}
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @php
                                                    $paymentStatus = $inscription->local_payment_status ?? $inscription->status;
                                                @endphp

                                                @if($paymentStatus == 'Completo')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Completo</span>
                                                @elseif($paymentStatus == 'Completando')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Completando</span>
                                                @elseif($paymentStatus == 'Adelanto')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Adelanto</span>
                                                @else
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Pendiente</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ optional($inscription->inscription_date)->format('d/m/Y') ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <div class="font-medium">{{ $inscription->getFullName() }}</div>
                                                <div class="text-xs text-gray-500">CI: {{ $inscription->ci ?? 'N/A' }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $inscription->phone ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $inscription->getAdvisorName() }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ optional($inscription->program)->name ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                @php
                                                    $requiredDocuments = [
                                                        'CI' => (bool) $inscription->has_identity_card,
                                                        'Titulo Profesional' => (bool) $inscription->has_degree_title,
                                                        'Diploma Academico' => (bool) $inscription->has_academic_diploma,
                                                        'Certificado de Nacimiento' => (bool) $inscription->has_birth_certificate,
                                                    ];
                                                    $missingDocuments = collect($requiredDocuments)
                                                        ->filter(fn ($isPresent) => !$isPresent)
                                                        ->keys()
                                                        ->values();
                                                    $isDocumentationComplete = $missingDocuments->isEmpty();
                                                @endphp

                                                @if($isDocumentationComplete)
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                        Completa
                                                    </span>
                                                @else
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                        Incompleta
                                                    </span>
                                                    <div class="mt-1 text-xs text-yellow-800">
                                                        Falta: {{ $missingDocuments->join(', ') }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $inscription->payment_plan ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $inscription->payment_method ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ number_format((float) ($inscription->enrollment_fee ?? 0), 2) }} Bs
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ number_format((float) ($inscription->first_installment ?? 0), 2) }} Bs
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ number_format((float) ($inscription->total_paid ?? 0), 2) }} Bs
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="13" class="px-6 py-4 text-center text-sm text-gray-500">
                                                No hay inscripciones asociadas a miembros de este equipo.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">
                            {{ $teamInscriptions->links() }}
                        </div>
                        <div class="space-y-4">
                        </div>
                    </x-card>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para agregar miembro -->
    @if($canManageMembers)
    <div id="addMemberModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-10 mx-auto p-0 border max-w-md shadow-lg rounded-lg bg-white">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Agregar Miembro al Equipo</h3>
                    <button type="button" onclick="closeAddMemberModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <form method="POST" action="{{ route('marketing-teams.add-member', $team) }}">
                    @csrf
                    <div class="mb-6">
                        <x-label for="user_id" :value="__('Seleccionar Usuario')" />
                        <select id="user_id" name="user_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                            <option value="">Seleccionar usuario...</option>
                            @foreach($availableUsers as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeAddMemberModal()" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Cancelar
                        </button>
                        <x-primary-button type="submit">
                            Agregar Miembro
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <script>
        function openAddMemberModal() {
            document.getElementById('addMemberModal').classList.remove('hidden');
        }

        function closeAddMemberModal() {
            document.getElementById('addMemberModal').classList.add('hidden');
        }

        // Cerrar modal al hacer clic fuera
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('addMemberModal');
            if (modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) {
                        closeAddMemberModal();
                    }
                });
            }
        });
    </script>
</x-app-layout>
