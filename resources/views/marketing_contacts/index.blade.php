<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">Contactos de Marketing</h2>
    </x-slot>

    <div class="w-full sm:px-6 lg:px-8">

        @if(session('success'))
            <x-alert type="success" :message="session('success')" class="mb-4"/>
        @endif
        @if(session('error'))
            <x-alert type="error" :message="session('error')" class="mb-4"/>
        @endif

        <!-- Filtros -->
        @if($isTeamLeader && auth()->user()->can('marketing_contact.view_team') && !auth()->user()->can('marketing_contact.view'))
        <div class="mb-3 p-3 bg-blue-50 border border-blue-200 rounded-md text-sm text-blue-800 flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            Eres líder de equipo — estás viendo tus contactos y los de todos tus miembros.
        </div>
        @endif

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-4">
            <div class="p-4">
                <form method="GET" action="{{ route('marketing-contacts.index') }}" class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Nombre o teléfono..."
                        class="border-gray-300 rounded-md text-sm focus:ring-indigo-500 focus:border-indigo-500 col-span-2 md:col-span-1">

                    <select name="referencia" class="border-gray-300 rounded-md text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Todas las referencias</option>
                        @foreach(\App\Models\MarketingContact::REFERENCIAS as $r)
                            <option value="{{ $r }}" @selected(request('referencia') === $r)>{{ $r }}</option>
                        @endforeach
                    </select>

                    <select name="origen" class="border-gray-300 rounded-md text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Todos los orígenes</option>
                        @foreach(\App\Models\MarketingContact::ORIGENES as $o)
                            <option value="{{ $o }}" @selected(request('origen') === $o)>{{ $o }}</option>
                        @endforeach
                    </select>

                    <select name="estado_pago" class="border-gray-300 rounded-md text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Estado de pago</option>
                        @foreach(\App\Models\MarketingContact::ESTADOS_PAGO as $e)
                            <option value="{{ $e }}" @selected(request('estado_pago') === $e)>{{ $e }}</option>
                        @endforeach
                    </select>

                    <select name="program_id" class="border-gray-300 rounded-md text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Todos los programas</option>
                        @foreach($programs as $prog)
                            <option value="{{ $prog->id }}" @selected(request('program_id') == $prog->id)>{{ $prog->name }}</option>
                        @endforeach
                    </select>

                    <select name="conversion" class="border-gray-300 rounded-md text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Conversión</option>
                        <option value="1" @selected(request('conversion') === '1')>Convertidos</option>
                        <option value="0" @selected(request('conversion') === '0')>No convertidos</option>
                    </select>

                    <div class="flex gap-2 col-span-2 md:col-span-2">
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700">Filtrar</button>
                        <a href="{{ route('marketing-contacts.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 text-sm rounded-md hover:bg-gray-300">Limpiar</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-4 flex justify-between items-center border-b">
                <span class="text-sm text-gray-500">{{ $contacts->total() }} contacto(s)</span>
                @can('marketing_contact.create')
                <a href="{{ route('marketing-contacts.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-md hover:bg-indigo-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nuevo Contacto
                </a>
                @endcan
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <x-table-heading>Nombre</x-table-heading>
                            <x-table-heading>Teléfono</x-table-heading>
                            <x-table-heading>Programa</x-table-heading>
                            <x-table-heading>Referencia</x-table-heading>
                            <x-table-heading>Origen</x-table-heading>
                            <x-table-heading>Estado Pago</x-table-heading>
                            <x-table-heading>Conv.</x-table-heading>
                            <x-table-heading>Resp/Llamadas</x-table-heading>
                            <x-table-heading>Fecha</x-table-heading>
                            <x-table-heading>Asesor</x-table-heading>
                            <x-table-heading>Acciones</x-table-heading>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($contacts as $contact)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $contact->nombre }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $contact->telefono }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 max-w-[160px] truncate" title="{{ $contact->program?->name }}">
                                {{ $contact->program?->name ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-sm">
                                @php
                                    $refColors = [
                                        'Interesado' => 'bg-green-100 text-green-800',
                                        'No interesado' => 'bg-red-100 text-red-800',
                                        'Maestro' => 'bg-blue-100 text-blue-800',
                                        'Sin título en provisión nacional' => 'bg-yellow-100 text-yellow-800',
                                        'Sin respuesta' => 'bg-gray-100 text-gray-600',
                                    ];
                                    $refColor = $refColors[$contact->referencia] ?? 'bg-gray-100 text-gray-600';
                                @endphp
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $refColor }}">
                                    {{ $contact->referencia }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $contact->origen }}</td>
                            <td class="px-4 py-3 text-sm">
                                @if($contact->estado_pago)
                                    @php
                                        $pagoColors = [
                                            'Esperando pago' => 'bg-yellow-100 text-yellow-800',
                                            'Adelanto realizado' => 'bg-blue-100 text-blue-800',
                                            'Pago completo' => 'bg-green-100 text-green-800',
                                        ];
                                        $pagoColor = $pagoColors[$contact->estado_pago] ?? 'bg-gray-100 text-gray-600';
                                    @endphp
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $pagoColor }}">
                                        {{ $contact->estado_pago }}
                                    </span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($contact->conversion)
                                    <span class="text-green-600" title="Inscrito convertido">
                                        <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </span>
                                @else
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-center text-gray-500">
                                {{ $contact->responses_count }}/{{ $contact->calls_count }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $contact->fecha->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $contact->creator?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('marketing-contacts.show', $contact) }}" class="text-indigo-600 hover:text-indigo-900" title="Ver">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                    @if(auth()->user() && (auth()->user()->can('marketing_contact.edit') || (auth()->user()->can('marketing_contact.edit_own') && auth()->id() === (int) $contact->created_by)))
                                    <a href="{{ route('marketing-contacts.edit', $contact) }}" class="text-yellow-600 hover:text-yellow-900" title="Editar">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                    @endif
                                    @if(auth()->user() && (auth()->user()->can('marketing_contact.delete') || (auth()->user()->can('marketing_contact.delete_own') && auth()->id() === (int) $contact->created_by)))
                                    <form method="POST" action="{{ route('marketing-contacts.destroy', $contact) }}" class="inline" onsubmit="return confirm('¿Eliminar este contacto?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900" title="Eliminar">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="11" class="px-4 py-8 text-center text-gray-500 text-sm">No hay contactos registrados.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($contacts->hasPages())
            <div class="p-4 border-t">
                {{ $contacts->links() }}
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
