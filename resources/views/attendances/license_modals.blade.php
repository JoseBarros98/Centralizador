<!-- Modal para otorgar licencia -->
<div id="licenseModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-10 mx-auto p-4 border w-full max-w-md shadow-lg rounded-md bg-white">
        <div class="mt-2">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-lg font-medium text-gray-900">
                    Otorgar Licencia/Permiso
                </h3>
                <button onclick="closeLicenseModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form id="licenseForm" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Estudiante:</label>
                    <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded text-center" id="studentName"></p>
                </div>
                
                <div class="mb-3">
                    <label for="license_type" class="block text-sm font-medium text-gray-700 mb-1">
                        Tipo de Licencia/Permiso
                    </label>
                    <select name="license_type" id="license_type" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required>
                        <option value="">Seleccionar...</option>
                        <option value="permiso">🔹 Permiso General</option>
                        <option value="licencia_medica">🏥 Licencia Médica</option>
                        <option value="licencia_laboral">💼 Licencia Laboral</option>
                        <option value="emergencia_familiar">👨‍👩‍👧‍👦 Emergencia Familiar</option>
                        <option value="otro">📝 Otro</option>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label for="license_notes" class="block text-sm font-medium text-gray-700 mb-1">
                        Notas/Observaciones
                    </label>
                    <textarea name="license_notes" id="license_notes" rows="2" 
                              class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 resize-none"
                              placeholder="Detalles sobre la licencia o permiso..."></textarea>
                </div>
                
                <div class="flex items-center justify-end space-x-2">
                    <button type="button" onclick="closeLicenseModal()"
                            class="px-3 py-2 bg-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300 transition-colors">
                        Cancelar
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors">
                        Otorgar Licencia
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para ver detalles de licencia -->
<div id="licenseDetailsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-10 mx-auto p-4 border w-full max-w-md shadow-lg rounded-md bg-white">
        <div class="mt-2">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-lg font-medium text-gray-900">
                    Detalles de Licencia/Permiso
                </h3>
                <button onclick="closeLicenseDetailsModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Estudiante:</label>
                    <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded text-center" id="detailsStudentName"></p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo:</label>
                    <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded text-center" id="detailsLicenseType"></p>
                </div>
                
                <div id="detailsNotesSection">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notas:</label>
                    <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded" id="detailsLicenseNotes"></p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Otorgado por:</label>
                    <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded text-center" id="detailsGrantedBy"></p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de otorgamiento:</label>
                    <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded text-center" id="detailsGrantedAt"></p>
                </div>
            </div>
            
            <div class="flex items-center justify-center mt-4">
                <button type="button" onclick="closeLicenseDetailsModal()"
                        class="px-4 py-2 bg-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300 transition-colors">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Función para otorgar licencia a participante ausente (inscrito que no asistió)
function grantLicenseForAbsent(inscriptionId, studentName, programId, moduleId, classId) {
    currentInscriptionId = inscriptionId;
    currentProgramId = programId;
    currentModuleId = moduleId;
    currentClassId = classId;
    
    document.getElementById('studentName').textContent = studentName;
    document.getElementById('licenseForm').action = `/programs/${programId}/modules/${moduleId}/classes/${classId}/absent-inscriptions/${inscriptionId}/grant-license`;
    document.getElementById('licenseModal').classList.remove('hidden');
}

