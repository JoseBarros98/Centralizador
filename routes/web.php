<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AcademicDashboardController;
use App\Http\Controllers\AccountingDashboardController;
use App\Http\Controllers\IncomeExpenseDashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DocumentFollowupController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\GradeFollowupController;
use App\Http\Controllers\GraduationCiteController;
use App\Http\Controllers\InscriptionController;
use App\Http\Controllers\ModuleClassController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\ModuleRecoveryController;
use App\Http\Controllers\ParticipantController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\ProgramAllocationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\SuggestionController;
use App\Http\Controllers\ContentPillarController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TypeOfArtController;
use App\Http\Controllers\ArtRequestController;
use App\Http\Controllers\ArtRequestDashboardController;
use App\Http\Controllers\ArtRequestModificationController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\MarketingTeamController;
use App\Http\Controllers\UniversityController;
use App\Http\Controllers\ProfessionController;
use App\Http\Controllers\PaymentRequestController;
use App\Http\Controllers\ManagementIncomeController;
use App\Http\Controllers\ManagementExpenseController;
use App\Http\Controllers\ManagementInvestmentController;
use App\Models\ProgramAllocation;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
  return redirect()->route('dashboard');
});

Route::middleware('auth')->group(function () {
  Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard')->middleware('role:admin|marketing|academic|academico|accountant|design');
  Route::get('/dashboard/academic', [AcademicDashboardController::class, 'index'])->name('dashboard.academic')->middleware('role:admin|academico|academic');
  Route::get('/dashboard/academic/area/{area}', [AcademicDashboardController::class, 'getAreaData'])->name('dashboard.academic.area')->middleware('role:admin|academico|academic');
  Route::get('/dashboard/academic/stats', [AcademicDashboardController::class, 'getDetailedStats'])->name('dashboard.academic.stats')->middleware('role:admin|academico|academic');
  Route::get('/dashboard/accounting', [AccountingDashboardController::class, 'index'])->name('dashboard.accounting')->middleware('role:admin|accountant');
  Route::get('/dashboard/income-expense', [IncomeExpenseDashboardController::class, 'index'])->name('dashboard.income-expense')->middleware('role:admin|accountant');

  Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
  Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
  Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

  // Rutas para usuarios
  Route::resource('users', UserController::class);
  Route::patch('/users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');

  // Rutas para programas
  Route::resource('programs', ProgramController::class);
  Route::patch('/programs/{program}/state', [ProgramController::class, 'updateState'])->name('programs.updateState');

  // Ruta para vista de inscritos de un programa
  Route::get('/programs/{program}/inscriptions', [ProgramController::class, 'inscriptions'])->name('programs.inscriptions');
  Route::get('/programs/{program}/inscriptions/{inscription}', [InscriptionController::class, 'showForProgram'])->name('programs.inscription_show');
    // Subida de documentos desde la vista de detalle de inscripción en programa
  Route::post('/programs/{program}/inscriptions/{inscription}/documents', [InscriptionController::class, 'uploadDocumentForProgram'])->name('programs.inscriptions.documents.upload');
  
  // Rutas para módulos (anidadas)
  Route::resource('programs.modules', ModuleController::class);
  Route::post('/programs/{program}/modules/{module}/reorder', [ModuleController::class, 'reorder'])->name('modules.reorder');
  
  // Rutas para módulos (no anidadas para compatibilidad)
  Route::get('/modules/{module}', [ModuleController::class, 'show'])->name('modules.show');
  Route::get('/modules/create', [ModuleController::class, 'create'])->name('modules.create');
  Route::post('/modules', [ModuleController::class, 'store'])->name('modules.store');
  Route::get('/modules/{module}/edit', [ModuleController::class, 'edit'])->name('modules.edit');
  Route::put('/modules/{module}', [ModuleController::class, 'update'])->name('modules.update');
  Route::patch('/modules/{module}/status', [ModuleController::class, 'updateStatus'])->name('modules.updateStatus');
  Route::delete('/modules/{module}', [ModuleController::class, 'destroy'])->name('modules.destroy');

  // Rutas para recuperatorio de módulos
  Route::get('/programs/{program}/modules/{module}/recovery', [ModuleRecoveryController::class, 'edit'])->name('modules.recovery.edit');
  Route::put('/programs/{program}/modules/{module}/recovery', [ModuleRecoveryController::class, 'update'])->name('modules.recovery.update');

  // Rutas para clases de módulos
  Route::resource('programs.modules.classes', ModuleClassController::class);
  
  // Rutas para clases (no anidadas para compatibilidad)
  Route::get('/classes/{class}', [ModuleClassController::class, 'show'])->name('classes.show');
  Route::get('/classes/create', [ModuleClassController::class, 'create'])->name('classes.create');
  Route::post('/classes', [ModuleClassController::class, 'store'])->name('classes.store');
  Route::get('/classes/{class}/edit', [ModuleClassController::class, 'edit'])->name('classes.edit');
  Route::put('/classes/{class}', [ModuleClassController::class, 'update'])->name('classes.update');
  Route::delete('/classes/{class}', [ModuleClassController::class, 'destroy'])->name('classes.destroy');

  // Rutas para calendario
  Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
  Route::get('/calendar/events', [CalendarController::class, 'getEvents'])->name('calendar.events');

  // Rutas para inscripciones
  Route::resource('inscriptions', InscriptionController::class);
  Route::post('/inscriptions/sync', [InscriptionController::class, 'sync'])->name('inscriptions.sync');
  Route::patch('/inscriptions/{inscription}/payment-history/{history}', [InscriptionController::class, 'updatePaymentHistory'])->name('inscriptions.payment-history.update');
  Route::delete('/inscriptions/{inscription}/payment-history/{history}', [InscriptionController::class, 'destroyPaymentHistory'])->name('inscriptions.payment-history.destroy');

  // Rutas para actualizar documentos de inscripciones
  Route::patch('/inscriptions/{inscription}/documents', [InscriptionController::class, 'updateDocuments'])->name('inscriptions.update-documents');
  Route::patch('/inscriptions/{inscription}/access', [InscriptionController::class, 'updateAccess'])->name('inscriptions.updateAccess');
  Route::patch('/inscriptions/{inscription}/academic-status', [InscriptionController::class, 'updateAcademicStatus'])->name('inscriptions.updateAcademicStatus');
  Route::post('/inscriptions/{inscription}/commitment-letter', [InscriptionController::class, 'uploadCommitmentLetter'])->name('inscriptions.upload-commitment-letter');
  Route::delete('/inscriptions/{inscription}/commitment-letter', [InscriptionController::class, 'deleteCommitmentLetter'])->name('inscriptions.delete-commitment-letter');
  Route::get('/inscriptions/{inscription}/commitment-letter', [InscriptionController::class, 'serveCommitmentLetter'])->name('inscriptions.commitment-letter');
  Route::patch('/inscriptions/{inscription}/document-observations', [InscriptionController::class, 'updateDocumentObservations'])->name('inscriptions.document-observations');

  // Rutas API para búsqueda en inscripciones
  Route::get('/api/inscriptions/search-universities', [InscriptionController::class, 'searchUniversities'])->name('inscriptions.search-universities');
  Route::get('/api/inscriptions/search-professions', [InscriptionController::class, 'searchProfessions'])->name('inscriptions.search-professions');

  // Rutas API para búsqueda de docentes
  Route::get('/api/teachers/search', [TeacherController::class, 'search'])->name('teachers.search');

  // Rutas para sugerencias de autocompletado
  Route::get('/suggestions/ci', [SuggestionController::class, 'checkCI'])->name('suggestions.ci');
  Route::get('/suggestions/first_name', [SuggestionController::class, 'firstName'])->name('suggestions.first_name');
  Route::get('/suggestions/paternal_surname', [SuggestionController::class, 'paternalSurname'])->name('suggestions.paternal_surname');
  Route::get('/suggestions/maternal_surname', [SuggestionController::class, 'maternalSurname'])->name('suggestions.maternal_surname');
  Route::get('/suggestions/residence', [SuggestionController::class, 'residence'])->name('suggestions.residence');
  Route::get('/suggestions/profession', [SuggestionController::class, 'profession'])->name('suggestions.profession');
  Route::get('/suggestions/phone', [SuggestionController::class, 'phone'])->name('suggestions.phone');
  

  // Rutas para participantes
  Route::get('/programs/{program}/participants', [ParticipantController::class, 'index'])->name('participants.index');
  Route::get('/programs/{program}/participants/upload', [ParticipantController::class, 'upload'])->name('participants.upload');
  Route::post('/programs/{program}/participants/process', [ParticipantController::class, 'process'])->name('participants.process');

  // Rutas para asistencias
  Route::get('/programs/{program}/modules/{module}/classes/{class}/attendances', [AttendanceController::class, 'show'])->name('attendances.show');
  Route::get('/programs/{program}/modules/{module}/classes/{class}/attendances/licenses', [AttendanceController::class, 'showWithLicenses'])->name('attendances.show_with_licenses');
  Route::post('/programs/{program}/modules/{module}/classes/{class}/attendances/{attendance}/grant-license', [AttendanceController::class, 'grantLicense'])->name('attendances.grant_license');
  Route::post('/programs/{program}/modules/{module}/classes/{class}/attendances/{attendance}/revoke-license', [AttendanceController::class, 'revokeLicense'])->name('attendances.revoke_license');
  Route::post('/programs/{program}/modules/{module}/classes/{class}/absent-inscriptions/{inscription}/grant-license', [AttendanceController::class, 'grantLicenseToAbsent'])->name('attendances.grant_license_absent');
  Route::post('/programs/{program}/modules/{module}/classes/{class}/absent-inscriptions/{inscription}/revoke-license', [AttendanceController::class, 'revokeLicenseFromAbsent'])->name('attendances.revoke_license_absent');
  Route::get('/programs/{program}/modules/{module}/classes/{class}/attendances/upload', [AttendanceController::class, 'uploadForm'])->name('attendances.upload');
  Route::post('/programs/{program}/modules/{module}/classes/{class}/attendances/upload', [AttendanceController::class, 'upload'])->name('attendances.process_upload');
  Route::get('/programs/{program}/modules/{module}/attendances/summary', [AttendanceController::class, 'summary'])->name('attendances.summary');
  Route::get('/programs/{program}/modules/{module}/attendances/summary/pdf', [AttendanceController::class, 'exportPdf'])->name('attendances.summary.pdf');
  Route::get('/programs/{program}/modules/{module}/classes/{class}/attendances/recalculate', [AttendanceController::class, 'recalculatePercentages'])->name('attendances.recalculate');

  // Rutas para calificaciones
  Route::get('/programs/{program}/modules/{module}/grades/upload', [GradeController::class, 'uploadForm'])->name('grades.upload');
  Route::post('/programs/{program}/modules/{module}/grades/upload', [GradeController::class, 'upload'])->name('grades.process_upload');
  Route::get('/programs/{program}/modules/{module}/grades/summary', [GradeController::class, 'summary'])->name('grades.summary');
  Route::patch('/programs/{program}/modules/{module}/grades/{grade}', [GradeController::class, 'updateGrade'])->name('grades.update');
  Route::get('/programs/{program}/grades/summary', [GradeController::class, 'programSummary'])->name('grades.programSummary');
  Route::get('/programs/{program}/grades/report', [GradeController::class, 'programSummaryReport'])->name('grades.programSummaryReport');
  Route::post('/programs/{program}/add-module', [GradeController::class, 'addModuleToProgram'])->name('programs.addModule');
  Route::delete('/programs/{program}/modules/{module}/remove', [GradeController::class, 'removeModuleFromProgram'])->name('programs.removeModule');
  Route::post('/programs/{program}/modules/{module}/reorder', [GradeController::class, 'reorderProgramModules'])->name('programs.reorderModules');
  Route::put('/programs/{program}/modules/{module}/update-hours', [GradeController::class, 'updateModuleHours'])->name('programs.updateModuleHours');
  Route::put('/programs/{program}/update-moodle-link', [GradeController::class, 'updateMoodleLink'])->name('programs.updateMoodleLink');
  Route::put('/programs/{program}/inscriptions/{inscription}/update-residence', [GradeController::class, 'updateParticipantResidence'])->name('programs.updateParticipantResidence');
  Route::put('/programs/{program}/inscriptions/{inscription}/update-name-field', [GradeController::class, 'updateParticipantNameField'])->name('programs.updateParticipantNameField');
  Route::put('/programs/{program}/inscriptions/{inscription}/update-requirement', [GradeController::class, 'updateParticipantRequirement'])->name('programs.updateParticipantRequirement');
  Route::put('/programs/{program}/inscriptions/{inscription}/update-grade', [GradeController::class, 'updateModuleGrade'])->name('programs.updateModuleGrade');

  // Rutas para seguimiento de calificaciones
  Route::get('/programs/{program}/modules/{module}/grades/{grade}/followup/create', [GradeFollowupController::class, 'create'])->name('grade_followups.create');
  Route::post('/programs/{program}/modules/{module}/grades/{grade}/followup', [GradeFollowupController::class, 'store'])->name('grade_followups.store');
  Route::get('/programs/{program}/modules/{module}/grades/{grade}/followup', [GradeFollowupController::class, 'show'])->name('grade_followups.show');
  Route::post('/programs/{program}/modules/{module}/grades/{grade}/followup/close', [GradeFollowupController::class, 'close'])->name('grade_followups.close');
  Route::get('/programs/{program}/modules/{module}/grades/{grade}/followup/create-new', [GradeFollowupController::class, 'createNew'])->name('grade_followups.create_new');
  Route::get('/programs/{program}/modules/{module}/grades/{grade}/followup/history', [GradeFollowupController::class, 'history'])->name('grade_followups.history');
  Route::get('/programs/{program}/modules/{module}/grades/{grade}/followup/{followup}', [GradeFollowupController::class, 'showFollowup'])->name('grade_followups.show_followup');
  
  // Rutas para contactos de seguimiento
  Route::get('/programs/{program}/modules/{module}/grades/{grade}/followup/contact/{type}', [GradeFollowupController::class, 'addContact'])->name('grade_followups.add_contact');
  Route::post('/programs/{program}/modules/{module}/grades/{grade}/followup/contact', [GradeFollowupController::class, 'storeContact'])->name('grade_followups.store_contact');
  Route::delete('/programs/{program}/modules/{module}/grades/{grade}/followup/contact/{contact}', [GradeFollowupController::class, 'deleteContact'])->name('grade_followups.delete_contact');
  
  // Rutas para recuperación de calificaciones
  Route::get('/programs/{program}/modules/{module}/grades/{grade}/followup/recovery', [GradeFollowupController::class, 'addRecovery'])->name('grade_followups.add_recovery');
  Route::post('/programs/{program}/modules/{module}/grades/{grade}/followup/recovery', [GradeFollowupController::class, 'storeRecovery'])->name('grade_followups.store_recovery');

  // Rutas para recibos (simplificadas)
  Route::get('/inscriptions/{inscription}/receipts/create', [ReceiptController::class, 'create'])->name('receipts.create');
  Route::post('/inscriptions/{inscription}/receipts', [ReceiptController::class, 'store'])->name('receipts.store');
  Route::delete('/inscriptions/{inscription}/receipts/{receipt}', [ReceiptController::class, 'destroy'])->name('receipts.destroy');
  
  // Ruta para servir archivos directamente
  Route::get('/receipts/file/{receipt}', [ReceiptController::class, 'serveFile'])->name('receipts.serve');

  // Rutas para documentos
  Route::get('/inscriptions/{inscription}/documents/create', [DocumentController::class, 'create'])->name('documents.create');
  Route::post('/inscriptions/{inscription}/documents', [DocumentController::class, 'store'])->name('documents.store');
  Route::delete('/inscriptions/{inscription}/documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');
  Route::get('/documents/file/{document}', [DocumentController::class, 'serve'])->name('documents.serve');
  Route::get('/documents/file/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
  
  // Rutas para seguimiento de documentos
  Route::get('/programs/{program}/inscriptions/{inscription}/document-followups/create', [DocumentFollowupController::class, 'create'])->name('document_followups.create');
  Route::post('/programs/{program}/inscriptions/{inscription}/document-followups', [DocumentFollowupController::class, 'store'])->name('document_followups.store');
  Route::get('/programs/{program}/inscriptions/{inscription}/document-followups', [DocumentFollowupController::class, 'show'])->name('document_followups.show');
  Route::get('/programs/{program}/inscriptions/{inscription}/document-followups/contact/{type}', [DocumentFollowupController::class, 'addContact'])->name('document_followups.add_contact');
  Route::post('/programs/{program}/inscriptions/{inscription}/document-followups/contact', [DocumentFollowupController::class, 'storeContact'])->name('document_followups.store_contact');
  Route::delete('/programs/{program}/inscriptions/{inscription}/document-followups/contact/{contact}', [DocumentFollowupController::class, 'deleteContact'])->name('document_followups.delete_contact');
  Route::post('/programs/{program}/inscriptions/{inscription}/document-followups/close', [DocumentFollowupController::class, 'close'])->name('document_followups.close');
  Route::get('/programs/{program}/inscriptions/{inscription}/document-followups/create-new', [DocumentFollowupController::class, 'createNew'])->name('document_followups.create_new');
  Route::get('/programs/{program}/inscriptions/{inscription}/document-followups/history', [DocumentFollowupController::class, 'history'])->name('document_followups.history');
  Route::get('/programs/{program}/inscriptions/{inscription}/document-followups/{followup}/view', [DocumentFollowupController::class, 'showFollowup'])->name('document_followups.show_followup');

    // Rutas para pilares de contenido
  Route::resource('content-pillars', ContentPillarController::class);
  
  // Rutas para archivos de pilares de contenido
  Route::post('/content-pillars/{contentPillar}/files', [ContentPillarController::class, 'uploadFile'])->name('content-pillars.files.upload')->middleware('large_upload');
  Route::delete('/content-pillars/files/{file}', [ContentPillarController::class, 'deleteFile'])->name('content-pillars.files.delete');
  Route::get('/content-pillars/files/{file}', [ContentPillarController::class, 'serveFile'])->name('content-pillars.files.serve');
  Route::get('/content-pillars/files/{file}/download', [ContentPillarController::class, 'downloadFile'])->name('content-pillars.files.download');
  Route::patch('/content-pillars/{contentPillar}/toggle-active', [ContentPillarController::class, 'toggleActive'])->name('content-pillars.toggle-active');

  // Rutas para tipos de arte
  Route::resource('type_of_arts', TypeOfArtController::class);

  // Rutas para archivos de tipo de arte
  Route::post('/type_of_arts/{typeOfArt}/files', [TypeOfArtController::class, 'uploadFile'])->name('type_of_arts.files.upload')->middleware('large_upload');
  Route::delete('/type_of_arts/files/{file}', [TypeOfArtController::class, 'deleteFile'])->name('type_of_arts.files.delete');
  Route::get('/type_of_arts/files/{file}', [TypeOfArtController::class, 'serveFile'])->name('type_of_arts.files.serve');
  Route::get('/type_of_arts/files/{file}/download', [TypeOfArtController::class, 'downloadFile'])->name('type_of_arts.files.download');
  Route::patch('/type_of_arts/{typeOfArt}/toggle-active', [TypeOfArtController::class, 'toggleActive'])->name('type_of_arts.toggle-active');

  // Art Requests
  Route::get('art-requests-dashboard', [ArtRequestDashboardController::class, 'index'])->name('art_requests.dashboard')->middleware('role:admin|design');
  Route::resource('art_requests', ArtRequestController::class);
  Route::post('art-requests/{artRequest}/files', [ArtRequestController::class, 'addFile'])->name('art_requests.files.add')->middleware('large_upload');
  Route::delete('art-request-files/{file}', [ArtRequestController::class, 'deleteFile'])->name('art_requests.files.destroy');
  Route::get('art-request-files/{file}/serve', [ArtRequestController::class, 'serveFile'])->name('art_requests.files.serve');
  Route::get('art-request-files/{file}/download', [ArtRequestController::class, 'downloadFile'])->name('art_requests.files.download');
  Route::patch('art-requests/{artRequest}/toggle-active', [ArtRequestController::class, 'toggleActive'])->name('art_requests.toggle_active');

  // Art Request Modifications
  Route::post('art-requests/{artRequest}/modifications', [ArtRequestModificationController::class, 'store'])->name('art-requests.modifications.store');
  Route::get('art-requests/{artRequest}/modifications', [ArtRequestModificationController::class, 'index'])->name('art-requests.modifications.index');
  Route::get('art-requests/{artRequest}/modifications/summary', [ArtRequestModificationController::class, 'summary'])->name('art-requests.modifications.summary');
  Route::patch('art-requests/{artRequest}/modifications/{modification}/toggle', [ArtRequestModificationController::class, 'toggle'])->name('art-requests.modifications.toggle');
  Route::delete('art-requests/{artRequest}/modifications/{modification}', [ArtRequestModificationController::class, 'destroy'])->name('art-requests.modifications.destroy');

    // Rutas para docentes
  Route::resource('teachers', TeacherController::class);
  
  // Ruta para calificar módulos de docentes
  Route::post('/teachers/{teacher}/modules/{module}/rate', [TeacherController::class, 'rateModule'])->name('teachers.modules.rate');

  // Rutas para archivos de tipo de arte
  Route::post('/teachers/{teacher}/files', [TeacherController::class, 'uploadFile'])->name('teachers.files.upload')->middleware('large_upload');
  Route::delete('/teachers/files/{file}', [TeacherController::class, 'deleteFile'])->name('teachers.files.delete');
  Route::get('/teachers/files/{file}', [TeacherController::class, 'serveFile'])->name('teachers.files.serve');
  Route::get('/teachers/files/{file}/download', [TeacherController::class, 'downloadFile'])->name('teachers.files.download');

  // Rutas para el módulo de equipos de marketing
  Route::resource('marketing-teams', MarketingTeamController::class);
  Route::patch('/marketing-teams/{marketingTeam}/deactivate', [MarketingTeamController::class, 'deactivate'])->name('marketing-teams.deactivate');
  Route::post('/marketing-teams/{marketingTeam}/members', [MarketingTeamController::class, 'addMember'])->name('marketing-teams.add-member');
  Route::patch('/marketing-teams/{marketingTeam}/members/{user}/deactivate', [MarketingTeamController::class, 'deactivateMember'])->name('marketing-teams.deactivate-member');
  Route::delete('/marketing-teams/{marketingTeam}/members/{user}', [MarketingTeamController::class, 'removeMember'])->name('marketing-teams.remove-member');
  Route::patch('/marketing-teams/{marketingTeam}/restore', [MarketingTeamController::class, 'restore'])->name('marketing-teams.restore');

  // Rutas para Google Drive (solo administradores)
  Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/google-drive/setup', [App\Http\Controllers\GoogleDriveController::class, 'setup'])->name('google-drive.setup');
    Route::get('/google-drive/callback', [App\Http\Controllers\GoogleDriveController::class, 'handleCallback'])->name('google-drive.callback');
    Route::get('/google-drive/diagnose', [App\Http\Controllers\GoogleDriveController::class, 'diagnose'])->name('google-drive.diagnose');
    Route::post('/google-drive/test', [App\Http\Controllers\GoogleDriveController::class, 'testConnection'])->name('google-drive.test');
    Route::post('/google-drive/create-folder', [App\Http\Controllers\GoogleDriveController::class, 'createMainFolder'])->name('google-drive.create-folder');

    Route::get('/google-calendar/setup', [App\Http\Controllers\GoogleCalendarController::class, 'setup'])->name('google-calendar.setup');
    Route::get('/google-calendar/callback', [App\Http\Controllers\GoogleCalendarController::class, 'handleCallback'])->name('google-calendar.callback');
    Route::post('/google-calendar/test', [App\Http\Controllers\GoogleCalendarController::class, 'testConnection'])->name('google-calendar.test');
    
    // Rutas para sincronización
    Route::get('/sync', [App\Http\Controllers\SyncController::class, 'index'])->name('sync.index');
    Route::post('/sync/programs', [App\Http\Controllers\SyncController::class, 'syncPrograms'])->name('sync.programs');
    Route::post('/sync/modules', [App\Http\Controllers\SyncController::class, 'syncModules'])->name('sync.modules');
    Route::post('/sync/inscriptions', [App\Http\Controllers\SyncController::class, 'syncInscriptions'])->name('sync.inscriptions');
    Route::post('/sync/all', [App\Http\Controllers\SyncController::class, 'syncAll'])->name('sync.all');
  });

  // Rutas para logs
  Route::get('/logs', [App\Http\Controllers\LogController::class, 'index'])->name('logs.index');
  Route::get('/logs/download', [App\Http\Controllers\LogController::class, 'download'])->name('logs.download');
  Route::post('/logs/clear', [App\Http\Controllers\LogController::class, 'clear'])->name('logs.clear');

  // Rutas para notificaciones
  Route::prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/', [App\Http\Controllers\NotificationController::class, 'index'])->name('index');
    Route::get('/unread', [App\Http\Controllers\NotificationController::class, 'unread'])->name('unread');
    Route::patch('/{id}/read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('mark-as-read');
    Route::patch('/mark-all-read', [App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
    Route::delete('/{id}', [App\Http\Controllers\NotificationController::class, 'destroy'])->name('destroy');
    Route::get('/count', [App\Http\Controllers\NotificationController::class, 'getUnreadCount'])->name('count');
  });

  // Rutas para universidades
  Route::resource('universities', UniversityController::class);
  Route::patch('/universities/{university}/toggle-status', [UniversityController::class, 'toggleStatus'])->name('universities.toggleStatus');
  
  // Rutas para profesiones
  Route::resource('professions', ProfessionController::class);
  Route::patch('/professions/{profession}/toggle-status', [ProfessionController::class, 'toggleStatus'])->name('professions.toggleStatus');

  // Rutas para solicitudes de pago a docentes
  Route::resource('payment_requests', PaymentRequestController::class);
  Route::patch('/payment_requests/{paymentRequest}/update-students', [PaymentRequestController::class, 'updateStudents'])->name('payment_requests.update_students');
  Route::get('/payment_requests/export/excel', [PaymentRequestController::class, 'export'])->name('payment_requests.export');
  Route::post('/payment_requests/buscar-programa', [PaymentRequestController::class, 'buscarPorPrograma'])->name('payment_requests.buscar_programa');
  Route::post('/payment_requests/buscar-docente', [PaymentRequestController::class, 'buscarPorTeacher'])->name('payment_requests.buscar_docente');
  Route::get('/payment_requests/module/{moduleId}/info', [PaymentRequestController::class, 'getModuleInfo'])->name('payment_requests.module_info');
  Route::patch('/payment_requests/{paymentRequest}/cambiar-estado', [PaymentRequestController::class, 'cambiarEstado'])->name('payment_requests.cambiar_estado');
  Route::get('/payment_requests/reporte', [PaymentRequestController::class, 'reporte'])->name('payment_requests.reporte');

  // Rutas para CITES de titulación
  Route::resource('graduation-cites', GraduationCiteController::class);
  Route::get('/api/graduation-cites/participants/search', [GraduationCiteController::class, 'searchParticipants'])->name('graduation-cites.participants.search');

  // Rutas para asignación por programa
  Route::resource('program-allocation', ProgramAllocationController::class)->only(['index', 'store', 'destroy']);
  Route::patch('/program-allocation/{allocation}/update-field', [ProgramAllocationController::class, 'updateField'])->name('program-allocation.update-field');
  Route::post('/program-allocation/import-previous-month', [ProgramAllocationController::class, 'importFromPreviousMonth'])->name('program-allocation.import-previous-month');

  // Rutas para ingresos por gestion
  Route::get('management-incomes',        [ManagementIncomeController::class, 'index'])->name('management-incomes.index');
  Route::get('management-incomes/items',  [ManagementIncomeController::class, 'getItemsForYear'])->name('management-incomes.items');
  Route::post('management-incomes/cell',  [ManagementIncomeController::class, 'upsertCell'])->name('management-incomes.cell');
  Route::patch('management-incomes/item', [ManagementIncomeController::class, 'renameItem'])->name('management-incomes.renameItem');
  Route::delete('management-incomes/item',[ManagementIncomeController::class, 'destroyItem'])->name('management-incomes.destroyItem');

  // Rutas para inversiones por gestion
  Route::get('management-investments',        [ManagementInvestmentController::class, 'index'])->name('management-investments.index');
  Route::get('management-investments/items',  [ManagementInvestmentController::class, 'getItemsForYear'])->name('management-investments.items');
  Route::post('management-investments/cell',  [ManagementInvestmentController::class, 'upsertCell'])->name('management-investments.cell');
  Route::patch('management-investments/item', [ManagementInvestmentController::class, 'renameItem'])->name('management-investments.renameItem');
  Route::delete('management-investments/item',[ManagementInvestmentController::class, 'destroyItem'])->name('management-investments.destroyItem');

  // Rutas para egresos por gestion
  Route::get('management-expenses',        [ManagementExpenseController::class, 'index'])->name('management-expenses.index');
  Route::get('management-expenses/items',  [ManagementExpenseController::class, 'getItemsForYear'])->name('management-expenses.items');
  Route::post('management-expenses/cell',  [ManagementExpenseController::class, 'upsertCell'])->name('management-expenses.cell');
  Route::patch('management-expenses/item', [ManagementExpenseController::class, 'renameItem'])->name('management-expenses.renameItem');
  Route::delete('management-expenses/item',[ManagementExpenseController::class, 'destroyItem'])->name('management-expenses.destroyItem');

  // Rutas para vinculación de asesores
  Route::middleware('permission:inscriptions.sync')->prefix('advisors')->name('advisors.')->group(function () {
    Route::get('/link', [App\Http\Controllers\AdvisorLinkingController::class, 'index'])->name('link.index');
    Route::post('/link', [App\Http\Controllers\AdvisorLinkingController::class, 'linkAdvisor'])->name('link.store');
    Route::post('/auto-link', [App\Http\Controllers\AdvisorLinkingController::class, 'autoLink'])->name('link.auto');
    Route::get('/stats', [App\Http\Controllers\AdvisorLinkingController::class, 'getStats'])->name('link.stats');
  });
});

// API para autocompletado y verificación de CI
Route::middleware(['auth'])->prefix('api')->group(function () {
    Route::get('/suggestions/residence', [App\Http\Controllers\SuggestionController::class, 'getResidenceSuggestions']);
    Route::get('/suggestions/profession', [App\Http\Controllers\SuggestionController::class, 'getProfessionSuggestions']);
    Route::get('/check-ci/{ci}', [App\Http\Controllers\SuggestionController::class, 'checkCI']);
});

require __DIR__.'/auth.php';
