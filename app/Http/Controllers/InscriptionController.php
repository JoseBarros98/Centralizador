<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Inscription;
use App\Models\InscriptionPaymentHistory;
use App\Models\Program;
use App\Services\InscriptionMonthlyViewService;
use App\Models\Receipt;
use App\Models\Document;
use App\Models\University;
use App\Models\Profession;
use App\Notifications\InscriptionChecklistUpdatedNotification;
use App\Services\AdvisorLinkingService;
use App\Services\GoogleDriveService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InscriptionController extends Controller
{
    protected $googleDriveService;
    private ?int $externalSystemUserId = null;

    public function __construct(GoogleDriveService $googleDriveService)
    {
        $this->googleDriveService = $googleDriveService;
        $this->middleware(function ($request, $next) {
            if (!$this->canViewInscriptions($request->user())) {
                abort(403, 'No tienes permiso para ver inscripciones.');
            }

            return $next($request);
        })->only(['index', 'show', 'serveCommitmentLetter']);
        $this->middleware(['permission:inscription.create'])->only(['create', 'store']);
        $this->middleware(function ($request, $next) {
            if (!$this->canEditInscriptions($request->user())) {
                abort(403, 'No tienes permiso para editar inscripciones.');
            }

            return $next($request);
        })->only(['edit', 'update', 'updateDocuments', 'uploadCommitmentLetter', 'deleteCommitmentLetter', 'updateDocumentObservations']);
        $this->middleware(['permission:inscription.delete'])->only(['destroy']);
    }

    private function canViewInscriptions(?User $user): bool
    {
        return $user !== null
            && ($user->hasPermissionTo('inscription.view') || $user->leadsActiveMarketingTeam());
    }

    private function canEditInscriptions(?User $user): bool
    {
        return $user !== null
            && ($user->hasPermissionTo('inscription.edit') || $user->leadsActiveMarketingTeam());
    }

    private function getExternalSystemUserId(): ?int
    {
        if ($this->externalSystemUserId !== null) {
            return $this->externalSystemUserId;
        }

        $externalSystemUserId = User::where('email', 'sistema.externo@centtest.local')->value('id');
        $this->externalSystemUserId = $externalSystemUserId ? (int) $externalSystemUserId : 0;

        return $this->externalSystemUserId ?: null;
    }

    private function isExternalUnassignedInscription(Inscription $inscription): bool
    {
        $externalSystemUserId = $this->getExternalSystemUserId();

        return $externalSystemUserId !== null
            && (int) $inscription->created_by === $externalSystemUserId;
    }

    /**
     * Verificar si el usuario tiene permiso para modificar la inscripción
     * Los asesores (marketing) solo pueden modificar sus propias inscripciones
     */
    private function authorizeInscriptionAccess(Inscription $inscription, string $ability = 'view')
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $abilityPermissions = [
            'view' => 'inscription.view',
            'edit' => 'inscription.edit',
            'delete' => 'inscription.delete',
        ];

        if (!$user) {
            abort(403, 'No tienes permiso para acceder a esta inscripción.');
        }

        if ($user->hasRole(['admin', 'academic'])) {
            return;
        }

        if ($this->isExternalUnassignedInscription($inscription) && $user->leadsActiveMarketingTeam()) {
            return;
        }

        if ($user->hasRole('marketing') && $inscription->created_by === $user->id) {
            return;
        }

        if (!$user->hasRole('marketing')
            && isset($abilityPermissions[$ability])
            && $user->hasPermissionTo($abilityPermissions[$ability])) {
            return;
        }

        abort(403, 'No tienes permiso para acceder a esta inscripción.');
    }

    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $externalSystemUserId = $this->getExternalSystemUserId();
        $isMarketingAdvisor = $user->hasRole('marketing') && !$user->hasRole(['admin', 'academic']);
        $isTeamLeader = $user->leadsActiveMarketingTeam();
        
        // Función auxiliar para construir la query con los filtros comunes (sin estado)
        $buildCommonQuery = function() use ($request, $user, $externalSystemUserId, $isMarketingAdvisor, $isTeamLeader) {
            $query = Inscription::with(['programs', 'creator', 'updater', 'paymentHistory']);
            
            // Filtrar por asesor
            if ($isMarketingAdvisor) {
                $query->where(function ($advisorQuery) use ($user, $isTeamLeader, $externalSystemUserId) {
                    $advisorQuery->where('created_by', $user->id);

                    if ($isTeamLeader && $externalSystemUserId !== null) {
                        $advisorQuery->orWhere('created_by', $externalSystemUserId);
                    }
                });
            } elseif (!$user->hasPermissionTo('inscription.view') && $isTeamLeader) {
                $query->where('created_by', $externalSystemUserId ?? 0);
            }
            
            // Filtros de fecha
            if ($request->has('month') && $request->month != 'all' && $request->has('year') && $request->year != 'all') {
                $monthStart = Carbon::createFromDate($request->year, $request->month, 1)->startOfMonth();
                $monthEnd = $monthStart->copy()->endOfMonth();
                $query->where(function ($q) use ($monthStart, $monthEnd) {
                    $q->whereBetween('inscription_date', [$monthStart, $monthEnd])
                      ->orWhereHas('paymentHistory', function ($subQ) use ($monthStart, $monthEnd) {
                          $subQ->whereBetween('status_date', [$monthStart, $monthEnd]);
                      });
                });
            } elseif ($request->has('year') && $request->year != 'all') {
                $yearStart = Carbon::createFromDate($request->year, 1, 1)->startOfYear();
                $yearEnd = $yearStart->copy()->endOfYear();
                $query->where(function ($q) use ($yearStart, $yearEnd) {
                    $q->whereBetween('inscription_date', [$yearStart, $yearEnd])
                      ->orWhereHas('paymentHistory', function ($subQ) use ($yearStart, $yearEnd) {
                          $subQ->whereBetween('status_date', [$yearStart, $yearEnd]);
                      });
                });
            } elseif ($request->has('month') && $request->month != 'all') {
                $monthStart = Carbon::createFromDate(Carbon::now()->year, $request->month, 1)->startOfMonth();
                $monthEnd = $monthStart->copy()->endOfMonth();
                $query->where(function ($q) use ($monthStart, $monthEnd) {
                    $q->whereBetween('inscription_date', [$monthStart, $monthEnd])
                      ->orWhereHas('paymentHistory', function ($subQ) use ($monthStart, $monthEnd) {
                          $subQ->whereBetween('status_date', [$monthStart, $monthEnd]);
                      });
                });
            } else {
                // Por defecto, mostrar el mes actual
                $monthStart = Carbon::now()->startOfMonth();
                $monthEnd = Carbon::now()->endOfMonth();
                $query->where(function ($q) use ($monthStart, $monthEnd) {
                    $q->whereBetween('inscription_date', [$monthStart, $monthEnd])
                      ->orWhereHas('paymentHistory', function ($subQ) use ($monthStart, $monthEnd) {
                          $subQ->whereBetween('status_date', [$monthStart, $monthEnd]);
                      });
                });
            }
            
            // Filtro por programa (relación many-to-many vía pivot inscription_program)
            if ($request->has('program_id') && $request->program_id != '') {
                $query->whereHas('programs', function ($q) use ($request) {
                    $q->where('programs.id', $request->program_id);
                });
            }
            
            // Filtro por usuario
            if ($request->has('created_by') && $request->created_by != '') {
                if (!$user->hasRole('marketing') || $user->hasRole(['admin', 'academic'])) {
                    $query->where('created_by', $request->created_by);
                }
            }
            
            // Campo de búsqueda
            if ($request->has('search') && $request->search != '') {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('code', 'like', "%{$search}%")
                      ->orWhere('full_name', 'like', "%{$search}%")
                      ->orWhere('ci', 'like', "%{$search}%");
                });
            }
            
            return $query;
        };
        
        // Construir la query para estadísticas (sin filtro de estado)
        $statsQuery = $buildCommonQuery();
        
        // Construir la query base (con todos los filtros) para la tabla
        $baseQuery = $buildCommonQuery();
        
        // Aplicar filtro de estado al $baseQuery (DESPUÉS de construir para que no afecte las estadísticas)
        if ($request->has('status') && $request->status != '') {
            $baseQuery->where('local_payment_status', $request->status);
        }
        
        // Obtener inscripciones paginadas para la tabla
        $inscriptions = $baseQuery->orderBy('inscription_date', 'desc')->paginate(15)->withQueryString();
        
        // Setear el mes/año display para cada inscripción
        // Esto permite que las vistas muestren el estado de pago del mes seleccionado
        $displayMonth = $request->input('month');
        $displayYear = $request->input('year');
        
        // Solo setear display_month y display_year si NO están en 'all'
        if ($displayMonth && $displayMonth != 'all' && $displayYear && $displayYear != 'all') {
            foreach ($inscriptions as $inscription) {
                $inscription->display_month = $displayMonth;
                $inscription->display_year = $displayYear;
            }
        }
        
        // Obtener programas que tienen inscripciones en el período seleccionado
        $programs = Program::whereHas('inscriptions', function ($q) use ($request, $user, $externalSystemUserId, $isMarketingAdvisor, $isTeamLeader) {
            // Mismo rango de fechas que el filtro principal
            if ($request->has('month') && $request->month != 'all' && $request->has('year') && $request->year != 'all') {
                $monthStart = Carbon::createFromDate($request->year, $request->month, 1)->startOfMonth();
                $monthEnd   = $monthStart->copy()->endOfMonth();
                $q->whereBetween('inscription_date', [$monthStart, $monthEnd]);
            } elseif ($request->has('year') && $request->year != 'all') {
                $yearStart = Carbon::createFromDate($request->year, 1, 1)->startOfYear();
                $yearEnd   = $yearStart->copy()->endOfYear();
                $q->whereBetween('inscription_date', [$yearStart, $yearEnd]);
            } elseif ($request->has('month') && $request->month != 'all') {
                $monthStart = Carbon::createFromDate(Carbon::now()->year, $request->month, 1)->startOfMonth();
                $monthEnd   = $monthStart->copy()->endOfMonth();
                $q->whereBetween('inscription_date', [$monthStart, $monthEnd]);
            } else {
                $monthStart = Carbon::now()->startOfMonth();
                $monthEnd   = Carbon::now()->endOfMonth();
                $q->whereBetween('inscription_date', [$monthStart, $monthEnd]);
            }

            // Mismo filtro de asesor que el filtro principal
            if ($isMarketingAdvisor) {
                $q->where(function ($aq) use ($user, $isTeamLeader, $externalSystemUserId) {
                    $aq->where('created_by', $user->id);
                    if ($isTeamLeader && $externalSystemUserId !== null) {
                        $aq->orWhere('created_by', $externalSystemUserId);
                    }
                });
            } elseif (!$user->hasPermissionTo('inscription.view') && $isTeamLeader) {
                $q->where('created_by', $externalSystemUserId ?? 0);
            }
        })->orderBy('name')->pluck('name', 'id');
        
        // Obtener usuarios que han creado inscripciones
        // Solo mostrar el filtro de creadores si el usuario no es marketing o si es admin/academic
        $creators = collect([]);
        if (!$isMarketingAdvisor) {
            $creators = User::role('marketing')
                            ->whereIn('id', Inscription::select('created_by')->distinct()->pluck('created_by'))
                            ->where('active', true)
                            ->pluck('name', 'id');

            if (!$user->hasPermissionTo('inscription.view') && $isTeamLeader && $externalSystemUserId !== null) {
                $creators = collect();
            }
        }
        
        // Calcular estadísticas directamente de la query
        $totalCount = $statsQuery->count();
        $otraSedeCount = (clone $statsQuery)
            ->where(function ($query) use ($externalSystemUserId) {
                if ($externalSystemUserId !== null) {
                    $query->where('created_by', $externalSystemUserId);
                }

                $query->orWhereNull('created_by');
            })
            ->count();
        $inscritosLatamCount = max(0, $totalCount - $otraSedeCount);
        $completoCount = (clone $statsQuery)->where('local_payment_status', 'Completo')->count();
        $completandoCount = (clone $statsQuery)->where('local_payment_status', 'Completando')->count();
        $adelantoCount = (clone $statsQuery)->where('local_payment_status', 'Adelanto')->count();
        
        // Calcular total pagado: si hay filtro de mes, sumar solo del historial de ese mes
        $totalPaid = 0;
        if ($request->has('month') && $request->month != 'all' && $request->has('year') && $request->year != 'all') {
            // Sumar montos pagados solo en el mes/año especificado
            $month = $request->month;
            $year = $request->year;
            $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
            
            $allInscriptions = (clone $statsQuery)->get();
            foreach ($allInscriptions as $inscription) {
                // Obtener el último cambio de estado EN ESTE MES
                $lastChangeThisMonth = $inscription->paymentHistory()
                    ->whereBetween('status_date', [$startDate->toDateString(), $endDate->toDateString()])
                    ->orderBy('status_date', 'DESC')
                    ->first();
                
                if ($lastChangeThisMonth) {
                    $totalPaid += $lastChangeThisMonth->amount_paid;
                }
            }
        } else {
            // Si no hay filtro de mes, sumar el total_paid global de todas las inscripciones
            $totalPaid = (clone $statsQuery)->sum('total_paid');
        }
        
        $stats = [
            'total' => $totalCount,
            'inscritos_latam' => $inscritosLatamCount,
            'otra_sede' => $otraSedeCount,
            'completo' => $completoCount,
            'completando' => $completandoCount,
            'adelanto' => $adelantoCount,
            'total_paid' => $totalPaid,
        ];
        
        // Determinar el título de las estadísticas
        $statsTitle = 'Estadísticas';
        if ($request->has('month') && $request->month != 'all' && $request->has('year') && $request->year != 'all') {
            $monthName = Carbon::createFromDate(null, $request->month, 1)->translatedFormat('F');
            $statsTitle = "Estadísticas de {$monthName} {$request->year}";
        } elseif ($request->has('year') && $request->year != 'all') {
            $statsTitle = "Estadísticas del Año {$request->year}";
        } elseif ($request->has('month') && $request->month != 'all') {
            $monthName = Carbon::createFromDate(null, $request->month, 1)->translatedFormat('F');
            $statsTitle = "Estadísticas de {$monthName}";
        } elseif ($request->has('program_id') && $request->program_id != '') {
            $program = Program::find($request->program_id);
            $statsTitle = "Estadísticas del Programa: {$program->name}";
        }
        
        return view('inscriptions.index', compact('inscriptions', 'programs', 'creators', 'stats', 'statsTitle'));
    }

    public function create()
    {
        $programs = Program::where('state', 'Inscripciones')->get(['id', 'name']);
        $universities = University::pluck('name', 'id');
        $professions = Profession::orderBy('name')->pluck('name', 'id');

        return view('inscriptions.create', compact('programs', 'universities', 'professions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'paternal_surname' => 'nullable|string|max:255',
            'maternal_surname' => 'nullable|string|max:255',
            'ci' => 'required|string|max:20',
            'birth_date' => 'nullable|date',
            'email' => 'nullable|email|max:255',
            'civil_status' => 'required|in:Soltero,Casado,Divorciado,Viudo',
            'university_id' => 'nullable|exists:universities,id',
            'phone' => 'required|string|max:20',
            'program_id' => 'required|exists:programs,id',
            'payment_plan' => 'nullable|string|max:255',
            'payment_method' => 'required|in:QR,Efectivo,Deposito,Transferencia',
            'enrollment_fee' => 'required|numeric|min:0',
            'first_installment' => 'required|numeric|min:0',
            'total_paid' => 'required|numeric|min:0',
            'status' => 'required|in:Completo,Completando,Adelanto',
            'profession_id' => 'nullable|exists:professions,id',
            'residence' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'inscription_date' => 'required|date',
            'notes' => 'nullable|string',
            'certification' => 'nullable|string',
            'receipt_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'document_types' => 'nullable|array',
            'document_types.*' => 'nullable|string|max:255',
            'document_files' => 'nullable|array',
            'document_files.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx|max:10240',
            'document_descriptions' => 'nullable|array',
            'document_descriptions.*' => 'nullable|string|max:255',
            'gender' => 'required|in:Masculino,Femenino',
        ]);
        
        // Verificar si ya existe una inscripción con el mismo CI
        $existingInscription = Inscription::where('ci', $validated['ci'])->first();
        
        if ($existingInscription) {
            // Obtener el mes y año de la inscripción existente y la nueva
            $existingDate = Carbon::parse($existingInscription->inscription_date);
            $newDate = Carbon::parse($validated['inscription_date']);
            
            $sameMonth = $existingDate->month == $newDate->month && $existingDate->year == $newDate->year;
            
            // Caso 1: Mismo mes - Adelanto a Completando = Completo
            if ($sameMonth && $existingInscription->status == 'Adelanto' && $validated['status'] == 'Completando') {
                // Actualizar a Completo
                $existingInscription->status = 'Completo';
                $existingInscription->total_paid += $validated['total_paid'];
                $existingInscription->updated_by = Auth::id();
                $existingInscription->save();
                
                // Si hay un archivo de recibo, guardarlo SOLO EN GOOGLE DRIVE
                if ($request->hasFile('receipt_file')) {
                    $file = $request->file('receipt_file');
                    
                    // Crear nombre de la subcarpeta basado en la inscripción
                    $studentName = $existingInscription->first_name . ' ' . $existingInscription->paternal_surname;
                    $folderName = $studentName . ' - ' . $existingInscription->code;
                    
                    // Subir archivo DIRECTAMENTE a Google Drive - SIN fallback local
                    $driveFile = $this->uploadToGoogleDrive($file, $existingInscription);
                    
                    $receipt = new Receipt([
                        'inscription_id' => $existingInscription->id,
                        'file_path' => '', // String vacío - NO se guarda localmente
                        'file_name' => $file->getClientOriginalName(),
                        'file_type' => $file->getClientMimeType(),
                        'file_size' => $driveFile['size'] ?? $file->getSize(),
                        'google_drive_id' => $driveFile['id'],
                        'google_drive_link' => $driveFile['webViewLink'],
                        'stored_in_drive' => true,
                        'created_by' => Auth::id(),
                    ]);
                    
                    $receipt->save();
                }
                
            // Guardar múltiples documentos SOLO EN GOOGLE DRIVE
            $documentTypes = $request->input('document_types', []);
            $documentDescriptions = $request->input('document_descriptions', []);
            $documentFiles = $request->file('document_files', []);

            foreach ($documentTypes as $index => $type) {
                if (isset($documentFiles[$index]) && $documentFiles[$index]) {
                    $file = $documentFiles[$index];
                    
                    // Crear nombre de la subcarpeta basado en la inscripción
                    $studentName = $existingInscription->first_name . ' ' . $existingInscription->paternal_surname;
                    $folderName = $studentName . ' - ' . $existingInscription->code;
                    
                    // Subir archivo DIRECTAMENTE a Google Drive - SIN fallback local
                    $driveFile = $this->uploadToGoogleDrive($file, $existingInscription);

                    $document = new Document([
                        'inscription_id' => $existingInscription->id,
                        'file_path' => '', // String vacío - NO se guarda localmente
                        'file_name' => $file->getClientOriginalName(),
                        'file_type' => $file->getClientMimeType(),
                        'file_size' => $driveFile['size'] ?? $file->getSize(),
                        'document_type' => $type,
                        'description' => $documentDescriptions[$index] ?? null,
                        'google_drive_id' => $driveFile['id'],
                        'google_drive_link' => $driveFile['webViewLink'],
                        'stored_in_drive' => true,
                        'created_by' => Auth::id(),
                    ]);
                    $document->save();
                }
            }
                
                return redirect()->route('inscriptions.index')
                    ->with('success', 'Inscripción actualizada a estado Completo correctamente.');
            } 
            // Caso 2: Mes diferente - Mantener registros separados
            else if (!$sameMonth) {
                // Generar código único
                $code = Inscription::generateCode($validated['first_name'], $validated['ci']);
                
                // Crear nueva inscripción para el mes actual
                $inscription = new Inscription($validated);
                $inscription->code = $code;
                $inscription->created_by = Auth::id();
                $inscription->save();
                
                // Si hay un archivo de recibo, guardarlo SOLO EN GOOGLE DRIVE
                if ($request->hasFile('receipt_file')) {
                    $file = $request->file('receipt_file');
                    
                    // Crear nombre de la subcarpeta basado en la inscripción
                    $studentName = $inscription->first_name . ' ' . $inscription->paternal_surname;
                    $folderName = $studentName . ' - ' . $inscription->code;
                    
                    // Subir archivo DIRECTAMENTE a Google Drive - SIN fallback local
                    $driveFile = $this->uploadToGoogleDrive($file, $inscription);
                    
                    $receipt = new Receipt([
                        'inscription_id' => $inscription->id,
                        'file_path' => '', // String vacío - NO se guarda localmente
                        'file_name' => $file->getClientOriginalName(),
                        'file_type' => $file->getClientMimeType(),
                        'file_size' => $driveFile['size'] ?? $file->getSize(),
                        'google_drive_id' => $driveFile['id'],
                        'google_drive_link' => $driveFile['webViewLink'],
                        'stored_in_drive' => true,
                        'created_by' => Auth::id(),
                    ]);
                    
                    $receipt->save();
                }
                
                // Guardar múltiples documentos SOLO EN GOOGLE DRIVE
                $documentTypes = $request->input('document_types', []);
                $documentDescriptions = $request->input('document_descriptions', []);
                $documentFiles = $request->file('document_files', []);

                foreach ($documentTypes as $index => $type) {
                    if (isset($documentFiles[$index]) && $documentFiles[$index]) {
                        $file = $documentFiles[$index];
                        
                        // Crear nombre de la subcarpeta basado en la inscripción
                        $studentName = $inscription->first_name . ' ' . $inscription->paternal_surname;
                        $folderName = $studentName . ' - ' . $inscription->code;
                        
                        // Subir archivo DIRECTAMENTE a Google Drive - SIN fallback local
                        $driveFile = $this->uploadToGoogleDrive($file, $inscription);

                        $document = new Document([
                            'inscription_id' => $inscription->id,
                            'file_path' => '', // String vacío - NO se guarda localmente
                            'file_name' => $file->getClientOriginalName(),
                            'file_type' => $file->getClientMimeType(),
                            'file_size' => $driveFile['size'] ?? $file->getSize(),
                            'document_type' => $type,
                            'description' => $documentDescriptions[$index] ?? null,
                            'google_drive_id' => $driveFile['id'],
                            'google_drive_link' => $driveFile['webViewLink'],
                            'stored_in_drive' => true,
                            'created_by' => Auth::id(),
                        ]);
                        $document->document_type = $type;
                        $document->description = $documentDescriptions[$index] ?? null;
                        $document->created_by = Auth::id();
                        $document->save();
                    }
                }
                
                return redirect()->route('inscriptions.index')
                    ->with('success', 'Nueva inscripción registrada correctamente.');
            }
            else {
                // Si no cumple con los casos especiales, mostrar error de CI duplicado
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['ci' => 'Ya existe una inscripción con este CI en el mismo mes.']);
            }
        }
        
        // Si no existe una inscripción previa, crear una nueva
        $code = Inscription::generateCode($validated['first_name'], $validated['ci']);
        
        // Crear nueva inscripción
        $inscription = new Inscription($validated);
        $inscription->code = $code;
        $inscription->created_by = Auth::id();
        $inscription->save();
        
        // Si hay un archivo de recibo, guardarlo SOLO EN GOOGLE DRIVE
        if ($request->hasFile('receipt_file')) {
            $file = $request->file('receipt_file');
            
            // Crear nombre de la subcarpeta basado en la inscripción
            $studentName = $inscription->first_name . ' ' . $inscription->paternal_surname;
            $folderName = $studentName . ' - ' . $inscription->code;
            
            // Subir archivo DIRECTAMENTE a Google Drive - SIN fallback local
            $driveFile = $this->uploadToGoogleDrive($file, $inscription);
            
            $receipt = new Receipt([
                'inscription_id' => $inscription->id,
                'file_path' => '', // String vacío - NO se guarda localmente
                'file_name' => $file->getClientOriginalName(),
                'file_type' => $file->getClientMimeType(),
                'file_size' => $driveFile['size'],
                'google_drive_id' => $driveFile['id'],
                'google_drive_link' => $driveFile['webViewLink'],
                'stored_in_drive' => true,
                'created_by' => Auth::id(),
            ]);
            
            $receipt->save();
        }

        // Guardar múltiples documentos SOLO EN GOOGLE DRIVE
        $documentTypes = $request->input('document_types', []);
        $documentDescriptions = $request->input('document_descriptions', []);
        $documentFiles = $request->file('document_files', []);

        // Mapeo de tipos de documentos a campos del checklist
        $checklistMapping = [
            'ci' => 'has_identity_card',
            'titulo' => 'has_degree_title',
            'diploma' => 'has_academic_diploma',
            'nacimiento' => 'has_birth_certificate',
        ];

        // Variable para detectar si se sube documentación completa
        $hasDocumentacionCompleta = in_array('documentacion_completa', $documentTypes);
        
        // Array para rastrear qué campos del checklist se actualizaron
        $updatedFields = [];

        foreach ($documentTypes as $index => $type) {
            if (isset($documentFiles[$index]) && $documentFiles[$index]) {
                $file = $documentFiles[$index];
                
                // Crear nombre de la subcarpeta basado en la inscripción
                $studentName = $inscription->first_name . ' ' . $inscription->paternal_surname;
                $folderName = $studentName . ' - ' . $inscription->code;
                
                // Subir archivo DIRECTAMENTE a Google Drive - SIN fallback local
                $driveFile = $this->uploadToGoogleDrive($file, $inscription);

                $document = new Document([
                    'inscription_id' => $inscription->id,
                    'file_path' => '', // String vacío - NO se guarda localmente
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getClientMimeType(),
                    'file_size' => $driveFile['size'],
                    'document_type' => $type,
                    'description' => $documentDescriptions[$index] ?? null,
                    'google_drive_id' => $driveFile['id'],
                    'google_drive_link' => $driveFile['webViewLink'],
                    'stored_in_drive' => true,
                    'created_by' => Auth::id(),
                ]);
                
                $document->save();

                // Actualizar automáticamente el checklist si el tipo corresponde
                if (isset($checklistMapping[$type])) {
                    $checklistField = $checklistMapping[$type];
                    $inscription->$checklistField = true;
                    $updatedFields[$checklistField] = true;
                }
            }
        }

        // Si se subió documentación completa, marcar todos los documentos del checklist
        if ($hasDocumentacionCompleta) {
            $allDocumentFields = ['has_identity_card', 'has_degree_title', 'has_academic_diploma', 'has_birth_certificate'];
            
            foreach ($allDocumentFields as $field) {
                $inscription->$field = true;
                $updatedFields[$field] = true;
            }
            
            Log::info("Documentación completa detectada - Todos los documentos del checklist marcados", [
                'inscription_id' => $inscription->id,
                'inscription_code' => $inscription->code
            ]);
        }

        // Guardar cambios en el checklist
        $inscription->updated_by = Auth::id();
        $inscription->save();
        
        // Enviar notificaciones para cada campo actualizado
        foreach ($updatedFields as $field => $value) {
            $this->sendChecklistNotifications($inscription, $field, true, 'document');
        }
        
        return redirect()->route('inscriptions.index')
            ->with('success', 'Inscripción creada correctamente.');
    }

    public function show(Inscription $inscription)
    {
        // Verificar autorización
        $this->authorizeInscriptionAccess($inscription, 'view');
        
        // Cargar los recibos, documentos, historial de pagos y relaciones relacionadas
        $inscription->load('receipts', 'documents', 'paymentHistory', 'university', 'profession', 'programs', 'creator', 'updater');
        
        return view('inscriptions.show', compact('inscription'));
    }

    public function edit(Inscription $inscription)
    {
        // Verificar autorización
        $this->authorizeInscriptionAccess($inscription, 'edit');
        
        // Solo cargar programas si la inscripción NO está sincronizada (puede editar)
        $programs = collect();
        if (!$inscription->is_synced) {
            $programs = Program::where('status', 'INSCRIPCION')->get(['id', 'name']);
        }
        
        $universities = University::pluck('name', 'id');
        $professions = Profession::pluck('name', 'id');
        
        // Cargar las relaciones necesarias (program se carga automáticamente via accessor)
        $inscription->load('receipts', 'documents', 'profession', 'programs');

        return view('inscriptions.edit', compact('inscription', 'programs', 'universities', 'professions'));
    }

    public function update(Request $request, Inscription $inscription)
    {
        // Verificar autorización
        $this->authorizeInscriptionAccess($inscription, 'edit');
        
        // Si la inscripción está sincronizada, algunos campos no son requeridos porque están deshabilitados
        $isSynced = $inscription->is_synced;
        
        $validated = $request->validate([
            'full_name' => ($isSynced ? 'nullable' : 'required') . '|string|max:255',
            'ci' => ($isSynced ? 'nullable' : 'required') . '|string|max:20',
            'birth_date' => 'nullable|date',
            'email' => 'nullable|email|max:255',
            'civil_status' => 'required|in:Soltero,Casado,Divorciado,Viudo',
            'university_id' => 'nullable|exists:universities,id',
            'phone' => ($isSynced ? 'nullable' : 'required') . '|string|max:20',
            'program_id' => ($isSynced ? 'nullable' : 'required') . '|exists:programs,id',
            'payment_plan' => 'nullable|string|max:255',
            'payment_method' => 'required|in:QR,Efectivo,Deposito,Transferencia',
            'enrollment_fee' => 'required|numeric|min:0',
            'first_installment' => 'required|numeric|min:0',
            'total_paid' => 'required|numeric|min:0',
            'status' => 'required|in:Completo,Completando,Adelanto',
            'local_payment_status' => 'nullable|in:Pendiente,Adelanto,Completando,Completo',
            'profession_id' => 'nullable|exists:professions,id',
            'residence' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'inscription_date' => ($isSynced ? 'nullable' : 'required') . '|date',
            'notes' => 'nullable|string',
            'certification' => 'nullable|string',
            'receipt_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'document_types' => 'nullable|array',
            'document_types.*' => 'nullable|string|max:255',
            'document_files' => 'nullable|array',
            'document_files.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx|max:10240',
            'document_descriptions' => 'nullable|array',
            'document_descriptions.*' => 'nullable|string|max:255',
            'gender' => 'required|in:Masculino,Femenino',
        ]);
        
        // CAPTURAR EL ESTADO DE PAGO ANTES de cualquier conversión o modificación
        $oldPaymentStatus = $inscription->local_payment_status ?? 'Pendiente';
        $newPaymentStatusFromForm = $validated['local_payment_status'] ?? null;
        $newTotalPaid = $validated['total_paid'] ?? $inscription->total_paid ?? 0;
        
        // Si la inscripción está sincronizada, proteger campos de la DB externa
        if ($isSynced) {
            // Campos que NO se pueden editar si están sincronizados
            $protectedFields = [
                'full_name', 'ci', 'birth_date', 'email', 'phone',
                'profession_id', 'program_id', 'inscription_date', 'payment_plan'
            ];
            
            foreach ($protectedFields as $field) {
                // Restaurar el valor original desde la base de datos
                $validated[$field] = $inscription->$field;
            }
            
            // También proteger el campo status para inscripciones sincronizadas
            $validated['status'] = $inscription->status;
            
            // LÓGICA DE ESTADOS DE PAGO PARA INSCRIPCIONES SINCRONIZADAS
            // Usar local_payment_status en lugar de status
            // NOTA: La BD externa trae UN SOLO registro por estudiante que se va actualizando
            // Ejemplo: Pedro se inscribe en mayo con Adelanto, en junio se actualiza a Completando
            if (isset($validated['local_payment_status']) && !empty($validated['local_payment_status'])) {
                $newPaymentStatus = $validated['local_payment_status'];
                $currentPaymentStatus = $inscription->local_payment_status ?? 'Pendiente';
                
                // Caso: Cambio de Adelanto → Completando
                if ($currentPaymentStatus == 'Adelanto' && $newPaymentStatus == 'Completando') {
                    // Verificar si el cambio es en el MISMO mes o en MESES DIFERENTES
                    $statusDate = $request->has('status_date') ? $request->input('status_date') : now()->toDateString();
                    $statusDateCarbon = Carbon::parse($statusDate);
                    
                    // Obtener el último cambio de estado anterior para saber en qué mes fue Adelanto
                    $lastHistory = $inscription->paymentHistory()
                        ->where('new_status', 'Adelanto')
                        ->orderBy('status_date', 'DESC')
                        ->first();
                    
                    if ($lastHistory) {
                        $adelantoDateCarbon = Carbon::parse($lastHistory->status_date);
                        
                        // Si están en el MISMO mes/año, convertir a Completo
                        if ($adelantoDateCarbon->year == $statusDateCarbon->year && 
                            $adelantoDateCarbon->month == $statusDateCarbon->month) {
                            $validated['local_payment_status'] = 'Completo';
                            
                            Log::info("Cambio de Adelanto a Completando en MISMO mes - Convertir a Completo", [
                                'inscription_id' => $inscription->id,
                                'ci' => $inscription->ci,
                                'adelanto_date' => $adelantoDateCarbon->format('Y-m-d'),
                                'completando_date' => $statusDateCarbon->format('Y-m-d'),
                                'final_status' => 'Completo',
                                'total_paid' => $validated['total_paid'] ?? $inscription->total_paid,
                                'user_id' => Auth::id()
                            ]);
                        } else {
                            // Si son en MESES DIFERENTES, mantener como Completando
                            Log::info("Cambio de Adelanto a Completando en MESES DIFERENTES - Mantener Completando", [
                                'inscription_id' => $inscription->id,
                                'ci' => $inscription->ci,
                                'adelanto_date' => $adelantoDateCarbon->format('Y-m-d'),
                                'completando_date' => $statusDateCarbon->format('Y-m-d'),
                                'status' => 'Completando',
                                'user_id' => Auth::id()
                            ]);
                        }
                    }
                }
            }
            
            Log::info("Inscripción sincronizada - Campos externos protegidos de edición", [
                'inscription_id' => $inscription->id,
                'user_id' => Auth::id()
            ]);
        }
        
        // REGISTRAR CAMBIO DE ESTADO EN HISTORIAL DE PAGOS (ANTES de actualizar)
        // Usar el estado original que vino del formulario, no el convertido
        $newPaymentStatus = $newPaymentStatusFromForm;
        
        // Actualizar inscripción
        $inscription->update($validated);
        $inscription->updated_by = Auth::id();
        $inscription->save();

        // Registrar en historial si el estado de pago cambió
        if ($newPaymentStatus && $oldPaymentStatus !== $newPaymentStatus) {
            $statusDate = now()->toDateString();
            
            // Si el nuevo estado tiene fecha diferente (diferente mes), registrar con esa fecha
            // Por ejemplo, si en mayo era Adelanto y en junio cambió a Completando
            if ($request->has('status_date')) {
                $statusDate = $request->input('status_date');
            }
            
            InscriptionPaymentHistory::create([
                'inscription_id' => $inscription->id,
                'ci' => $inscription->ci,
                'old_status' => $oldPaymentStatus,
                'new_status' => $newPaymentStatus,
                'amount_paid' => $newTotalPaid, // Guardar el total pagado hasta este cambio
                'status_date' => $statusDate,
                'notes' => "Cambio de estado: {$oldPaymentStatus} → {$newPaymentStatus}",
                'changed_by' => Auth::id(),
            ]);
            
            Log::info("Cambio de estado registrado en historial de pagos", [
                'inscription_id' => $inscription->id,
                'ci' => $inscription->ci,
                'old_status' => $oldPaymentStatus,
                'new_status' => $newPaymentStatus,
                'total_paid' => $newTotalPaid,
                'status_date' => $statusDate,
                'user_id' => Auth::id()
            ]);
        }
        
        // Si hay un archivo de recibo, guardarlo SOLO EN GOOGLE DRIVE
        if ($request->hasFile('receipt_file')) {
            $file = $request->file('receipt_file');
            
            // Crear nombre de la subcarpeta basado en la inscripción
            $studentName = $inscription->first_name . ' ' . $inscription->paternal_surname;
            $folderName = $studentName . ' - ' . $inscription->code;
            
            // Subir archivo DIRECTAMENTE a Google Drive - SIN fallback local
            $driveFile = $this->uploadToGoogleDrive($file, $inscription);
            
            $receipt = new Receipt([
                'inscription_id' => $inscription->id,
                'file_path' => '', // String vacío - NO se guarda localmente
                'file_name' => $file->getClientOriginalName(),
                'file_type' => $file->getClientMimeType(),
                'file_size' => $driveFile['size'],
                'google_drive_id' => $driveFile['id'],
                'google_drive_link' => $driveFile['webViewLink'],
                'stored_in_drive' => true,
                'created_by' => Auth::id(),
            ]);
            
            $receipt->save();
        }

        // Guardar múltiples documentos SOLO EN GOOGLE DRIVE
        $documentTypes = $request->input('document_types', []);
        $documentDescriptions = $request->input('document_descriptions', []);
        $documentFiles = $request->file('document_files', []);

        // Mapeo de tipos de documentos a campos del checklist
        $checklistMapping = [
            'ci' => 'has_identity_card',
            'titulo' => 'has_degree_title',
            'diploma' => 'has_academic_diploma',
            'nacimiento' => 'has_birth_certificate',
        ];

        // Variable para detectar si se sube documentación completa
        $hasDocumentacionCompleta = in_array('documentacion_completa', $documentTypes);
        
        // Array para rastrear qué campos del checklist se actualizaron
        $updatedFields = [];

        foreach ($documentTypes as $index => $type) {
            if (isset($documentFiles[$index]) && $documentFiles[$index]) {
                $file = $documentFiles[$index];
                
                // Crear nombre de la subcarpeta basado en la inscripción
                $studentName = $inscription->first_name . ' ' . $inscription->paternal_surname;
                $folderName = $studentName . ' - ' . $inscription->code;
                
                // Subir archivo DIRECTAMENTE a Google Drive - SIN fallback local
                $driveFile = $this->uploadToGoogleDrive($file, $inscription);

                $document = new Document([
                    'inscription_id' => $inscription->id,
                    'file_path' => '', // String vacío - NO se guarda localmente
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getClientMimeType(),
                    'file_size' => $driveFile['size'],
                    'document_type' => $type,
                    'description' => $documentDescriptions[$index] ?? null,
                    'google_drive_id' => $driveFile['id'],
                    'google_drive_link' => $driveFile['webViewLink'],
                    'stored_in_drive' => true,
                    'created_by' => Auth::id(),
                ]);
                
                $document->save();

                // Actualizar automáticamente el checklist si el tipo corresponde
                if (isset($checklistMapping[$type])) {
                    $checklistField = $checklistMapping[$type];
                    // Solo notificar si no estaba marcado previamente
                    if (!$inscription->$checklistField) {
                        $inscription->$checklistField = true;
                        $updatedFields[$checklistField] = true;
                    }
                }
            }
        }

        // Si se subió documentación completa, marcar todos los documentos del checklist
        if ($hasDocumentacionCompleta) {
            $allDocumentFields = ['has_identity_card', 'has_degree_title', 'has_academic_diploma', 'has_birth_certificate'];
            
            foreach ($allDocumentFields as $field) {
                if (!$inscription->$field) {
                    $inscription->$field = true;
                    $updatedFields[$field] = true;
                }
            }
            
            Log::info("Documentación completa detectada en actualización - Todos los documentos del checklist marcados", [
                'inscription_id' => $inscription->id,
                'inscription_code' => $inscription->code
            ]);
        }

        // Guardar cambios en el checklist
        $inscription->updated_by = Auth::id();
        $inscription->save();
        
        // Enviar notificaciones para cada campo actualizado
        foreach ($updatedFields as $field => $value) {
            $this->sendChecklistNotifications($inscription, $field, true, 'document');
        }
        
        return redirect()->route('inscriptions.index')
            ->with('success', 'Inscripción actualizada correctamente.');
    }

    public function destroy(Inscription $inscription)
    {
        // Verificar autorización
        $this->authorizeInscriptionAccess($inscription, 'delete');
        
        try {
            // Eliminar los recibos asociados SOLO DE GOOGLE DRIVE
            foreach ($inscription->receipts as $receipt) {
                if ($receipt->stored_in_drive && $receipt->google_drive_id) {
                    try {
                        $this->googleDriveService->deleteFile($receipt->google_drive_id);
                    } catch (\Exception $e) {
                        Log::error('Error al eliminar recibo de Drive: ' . $e->getMessage());
                    }
                }
                $receipt->delete();
            }

            // Eliminar los documentos asociados SOLO DE GOOGLE DRIVE
            foreach ($inscription->documents as $document) {
                if ($document->stored_in_drive && $document->google_drive_id) {
                    try {
                        $this->googleDriveService->deleteFile($document->google_drive_id);
                    } catch (\Exception $e) {
                        Log::error('Error al eliminar documento de Drive: ' . $e->getMessage());
                    }
                }
                $document->delete();
            }
            
            // Eliminar la inscripción
            $inscription->delete();
            
            return redirect()->route('inscriptions.index')
                ->with('success', 'Inscripción eliminada correctamente.');
                
        } catch (\Exception $e) {
            Log::error('Error al eliminar inscripción: ' . $e->getMessage());
            return redirect()->back()
                ->withErrors(['error' => 'Error al eliminar la inscripción: ' . $e->getMessage()]);
        }
    }

    public function updateDocuments(Request $request, Inscription $inscription)
    {
        // Verificar autorización
        $this->authorizeInscriptionAccess($inscription, 'edit');
        
        try {
            $validated = $request->validate([
                'field' => ['required', Rule::in([
                    'has_identity_card',
                    'has_degree_title',
                    'has_academic_diploma',
                    'has_birth_certificate',
                    'has_commitment_letter'
                ])],
                'value' => 'required|boolean',
                'observation' => 'nullable|string|max:500',
            ]);

            $field = $validated['field'];
            $value = $validated['value'];
            $observation = $validated['observation'] ?? null;

            $inscription->$field = $value;
            $inscription->updated_by = Auth::id();
            
            // Si se desmarca y hay observación, agregarla a las observaciones del documento
            if (!$value && $observation) {
                $currentObservations = $inscription->document_observations ?? '';
                $timestamp = now()->format('d/m/Y H:i');
                $userName = Auth::user()->name;
                $fieldName = $this->getFieldDisplayName($field, 'document');
                
                $newObservation = "[{$timestamp}] {$userName} desmarcó '{$fieldName}': {$observation}";
                
                $inscription->document_observations = $currentObservations 
                    ? $currentObservations . "\n\n" . $newObservation 
                    : $newObservation;
            }
            
            $inscription->save();

            // Enviar notificaciones
            $this->sendChecklistNotifications($inscription, $field, $value, 'document');

            return response()->json([
                'success' => true,
                'message' => 'Documento actualizado correctamente',
                'field' => $field,
                'value' => $value
            ]);
        } catch (\Exception $e) {
            Log::error("Error al actualizar documento: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el documento: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function updateAccess(Request $request, Inscription $inscription)
    {
        // Verificar autorización
        $this->authorizeInscriptionAccess($inscription, 'edit');
        
        try{
            $validated = $request->validate([
                'field' => ['required', Rule::in([
                    'was_added_to_the_group',
                    'accesses_were_sent',
                    'mail_was_sent',
                ])],
                'value' => 'required|boolean',
                'observation' => 'nullable|string|max:500',
            ]);

            $field = $validated['field'];
            $value = $validated['value'];
            $observation = $validated['observation'] ?? null;

            $inscription->$field = $value;
            $inscription->updated_by = Auth::id();
            
            // Si se desmarca y hay observación, agregarla a las observaciones del documento
            if (!$value && $observation) {
                $currentObservations = $inscription->document_observations ?? '';
                $timestamp = now()->format('d/m/Y H:i');
                $userName = Auth::user()->name;
                $fieldName = $this->getFieldDisplayName($field, 'access');
                
                $newObservation = "[{$timestamp}] {$userName} desmarcó '{$fieldName}': {$observation}";
                
                $inscription->document_observations = $currentObservations 
                    ? $currentObservations . "\n\n" . $newObservation 
                    : $newObservation;
            }
            
            $inscription->save();

            // Enviar notificaciones
            $this->sendChecklistNotifications($inscription, $field, $value, 'access');

            return response()->json([
                'success' => true,
                'message' => 'Accesos actualizado correctamente',
                'field' => $field,
                'value' => $value
            ]);
        } catch (\Exception $e){
            Log::error('Error al actualizar accesos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar accesos: ' .$e->getMessage()
            ],500);
        }
    }

    public function updateAcademicStatus(Request $request, Inscription $inscription)
    {
        // Verificar autorización
        $this->authorizeInscriptionAccess($inscription, 'edit');
        
        $validated = $request->validate([
            'academic_status' => ['required', Rule::in(['Activo', 'Retirado', 'Congelado', 'Cambio', 'Devolucion','En Tramite', 'Titulado'])],
        ]);

        $inscription->academic_status = $validated['academic_status'];
        $inscription->updated_by = Auth::id();
        $inscription->save();

        return response()->json([
            'success' => true,
            'message' => 'Estado académico actualizado correctamente',
            'value' => $inscription->academic_status
        ]);
    }

    public function uploadCommitmentLetter(Request $request, Inscription $inscription)
    {
        // Verificar autorización
        $this->authorizeInscriptionAccess($inscription, 'edit');
        
        try {
            $request->validate([
                'commitment_letter' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx|max:10240',
                'description' => 'nullable|string|max:255',
            ]);

            // Eliminar documento anterior de tipo compromiso si existe
            $oldDocument = $inscription->documents()->where('document_type', 'compromiso')->first();
            if ($oldDocument) {
                // TODOS los archivos están en Google Drive
                $this->googleDriveService->deleteFile($oldDocument->google_drive_id);
                $oldDocument->delete();
            }

            $file = $request->file('commitment_letter');
            
            // Crear nombre de la subcarpeta basado en la inscripción
            $studentName = $inscription->first_name . ' ' . $inscription->paternal_surname;
            $folderName = $studentName . ' - ' . $inscription->code;
            
            // Subir archivo DIRECTAMENTE a Google Drive - SIN fallback local
            $driveFile = $this->uploadToGoogleDrive($file, $inscription);
            
            $document = new Document([
                'inscription_id' => $inscription->id,
                'file_path' => '', // String vacío - NO se guarda localmente
                'file_name' => $file->getClientOriginalName(),
                'file_type' => $file->getClientMimeType(),
                'file_size' => $driveFile['size'],
                'document_type' => 'compromiso',
                'description' => $request->input('description'),
                'google_drive_id' => $driveFile['id'],
                'google_drive_link' => $driveFile['webViewLink'],
                'stored_in_drive' => true,
                'created_by' => Auth::id(),
            ]);
            
            $document->save();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Carta de compromiso subida correctamente',
                    'file_url' => route('documents.serve', $document),
                    'file_name' => $document->file_name
                ]);
            }

            return back()->with('success', 'Carta de compromiso subida correctamente');
        } catch (\Exception $e) {
            Log::error("Error al subir carta de compromiso: " . $e->getMessage());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al subir la carta de compromiso: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Error al subir la carta de compromiso: ' . $e->getMessage());
        }
    }

    public function deleteCommitmentLetter(Inscription $inscription, $documentId)
    {
        // Verificar autorización
        $this->authorizeInscriptionAccess($inscription, 'edit');
        
        try {
            $document = $inscription->documents()->where('id', $documentId)->first();
            if ($document) {
                // Eliminar SOLO de Google Drive
                if ($document->stored_in_drive && $document->google_drive_id) {
                    $this->googleDriveService->deleteFile($document->google_drive_id);
                } else {
                    Log::warning('Documento no tiene ID de Google Drive', [
                        'document_id' => $document->id,
                        'stored_in_drive' => $document->stored_in_drive,
                        'google_drive_id' => $document->google_drive_id
                    ]);
                }
                
                $document->delete();

                if (request()->ajax() || request()->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Documento eliminado correctamente'
                    ]);
                }
                return back()->with('success', 'Documento eliminado correctamente');
            }

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró el documento para eliminar'
                ], 404);
            }
            return back()->with('error', 'No se encontró el documento para eliminar');
        } catch (\Exception $e) {
            Log::error("Error al eliminar documento: " . $e->getMessage());
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al eliminar el documento: ' . $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Error al eliminar el documento: ' . $e->getMessage());
        }
    }

    public function serveCommitmentLetter(Inscription $inscription, $documentId)
    {
        $document = $inscription->documents()->where('id', $documentId)->first();
        if (!$document) {
            abort(404, 'Archivo no encontrado');
        }
        
        try {
            if ($document->stored_in_drive && $document->google_drive_id) {
                $fileContent = $this->googleDriveService->downloadFile($document->google_drive_id);
                
                $headers = [
                    'Content-Type' => $document->file_type,
                    'Content-Disposition' => 'inline; filename="' . $document->file_name . '"',
                ];
                
                return response($fileContent, 200, $headers);
            } else {
                Log::error('Documento no disponible en Drive', [
                    'document_id' => $document->id,
                    'stored_in_drive' => $document->stored_in_drive,
                    'google_drive_id' => $document->google_drive_id
                ]);
                abort(404, 'Archivo no encontrado en Google Drive');
            }
        } catch (\Exception $e) {
            Log::error("Error al servir documento: " . $e->getMessage());
            abort(500, 'Error al procesar el archivo');
        }
    }

    public function updateDocumentObservations(Request $request, Inscription $inscription)
    {
        // Verificar autorización
        $this->authorizeInscriptionAccess($inscription, 'edit');
        
        try {
            $validated = $request->validate([
                'document_observations' => 'nullable|string',
            ]);

            $inscription->document_observations = $validated['document_observations'];
            $inscription->updated_by = Auth::id();
            $inscription->save();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Observaciones actualizadas correctamente'
                ]);
            }

            return back()->with('success', 'Observaciones actualizadas correctamente');
        } catch (\Exception $e) {
            Log::error("Error al actualizar observaciones: " . $e->getMessage());
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar las observaciones: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Error al actualizar las observaciones: ' . $e->getMessage());
        }
    }

    public function monthlyReport(Request $request)
    {
        $year = $request->input('year', Carbon::now()->year);
        $month = $request->input('month', Carbon::now()->month);
        
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        
        $query = Inscription::with(['programs', 'creator', 'location']);
        
        $inscriptions = $query->whereBetween('inscription_date', [$startDate, $endDate])->get();
            
        $stats = [
            'total' => $inscriptions->count(),
            'completo' => $inscriptions->where('status', 'Completo')->count(),
            'completando' => $inscriptions->where('status', 'Completando')->count(),
            'adelanto' => $inscriptions->where('status', 'Adelanto')->count(),
            'total_paid' => $inscriptions->sum('total_paid'),
        ];
        
        $programStats = $inscriptions->groupBy('program_id')
            ->map(function ($items, $key) {
                $program = Program::find($key);
                return [
                    'name' => $program ? $program->name : 'Desconocido',
                    'count' => $items->count(),
                    'total_paid' => $items->sum('total_paid'),
                ];
            });
            
        $advisorStats = $inscriptions->groupBy('created_by')
            ->map(function ($items, $key) {
                $user = User::find($key);
                return [
                    'name' => $user ? $user->name : 'Desconocido',
                    'count' => $items->count(),
                    'total_paid' => $items->sum('total_paid'),
                ];
            });
            
        return view('reports.monthly', compact('inscriptions', 'stats', 'programStats', 'advisorStats', 'year', 'month'));
    }

    public function yearlyReport(Request $request)
    {
        $year = $request->input('year', Carbon::now()->year);
        
        $startDate = Carbon::createFromDate($year, 1, 1)->startOfYear();
        $endDate = $startDate->copy()->endOfYear();
        
        $query = Inscription::with(['programs', 'creator', 'location']);
        
        $inscriptions = $query->whereBetween('inscription_date', [$startDate, $endDate])->get();
        
        // Agrupar inscripciones por CI para identificar casos especiales
        $inscriptionsByCi = $inscriptions->groupBy('ci');
        
        // Crear una copia de las inscripciones para modificar los estados en el reporte anual
        $adjustedInscriptions = $inscriptions->map(function ($inscription) use ($inscriptionsByCi) {
            $inscriptionCopy = clone $inscription;
            
            // Si hay más de una inscripción con el mismo CI
            if ($inscriptionsByCi[$inscription->ci]->count() > 1) {
                $ciInscriptions = $inscriptionsByCi[$inscription->ci]->sortBy('inscription_date');
                
                // Verificar si hay un "Adelanto" previo y este es "Completando"
                $hasAdelantoBefore = false;
                foreach ($ciInscriptions as $ciInscription) {
                    if ($ciInscription->id == $inscription->id) {
                        // Si este es "Completando" y hubo un "Adelanto" previo, mostrarlo como "Completo"
                        if ($inscription->status == 'Completando' && $hasAdelantoBefore) {
                            $inscriptionCopy->status = 'Completo';
                        }
                        break;
                    }
                    
                    if ($ciInscription->status == 'Adelanto') {
                        $hasAdelantoBefore = true;
                    }
                }
            }
            
            return $inscriptionCopy;
        });
            
        $monthlyStats = [];
        
        for ($month = 1; $month <= 12; $month++) {
            $monthStart = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $monthEnd = $monthStart->copy()->endOfMonth();
            
            $monthInscriptions = $adjustedInscriptions->filter(function ($inscription) use ($monthStart, $monthEnd) {
                return $inscription->inscription_date->between($monthStart, $monthEnd);
            });
            
            $monthlyStats[$month] = [
                'month' => $monthStart->format('F'),
                'total' => $monthInscriptions->count(),
                'completo' => $monthInscriptions->where('status', 'Completo')->count(),
                'completando' => $monthInscriptions->where('status', 'Completando')->count(),
                'adelanto' => $monthInscriptions->where('status', 'Adelanto')->count(),
                'total_paid' => $monthInscriptions->sum('total_paid'),
            ];
        }
        
        $programStats = $inscriptions->groupBy('program_id')
            ->map(function ($items, $key) {
                $program = Program::find($key);
                return [
                    'name' => $program ? $program->name : 'Desconocido',
                    'count' => $items->count(),
                    'total_paid' => $items->sum('total_paid'),
                ];
            });
            
        return view('reports.yearly', compact('inscriptions', 'monthlyStats', 'programStats', 'year'));
    }

    public function showForProgram(Program $program, Inscription $inscription)
    {
        // Cargar relaciones necesarias
        $inscription->load('documents', 'receipts');
        return view('programs.inscription_show', compact('program', 'inscription'));
    }

    public function uploadDocumentForProgram(Request $request, Program $program, Inscription $inscription)
    {
        $request->validate([
            'document_types' => 'required|array',
            'document_types.*' => 'required|string|max:255',
            'document_files' => 'required|array',
            'document_files.*' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx|max:10240',
            'document_descriptions' => 'nullable|array',
            'document_descriptions.*' => 'nullable|string|max:255',
        ]);

        $documentTypes = $request->input('document_types', []);
        $documentDescriptions = $request->input('document_descriptions', []);
        $documentFiles = $request->file('document_files', []);

        // Mapeo de tipos de documentos a campos del checklist
        $checklistMapping = [
            'ci' => 'has_identity_card',
            'titulo' => 'has_degree_title',
            'diploma' => 'has_academic_diploma',
            'nacimiento' => 'has_birth_certificate',
        ];

        // Variable para detectar si se sube documentación completa
        $hasDocumentacionCompleta = in_array('documentacion_completa', $documentTypes);
        
        // Array para rastrear qué campos del checklist se actualizaron
        $updatedFields = [];

        foreach ($documentTypes as $index => $type) {
            if (isset($documentFiles[$index]) && $documentFiles[$index]) {
                $file = $documentFiles[$index];
                
                // Crear nombre de la subcarpeta basado en la inscripción
                $studentName = $inscription->first_name . ' ' . $inscription->paternal_surname;
                $folderName = $studentName . ' - ' . $inscription->code;
                
                // Subir archivo DIRECTAMENTE a Google Drive - SIN fallback local
                $driveFile = $this->uploadToGoogleDrive($file, $inscription);
                
                $document = new \App\Models\Document([
                    'inscription_id' => $inscription->id,
                    'file_path' => '', // String vacío - NO se guarda localmente
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getClientMimeType(),
                    'file_size' => $driveFile['size'],
                    'document_type' => $type,
                    'description' => $documentDescriptions[$index] ?? null,
                    'google_drive_id' => $driveFile['id'],
                    'google_drive_link' => $driveFile['webViewLink'],
                    'stored_in_drive' => true,
                    'created_by' => Auth::id(),
                ]);
                
                $document->save();

                // Actualizar automáticamente el checklist si el tipo corresponde
                if (isset($checklistMapping[$type])) {
                    $checklistField = $checklistMapping[$type];
                    // Solo marcar y notificar si no estaba marcado previamente
                    if (!$inscription->$checklistField) {
                        $inscription->$checklistField = true;
                        $updatedFields[$checklistField] = true;
                    }
                }
            }
        }

        // Si se subió documentación completa, marcar todos los documentos del checklist
        if ($hasDocumentacionCompleta) {
            $allDocumentFields = ['has_identity_card', 'has_degree_title', 'has_academic_diploma', 'has_birth_certificate'];
            
            foreach ($allDocumentFields as $field) {
                if (!$inscription->$field) {
                    $inscription->$field = true;
                    $updatedFields[$field] = true;
                }
            }
            
            Log::info("Documentación completa detectada desde inscription_show - Todos los documentos del checklist marcados", [
                'inscription_id' => $inscription->id,
                'inscription_code' => $inscription->code
            ]);
        }

        // Guardar cambios en el checklist
        $inscription->updated_by = Auth::id();
        $inscription->save();

        // Enviar notificaciones para cada campo actualizado
        foreach ($updatedFields as $field => $value) {
            $this->sendChecklistNotifications($inscription, $field, true, 'document');
        }

        return redirect()->route('programs.inscription_show', ['program' => $program->id, 'inscription' => $inscription->id])
            ->with('success', 'Archivo(s) subido(s) correctamente.');
    }

    private function uploadToGoogleDrive($file, Inscription $inscription)
    {
        // Crear estructura jerárquica: Inscripciones -> Programa -> Estudiante
        $programName = optional($inscription->program)->name ?? 'Sin Programa';
        $studentName = trim(($inscription->first_name ?? '') . ' ' . ($inscription->paternal_surname ?? ''));
        if (empty($studentName) && !empty($inscription->full_name)) {
            $studentName = trim($inscription->full_name);
        }
        if (empty($studentName)) {
            $studentName = 'Sin Nombre - ' . ($inscription->code ?? $inscription->id);
        }

        $folderId = $this->getOrCreateHierarchicalFolder('Inscripciones', $programName, $studentName);
        
        // Usar el path temporal del archivo subido
        $tempPath = $file->getRealPath();
        
        $driveFile = $this->googleDriveService->uploadFile(
            $tempPath,
            $file->getClientOriginalName(),
            $file->getClientMimeType(),
            'Documento de inscripción',
            $folderId
        );
        
        return $driveFile;
    }

    private function getOrCreateHierarchicalFolder($mainCategory, $subfolder, $tertiaryFolder = null)
    {
        return $this->googleDriveService->createHierarchicalFolder($mainCategory, $subfolder, $tertiaryFolder);
    }

    /**
     * API endpoint para buscar universidades
     */
    public function searchUniversities(Request $request)
    {
        $search = $request->input('q', '');
        
        $universities = University::where('active', true)
            ->where(function($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('initials', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'name', 'initials']);
        
        return response()->json($universities);
    }

    /**
     * API endpoint para buscar profesiones
     */
    public function searchProfessions(Request $request)
    {
        $search = $request->input('q', '');
        
        $professions = Profession::where('is_active', true)
            ->where('name', 'like', "%{$search}%")
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'name']);
        
        return response()->json($professions);
    }

    /**
     * Enviar notificaciones cuando se actualiza un checklist
     */
    private function sendChecklistNotifications(Inscription $inscription, string $field, bool $value, string $checklistType)
    {
        try {
            Log::info("Iniciando envío de notificaciones de checklist", [
                'inscription_id' => $inscription->id,
                'field' => $field,
                'value' => $value,
                'checklist_type' => $checklistType
            ]);

            $currentUser = Auth::user();
            $usersToNotify = collect();

            // 1. Notificar al creador de la inscripción (asesor)
            if ($inscription->created_by && $inscription->created_by !== $currentUser->id) {
                $creator = User::find($inscription->created_by);
                if ($creator) {
                    $usersToNotify->push($creator);
                    Log::info("Añadido creador a notificaciones", ['creator_id' => $creator->id, 'creator_name' => $creator->name]);
                }
            }

            // 2. Notificar a usuarios con rol 'academic'
            $academics = User::role('academic')->where('id', '!=', $currentUser->id)->get();
            $usersToNotify = $usersToNotify->merge($academics);
            Log::info("Añadidos académicos a notificaciones", ['academics_count' => $academics->count()]);

            // 3. Notificar a administradores
            $admins = User::role('admin')->where('id', '!=', $currentUser->id)->get();
            $usersToNotify = $usersToNotify->merge($admins);
            Log::info("Añadidos administradores a notificaciones", ['admins_count' => $admins->count()]);

            // Eliminar duplicados
            $usersToNotify = $usersToNotify->unique('id');

            Log::info("Total de usuarios a notificar (sin duplicados)", [
                'total_count' => $usersToNotify->count(),
                'user_ids' => $usersToNotify->pluck('id')->toArray()
            ]);

            // Enviar notificaciones
            if ($usersToNotify->isNotEmpty()) {
                Notification::send(
                    $usersToNotify,
                    new InscriptionChecklistUpdatedNotification($inscription, $field, $value, $currentUser, $checklistType)
                );

                Log::info("Notificaciones de checklist enviadas exitosamente", [
                    'inscription_id' => $inscription->id,
                    'field' => $field,
                    'value' => $value,
                    'checklist_type' => $checklistType,
                    'recipients_count' => $usersToNotify->count()
                ]);
            } else {
                Log::warning("No hay usuarios para notificar", [
                    'inscription_id' => $inscription->id,
                    'current_user_id' => $currentUser->id
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Error al enviar notificaciones de checklist", [
                'inscription_id' => $inscription->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // No lanzamos la excepción para no interrumpir el flujo principal
        }
    }

    /**
     * Obtener el nombre legible de un campo del checklist
     */
    private function getFieldDisplayName(string $field, string $type): string
    {
        $fieldNames = [
            'document' => [
                'has_identity_card' => 'Cédula de identidad',
                'has_degree_title' => 'Título en provisión nacional',
                'has_academic_diploma' => 'Diploma de grado académico',
                'has_birth_certificate' => 'Certificado de nacimiento',
                'has_commitment_letter' => 'Carta de compromiso',
            ],
            'access' => [
                'was_added_to_the_group' => 'Se añadió al grupo',
                'accesses_were_sent' => 'Se enviaron accesos',
                'mail_was_sent' => 'Se envió correo',
            ]
        ];

        return $fieldNames[$type][$field] ?? $field;
    }

    /**
     * Sincronizar inscripciones desde la base de datos externa
     */
    public function sync(Request $request)
    {
        try {
            $syncService = app(\App\Services\InscriptionSyncService::class);
            
            if ($request->has('program_id')) {
                $result = $syncService->syncByProgram($request->program_id);
            } else {
                $result = $syncService->syncAll();
            }

            $advisorLinkingResult = app(AdvisorLinkingService::class)->autoLinkAdvisorsByName();
            
            $message = "Sincronización completada: {$result['synced']} inscripciones sincronizadas";
            if ($result['errors'] > 0) {
                $message .= ", {$result['errors']} errores (revisa los logs)";
            }

            if (($advisorLinkingResult['success'] ?? false) === true) {
                $linkedCount = $advisorLinkingResult['linked'] ?? 0;
                $linkingErrors = $advisorLinkingResult['errors'] ?? 0;

                $message .= ". Vinculación de asesores: {$linkedCount} inscripciones vinculadas";

                if ($linkingErrors > 0) {
                    $message .= ", {$linkingErrors} errores";
                }
            }
            
            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Error en sincronización manual de inscripciones: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al sincronizar inscripciones: ' . $e->getMessage());
        }
    }
}

