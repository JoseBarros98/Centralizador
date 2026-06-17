<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Resumen de Calificaciones') }}
        </h2>
    </x-slot>

    <div>
        <div class="w-full sm:px-6 lg:px-8">
            <div class="flex items-center justify-end gap-2 mb-4">
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
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Calificaciones del Módulo: {{ $module->name }}</h3>
                    
                    <div class="mb-4 flex items-center gap-3">
                        <div style="position:relative; max-width:320px; width:100%;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                 style="position:absolute; left:9px; top:50%; transform:translateY(-50%); color:#9ca3af; pointer-events:none;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
                            </svg>
                            <input type="text"
                                   id="grade-search"
                                   placeholder="Buscar por nombre..."
                                   oninput="filtrarCalificaciones(this.value)"
                                   style="width:100%; padding:0.5rem 0.75rem 0.5rem 2.25rem; border:1px solid #d1d5db; border-radius:0.375rem; font-size:0.875rem; outline:none; box-sizing:border-box;"
                                   onfocus="this.style.borderColor='#818cf8'; this.style.boxShadow='0 0 0 2px #e0e7ff';"
                                   onblur="this.style.borderColor='#d1d5db'; this.style.boxShadow='none';">
                        </div>
                        <span id="grade-count" style="font-size:0.875rem; color:#6b7280;"></span>
                    </div>

                    <div style="overflow-x:auto;">
                        <table style="min-width:100%; border-collapse:collapse;">
                            <thead>
                                <tr style="background:#1f2937; color:white;">
                                    <th scope="col" style="position:sticky; left:0; z-index:20; background:#1f2937; padding:0.75rem 1rem; text-align:left; font-size:0.7rem; font-weight:600; color:white; text-transform:uppercase; letter-spacing:0.05em; white-space:nowrap; box-shadow:2px 0 0 #374151;">Acciones</th>
                                    <th scope="col" style="position:sticky; left:130px; z-index:20; background:#1f2937; padding:0.75rem 1.25rem; text-align:left; font-size:0.7rem; font-weight:600; color:white; text-transform:uppercase; letter-spacing:0.05em; white-space:nowrap; min-width:200px; box-shadow:2px 0 4px rgba(0,0,0,0.3);">Participante</th>
                                    @foreach($activityNames as $actName)
                                        <th scope="col" style="background:#1f2937; padding:0.75rem 0.75rem; text-align:center; font-size:0.65rem; font-weight:500; color:white; text-transform:uppercase; letter-spacing:0.03em; min-width:120px; max-width:140px; white-space:normal; word-break:break-word; vertical-align:top; line-height:1.3;" title="{{ $actName }}">
                                            {{ $actName }}
                                        </th>
                                    @endforeach
                                    <th scope="col" style="background:#1f2937; padding:0.75rem 1rem; text-align:left; font-size:0.7rem; font-weight:600; color:white; text-transform:uppercase; letter-spacing:0.05em; white-space:nowrap;">Calificación</th>
                                    <th scope="col" style="background:#1f2937; padding:0.75rem 1rem; text-align:left; font-size:0.7rem; font-weight:600; color:white; text-transform:uppercase; letter-spacing:0.05em; white-space:nowrap;">Forma de Aprobación</th>
                                    <th scope="col" style="background:#1f2937; padding:0.75rem 1rem; text-align:left; font-size:0.7rem; font-weight:600; color:white; text-transform:uppercase; letter-spacing:0.05em; white-space:nowrap;">Estado</th>
                                    <th scope="col" style="background:#1f2937; padding:0.75rem 1rem; text-align:left; font-size:0.7rem; font-weight:600; color:white; text-transform:uppercase; letter-spacing:0.05em; white-space:nowrap;">Seguimiento</th>
                                </tr>
                            </thead>
                            <tbody id="grades-tbody" class="divide-y divide-gray-100">
                                @forelse ($grades as $grade)
                                    <tr id="grade-row-{{ $grade->id }}" class="grade-row"
                                        data-search="{{ strtolower($grade->inscription ? $grade->inscription->getFullName() : $grade->name . ' ' . $grade->last_name) }}">
                                        <td style="position:sticky; left:0; z-index:10; background:white; padding:0.75rem 1rem; white-space:nowrap; width:130px; min-width:130px; box-shadow:2px 0 0 #e5e7eb;" class="text-sm font-medium">
                                            <div id="grade-actions-{{ $grade->id }}" class="flex space-x-3">
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
                                        <td style="position:sticky; left:130px; z-index:10; background:white; padding:0.75rem 1.25rem; white-space:nowrap; min-width:200px; box-shadow:2px 0 4px rgba(0,0,0,0.08);" class="text-sm font-medium text-gray-900">
                                            @if($grade->inscription)
                                                {{ $grade->inscription->getFullName() }}
                                            @else
                                                {{ $grade->name }} {{ $grade->last_name }} <span style="color:#9ca3af;">(sin asociado)</span>
                                            @endif
                                        </td>
                                        @foreach($activityNames as $actName)
                                            <td class="px-4 py-4 whitespace-nowrap text-center text-sm text-gray-700">
                                                {{ $grade->activities[$actName] ?? '—' }}
                                            </td>
                                        @endforeach
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
                                        <td id="grade-status-{{ $grade->id }}" class="px-6 py-4 whitespace-nowrap">
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

                    const statusCell = document.querySelector('#grade-status-' + gradeId + ' span');
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
            const actionsCell = document.getElementById('grade-actions-' + gradeId);
            
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
<script>
function filtrarCalificaciones(query) {
    const q = query.toLowerCase().trim();
    const rows = document.querySelectorAll('.grade-row');
    let visible = 0;

    rows.forEach(row => {
        const match = !q || row.dataset.search.includes(q);
        row.style.display = match ? '' : 'none';
        if (match) visible++;
    });

    const counter = document.getElementById('grade-count');
    counter.textContent = q ? (visible + ' de ' + rows.length + ' participantes') : '';
}
</script>
</x-app-layout>