<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            Titulación
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="w-full sm:px-6 lg:px-8 space-y-6">
            <div class="flex items-center justify-end gap-2 mb-4">
                @can('graduation_cite.create')
                    <button type="button"
                        onclick="document.getElementById('import-modal').style.display='flex'"
                        class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-xs font-semibold uppercase tracking-widest rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-indigo-900">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                        Importar desde Excel
                    </button>
                    <a href="{{ route('graduation-cites.create') }}" class="inline-flex items-center px-4 py-2 bg-white text-indigo-700 text-xs font-semibold uppercase tracking-widest rounded-md shadow-sm hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-indigo-900">
                        Nuevo CITE
                    </a>
                @endcan
            </div>

            {{-- Resultado de importación --}}
            @if(session('import_success'))
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                    <p class="text-green-800 font-medium text-sm">{{ session('import_success') }}</p>
                    @if(session('import_errors') && count(session('import_errors')) > 0)
                        <div class="mt-3">
                            <p class="text-yellow-700 text-sm font-medium mb-1">Advertencias (participantes no encontrados en el sistema):</p>
                            <ul class="list-disc list-inside text-sm text-yellow-700 space-y-1">
                                @foreach(session('import_errors') as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            @endif
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <form method="GET" action="{{ route('graduation-cites.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
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
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2" style="background-color:#4338ca">
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
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead>
                            <tr class="bg-gray-800 text-white text-xs">
                                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">CITE</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Fecha</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Concepto</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Monto por Participante</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Monto Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Participantes</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Registrado por</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-white uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
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

    {{-- Modal importar desde Excel --}}
    <div id="import-modal" class="hidden fixed inset-0 z-50 items-center justify-center bg-black bg-opacity-50" style="display:none">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-lg mx-4">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Importar CITEs desde Excel</h3>
                <button type="button" onclick="document.getElementById('import-modal').style.display='none'" class="text-gray-400 hover:text-gray-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form method="POST" action="{{ route('graduation-cites.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="px-6 py-5 space-y-4">
                    <div class="bg-blue-50 border border-blue-200 rounded-md p-3 text-sm text-blue-800">
                        <p class="font-medium mb-1">Formato esperado del Excel:</p>
                        <ul class="list-disc list-inside space-y-1 text-xs">
                            <li>Columnas requeridas: <strong>PARTICIPANTE, C.I, CITE</strong></li>
                            <li>Al menos una columna de tipo de pago: <strong>INSCRIPCION, MATRICULA, COLEGIATURA o CERTIFICACION</strong></li>
                            <li>El nombre del CITE puede tener cualquier formato: <code>CITE 3 - ENERO 2025</code>, <code>MAR-A-D84</code>, <code>CITE 027</code>, etc.</li>
                            <li>Si el nombre contiene mes y año se usará como fecha; si no, se usará la fecha actual</li>
                            <li>Los CITEs que ya existan en el sistema serán omitidos</li>
                            <li>Los participantes se buscan por número de C.I.</li>
                        </ul>
                    </div>

                    <div>
                        <label for="excel_file" class="block text-sm font-medium text-gray-700 mb-1">
                            Archivo Excel <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="file"
                            id="excel_file"
                            name="excel_file"
                            accept=".xlsx,.xls"
                            class="block w-full text-sm text-gray-700 border border-gray-300 rounded-md cursor-pointer focus:outline-none focus:ring-2 focus:ring-indigo-500 file:mr-3 file:py-2 file:px-4 file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                        >
                        @error('excel_file')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-200">
                    <button type="button"
                        onclick="document.getElementById('import-modal').style.display='none'"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">
                        Cancelar
                    </button>
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                        Importar
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if($errors->has('excel_file'))
        <script>document.getElementById('import-modal').style.display='flex';</script>
    @endif
</x-app-layout>