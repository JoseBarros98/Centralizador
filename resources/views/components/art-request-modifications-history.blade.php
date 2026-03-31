@props(['artRequest'])

<div class="bg-white overflow-hidden shadow-sm rounded-lg mt-6">
    <div class="p-6 bg-white border-b border-gray-200">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Historial de Modificaciones</h3>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                {{ $artRequest->getTotalModifications() }} cambios
            </span>
        </div>

        @if($artRequest->getTotalModifications() > 0)
            <!-- Resumen de tipos de modificaciones -->
            <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6">
                @foreach($artRequest->getModificationsSummary() as $type => $count)
                    @php
                        $colors = [
                            'COLOR' => 'bg-blue-50 border-blue-200',
                            'TAMAÑO' => 'bg-purple-50 border-purple-200',
                            'TEXTO' => 'bg-green-50 border-green-200',
                            'CONTENIDO' => 'bg-orange-50 border-orange-200',
                            'POSICIÓN' => 'bg-cyan-50 border-cyan-200',
                            'ESTILO' => 'bg-pink-50 border-pink-200',
                            'FUENTE' => 'bg-indigo-50 border-indigo-200',
                            'IMAGEN' => 'bg-red-50 border-red-200',
                            'OTRO' => 'bg-gray-50 border-gray-200',
                        ];
                    @endphp
                    <div class="border {{ $colors[$type] ?? 'bg-gray-50 border-gray-200' }} rounded p-3 text-center">
                        <div class="text-sm font-medium text-gray-700">{{ $type }}</div>
                        <div class="text-2xl font-bold mt-1">{{ $count }}</div>
                    </div>
                @endforeach
            </div>

            <!-- Timeline de modificaciones -->
            <div class="space-y-4">
                @forelse($artRequest->modifications as $modification)
                    <div class="flex gap-4 pb-4 border-b border-gray-100 last:border-b-0" id="modification-{{ $modification->id }}">
                        <!-- Checkbox de completado -->
                        <div class="flex-shrink-0 pt-1">
                            <input type="checkbox" 
                                name="is_completed" 
                                value="1" 
                                {{ $modification->is_completed ? 'checked' : '' }}
                                class="w-5 h-5 text-green-600 rounded cursor-pointer focus:ring-2 focus:ring-green-500 modification-toggle"
                                data-modification-id="{{ $modification->id }}"
                                data-art-request-id="{{ $artRequest->id }}"
                                title="Marcar como completado">
                        </div>

                        <!-- Badge de tipo -->
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center h-10 w-10 rounded-full bg-{{ $modification->type_color }}-100 {{ $modification->is_completed ? 'opacity-50' : '' }}">
                                <span class="text-lg">{{ $modification->type_icon }}</span>
                            </div>
                        </div>

                        <!-- Contenido -->
                        <div class="flex-1 min-w-0 {{ $modification->is_completed ? 'opacity-60' : '' }}">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-900 {{ $modification->is_completed ? 'line-through text-gray-500' : '' }}">
                                        {{ $modification->modification_type }}
                                        @if($modification->is_completed)
                                            <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                ✓ Completado
                                            </span>
                                        @endif
                                    </p>
                                    <p class="text-sm text-gray-700 mt-1 {{ $modification->is_completed ? 'line-through text-gray-500' : '' }}">
                                        {{ $modification->description }}
                                    </p>
                                </div>
                            </div>

                            <!-- Detalles de cambios -->
                            @if($modification->old_value || $modification->new_value)
                                <div class="mt-3 bg-gray-50 rounded p-3 text-xs">
                                    @if($modification->old_value)
                                        <div class="text-red-700">
                                            <strong>Antes:</strong>
                                            <code class="bg-white px-2 py-1 rounded ml-2">
                                                {{ is_array($modification->old_value) ? ($modification->old_value['value'] ?? '') : $modification->old_value }}
                                            </code>
                                        </div>
                                    @endif
                                    @if($modification->new_value)
                                        <div class="text-green-700 mt-1">
                                            <strong>Después:</strong>
                                            <code class="bg-white px-2 py-1 rounded ml-2">
                                                {{ is_array($modification->new_value) ? ($modification->new_value['value'] ?? '') : $modification->new_value }}
                                            </code>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            <!-- Detalles adicionales -->
                            @if($modification->details)
                                <div class="mt-2 text-xs text-gray-600 bg-gray-50 rounded p-2">
                                    @foreach($modification->details as $key => $value)
                                        <div><strong>{{ $key }}:</strong> {{ $value }}</div>
                                    @endforeach
                                </div>
                            @endif

                            <!-- Metadata -->
                            <div class="flex items-center justify-between mt-3 text-xs text-gray-500">
                                <span>
                                    Por: <strong>{{ $modification->creator->name ?? 'Sistema' }}</strong>
                                </span>
                                <span>
                                    {{ $modification->created_at->format('d/m/Y H:i') }}
                                </span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8">
                        <p class="text-gray-500 text-sm">No hay modificaciones registradas aún</p>
                    </div>
                @endforelse
            </div>
        @else
            <div class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m0 0h6m-6-6h6m0 0h6" />
                </svg>
                <p class="mt-2 text-gray-500">Este arte aún no tiene modificaciones registradas</p>
            </div>
        @endif
    </div>
