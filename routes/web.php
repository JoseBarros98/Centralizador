<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DocumentFollowupController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\GradeFollowupController;
use App\Http\Controllers\InscriptionController;
use App\Http\Controllers\ModuleClassController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\ModuleRecoveryController;
use App\Http\Controllers\ParticipantController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\SuggestionController;
use App\Http\Controllers\ContentPillarController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TypeOfArtController;
use App\Http\Controllers\ArtRequestController;
use App\Http\Controllers\TeacherController;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
  return redirect()->route('dashboard');
});

Route::middleware('auth')->group(function () {
  Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

  Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
  Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
  Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

  // Rutas para usuarios
  Route::resource('users', UserController::class);
  Route::patch('/users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');

  // Rutas para programas
  Route::resource('programs', ProgramController::class);

  // Ruta para vista de inscritos de un programa
  Route::get('/programs/{program}/inscriptions', [ProgramController::class, 'inscriptions'])->name('programs.inscriptions');
  Route::get('/programs/{program}/inscriptions/{inscription}', [InscriptionController::class, 'showForProgram'])->name('programs.inscription_show');
    // Subida de documentos desde la vista de detalle de inscripción en programa
  Route::post('/programs/{program}/inscriptions/{inscription}/documents', [InscriptionController::class, 'uploadDocumentForProgram'])->name('programs.inscriptions.documents.upload');
  
  // Rutas para módulos (anidadas)
  Route::resource('programs.modules', ModuleController::class);
  
  // Rutas para módulos (no anidadas para compatibilidad)
  Route::get('/modules/{module}', [ModuleController::class, 'show'])->name('modules.show');
  Route::get('/modules/create', [ModuleController::class, 'create'])->name('modules.create');
  Route::post('/modules', [ModuleController::class, 'store'])->name('modules.store');
  Route::get('/modules/{module}/edit', [ModuleController::class, 'edit'])->name('modules.edit');
  Route::put('/modules/{module}', [ModuleController::class, 'update'])->name('modules.update');
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

  // Rutas para actualizar documentos de inscripciones
  Route::patch('/inscriptions/{inscription}/documents', [InscriptionController::class, 'updateDocuments'])->name('inscriptions.update-documents');
  Route::patch('/inscriptions/{inscription}/access', [InscriptionController::class, 'updateAccess'])->name('inscriptions.updateAccess');
  Route::patch('/inscriptions/{inscription}/academic-status', [InscriptionController::class, 'updateAcademicStatus'])->name('inscriptions.updateAcademicStatus');
  Route::post('/inscriptions/{inscription}/commitment-letter', [InscriptionController::class, 'uploadCommitmentLetter'])->name('inscriptions.upload-commitment-letter');
  Route::delete('/inscriptions/{inscription}/commitment-letter', [InscriptionController::class, 'deleteCommitmentLetter'])->name('inscriptions.delete-commitment-letter');
  Route::get('/inscriptions/{inscription}/commitment-letter', [InscriptionController::class, 'serveCommitmentLetter'])->name('inscriptions.commitment-letter');
  Route::patch('/inscriptions/{inscription}/document-observations', [InscriptionController::class, 'updateDocumentObservations'])->name('inscriptions.document-observations');

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
  Route::get('/programs/{program}/modules/{module}/classes/{class}/attendances/upload', [AttendanceController::class, 'uploadForm'])->name('attendances.upload');
  Route::post('/programs/{program}/modules/{module}/classes/{class}/attendances/upload', [AttendanceController::class, 'upload'])->name('attendances.process_upload');
  Route::get('/programs/{program}/modules/{module}/attendances/summary', [AttendanceController::class, 'summary'])->name('attendances.summary');
  Route::get('/programs/{program}/modules/{module}/attendances/summary/pdf', [AttendanceController::class, 'exportPdf'])->name('attendances.summary.pdf');
  Route::get('/programs/{program}/modules/{module}/classes/{class}/attendances/recalculate', [AttendanceController::class, 'recalculatePercentages'])->name('attendances.recalculate');

  // Rutas para calificaciones
  Route::get('/programs/{program}/modules/{module}/grades/upload', [GradeController::class, 'uploadForm'])->name('grades.upload');
  Route::post('/programs/{program}/modules/{module}/grades/upload', [GradeController::class, 'upload'])->name('grades.process_upload');
  Route::get('/programs/{program}/modules/{module}/grades/summary', [GradeController::class, 'summary'])->name('grades.summary');
  Route::get('/programs/{program}/grades/summary', [GradeController::class, 'programSummary'])->name('grades.programSummary');

  // Rutas para seguimiento de calificaciones
  Route::get('/programs/{program}/modules/{module}/grades/{grade}/followup/create', [GradeFollowupController::class, 'create'])->name('grade_followups.create');
  Route::post('/programs/{program}/modules/{module}/grades/{grade}/followup', [GradeFollowupController::class, 'store'])->name('grade_followups.store');
  Route::get('/programs/{program}/modules/{module}/grades/{grade}/followup', [GradeFollowupController::class, 'show'])->name('grade_followups.show');
  
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
  
  // Rutas para seguimiento de documentos
  Route::get('/programs/{program}/inscriptions/{inscription}/document-followups/create', [DocumentFollowupController::class, 'create'])->name('document_followups.create');
  Route::post('/programs/{program}/inscriptions/{inscription}/document-followups', [DocumentFollowupController::class, 'store'])->name('document_followups.store');
  Route::get('/programs/{program}/inscriptions/{inscription}/document-followups', [DocumentFollowupController::class, 'show'])->name('document_followups.show');
  Route::get('/programs/{program}/inscriptions/{inscription}/document-followups/contact/{type}', [DocumentFollowupController::class, 'addContact'])->name('document_followups.add_contact');
  Route::post('/programs/{program}/inscriptions/{inscription}/document-followups/contact', [DocumentFollowupController::class, 'storeContact'])->name('document_followups.store_contact');
  Route::delete('/programs/{program}/inscriptions/{inscription}/document-followups/contact/{contact}', [DocumentFollowupController::class, 'deleteContact'])->name('document_followups.delete_contact');

    // Rutas para pilares de contenido
  Route::resource('content-pillars', ContentPillarController::class);
  
  // Rutas para archivos de pilares de contenido
  Route::post('/content-pillars/{contentPillar}/files', [ContentPillarController::class, 'uploadFile'])->name('content-pillars.files.upload');
  Route::delete('/content-pillars/files/{file}', [ContentPillarController::class, 'deleteFile'])->name('content-pillars.files.delete');
  Route::get('/content-pillars/files/{file}', [ContentPillarController::class, 'serveFile'])->name('content-pillars.files.serve');
  Route::get('/content-pillars/files/{file}/download', [ContentPillarController::class, 'downloadFile'])->name('content-pillars.files.download');
  Route::patch('/content-pillars/{contentPillar}/toggle-active', [ContentPillarController::class, 'toggleActive'])->name('content-pillars.toggle-active');

  // Rutas para tipos de arte
  Route::resource('type_of_arts', TypeOfArtController::class);

  // Rutas para archivos de tipo de arte
  Route::post('/type_of_arts/{typeOfArt}/files', [TypeOfArtController::class, 'uploadFile'])->name('type_of_arts.files.upload');
  Route::delete('/type_of_arts/files/{file}', [TypeOfArtController::class, 'deleteFile'])->name('type_of_arts.files.delete');
  Route::get('/type_of_arts/files/{file}', [TypeOfArtController::class, 'serveFile'])->name('type_of_arts.files.serve');
  Route::get('/type_of_arts/files/{file}/download', [TypeOfArtController::class, 'downloadFile'])->name('type_of_arts.files.download');
  Route::patch('/type_of_arts/{typeOfArt}/toggle-active', [TypeOfArtController::class, 'toggleActive'])->name('type_of_arts.toggle-active');

  // Art Requests
  Route::resource('art_requests', ArtRequestController::class);
  Route::post('art-requests/{artRequest}/files', [ArtRequestController::class, 'addFile'])->name('art_requests.files.add');
  Route::delete('art-request-files/{file}', [ArtRequestController::class, 'deleteFile'])->name('art_requests.files.destroy');
  Route::get('art-request-files/{file}/serve', [ArtRequestController::class, 'serveFile'])->name('art_requests.files.serve');
  Route::get('art-request-files/{file}/download', [ArtRequestController::class, 'downloadFile'])->name('art_requests.files.download');
  Route::patch('art-requests/{artRequest}/toggle-active', [ArtRequestController::class, 'toggleActive'])->name('art_requests.toggle_active');

    // Rutas para docentes
  Route::resource('teachers', TeacherController::class);

  // Rutas para archivos de tipo de arte
  Route::post('/teachers/{teacher}/files', [TeacherController::class, 'uploadFile'])->name('teachers.files.upload');
  Route::delete('/teachers/files/{file}', [TeacherController::class, 'deleteFile'])->name('teachers.files.delete');
  Route::get('/teachers/files/{file}', [TeacherController::class, 'serveFile'])->name('teachers.files.serve');
  Route::get('/teachers/files/{file}/download', [TeacherController::class, 'downloadFile'])->name('teachers.files.download');
});

// API para autocompletado y verificación de CI
Route::middleware(['auth'])->prefix('api')->group(function () {
    Route::get('/suggestions/residence', [App\Http\Controllers\SuggestionController::class, 'getResidenceSuggestions']);
    Route::get('/suggestions/profession', [App\Http\Controllers\SuggestionController::class, 'getProfessionSuggestions']);
    Route::get('/check-ci/{ci}', [App\Http\Controllers\SuggestionController::class, 'checkCI']);
});

require __DIR__.'/auth.php';
