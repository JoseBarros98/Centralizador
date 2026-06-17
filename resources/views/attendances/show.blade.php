@extends('layouts.app')

@section('content')
<style>
.status-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.125rem 0.625rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
}
.status-present { background-color: #dcfce7; color: #166534; }
.status-late    { background-color: #fef3c7; color: #92400e; }
.status-absent  { background-color: #fecaca; color: #991b1b; }
.status-license { background-color: #dbeafe; color: #1e40af; }
</style>

<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Asistencia a Clase</h1>
            <div class="text-sm breadcrumbs">
                <ul>
                    <li><a href="{{ route('dashboard') }}">Inicio</a></li>
                    <li><a href="{{ route('programs.show', $program) }}">{{ $program->name }}</a></li>
                    <li><a href="{{ route('programs.modules.show', [$program->id, $module->id]) }}">{{ $module->name }}</a></li>
                    <li>Asistencia</li>
                </ul>
            </div>
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('programs.modules.show', [$program->id, $module->id]) }}"
               class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition ease-in-out duration-150">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Volver al Módulo
            </a>
            <a href="{{ route('attendances.show_with_licenses', [$program->id, $module->id, $class->id]) }}"
               class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition ease-in-out duration-150">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Gestión de Licencias
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-4 bg-green-50 border border-green-200 text-green-800 rounded-md px-4 py-3 text-sm">
        {{ session('success') }}
    </div>
    @endif

    @if($errors->any())
    <div class="mb-4 bg-red-50 border border-red-200 text-red-800 rounded-md px-4 py-3 text-sm">
        @foreach($errors->all() as $error) <p>{{ $error }}</p> @endforeach
    </div>
    @endif

    <!-- Class Information -->
    <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Información de la Clase</h3>
        </div>
        <div class="border-t border-gray-200 px-4 py-5 sm:px-6">
            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-3">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Fecha</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $class->class_date->format('d/m/Y') }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Total de Asistencias</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $attendances->count() }} estudiantes</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Invitados sin vincular</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        {{ $attendances->where('is_registered_inscription', false)->count() }}
                    </dd>
                </div>
            </dl>
        </div>
    </div>

    <!-- Attendance Table -->
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Lista de Asistencia</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead>
                    <tr class="bg-gray-800 text-white text-xs">
                        <th class="px-6 py-3 text-left font-medium uppercase tracking-wider">Estudiante</th>
                        <th class="px-6 py-3 text-left font-medium uppercase tracking-wider">Documento</th>
                        <th class="px-6 py-3 text-left font-medium uppercase tracking-wider">Estado</th>
                        @can('program.manage_attendance')
                        <th class="px-6 py-3 text-left font-medium uppercase tracking-wider">Acciones</th>
                        @endcan
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($attendances as $attendance)
                    <tr class="{{ !$attendance->is_registered_inscription ? 'bg-amber-50' : '' }}">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                @if($attendance->inscription)
                                    {{ $attendance->inscription->getFullName() }}
                                @else
                                    {{ $attendance->name ?? 'Nombre no disponible' }}
                                    <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-amber-100 text-amber-800">Invitado</span>
                                @endif
                            </div>
                            <div class="text-sm text-gray-500">
                                {{ $attendance->inscription ? $attendance->inscription->email : ($attendance->email ?? '') }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            @if($attendance->inscription)
                                {{ $attendance->inscription->ci }}
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($attendance->license_type && $attendance->status === 'absent')
                                <span class="status-badge status-license">Licencia/Permiso</span>
                            @else
                                @php
                                    $statusClass = 'status-absent';
                                    $statusText  = 'Ausente';
                                    if ($attendance->status === 'present') { $statusClass = 'status-present'; $statusText = 'Presente'; }
                                    elseif ($attendance->status === 'late') { $statusClass = 'status-late'; $statusText = 'Tarde'; }
                                @endphp
                                <span class="status-badge {{ $statusClass }}">{{ $statusText }}</span>
                            @endif
                        </td>
                        @can('program.manage_attendance')
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @if(!$attendance->is_registered_inscription)
                                <button type="button"
                                        onclick="openLinkModal({{ $attendance->id }}, '{{ addslashes($attendance->name) }}')"
                                        class="inline-flex items-center px-3 py-1 bg-indigo-600 text-white text-xs rounded-md hover:bg-indigo-700 transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                    </svg>
                                    Vincular a inscrito
                                </button>
                            @else
                                <form method="POST"
                                      action="{{ route('attendances.unlink_inscription', [$program->id, $attendance->id]) }}"
                                      onsubmit="return confirm('¿Desvincular este registro del inscrito?')"
                                      class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="inline-flex items-center px-3 py-1 bg-gray-200 text-gray-700 text-xs rounded-md hover:bg-gray-300 transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                        </svg>
                                        Desvincular
                                    </button>
                                </form>
                            @endif
                        </td>
                        @endcan
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal de vinculación --}}
@can('program.manage_attendance')
<style>
#link-modal {
    position: fixed;
    inset: 0;
    z-index: 9999;
    display: none;
    align-items: center;
    justify-content: center;
    background: rgba(0,0,0,0.5);
    padding: 1rem;
}
#link-modal.open {
    display: flex;
}
#link-modal-card {
    background: #fff;
    border-radius: 0.5rem;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    width: 100%;
    max-width: 480px;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}
