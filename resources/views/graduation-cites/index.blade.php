<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-white leading-tight">
                Titulación
            </h2>
            @can('graduation_cite.create')
                <a href="{{ route('graduation-cites.create') }}" class="inline-flex items-center px-4 py-2 bg-white text-indigo-700 text-xs font-semibold uppercase tracking-widest rounded-md shadow-sm hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-indigo-900">
                    Nuevo CITE
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <form method="GET" action="{{ route('graduation-cites.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label for="cite_number" class="block text-sm font-medium text-gray-700 mb-1">CITE</label>
                            <input id="cite_number" type="text" name="cite_number" value="{{ request('cite_number') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Buscar por código">
                        </div>
                        <div>
                            <label for="payment_type" class="block text-sm font-medium text-gray-700 mb-1">Concepto</label>
                            <select id="payment_type" name="payment_type" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Todos</option>
                                <option value="inscripcion" {{ request('payment_type') === 'inscripcion' ? 'selected' : '' }}>Inscripción</option>
                                <option value="matricula" {{ request('payment_type') === 'matricula' ? 'selected' : '' }}>Matrícula</option>
                                <option value="colegiatura" {{ request('payment_type') === 'colegiatura' ? 'selected' : '' }}>Colegiatura</option>
                                <option value="certificacion" {{ request('payment_type') === 'certificacion' ? 'selected' : '' }}>Certificación</option>
                            </select>
                        </div>
                        <div>
                            <label for="participant" class="block text-sm font-medium text-gray-700 mb-1">Participante</label>
                            <input id="participant" type="text" name="participant" value="{{ request('participant') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Nombre o CI">
                        </div>
                        <div class="flex items-end gap-2">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                                Buscar
                            </button>
                            @if(request()->filled('cite_number') || request()->filled('payment_type') || request()->filled('participant'))
                                <a href="{{ route('graduation-cites.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                                    Limpiar
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CITE</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Concepto</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto por Participante</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Participantes</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registrado por</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($graduationCites as $graduationCite)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $graduationCite->cite_number }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $graduationCite->cite_date?->format('d/m/Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $graduationCite->payment_type_label }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Bs. {{ number_format((float) $graduationCite->amount_per_participant, 2) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Bs. {{ number_format((float) $graduationCite->total_amount, 2) }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">
                                        <div class="font-medium">{{ $graduationCite->participants_count }} participante(s)</div>
                                        <div class="text-xs text-gray-500">
                                            {{ $graduationCite->participants->take(2)->map(fn ($participant) => $participant->pivot->participant_full_name ?: $participant->getFullName())->implode(', ') }}
                                            @if($graduationCite->participants_count > 2)
                                                ...
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $graduationCite->creator->name ?? '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                        <div class="inline-flex items-center justify-end gap-3">
                                            <a href="{{ route('graduation-cites.show', $graduationCite) }}" class="text-indigo-600 hover:text-indigo-900" title="Ver detalles" aria-label="Ver detalles">
                                                <x-action-icons action="view" />
                                            </a>
                                            @can('graduation_cite.edit')
                                                <a href="{{ route('graduation-cites.edit', $graduationCite) }}" class="text-yellow-600 hover:text-yellow-900" title="Editar CITE" aria-label="Editar CITE">
                                                    <x-action-icons action="edit" />
                                                </a>
                                            @endcan
                                            @can('graduation_cite.delete')
                                                <form method="POST" action="{{ route('graduation-cites.destroy', $graduationCite) }}" class="inline" onsubmit="return confirm('¿Deseas eliminar este CITE?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900" title="Eliminar CITE" aria-label="Eliminar CITE">
                                                        <x-action-icons action="delete" />
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-4 text-sm text-center text-gray-500">No hay CITES de titulación registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-6">
                    {{ $graduationCites->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>