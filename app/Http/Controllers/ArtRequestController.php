<?php

namespace App\Http\Controllers;

use App\Models\ArtRequest;
use App\Models\ArtRequestFile;
use App\Models\User;
use App\Models\ContentPillar;
use App\Models\TypeOfArt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controller;

class ArtRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:content.view')->only(['index', 'show']);
        $this->middleware('permission:content.create')->only(['create', 'store']);
        $this->middleware('permission:content.edit')->only(['edit', 'update']);
        $this->middleware('permission:content.delete')->only(['destroy']);

        $this->middleware('permission:content.manage_files')->only(['addFile', 'deleteFile']);
        $this->middleware('permission:content.view')->only(['serveFile', 'downloadFile']);
        $this->middleware('permission:content.toggle_active')->only(['toggleActive']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ArtRequest::with(['requester', 'designer', 'contentPillar', 'typeOfArt'])
            ->active();

        // Filtros
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('designer_id')) {
            $query->where('designer_id', $request->designer_id);
        }

        if ($request->filled('content_pillar_id')) {
            $query->where('content_pillar_id', $request->content_pillar_id);
        }

        if ($request->filled('type_of_art_id')) {
            $query->where('type_of_art_id', $request->type_of_art_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('requester', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $artRequests = $query->orderBy('created_at', 'desc')->paginate(15);

        // Datos para filtros
        $designers = User::whereHas('roles', function($q) {
            $q->where('name', 'designer');
        })->get();
        
        $contentPillars = ContentPillar::where('active', true)->get();
        $typeOfArts = TypeOfArt::where('active', true)->get();

        // Estadísticas
        $stats = [
            'total' => ArtRequest::active()->count(),
            'pending' => ArtRequest::active()->where('status', 'NO INICIADO')->count(),
            'in_progress' => ArtRequest::active()->where('status', 'EN CURSO')->count(),
            'completed' => ArtRequest::active()->where('status', 'COMPLETO')->count(),
            'overdue' => ArtRequest::active()->where('delivery_date', '<', now())
                ->whereNotIn('status', ['COMPLETO', 'CANCELADO'])->count(),
        ];

        return view('art_requests.index', compact(
            'artRequests', 'designers', 'contentPillars', 'typeOfArts', 'stats'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $designers = User::whereHas('roles', function($q) {
            $q->where('name', 'design');
        })->get();
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
            'status' => 'required|in:COMPLETO,NO INICIADO,EN CURSO,RETRASADO,ESPERANDO APROBACIÓN,ESPERANDO INFORMACIÓN,CANCELADO,EN PAUSA',
            'priority' => 'required|in:ALTA,MEDIA,BAJA',
            'observations' => 'nullable|string',
            'file' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,mp4,avi,mov,zip,rar|max:51200',
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
                'status' => $request->status,
                'priority' => $request->priority,
                'observations' => $request->observations,
                'active' => true,
                'created_by' => Auth::id(),
            ]);

            // Manejar archivo único - SIMPLE
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('art_requests', $filename, 'public');
                
                ArtRequestFile::create([
                    'art_request_id' => $artRequest->id,
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
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
        $artRequest->load([
            'requester', 
            'designer', 
            'contentPillar', 
            'typeOfArt', 
            'files', 
            'creator', 
            'updater'
        ]);
        
        return view('art_requests.show', compact('artRequest'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ArtRequest $artRequest)
    {
        $artRequest->load('files');
        
        $designers = User::whereHas('roles', function($q) {
            $q->where('name', 'design');
        })->get();
        $contentPillars = ContentPillar::where('active', true)->orderBy('name')->get();
        $typeOfArts = TypeOfArt::where('active', true)->orderBy('name')->get();

        return view('art_requests.edit', compact('artRequest', 'designers', 'contentPillars', 'typeOfArts'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ArtRequest $artRequest)
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
            'status' => 'required|in:COMPLETO,NO INICIADO,EN CURSO,RETRASADO,ESPERANDO APROBACIÓN,ESPERANDO INFORMACIÓN,CANCELADO,EN PAUSA',
            'priority' => 'required|in:ALTA,MEDIA,BAJA',
            'observations' => 'nullable|string',
            'file' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,mp4,avi,mov,zip,rar|max:51200',
            'file_description' => 'nullable|string',
        ]);

        try {
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
            $artRequest->status = $request->status;
            $artRequest->priority = $request->priority;
            $artRequest->observations = $request->observations;
            $artRequest->updated_by = Auth::id();
            $artRequest->save();

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
            'file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,mp4,avi,mov,zip,rar|max:51200',
            'file_description' => 'nullable|string',
        ]);

        try {
            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('art_requests', $filename, 'public');
            
            ArtRequestFile::create([
                'art_request_id' => $artRequest->id,
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
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
        try {
            // Eliminar archivos físicos
            foreach ($artRequest->files as $file) {
                Storage::disk('public')->delete($file->file_path);
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
            
            // Eliminar archivo físico
            Storage::disk('public')->delete($file->file_path);
            
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
            $path = Storage::disk('public')->path($file->file_path);
            
            if (!file_exists($path)) {
                abort(404, 'Archivo no encontrado');
            }
            
            $headers = [
                'Content-Type' => $file->file_type,
                'Content-Disposition' => 'inline; filename="' . $file->file_name . '"',
            ];
            
            return response()->file($path, $headers);
        } catch (\Exception $e) {
            Log::error('Error al servir archivo: ' . $e->getMessage());
            abort(500, 'Error al servir archivo');
        }
    }

    /**
     * Download a file from an art request.
     */
    public function downloadFile(ArtRequestFile $file)
    {
        try {
            $path = Storage::disk('public')->path($file->file_path);
            
            if (!file_exists($path)) {
                abort(404, 'Archivo no encontrado');
            }
            
            return response()->download($path, $file->file_name);
        } catch (\Exception $e) {
            Log::error('Error al descargar archivo: ' . $e->getMessage());
            abort(500, 'Error al descargar archivo');
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
}
