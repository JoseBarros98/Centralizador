<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-white leading-tight">
                {{ __('Resumen de Calificaciones') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('grades.upload', ['program' => $program->id, 'module' => $module->id]) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:border-blue-800 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                    </svg>
                    Subir Calificaciones
                </a>
                <a href="{{ route('programs.modules.show', ['program' => $program->id, 'module' => $module->id]) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-800 focus:outline-none focus:border-gray-800 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                    </svg>
                    Volver
                </a>
            </div>
        </div>
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Calificaciones del Módulo: {{ $module->name }}</h3>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Participante</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Calificación</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Forma de Aprobación</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Seguimiento</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($grades as $grade)
                                    <tr id="grade-row-{{ $grade->id }}">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            @if($grade->inscription)
                                                {{ $grade->inscription->getFullName() }}
                                            @else
                                                {{ $grade->name }} {{ $grade->last_name }} (no asociado)
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <div id="grade-display-{{ $grade->id }}">
                                                <span class="text-lg font-semibold">{{ $grade->grade }}</span>
                                                <button onclick="toggleEditMode({{ $grade->id }})" class="ml-2 text-indigo-600 hover:text-indigo-900">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                </button>
                                            </div>
                                            
                                            <div id="grade-edit-{{ $grade->id }}" class="hidden">
                                                <form onsubmit="updateGrade(event, {{ $grade->id }})" class="flex items-center space-x-2">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="number" name="grade" value="{{ $grade->grade }}" 
                                                           class="w-20 px-2 py-1 border border-gray-300 rounded text-sm focus:ring-indigo-500 focus:border-indigo-500"
                                                           min="0" max="100" step="0.01" required>
                                                    <button type="submit" class="text-green-600 hover:text-green-900">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                        </svg>
                                                    </button>
                                                    <button type="button" onclick="toggleEditMode({{ $grade->id }})" class="text-red-600 hover:text-red-900">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <div id="approval-display-{{ $grade->id }}">
                                                @php
                                                    $approvalTypes = [
                                                        'regular' => 'Regular',
                                                        'recuperatorio' => 'Recuperatorio', 
                                                        'tutoria' => 'Tutoría'
                                                    ];
                                                @endphp
                                                <span data-approval-type="{{ $grade->approval_type ?? 'regular' }}" 
                                                      class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                    @if(($grade->approval_type ?? 'regular') === 'regular') bg-blue-100 text-blue-800
                                                    @elseif(($grade->approval_type ?? 'regular') === 'recuperatorio') bg-yellow-100 text-yellow-800
                                                    @else bg-purple-100 text-purple-800 @endif">
                                                    {{ $approvalTypes[$grade->approval_type ?? 'regular'] ?? 'Regular' }}
                                                </span>
                                                <button onclick="toggleApprovalEditMode({{ $grade->id }})" class="ml-2 text-indigo-600 hover:text-indigo-900">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                </button>
                                            </div>
                                            
                                            <div id="approval-edit-{{ $grade->id }}" class="hidden">
                                                <form onsubmit="updateApprovalType(event, {{ $grade->id }})" class="flex items-center space-x-2">
                                                    @csrf
                                                    @method('PATCH')
                                                    <select name="approval_type" 
                                                            class="text-xs leading-5 font-semibold rounded-full border-0 focus:ring-2 focus:ring-indigo-500">
                                                        <option value="regular" {{ ($grade->approval_type ?? 'regular') === 'regular' ? 'selected' : '' }}>Regular</option>
                                                        <option value="recuperatorio" {{ ($grade->approval_type ?? 'regular') === 'recuperatorio' ? 'selected' : '' }}>Recuperatorio</option>
                                                        <option value="tutoria" {{ ($grade->approval_type ?? 'regular') === 'tutoria' ? 'selected' : '' }}>Tutoría</option>
                                                    </select>
                                                    <button type="submit" class="text-green-600 hover:text-green-900">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                        </svg>
                                                    </button>
                                                    <button type="button" onclick="toggleApprovalEditMode({{ $grade->id }})" class="text-red-600 hover:text-red-900">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($grade->grade >= 71)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    Aprobado
                                                </span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                    Reprobado
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            @if($grade->hasOpenFollowup())
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    Con seguimiento abierto
                                                </span>
                                            @elseif($grade->followups()->count() > 0)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                    Con seguimiento cerrado
                                                </span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                    Sin seguimiento
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-3">
                                                @if($grade->hasOpenFollowup())
                                                    <a href="{{ route('grade_followups.show', ['program' => $program->id, 'module' => $module->id, 'grade' => $grade->id]) }}"
                                                       class="text-indigo-600 hover:text-indigo-900 inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded-md bg-indigo-100 hover:bg-indigo-200 transition-colors duration-200" 
                                                       title="Ver Seguimiento">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                        </svg>
                                                        Ver
                                                    </a>
                                                    @if($grade->followups()->count() > 1)
                                                        <a href="{{ route('grade_followups.history', ['program' => $program->id, 'module' => $module->id, 'grade' => $grade->id]) }}"
                                                           class="text-purple-600 hover:text-purple-900 inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded-md bg-purple-100 hover:bg-purple-200 transition-colors duration-200" 
                                                           title="Ver Historial">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                            </svg>
                                                            Historial
                                                        </a>
                                                    @endif
                                                @elseif($grade->followups()->count() > 0)
                                                    <a href="{{ route('grade_followups.history', ['program' => $program->id, 'module' => $module->id, 'grade' => $grade->id]) }}"
                                                       class="text-purple-600 hover:text-purple-900 inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded-md bg-purple-100 hover:bg-purple-200 transition-colors duration-200" 
                                                       title="Ver Historial">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                        Historial
                                                    </a>
                                                    @if($grade->grade < 71)
                                                        <a href="{{ route('grade_followups.create', ['program' => $program->id, 'module' => $module->id, 'grade' => $grade->id]) }}"
                                                           class="text-green-600 hover:text-green-900 inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded-md bg-green-100 hover:bg-green-200 transition-colors duration-200" 
                                                           title="Crear Nuevo Seguimiento">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                                            </svg>
                                                            Crear
                                                        </a>
                                                    @endif
                                                @else
                                                    @if($grade->grade < 71)
                                                        <a href="{{ route('grade_followups.create', ['program' => $program->id, 'module' => $module->id, 'grade' => $grade->id]) }}"
                                                           class="text-green-600 hover:text-green-900 inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded-md bg-green-100 hover:bg-green-200 transition-colors duration-200" 
                                                           title="Iniciar Seguimiento">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                            </svg>
                                                            Iniciar
                                                        </a>
                                                    @endif
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No hay calificaciones para mostrar</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleEditMode(gradeId) {
            const displayElement = document.getElementById('grade-display-' + gradeId);
            const editElement = document.getElementById('grade-edit-' + gradeId);
            
            displayElement.classList.toggle('hidden');
            editElement.classList.toggle('hidden');
        }

        function toggleApprovalEditMode(gradeId) {
            const displayElement = document.getElementById('approval-display-' + gradeId);
            const editElement = document.getElementById('approval-edit-' + gradeId);
            
            displayElement.classList.toggle('hidden');
            editElement.classList.toggle('hidden');
        }

        async function updateGrade(event, gradeId) {
            event.preventDefault();
            
            const form = event.target;
            const formData = new FormData(form);
            const gradeValue = formData.get('grade');
            
            try {
                const url = '/programs/{{ $program->id }}/modules/{{ $module->id }}/grades/' + gradeId;
                const response = await fetch(url, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        grade: gradeValue,
                        approval_type: document.querySelector('#approval-display-' + gradeId + ' span').getAttribute('data-approval-type') || 'regular'
                    })
                });

                if (response.ok) {
                    document.querySelector('#grade-display-' + gradeId + ' span').textContent = gradeValue;
                    
                    const statusCell = document.querySelector('#grade-row-' + gradeId + ' td:nth-child(4) span');
                    if (parseFloat(gradeValue) >= 71) {
                        statusCell.className = 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800';
                        statusCell.textContent = 'Aprobado';
                    } else {
                        statusCell.className = 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800';
                        statusCell.textContent = 'Reprobado';
                    }
                    
                    updateFollowupButtons(gradeId, parseFloat(gradeValue));
                    toggleEditMode(gradeId);
                    showSuccessMessage('Calificación actualizada correctamente');
                } else {
                    throw new Error('Error al actualizar la calificación');
                }
            } catch (error) {
                console.error('Error:', error);
                showErrorMessage('Error al actualizar la calificación');
            }
        }

        async function updateApprovalType(event, gradeId) {
            event.preventDefault();
            
            const form = event.target;
            const formData = new FormData(form);
            const approvalType = formData.get('approval_type');
            const currentGrade = document.querySelector('#grade-display-' + gradeId + ' span').textContent;
            
            const approvalTypeLabels = {
                'regular': 'Regular',
                'recuperatorio': 'Recuperatorio',
                'tutoria': 'Tutoría'
            };
            
            const approvalTypeColors = {
                'regular': 'bg-blue-100 text-blue-800',
                'recuperatorio': 'bg-yellow-100 text-yellow-800', 
                'tutoria': 'bg-purple-100 text-purple-800'
            };
            
            try {
                const url = '/programs/{{ $program->id }}/modules/{{ $module->id }}/grades/' + gradeId;
                const response = await fetch(url, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        grade: currentGrade,
                        approval_type: approvalType
                    })
                });

                if (response.ok) {
                    const displaySpan = document.querySelector('#approval-display-' + gradeId + ' span');
                    displaySpan.textContent = approvalTypeLabels[approvalType];
                    displaySpan.className = 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full ' + approvalTypeColors[approvalType];
                    displaySpan.setAttribute('data-approval-type', approvalType);
                    
                    toggleApprovalEditMode(gradeId);
                    showSuccessMessage('Forma de aprobación actualizada correctamente');
                } else {
                    throw new Error('Error al actualizar la forma de aprobación');
                }
            } catch (error) {
                console.error('Error:', error);
                showErrorMessage('Error al actualizar la forma de aprobación');
            }
        }

        function showSuccessMessage(message) {
            const notification = document.createElement('div');
            notification.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded shadow-lg z-50';
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(function() {
                notification.remove();
            }, 3000);
        }

        function showErrorMessage(message) {
            const notification = document.createElement('div');
            notification.className = 'fixed top-4 right-4 bg-red-500 text-white px-4 py-2 rounded shadow-lg z-50';
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(function() {
                notification.remove();
            }, 3000);
        }

        function updateFollowupButtons(gradeId, newGrade) {
            const actionsCell = document.querySelector('#grade-row-' + gradeId + ' td:nth-child(6) div');
            
            if (!actionsCell) return;
            
            const hasExistingFollowups = actionsCell.querySelector('a[title*="Historial"]') !== null;
            const hasOpenFollowup = actionsCell.querySelector('a[title*="Ver Seguimiento"]') !== null;
            
            if (hasOpenFollowup) {
                return;
            }
            
            if (hasExistingFollowups) {
                const createButton = actionsCell.querySelector('a[title*="Crear"]');
                
                if (newGrade < 71) {
                    if (!createButton) {
                        const historialButton = actionsCell.querySelector('a[title*="Historial"]');
                        if (historialButton) {
                            const newCreateButton = document.createElement('a');
                            newCreateButton.href = '{{ url("/programs/" . $program->id . "/modules/" . $module->id . "/grades/") }}' + '/' + gradeId + '/followup/create';
                            newCreateButton.className = 'text-green-600 hover:text-green-900 inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded-md bg-green-100 hover:bg-green-200 transition-colors duration-200';
                            newCreateButton.title = 'Crear Nuevo Seguimiento';
                            newCreateButton.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>Crear';
                            actionsCell.appendChild(newCreateButton);
                        }
                    }
                } else {
                    if (createButton) {
                        createButton.remove();
                    }
                }
            } else {
                const initButton = actionsCell.querySelector('a[title*="Iniciar"]');
                
                if (newGrade < 71) {
                    if (!initButton) {
                        const newInitButton = document.createElement('a');
                        newInitButton.href = '{{ url("/programs/" . $program->id . "/modules/" . $module->id . "/grades/") }}' + '/' + gradeId + '/followup/create';
                        newInitButton.className = 'text-green-600 hover:text-green-900 inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded-md bg-green-100 hover:bg-green-200 transition-colors duration-200';
                        newInitButton.title = 'Iniciar Seguimiento';
                        newInitButton.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>Iniciar';
                        actionsCell.appendChild(newInitButton);
                    }
                } else {
                    if (initButton) {
                        initButton.remove();
                    }
                }
            }
        }
    </script>
</x-app-layout>