// Función para revocar licencia de participante ausente
function revokeLicenseForAbsent(inscriptionId, programId, moduleId, classId) {
    if (confirm('¿Está seguro que desea revocar esta licencia/permiso?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/programs/${programId}/modules/${moduleId}/classes/${classId}/absent-inscriptions/${inscriptionId}/revoke-license`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        form.appendChild(csrfToken);
        document.body.appendChild(form);
        form.submit();
    }
}

// Variables globales para el modal de licencia
let currentAttendanceId = null;
let currentInscriptionId = null;
let currentProgramId = null;
let currentModuleId = null;
let currentClassId = null;

// Función para abrir el modal de otorgar licencia
function openLicenseModal(attendanceId, studentName, programId, moduleId, classId) {
    currentAttendanceId = attendanceId;
    currentProgramId = programId;
    currentModuleId = moduleId;
    currentClassId = classId;
    
    document.getElementById('studentName').textContent = studentName;
    document.getElementById('licenseForm').action = `/programs/${programId}/modules/${moduleId}/classes/${classId}/attendances/${attendanceId}/grant-license`;
    document.getElementById('licenseModal').classList.remove('hidden');
}

// Función para cerrar el modal de otorgar licencia
function closeLicenseModal() {
    document.getElementById('licenseModal').classList.add('hidden');
    document.getElementById('licenseForm').reset();
    currentAttendanceId = null;
    currentProgramId = null;
    currentModuleId = null;
    currentClassId = null;
}

// Función para mostrar detalles de licencia
function showLicenseDetails(attendanceId, studentName, licenseType, licenseNotes, grantedBy, grantedAt) {
    const licenseTypeTexts = {
        'permiso': '🔹 Permiso General',
        'licencia_medica': '🏥 Licencia Médica',
        'licencia_laboral': '💼 Licencia Laboral',
        'emergencia_familiar': '👨‍👩‍👧‍👦 Emergencia Familiar',
        'otro': '📝 Otro'
    };
    
    document.getElementById('detailsStudentName').textContent = studentName;
    document.getElementById('detailsLicenseType').textContent = licenseTypeTexts[licenseType] || licenseType;
    
    // Mostrar u ocultar la sección de notas
    const notesSection = document.getElementById('detailsNotesSection');
    if (licenseNotes && licenseNotes.trim() !== '') {
        document.getElementById('detailsLicenseNotes').textContent = licenseNotes;
        notesSection.style.display = 'block';
    } else {
        notesSection.style.display = 'none';
    }
    
    document.getElementById('detailsGrantedBy').textContent = grantedBy || 'N/A';
    document.getElementById('detailsGrantedAt').textContent = grantedAt || 'N/A';
    
    document.getElementById('licenseDetailsModal').classList.remove('hidden');
}

// Función para cerrar el modal de detalles
function closeLicenseDetailsModal() {
    document.getElementById('licenseDetailsModal').classList.add('hidden');
}

// Función para revocar licencia
function revokeLicense(attendanceId, programId, moduleId, classId) {
    if (confirm('¿Está seguro que desea revocar esta licencia/permiso?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/programs/${programId}/modules/${moduleId}/classes/${classId}/attendances/${attendanceId}/revoke-license`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        form.appendChild(csrfToken);
        document.body.appendChild(form);
        form.submit();
    }
}

// Cerrar modales al hacer clic fuera de ellos
document.getElementById('licenseModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeLicenseModal();
    }
});

document.getElementById('licenseDetailsModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeLicenseDetailsModal();
    }
});

// Cerrar modales con la tecla Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeLicenseModal();
        closeLicenseDetailsModal();
    }
});

// Validación del formulario de licencia
document.getElementById('licenseForm').addEventListener('submit', function(e) {
    const licenseType = document.getElementById('license_type').value;
    if (!licenseType) {
        e.preventDefault();
        alert('Por favor seleccione un tipo de licencia/permiso.');
        return false;
    }
});
</script>

<style>
/* Estilos adicionales para los modales */
.modal-overlay {
    backdrop-filter: blur(4px);
}

/* Animaciones suaves para los modales */
#licenseModal, #licenseDetailsModal {
    transition: opacity 0.25s ease-out;
}

#licenseModal.hidden, #licenseDetailsModal.hidden {
    opacity: 0;
    pointer-events: none;
}

#licenseModal:not(.hidden), #licenseDetailsModal:not(.hidden) {
    opacity: 1;
    pointer-events: auto;
}

/* Estilos para los botones de estado */
.status-badge {
    transition: all 0.2s ease-in-out;
}

.status-badge:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
</style>