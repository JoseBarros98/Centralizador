<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            Detalle del CITE de Titulación
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="w-full sm:px-6 lg:px-8 space-y-6">
            <div class="flex items-center justify-end gap-2 mb-4">
                @can('graduation_cite.edit')
                    <a href="{{ route('graduation-cites.edit', $graduationCite) }}" class="inline-flex items-center px-4 py-2 bg-white text-indigo-700 text-xs font-semibold uppercase tracking-widest rounded-md shadow-sm hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-indigo-900">
                        Editar
                    </a>
                @endcan
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200 grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div>
                        <p class="text-sm font-medium text-gray-500">CITE</p>
                        <p class="mt-1 text-base font-semibold text-gray-900">{{ $graduationCite->cite_number }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Fecha</p>
                        <p class="mt-1 text-base font-semibold text-gray-900">{{ $graduationCite->cite_date?->format('d/m/Y') }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Concepto</p>
                        <p class="mt-1 text-base font-semibold text-gray-900">{{ $graduationCite->payment_type_label }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Estado de Pago</p>
                        <p class="mt-1">
                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $graduationCite->payment_status_color }}">
                                {{ $graduationCite->payment_status_label }}
                            </span>
                        </p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Monto Total</p>
                        <p class="mt-1 text-base font-semibold text-gray-900">Bs. {{ number_format((float) $graduationCite->total_amount, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Registrado por</p>
                        <p class="mt-1 text-base font-semibold text-gray-900">{{ $graduationCite->creator->name ?? '-' }}</p>
                    </div>
                    <div class="md:col-span-4">
                        <p class="text-sm font-medium text-gray-500">Observaciones</p>
                        <p class="mt-1 text-sm text-gray-700 whitespace-pre-line">{{ $graduationCite->observations ?: 'Sin observaciones.' }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                        <div class="flex items-center gap-3">
                            <h3 class="text-lg font-semibold text-gray-900">Participantes</h3>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800">
                                {{ $totalCount }} registrados
                            </span>
                        </div>
                        <form method="GET" action="{{ route('graduation-cites.show', $graduationCite) }}" class="flex items-center gap-2">
                            <input
                                type="text"
                                name="search"
                                value="{{ $search }}"
                                placeholder="Buscar por nombre o CI..."
                                class="w-64 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                            >
                            <button type="submit" class="inline-flex items-center px-3 py-2 text-white text-xs font-semibold rounded-md" style="background-color:#4338ca">
                                Buscar
                            </button>
                            @if($search)
                                <a href="{{ route('graduation-cites.show', $graduationCite) }}" class="inline-flex items-center px-3 py-2 bg-gray-200 text-gray-700 text-xs font-semibold rounded-md hover:bg-gray-300">
                                    Limpiar
                                </a>
                            @endif
                        </form>
                    </div>

                    @if($search && $participants->isEmpty())
                        <p class="text-sm text-gray-500 text-center py-6">No se encontraron participantes para "{{ $search }}".</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-100">
                                <thead>
                                    <tr class="bg-gray-800 text-white text-xs">
                                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Nombre Completo</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">CI</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Programa</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-white uppercase tracking-wider">Monto</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($participants as $participant)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $participant->pivot->participant_full_name ?: $participant->getFullName() }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $participant->pivot->participant_ci ?: ($participant->ci ?: 'Sin CI') }}</td>
                                            <td class="px-6 py-4 text-sm text-gray-700">{{ $participant->pivot->participant_program ?: 'Sin programa' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-gray-900">Bs. {{ number_format((float) $participant->pivot->amount, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if($participants->hasPages())
                            <div class="mt-4">
                                {{ $participants->links() }}
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