</style>

<div id="link-modal" aria-modal="true" role="dialog">
    <div id="link-modal-card">
        <!-- Header -->
        <div style="padding:1rem 1.25rem; border-bottom:1px solid #e5e7eb; display:flex; align-items:center; justify-content:space-between; flex-shrink:0;">
            <h3 style="font-size:1rem; font-weight:600; color:#111827; margin:0;">Vincular invitado a inscrito</h3>
            <button type="button" onclick="closeLinkModal()"
                    style="background:none; border:none; cursor:pointer; color:#9ca3af; padding:0.25rem; line-height:1;"
                    aria-label="Cerrar">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form id="link-form" method="POST" action="" style="display:flex; flex-direction:column; flex:1; overflow:hidden;">
            @csrf
            <!-- Body scrollable -->
            <div style="padding:1rem 1.25rem; overflow-y:auto; flex:1; display:flex; flex-direction:column; gap:0.875rem;">
                <div style="background:#fef3c7; border:1px solid #fde68a; border-radius:0.375rem; padding:0.625rem 0.875rem;">
                    <p style="font-size:0.75rem; color:#92400e; margin:0 0 0.125rem;">Nombre en el archivo de asistencia</p>
                    <p id="modal-guest-name" style="font-size:0.875rem; font-weight:600; color:#78350f; margin:0;"></p>
                </div>

                <div>
                    <label for="inscription-search" style="display:block; font-size:0.8125rem; font-weight:500; color:#374151; margin-bottom:0.375rem;">
                        Buscar inscrito del programa
                    </label>
                    <input type="text"
                           id="inscription-search"
                           placeholder="Escribe el nombre..."
                           autocomplete="off"
                           oninput="filterInscriptions(this.value)"
                           style="width:100%; border:1px solid #d1d5db; border-radius:0.375rem; padding:0.5rem 0.75rem; font-size:0.875rem; outline:none; box-sizing:border-box;"
                           onfocus="this.style.borderColor='#6366f1'; this.style.boxShadow='0 0 0 2px #e0e7ff';"
                           onblur="this.style.borderColor='#d1d5db'; this.style.boxShadow='none';">
                </div>

                <div id="inscription-list"
                     style="border:1px solid #e5e7eb; border-radius:0.375rem; max-height:220px; overflow-y:auto; overflow-x:hidden;">
                    @foreach($programInscriptions as $ins)
                    <label class="inscription-option"
                           data-name="{{ strtolower($ins->getFullName()) }}"
                           style="display:flex; align-items:center; width:100%; box-sizing:border-box; padding:0.5rem 0.875rem; cursor:pointer; border-bottom:1px solid #f3f4f6; gap:0.625rem; white-space:nowrap;"
                           onmouseover="this.style.background='#eef2ff';"
                           onmouseout="this.style.background='';">
                        <input type="radio" name="inscription_id" value="{{ $ins->id }}"
                               style="accent-color:#6366f1; flex-shrink:0;">
                        <span style="font-size:0.8125rem; color:#1f2937; flex:1; overflow:hidden; text-overflow:ellipsis;">{{ $ins->getFullName() }}</span>
                        @if($ins->ci)
                            <span style="font-size:0.75rem; color:#9ca3af; flex-shrink:0; margin-left:0.5rem;">{{ $ins->ci }}</span>
                        @endif
                    </label>
                    @endforeach
                </div>

                <p style="font-size:0.75rem; color:#6b7280; margin:0;">
                    El alias se guardará permanentemente. Las clases anteriores donde aparezca este nombre también se actualizarán.
                </p>
            </div>

            <!-- Footer -->
            <div style="padding:0.875rem 1.25rem; border-top:1px solid #e5e7eb; display:flex; justify-content:flex-end; gap:0.625rem; flex-shrink:0;">
                <button type="button" onclick="closeLinkModal()"
                        style="padding:0.5rem 1rem; font-size:0.875rem; font-weight:500; color:#374151; background:#f3f4f6; border:none; border-radius:0.375rem; cursor:pointer;">
                    Cancelar
                </button>
                <button type="submit"
                        style="padding:0.5rem 1rem; font-size:0.875rem; font-weight:500; color:#fff; background:#4f46e5; border:none; border-radius:0.375rem; cursor:pointer;">
                    Vincular
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openLinkModal(attendanceId, guestName) {
    document.getElementById('modal-guest-name').textContent = guestName;
    document.getElementById('link-form').action =
        '{{ url("/programs/{$program->id}/attendances") }}/' + attendanceId + '/link-to-inscription';
    document.getElementById('inscription-search').value = '';
    filterInscriptions('');
    document.querySelectorAll('input[name="inscription_id"]').forEach(r => r.checked = false);
    document.getElementById('link-modal').classList.add('open');
    document.getElementById('inscription-search').focus();
}

function closeLinkModal() {
    document.getElementById('link-modal').classList.remove('open');
}

function filterInscriptions(query) {
    const q = query.toLowerCase();
    document.querySelectorAll('.inscription-option').forEach(el => {
        el.style.display = el.dataset.name.includes(q) ? '' : 'none';
    });
}

document.getElementById('link-modal').addEventListener('click', function(e) {
    if (e.target === this) closeLinkModal();
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeLinkModal();
});
</script>
@endcan

@endsection