</div>

<script>
document.querySelectorAll('.modification-toggle').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        const modificationId = this.dataset.modificationId;
        const artRequestId = this.dataset.artRequestId;
        const isChecked = this.checked;

        // Mostrar visualmente que está cargando
        const modElement = document.getElementById(`modification-${modificationId}`);
        modElement.classList.add('opacity-50', 'pointer-events-none');

        // Obtener CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || 
                          document.querySelector('input[name="_token"]')?.value;

        console.log('Toggle request:', {
            url: `/art-requests/${artRequestId}/modifications/${modificationId}/toggle`,
            method: 'PATCH',
            csrfToken: csrfToken ? 'presente' : 'FALTA',
            isChecked
        });

        // Enviar petición AJAX
        fetch(`/art-requests/${artRequestId}/modifications/${modificationId}/toggle`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken || '',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ is_completed: isChecked })
        })
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Response ok:', response.ok);
            
            if (!response.ok) {
                return response.text().then(text => {
                    throw new Error(`HTTP ${response.status}: ${text}`);
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('Success response:', data);
            
            // Actualizar la UI
            const title = modElement.querySelector('.text-sm.font-medium');
            const icon = modElement.querySelector('[class*="bg-"][class*="-100"]');
            const contentDiv = modElement.querySelector('.flex-1');
            
            if (isChecked) {
                // Marcar como completado
                if (icon) icon.classList.add('opacity-50');
                contentDiv.classList.add('opacity-60');
                
                // Agregar badge de completado
                if (!title.querySelector('.inline-flex')) {
                    title.innerHTML += ' <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">✓ Completado</span>';
                }
                
                // Agregar tachado
                title.classList.add('line-through', 'text-gray-500');
                const desc = modElement.querySelector('.text-sm.text-gray-700');
                if (desc) desc.classList.add('line-through', 'text-gray-500');
            } else {
                // Desmarcar como completado
                if (icon) icon.classList.remove('opacity-50');
                contentDiv.classList.remove('opacity-60');
                
                // Remover badge
                const completedBadge = title.querySelector('.inline-flex');
                if (completedBadge) completedBadge.remove();
                
                // Remover tachado
                title.classList.remove('line-through', 'text-gray-500');
                const desc = modElement.querySelector('.text-sm.text-gray-700');
                if (desc) desc.classList.remove('line-through', 'text-gray-500');
            }
            
            modElement.classList.remove('opacity-50', 'pointer-events-none');
        })
        .catch(error => {
            console.error('Error completo:', error);
            console.error('Stack:', error.stack);
            
            // En caso de error, revertir el checkbox
            checkbox.checked = !isChecked;
            modElement.classList.remove('opacity-50', 'pointer-events-none');
            alert(`Error al actualizar: ${error.message}`);
        });
    });
});
</script>
