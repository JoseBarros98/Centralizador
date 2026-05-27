<?php

namespace App\Http\Controllers;

use App\Models\ArtRequest;
use App\Models\ArtRequestFile;
use App\Models\ArtRequestHistory;
use App\Models\User;
use App\Models\ContentPillar;
use App\Models\TypeOfArt;
use App\Services\GoogleDriveService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controller;

class ArtRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = $request->user();
            if ($user && ($user->can('content.view') || $user->can('content.view_own'))) {
                return $next($request);
            }
            abort(403);
        })->only(['index', 'show']);
        $this->middleware('permission:content.create')->only(['create', 'store']);
        $this->middleware(function ($request, $next) {
            $user = $request->user();
            if ($user && ($user->can('content.edit') || $user->can('content.edit_own'))) {
                return $next($request);
            }
            abort(403);
        })->only(['edit', 'update']);
        $this->middleware(function ($request, $next) {
            $user = $request->user();
            if ($user && ($user->can('content.delete') || $user->can('content.delete_own'))) {
                return $next($request);
            }
            abort(403);
        })->only(['destroy']);

        $this->middleware(function ($request, $next) {
            $user = $request->user();
            if ($user && ($user->can('content.view') || $user->can('content.view_own'))) {
                return $next($request);
            }
            abort(403);
        })->only(['kanban', 'calendar']);
        $this->middleware(function ($request, $next) {
            $user = $request->user();
            if ($user && ($user->can('content.edit') || $user->can('content.edit_own') || $user->can('content.toggle_active'))) {
                return $next($request);
            }
            abort(403);
        })->only(['updateStatus']);
        $this->middleware('permission:content.manage_files')->only(['addFile', 'deleteFile']);
        $this->middleware(function ($request, $next) {
            $user = $request->user();
            if ($user && ($user->can('content.view') || $user->can('content.view_own'))) {
                return $next($request);
            }
            abort(403);
        })->only(['serveFile', 'downloadFile']);
        $this->middleware('permission:content.toggle_active')->only(['toggleActive']);
    }

    private function authorizeArtRequestAccess(ArtRequest $artRequest, string $ability): void
    {
        $user = Auth::user();
        if (!$user instanceof \App\Models\User) abort(403);

        $isOwner = (int) $artRequest->created_by === (int) $user->id;

        if ($ability === 'view'   && $user->can('content.view'))                             return;
        if ($ability === 'view'   && $user->can('content.view_own')   && $isOwner)           return;
        if ($ability === 'edit'   && $user->can('content.edit'))                             return;
        if ($ability === 'edit'   && $user->can('content.edit_own')   && $isOwner)           return;
        if ($ability === 'delete' && $user->can('content.delete'))                           return;
        if ($ability === 'delete' && $user->can('content.delete_own') && $isOwner)           return;

        abort(403);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user instanceof \App\Models\User) abort(403);

        $dateFrom = \Carbon\Carbon::createFromFormat('Y-m-d',
            $request->filled('date_from') ? $request->date_from : now()->startOfMonth()->format('Y-m-d')
        )->startOfDay();

        $dateTo = \Carbon\Carbon::createFromFormat('Y-m-d',
            $request->filled('date_to') ? $request->date_to : now()->endOfMonth()->format('Y-m-d')
        )->endOfDay();

        $query = ArtRequest::with(['requester', 'designer', 'contentPillar', 'typeOfArt'])
            ->active();

        if (!$user->can('content.view')) {
            $query->where('created_by', $user->id);
        }

        if ($dateFrom) $query->where('created_at', '>=', $dateFrom);
        if ($dateTo)   $query->where('created_at', '<=', $dateTo);

        if ($request->filled('status'))           $query->where('status', $request->status);
        if ($request->filled('priority'))         $query->where('priority', $request->priority);
        if ($request->filled('designer_id'))      $query->where('designer_id', $request->designer_id);
        if ($request->filled('content_pillar_id')) $query->where('content_pillar_id', $request->content_pillar_id);
        if ($request->filled('type_of_art_id'))   $query->where('type_of_art_id', $request->type_of_art_id);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('requester', fn($q) => $q->where('name', 'like', "%{$search}%"));
            });
        }

        $artRequests = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        $designers      = User::whereHas('roles', fn($q) => $q->where('name', 'design'))->get();
        $contentPillars = ContentPillar::where('active', true)->get();
        $typeOfArts     = TypeOfArt::where('active', true)->get();

        // Stats con el mismo filtro de visibilidad y fechas
        $statsBase = ArtRequest::active();
        if (!$user->can('content.view')) $statsBase->where('created_by', $user->id);
        if ($dateFrom) $statsBase->where('created_at', '>=', $dateFrom);
        if ($dateTo)   $statsBase->where('created_at', '<=', $dateTo);

        $stats = [
            'total'       => (clone $statsBase)->count(),
            'pending'     => (clone $statsBase)->where('status', 'NO INICIADO')->count(),
            'in_progress' => (clone $statsBase)->where('status', 'EN CURSO')->count(),
            'completed'   => (clone $statsBase)->where('status', 'COMPLETO')->count(),
            'overdue'     => (clone $statsBase)->whereDate('delivery_date', '<', now()->toDateString())
                                ->whereNotIn('status', ['COMPLETO', 'CANCELADO'])->count(),
        ];

        return view('art_requests.index', compact(
            'artRequests', 'designers', 'contentPillars', 'typeOfArts', 'stats', 'dateFrom', 'dateTo'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $designers = User::whereHas('roles', function($q) {
            $q->where('name', 'design');
        })->active()->orderBy('name')->get();
        $contentPillars = ContentPillar::where('active', true)->orderBy('name')->get();
        $typeOfArts = TypeOfArt::where('active', true)->orderBy('name')->get();

        return view('art_requests.create', compact('designers', 'contentPillars', 'typeOfArts'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'request_date' => 'required|date',
            'delivery_date' => 'required|date|after_or_equal:request_date',
            'designer_id' => 'nullable|exists:users,id',
            'content_pillar_id' => 'nullable|exists:content_pillars,id',
            'type_of_art_id' => 'required|exists:type_of_art,id',
            'description' => 'required|string',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'details' => 'nullable|string',
            'status' => 'required|in:COMPLETO,NO INICIADO,EN CURSO,RETRASADO,ESPERANDO APROBACION,ESPERANDO INFORMACION,CANCELADO,EN PAUSA',
            'priority' => 'required|in:ALTA,MEDIA,BAJA',
            'observations' => 'nullable|string',
            'file' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,mp4,avi,mov,zip,rar|max:512000',
            'file_description' => 'nullable|string',
        ]);

        try {
            $artRequest = ArtRequest::create([
                'requester_id' => Auth::id(),
                'request_date' => $request->request_date,
                'delivery_date' => $request->delivery_date,
                'designer_id' => $request->designer_id,
                'content_pillar_id' => $request->content_pillar_id,
                'type_of_art_id' => $request->type_of_art_id,
                'description' => $request->description,
                'title' => $request->title,
                'content' => $request->content,
                'details' => $request->details,
                'status'          => $request->status,
                'priority'        => $request->priority,
                'observations'    => $request->observations,
                'active'          => true,
                'created_by'      => Auth::id(),
            ]);

            try {
                ArtRequestHistory::create([
                    'art_request_id' => $artRequest->id,
                    'user_id'        => Auth::id(),
                    'from_status'    => null,
                    'to_status'      => $artRequest->status,
                ]);
            } catch (\Exception $e) {
                Log::warning('No se pudo registrar historial de estado: ' . $e->getMessage());
            }

            // Manejar archivo único SOLO EN GOOGLE DRIVE
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                
                // Subir a Google Drive
                $googleDriveService = new GoogleDriveService();
                $designerName = $artRequest->designer ? $artRequest->designer->name : 'Sin Asignar';
                $requesterName = $artRequest->requester ? $artRequest->requester->name : 'Desconocido';
                $folderId = $googleDriveService->createHierarchicalFolder(
                    'Solicitudes de Arte',
                    $designerName,
                    $requesterName,
                    $artRequest->title
                );
                
                $driveFileResult = $googleDriveService->uploadFile(
                    $file->getRealPath(),
                    $file->getClientOriginalName(),
                    $file->getClientMimeType(),
                    $request->file_description ?? null,
                    $folderId
                );
                
                ArtRequestFile::create([
                    'art_request_id' => $artRequest->id,
                    'file_path' => '', // Ya no necesitamos path local
                    'file_name' => $file->getClientOriginalName(),
                    'stored_in_drive' => true,
                    'google_drive_id' => $driveFileResult['id'],
                    'file_type' => $file->getClientMimeType(),
                    'file_category' => $this->determineFileCategory($file->getClientMimeType()),
                    'description' => $request->file_description,
                    'created_by' => Auth::id(),
                ]);
            }

            return redirect()->route('art_requests.show', $artRequest)
                ->with('success', 'Solicitud de arte creada correctamente.');
                
        } catch (\Exception $e) {
            Log::error('Error al crear solicitud de arte: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Error al crear la solicitud: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ArtRequest $artRequest)
    {
        $this->authorizeArtRequestAccess($artRequest, 'view');
        $artRequest->load([
            'requester',
            'designer',
            'contentPillar',
            'typeOfArt',
            'files',
            'creator',
            'updater',
            'comments.user',
            'statusHistory.user',
        ]);
        
        return view('art_requests.show', compact('artRequest'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ArtRequest $artRequest)
    {
        $this->authorizeArtRequestAccess($artRequest, 'edit');
        $artRequest->load('files');
        
        $designers = User::whereHas('roles', function($q) {
            $q->where('name', 'design');
        })->active()->orderBy('name')->get();
        $contentPillars = ContentPillar::where('active', true)->orderBy('name')->get();
        $typeOfArts = TypeOfArt::where('active', true)->orderBy('name')->get();

        return view('art_requests.edit', compact('artRequest', 'designers', 'contentPillars', 'typeOfArts'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ArtRequest $artRequest)
    {
        $this->authorizeArtRequestAccess($artRequest, 'edit');

        $request->validate([
            'request_date' => 'required|date',
            'delivery_date' => 'required|date|after_or_equal:request_date',
            'designer_id' => 'nullable|exists:users,id',
            'content_pillar_id' => 'nullable|exists:content_pillars,id',
            'type_of_art_id' => 'required|exists:type_of_art,id',
            'description' => 'required|string',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'details' => 'nullable|string',
            'status' => 'required|in:COMPLETO,NO INICIADO,EN CURSO,RETRASADO,ESPERANDO APROBACION,ESPERANDO INFORMACION,CANCELADO,EN PAUSA',
            'priority' => 'required|in:ALTA,MEDIA,BAJA',
            'observations' => 'nullable|string',
            'file' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,mp4,avi,mov,zip,rar|max:512000',
            'file_description' => 'nullable|string',
        ]);

        try {
            $oldStatus = $artRequest->status;
            $newStatus = $request->status;

            // Actualizar SOLO los campos básicos - SIN TOCAR ARCHIVOS
            $artRequest->request_date = $request->request_date;
            $artRequest->delivery_date = $request->delivery_date;
            $artRequest->designer_id = $request->designer_id;
            $artRequest->content_pillar_id = $request->content_pillar_id;
            $artRequest->type_of_art_id = $request->type_of_art_id;
            $artRequest->description = $request->description;
            $artRequest->title = $request->title;
            $artRequest->content = $request->content;
            $artRequest->details = $request->details;
            $artRequest->status = $newStatus;
            $artRequest->priority     = $request->priority;
            $artRequest->observations = $request->observations;
            $artRequest->updated_by   = Auth::id();

            $this->applyTimeTracking($artRequest, $oldStatus, $newStatus);

            $artRequest->save();

            if ($oldStatus !== $request->status) {
                try {
                    ArtRequestHistory::create([
                        'art_request_id' => $artRequest->id,
                        'user_id'        => Auth::id(),
                        'from_status'    => $oldStatus,
                        'to_status'      => $request->status,
                    ]);
                } catch (\Exception $e) {
                    Log::warning('No se pudo registrar historial de estado: ' . $e->getMessage());
                }
            }

            return redirect()->route('art_requests.show', $artRequest)
                ->with('success', 'Solicitud de arte actualizada correctamente.');
                
        } catch (\Exception $e) {
            Log::error('Error al actualizar solicitud de arte: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Error al actualizar la solicitud: ' . $e->getMessage()]);
        }
    }

    /**
     * Agregar archivo a solicitud existente
     */
    public function addFile(Request $request, ArtRequest $artRequest)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,mp4,avi,mov,zip,rar|max:512000',
            'file_description' => 'nullable|string',
        ]);

        try {
            $file = $request->file('file');
            
            // Subir a Google Drive
            $googleDriveService = new GoogleDriveService();
            
            // Crear estructura jerárquica: "Solicitudes de Arte" -> Diseñador -> Solicitante -> Título
            $designerName = $artRequest->designer ? $artRequest->designer->name : 'Sin Asignar';
            $requesterName = $artRequest->requester ? $artRequest->requester->name : 'Desconocido';
            $folderId = $googleDriveService->createHierarchicalFolder(
                'Solicitudes de Arte',
                $designerName,
                $requesterName,
                $artRequest->title
            );
            
            $driveFileResult = $googleDriveService->uploadFile(
                $file->getRealPath(),
                $file->getClientOriginalName(),
                $file->getClientMimeType(),
                $request->file_description ?? null,
                $folderId
            );
            
            ArtRequestFile::create([
                'art_request_id' => $artRequest->id,
                'file_path' => '', // Ya no necesitamos path local
                'file_name' => $file->getClientOriginalName(),
                'stored_in_drive' => true,
                'google_drive_id' => $driveFileResult['id'],
                'file_type' => $file->getClientMimeType(),
                'file_category' => $this->determineFileCategory($file->getClientMimeType()),
                'description' => $request->file_description,
                'created_by' => Auth::id(),
            ]);

            return redirect()->route('art_requests.show', $artRequest)
                ->with('success', 'Archivo agregado correctamente.');
                
        } catch (\Exception $e) {
            Log::error('Error al agregar archivo: ' . $e->getMessage());
            
            return redirect()->back()
                ->withErrors(['file' => 'Error al agregar archivo: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ArtRequest $artRequest)
    {
        $this->authorizeArtRequestAccess($artRequest, 'delete');

        try {
            // Eliminar archivos SOLO de Google Drive
            $googleDriveService = new GoogleDriveService();
            
            foreach ($artRequest->files as $file) {
                if ($file->stored_in_drive && $file->google_drive_id) {
                    try {
                        $googleDriveService->deleteFile($file->google_drive_id);
                    } catch (\Exception $e) {
                        Log::error('Error al eliminar archivo de Drive: ' . $e->getMessage());
                    }
                }
            }
            
            // Eliminar la solicitud (los archivos se eliminarán en cascada)
            $artRequest->delete();
            
            return redirect()->route('art_requests.index')
                ->with('success', 'Solicitud de arte eliminada correctamente.');
                
        } catch (\Exception $e) {
            Log::error('Error al eliminar solicitud de arte: ' . $e->getMessage());
            
            return redirect()->back()
                ->withErrors(['error' => 'Error al eliminar la solicitud: ' . $e->getMessage()]);
        }
    }

    /**
     * Delete a file from an art request.
     */
    public function deleteFile(ArtRequestFile $file)
    {
        try {
            $artRequest = $file->artRequest;
            
            // Eliminar SOLO de Google Drive
            if ($file->stored_in_drive && $file->google_drive_id) {
                $googleDriveService = new GoogleDriveService();
                $googleDriveService->deleteFile($file->google_drive_id);
            } else {
                Log::warning('Archivo no tiene ID de Google Drive', [
                    'file_id' => $file->id,
                    'stored_in_drive' => $file->stored_in_drive,
                    'google_drive_id' => $file->google_drive_id
                ]);
            }
            
            // Eliminar registro
            $file->delete();
            
            return redirect()->route('art_requests.show', $artRequest)
                ->with('success', 'Archivo eliminado correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al eliminar archivo: ' . $e->getMessage());
            
            return redirect()->back()
                ->withErrors(['file' => 'Error al eliminar archivo: ' . $e->getMessage()]);
        }
    }

    /**
     * Serve a file from an art request.
     */
    public function serveFile(ArtRequestFile $file)
    {
        try {
            Log::info('Intentando servir archivo', [
                'file_id' => $file->id,
                'file_name' => $file->file_name,
                'stored_in_drive' => $file->stored_in_drive,
                'google_drive_id' => $file->google_drive_id
            ]);
            
            if ($file->stored_in_drive && $file->google_drive_id) {
                Log::info('Archivo está en Drive, descargando...', ['google_drive_id' => $file->google_drive_id]);
                
                $googleDriveService = new GoogleDriveService();
                $fileContent = $googleDriveService->downloadFile($file->google_drive_id);
                
                Log::info('Archivo descargado exitosamente', ['size' => strlen($fileContent)]);
                
                $headers = [
                    'Content-Type' => $file->file_type,
                    'Content-Disposition' => 'inline; filename="' . $file->file_name . '"',
                ];
                
                return response($fileContent, 200, $headers);
            } else {
                Log::error('Archivo no disponible en Drive', [
                    'file_id' => $file->id,
                    'stored_in_drive' => $file->stored_in_drive,
                    'google_drive_id' => $file->google_drive_id
                ]);
                abort(404, 'Archivo no encontrado en Google Drive');
            }
        } catch (\Exception $e) {
            Log::error('Error al servir archivo', [
                'file_id' => $file->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            abort(500, 'Error al servir archivo: ' . $e->getMessage());
        }
    }

    /**
     * Download a file from an art request.
     */
    public function downloadFile(ArtRequestFile $file)
    {
        try {
            Log::info('Intentando descargar archivo', [
                'file_id' => $file->id,
                'file_name' => $file->file_name,
                'stored_in_drive' => $file->stored_in_drive,
                'google_drive_id' => $file->google_drive_id
            ]);
            
            if ($file->stored_in_drive && $file->google_drive_id) {
                Log::info('Archivo está en Drive, descargando...', ['google_drive_id' => $file->google_drive_id]);
                
                $googleDriveService = new GoogleDriveService();
                $fileContent = $googleDriveService->downloadFile($file->google_drive_id);
                
                Log::info('Archivo descargado exitosamente', ['size' => strlen($fileContent)]);
                
                $headers = [
                    'Content-Type' => $file->file_type,
                    'Content-Disposition' => 'attachment; filename="' . $file->file_name . '"',
                ];
                
                return response($fileContent, 200, $headers);
            } else {
                Log::error('Archivo no disponible en Drive para descarga', [
                    'file_id' => $file->id,
                    'stored_in_drive' => $file->stored_in_drive,
                    'google_drive_id' => $file->google_drive_id
                ]);
                abort(404, 'Archivo no encontrado en Google Drive');
            }
        } catch (\Exception $e) {
            Log::error('Error al descargar archivo', [
                'file_id' => $file->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            abort(500, 'Error al descargar archivo: ' . $e->getMessage());
        }
    }

    /**
     * Determine file category based on MIME type.
     */
    private function determineFileCategory($mimeType)
    {
        if (str_starts_with($mimeType, 'image/')) {
            return 'IMAGEN';
        } elseif (str_starts_with($mimeType, 'video/')) {
            return 'VIDEO';
        } else {
            return 'DOCUMENTO';
        }
    }

    private function applyTimeTracking(ArtRequest $artRequest, string $oldStatus, string $newStatus): void
    {
        if ($newStatus === 'EN CURSO' && $oldStatus !== 'EN CURSO' && !$artRequest->started_at) {
            $artRequest->started_at = now();
        }

        if ($newStatus === 'COMPLETO' && $artRequest->started_at && !$artRequest->actual_hours) {
            $artRequest->actual_hours = round($artRequest->started_at->diffInMinutes(now()) / 60, 4);
        }
    }

    /**
     * Toggle active status of art request.
     */
    public function toggleActive(ArtRequest $artRequest)
    {
        $artRequest->active = !$artRequest->active;
        $artRequest->updated_by = Auth::id();
        $artRequest->save();

        return redirect()->route('art_requests.show', $artRequest)
            ->with('success', 'Estado de la solicitud actualizado correctamente.');
    }

    /**
     * Calendar view.
     */
    public function calendar(Request $request)
    {
        $user = Auth::user();
        if (!$user instanceof \App\Models\User) abort(403);

        $year = $request->filled('year') ? (int) $request->year : now()->year;

        $dateFrom = \Carbon\Carbon::create($year, 1, 1)->startOfDay();
        $dateTo   = \Carbon\Carbon::create($year, 12, 31)->endOfDay();

        $query = ArtRequest::with(['requester', 'designer', 'typeOfArt'])
            ->active()
            ->whereNotNull('delivery_date')
            ->whereBetween('delivery_date', [$dateFrom, $dateTo]);

        if (!$user->can('content.view')) {
            $query->where('created_by', $user->id);
        }

        if ($request->filled('designer_id')) {
            $query->where('designer_id', $request->designer_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $colorMap = [
            'NO INICIADO'           => '#9ca3af',
            'EN CURSO'              => '#3b82f6',
            'ESPERANDO APROBACION'  => '#f59e0b',
            'ESPERANDO INFORMACION' => '#f97316',
            'EN PAUSA'              => '#a855f7',
            'RETRASADO'             => '#ef4444',
            'COMPLETO'              => '#22c55e',
            'CANCELADO'             => '#64748b',
        ];

        $events = $query->get()->map(function ($r) use ($colorMap) {
            return [
                'id'              => $r->id,
                'title'           => $r->title,
                'start'           => $r->delivery_date->format('Y-m-d'),
                'backgroundColor' => $colorMap[$r->status] ?? '#9ca3af',
                'borderColor'     => $colorMap[$r->status] ?? '#9ca3af',
                'textColor'       => '#ffffff',
                'extendedProps'   => [
                    'url'      => route('art_requests.show', $r),
                    'status'   => $r->status,
                    'priority' => $r->priority,
                    'designer' => $r->designer?->name ?? 'Sin asignar',
                    'requester'=> $r->requester->name,
                ],
            ];
        })->values();

        $designers = User::whereHas('roles', fn($q) => $q->where('name', 'design'))->get();

        $statuses = [
            'NO INICIADO', 'EN CURSO', 'ESPERANDO APROBACION', 'ESPERANDO INFORMACION',
            'EN PAUSA', 'RETRASADO', 'COMPLETO', 'CANCELADO',
        ];

        return view('art_requests.calendar', compact('events', 'designers', 'statuses', 'year', 'colorMap'));
    }

    /**
     * Kanban board view.
     */
    public function kanban(Request $request)
    {
        $user = Auth::user();
        if (!$user instanceof \App\Models\User) abort(403);

        $dateFrom = \Carbon\Carbon::createFromFormat('Y-m-d',
            $request->filled('date_from') ? $request->date_from : now()->startOfMonth()->format('Y-m-d')
        )->startOfDay();

        $dateTo = \Carbon\Carbon::createFromFormat('Y-m-d',
            $request->filled('date_to') ? $request->date_to : now()->endOfMonth()->format('Y-m-d')
        )->endOfDay();

        $query = ArtRequest::with(['requester', 'designer', 'typeOfArt'])
            ->active()
            ->whereBetween('created_at', [$dateFrom, $dateTo]);

        if (!$user->can('content.view')) {
            $query->where('created_by', $user->id);
        }

        if ($request->filled('designer_id')) {
            $query->where('designer_id', $request->designer_id);
        }

        $priorityOrder = ['ALTA' => 0, 'MEDIA' => 1, 'BAJA' => 2];
        $all = $query->orderBy('delivery_date')->get()
            ->sortBy(fn($r) => $priorityOrder[$r->priority] ?? 1);

        $statuses = [
            'NO INICIADO'           => ['label' => 'No Iniciado',           'bg' => 'bg-gray-100',   'border' => 'border-gray-400',   'dot' => 'bg-gray-400'],
            'EN CURSO'              => ['label' => 'En Curso',              'bg' => 'bg-blue-50',    'border' => 'border-blue-500',   'dot' => 'bg-blue-500'],
            'ESPERANDO APROBACION'  => ['label' => 'Esp. Aprobación',       'bg' => 'bg-amber-50',   'border' => 'border-amber-500',  'dot' => 'bg-amber-500'],
            'ESPERANDO INFORMACION' => ['label' => 'Esp. Información',      'bg' => 'bg-purple-50',  'border' => 'border-purple-500', 'dot' => 'bg-purple-500'],
            'EN PAUSA'              => ['label' => 'En Pausa',              'bg' => 'bg-orange-50',  'border' => 'border-orange-500', 'dot' => 'bg-orange-500'],
            'RETRASADO'             => ['label' => 'Retrasado',             'bg' => 'bg-red-50',     'border' => 'border-red-500',    'dot' => 'bg-red-500'],
            'COMPLETO'              => ['label' => 'Completo',              'bg' => 'bg-green-50',   'border' => 'border-green-500',  'dot' => 'bg-green-500'],
            'CANCELADO'             => ['label' => 'Cancelado',             'bg' => 'bg-slate-100',  'border' => 'border-slate-400',  'dot' => 'bg-slate-400'],
        ];

        $grouped = collect($statuses)->mapWithKeys(fn($meta, $status) => [
            $status => $all->filter(fn($r) => $r->status === $status)->values(),
        ]);

        $designers = User::whereHas('roles', fn($q) => $q->where('name', 'design'))->get();

        return view('art_requests.kanban', compact('grouped', 'statuses', 'designers', 'dateFrom', 'dateTo'));
    }

    /**
     * Update status via AJAX (Kanban drag-and-drop).
     */
    public function updateStatus(Request $request, ArtRequest $artRequest)
    {
        $user = Auth::user();
        if (!$user instanceof \App\Models\User) abort(403);
        if (!($user->can('content.edit') || $user->can('content.edit_own') || $user->can('content.toggle_active'))) {
            abort(403);
        }

        $request->validate([
            'status' => 'required|in:NO INICIADO,EN CURSO,COMPLETO,RETRASADO,ESPERANDO APROBACION,ESPERANDO INFORMACION,CANCELADO,EN PAUSA',
        ]);

        $oldStatus = $artRequest->status;
        $newStatus = $request->status;

        $artRequest->status     = $newStatus;
        $artRequest->updated_by = Auth::id();
        $this->applyTimeTracking($artRequest, $oldStatus, $newStatus);
        $artRequest->save();

        try {
            ArtRequestHistory::create([
                'art_request_id' => $artRequest->id,
                'user_id'        => Auth::id(),
                'from_status'    => $oldStatus,
                'to_status'      => $request->status,
            ]);
        } catch (\Exception $e) {
            Log::warning('No se pudo registrar historial de estado: ' . $e->getMessage());
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'status' => $artRequest->status]);
        }

        return redirect()->route('art_requests.show', $artRequest)
            ->with('success', 'Estado actualizado correctamente.');
    }
}
