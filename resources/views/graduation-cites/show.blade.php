<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-white leading-tight">
                Detalle del CITE de Titulación
            </h2>
            <div class="flex items-center gap-2">
                @can('graduation_cite.edit')
                    <a href="{{ route('graduation-cites.edit', $graduationCite) }}" class="inline-flex items-center px-4 py-2 bg-white text-indigo-700 text-xs font-semibold uppercase tracking-widest rounded-md shadow-sm hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-indigo-900">
                        Editar
                    </a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

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
                        <p class="text-sm font-medium text-gray-500">Monto por Participante</p>
                        <p class="mt-1 text-base font-semibold text-gray-900">Bs. {{ number_format((float) $graduationCite->amount_per_participant, 2) }}</p>
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
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Participantes</h3>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800">
                            {{ $graduationCite->participants->count() }} registrados
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre Completo</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CI</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Programa</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($graduationCite->participants as $participant)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $participant->pivot->participant_full_name ?: $participant->getFullName() }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $participant->pivot->participant_ci ?: ($participant->ci ?: 'Sin CI') }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-700">{{ $participant->pivot->participant_program ?: ($participant->programs->pluck('name')->filter()->implode(', ') ?: 'Sin programa') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>