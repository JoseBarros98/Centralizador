@if($errors->any())
<div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-md">
    <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

    <!-- Nombre -->
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre <span class="text-red-500">*</span></label>
        <input type="text" name="nombre" value="{{ old('nombre', $marketingContact->nombre ?? '') }}"
            class="w-full border-gray-300 rounded-md text-sm focus:ring-indigo-500 focus:border-indigo-500 @error('nombre') border-red-500 @enderror">
        @error('nombre')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <!-- Profesión -->
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Profesión</label>
        <input type="text" name="profesion" value="{{ old('profesion', $marketingContact->profesion ?? '') }}"
            class="w-full border-gray-300 rounded-md text-sm focus:ring-indigo-500 focus:border-indigo-500">
    </div>

    <!-- Teléfono -->
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono <span class="text-red-500">*</span></label>
        <input type="text" name="telefono" value="{{ old('telefono', $marketingContact->telefono ?? '') }}"
            class="w-full border-gray-300 rounded-md text-sm focus:ring-indigo-500 focus:border-indigo-500 @error('telefono') border-red-500 @enderror">
        @error('telefono')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <!-- Fecha -->
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha <span class="text-red-500">*</span></label>
        <input type="date" name="fecha" value="{{ old('fecha', isset($marketingContact) ? $marketingContact->fecha?->format('Y-m-d') : date('Y-m-d')) }}"
            class="w-full border-gray-300 rounded-md text-sm focus:ring-indigo-500 focus:border-indigo-500 @error('fecha') border-red-500 @enderror">
        @error('fecha')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <!-- Programa -->
    <div class="relative">
        <label class="block text-sm font-medium text-gray-700 mb-1">Programa</label>
        <input type="text" id="program-search"
            placeholder="Buscar programa..."
            autocomplete="off"
            value="{{ old('program_id') ? ($programs->firstWhere('id', old('program_id'))?->name ?? '') : ($marketingContact->program?->name ?? '') }}"
            class="w-full border-gray-300 rounded-md text-sm focus:ring-indigo-500 focus:border-indigo-500">
        <input type="hidden" id="program_id" name="program_id"
            value="{{ old('program_id', $marketingContact->program_id ?? '') }}">
        <div id="program-results" class="absolute z-10 w-full bg-white shadow-lg rounded-md mt-1 max-h-60 overflow-auto hidden border border-gray-200"></div>
        @error('program_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <!-- Referencia -->
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Referencia <span class="text-red-500">*</span></label>
        <select name="referencia" class="w-full border-gray-300 rounded-md text-sm focus:ring-indigo-500 focus:border-indigo-500 @error('referencia') border-red-500 @enderror">
            <option value="">Seleccionar...</option>
            @foreach(\App\Models\MarketingContact::REFERENCIAS as $ref)
                <option value="{{ $ref }}" @selected(old('referencia', $marketingContact->referencia ?? '') === $ref)>{{ $ref }}</option>
            @endforeach
        </select>
        @error('referencia')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <!-- Origen -->
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Origen <span class="text-red-500">*</span></label>
        <select name="origen" class="w-full border-gray-300 rounded-md text-sm focus:ring-indigo-500 focus:border-indigo-500 @error('origen') border-red-500 @enderror">
            <option value="">Seleccionar...</option>
            @foreach(\App\Models\MarketingContact::ORIGENES as $org)
                <option value="{{ $org }}" @selected(old('origen', $marketingContact->origen ?? '') === $org)>{{ $org }}</option>
            @endforeach
        </select>
        @error('origen')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <!-- Estado de pago -->
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Estado de Pago</label>
        <select name="estado_pago" class="w-full border-gray-300 rounded-md text-sm focus:ring-indigo-500 focus:border-indigo-500">
            <option value="">Sin estado</option>
            @foreach(\App\Models\MarketingContact::ESTADOS_PAGO as $ep)
                <option value="{{ $ep }}" @selected(old('estado_pago', $marketingContact->estado_pago ?? '') === $ep)>{{ $ep }}</option>
            @endforeach
        </select>
    </div>

    <!-- Conversión -->
    <div class="flex items-center gap-3 pt-5">
        <input type="hidden" name="conversion" value="0">
        <input type="checkbox" name="conversion" id="conversion" value="1"
            {{ old('conversion', $marketingContact->conversion ?? false) ? 'checked' : '' }}
            class="h-4 w-4 text-indigo-600 border-gray-300 rounded">
        <label for="conversion" class="text-sm font-medium text-gray-700">Inscrito convertido</label>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput      = document.getElementById('program-search');
    const hiddenInput      = document.getElementById('program_id');
    const resultsContainer = document.getElementById('program-results');
    const apiUrl           = '{{ route('marketing-contacts.search-programs') }}';
    let searchTimeout;

    searchInput.addEventListener('input', function () {
        clearTimeout(searchTimeout);
        const query = this.value.trim();

        if (query.length < 2) {
            resultsContainer.classList.add('hidden');
            resultsContainer.innerHTML = '';
            if (query.length === 0) hiddenInput.value = '';
            return;
        }

        searchTimeout = setTimeout(() => {
            fetch(`${apiUrl}?q=${encodeURIComponent(query)}`)
                .then(r => r.json())
                .then(data => {
                    if (data.length === 0) {
                        resultsContainer.innerHTML = '<div class="p-2 text-gray-500 text-sm">Sin resultados</div>';
                    } else {
                        resultsContainer.innerHTML = '<ul class="divide-y divide-gray-100">' +
                            data.map(p =>
                                `<li class="px-3 py-2 hover:bg-indigo-50 cursor-pointer text-sm" data-id="${p.id}" data-name="${p.name}">${p.name}</li>`
                            ).join('') +
                        '</ul>';

                        resultsContainer.querySelectorAll('li').forEach(li => {
                            li.addEventListener('click', function () {
                                hiddenInput.value = this.dataset.id;
                                searchInput.value = this.dataset.name;
                                resultsContainer.classList.add('hidden');
                            });
                        });
                    }
                    resultsContainer.classList.remove('hidden');
                })
                .catch(() => resultsContainer.classList.add('hidden'));
        }, 300);
    });

    searchInput.addEventListener('blur', function () {
        setTimeout(() => {
            if (this.value.trim() === '') hiddenInput.value = '';
        }, 200);
    });

    document.addEventListener('click', function (e) {
        if (!searchInput.contains(e.target) && !resultsContainer.contains(e.target)) {
            resultsContainer.classList.add('hidden');
        }
    });
});
</script>
