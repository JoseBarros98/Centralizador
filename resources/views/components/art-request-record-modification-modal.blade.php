@props(['artRequest'])

<div id="recordModificationModal" class="hidden fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-lg max-w-md w-full">
            <!-- Header -->
            <div class="px-4 py-3 bg-gradient-to-r from-blue-500 to-blue-600 rounded-t-lg flex items-center justify-between">
                <h3 class="text-base font-medium text-white">Registrar Cambio</h3>
                <button onclick="document.getElementById('recordModificationModal').classList.add('hidden')" class="text-white hover:text-gray-200">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Body -->
            <form action="{{ route('art-requests.modifications.store', $artRequest) }}" method="POST" class="p-4 space-y-3">
                @csrf

                <!-- Tipo de modificación -->
                <div>
                    <label for="modification_type" class="block text-xs font-medium text-gray-700 mb-1">
                        Tipo <span class="text-red-500">*</span>
                    </label>
                    <select name="modification_type" id="modification_type" required class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Selecciona...</option>
                        <option value="COLOR">🎨 Color</option>
                        <option value="TAMAÑO">📏 Tamaño</option>
                        <option value="TEXTO">✍️ Texto</option>
                        <option value="CONTENIDO">📝 Contenido</option>
                        <option value="POSICIÓN">↔️ Posición</option>
                        <option value="ESTILO">🎭 Estilo</option>
                        <option value="FUENTE">🔤 Fuente</option>
                        <option value="IMAGEN">🖼️ Imagen</option>
                        <option value="OTRO">⚙️ Otro</option>
                    </select>
                </div>

                <!-- Descripción -->
                <div>
                    <label for="description" class="block text-xs font-medium text-gray-700 mb-1">
                        Descripción <span class="text-red-500">*</span>
                    </label>
                    <textarea name="description" id="description" rows="2" required
                        class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Qué cambios se realizaron..."></textarea>
                </div>

                <!-- Valores anteriores y nuevos -->
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label for="old_value" class="block text-xs font-medium text-gray-700 mb-1">
                            Valor Anterior
                        </label>
                        <input type="text" name="old_value" id="old_value" 
                            class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                            placeholder="ej: #FF0000">
                    </div>
                    <div>
                        <label for="new_value" class="block text-xs font-medium text-gray-700 mb-1">
                            Valor Nuevo
                        </label>
                        <input type="text" name="new_value" id="new_value" 
                            class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                            placeholder="ej: #0000FF">
                    </div>
                </div>

                <!-- Detalles adicionales -->
                <div>
                    <label for="details" class="block text-xs font-medium text-gray-700 mb-1">
                        Detalles (Opcional)
                    </label>
                    <textarea name="details" id="details" rows="2"
                        class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Información adicional..."></textarea>
                </div>

                <!-- Botones -->
                <div class="flex justify-end gap-2 pt-3">
                    <button type="button" onclick="document.getElementById('recordModificationModal').classList.add('hidden')"
                        class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Cancelar
                    </button>
                    <button type="submit" class="px-3 py-1.5 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Registrar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openRecordModificationModal() {
        document.getElementById('recordModificationModal').classList.remove('hidden');
    }

    function closeRecordModificationModal() {
        document.getElementById('recordModificationModal').classList.add('hidden');
    }
</script>
