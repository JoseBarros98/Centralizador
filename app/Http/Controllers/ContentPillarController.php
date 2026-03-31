<?php

namespace App\Http\Controllers;

use App\Models\ContentPillar;
use App\Models\ContentPillarFile;
use App\Services\GoogleDriveService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controller;

class ContentPillarController extends Controller
{
    protected $googleDriveService;

    public function __construct(GoogleDriveService $googleDriveService)
    {
        $this->googleDriveService = $googleDriveService;
        $this->middleware('permission:content_pillar.view')->only(['index', 'show']);
        $this->middleware('permission:content_pillar.create')->only(['create', 'store']);
        $this->middleware('permission:content_pillar.edit')->only(['edit', 'update']);
        $this->middleware('permission:content_pillar.delete')->only(['destroy']);
        $this->middleware('permission:content_pillar.manage_files')->only(['uploadFile', 'deleteFile']);
        $this->middleware('permission:content_pillar.view')->only(['serveFile', 'downloadFile']);
        $this->middleware('permission:content_pillar.toggle_active')->only(['toggleActive']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ContentPillar::with('creator');

        // Búsqueda por nombre o descripción
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $contentPillars = $query->orderBy('name')->paginate(15);
        
        return view('content_pillars.index', compact('contentPillars'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('content_pillars.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'active' => 'boolean',
            'files' => 'nullable|array',
            'files.*' => 'file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,mp4,zip,rar|max:512000',
            'file_descriptions' => 'nullable|array',
            'file_descriptions.*' => 'nullable|string',
        ]);

        $contentPillar = new ContentPillar([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'active' => $request->has('active'),
        ]);
        
        $contentPillar->created_by = Auth::id();
        $contentPillar->save();

        // Procesar archivos si existen
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $index => $file) {
                // Subir archivo DIRECTAMENTE a Google Drive - SIN fallback local
                $driveFile = $this->uploadToGoogleDrive($file, $contentPillar->name);
                
                $contentPillarFile = new ContentPillarFile([
                    'content_pillar_id' => $contentPillar->id,
                    'file_path' => '', // String vacío - NO se guarda localmente
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getClientMimeType(),
                    'description' => $request->input("file_descriptions.{$index}") ?? null,
                    'google_drive_id' => $driveFile['id'],
                    'google_drive_link' => $driveFile['webViewLink'],
                    'file_size' => $driveFile['size'],
                    'stored_in_drive' => true,
                ]);
                
                $contentPillarFile->created_by = Auth::id();
                $contentPillarFile->save();
            }
        }

        return redirect()->route('content-pillars.show', $contentPillar)
            ->with('success', 'Pilar de contenido creado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ContentPillar $contentPillar)
    {
        $contentPillar->load('files', 'creator', 'updater');
        
        return view('content_pillars.show', compact('contentPillar'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ContentPillar $contentPillar)
    {
        $contentPillar->load('files');
        
        return view('content_pillars.edit', compact('contentPillar'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ContentPillar $contentPillar)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'active' => 'boolean',
                'files' => 'nullable|array',
                'files.*' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,mp4,zip,rar|max:512000',
                'file_descriptions' => 'nullable|array',
                'file_descriptions.*' => 'nullable|string',
            ]);

            // Actualizar los datos básicos del pilar
            $contentPillar->name = $validated['name'];
            $contentPillar->description = $validated['description'] ?? null;
            $contentPillar->active = $request->has('active');
            $contentPillar->updated_by = Auth::id();
            $contentPillar->save();

            // Procesar nuevos archivos si existen - SOLO GOOGLE DRIVE
            if ($request->hasFile('files')) {
                $files = $request->file('files');
                $descriptions = $request->input('file_descriptions', []);
                
                foreach ($files as $index => $file) {
                    // Solo procesar si el archivo no está vacío y es válido
                    if ($file && $file->isValid()) {
                        try {
                            // Subir archivo DIRECTAMENTE a Google Drive
                            $driveFile = $this->uploadToGoogleDrive($file, $contentPillar->name);
                            
                            $contentPillarFile = new ContentPillarFile([
                                'content_pillar_id' => $contentPillar->id,
                                'file_path' => '', // String vacío - NO se guarda localmente
                                'file_name' => $file->getClientOriginalName(),
                                'file_type' => $file->getClientMimeType(),
                                'description' => $descriptions[$index] ?? null,
                                'google_drive_id' => $driveFile['id'],
                                'google_drive_link' => $driveFile['webViewLink'],
                                'stored_in_drive' => true,
                            ]);
                            
                            $contentPillarFile->created_by = Auth::id();
                            $contentPillarFile->save();
                        } catch (\Exception $e) {
                            Log::error('Error al subir archivo en update: ' . $e->getMessage());
                            // Continuar con otros archivos si hay error en uno
                        }
                    }
                }
            }

            return redirect()->route('content-pillars.show', $contentPillar)
                ->with('success', 'Pilar de contenido actualizado correctamente.');
                
        } catch (\Exception $e) {
            Log::error('Error al actualizar pilar de contenido: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Error al actualizar el pilar de contenido: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ContentPillar $contentPillar)
    {
        try {
            // Eliminar archivos SOLO de Google Drive
            foreach ($contentPillar->files as $file) {
                if ($file->stored_in_drive && $file->google_drive_id) {
                    try {
                        $googleDriveService = new GoogleDriveService();
                        $googleDriveService->deleteFile($file->google_drive_id);
                    } catch (\Exception $e) {
                        Log::error('Error al eliminar archivo de Drive: ' . $e->getMessage());
                    }
                }
            }
            
            // Eliminar el pilar (los archivos se eliminarán en cascada)
            $contentPillar->delete();
            
            return redirect()->route('content-pillars.index')
                ->with('success', 'Pilar de contenido eliminado correctamente.');
                
        } catch (\Exception $e) {
            Log::error('Error al eliminar pilar de contenido: ' . $e->getMessage());
            return redirect()->back()
                ->withErrors(['error' => 'Error al eliminar el pilar de contenido: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Upload a file to a content pillar.
     */
    public function uploadFile(Request $request, ContentPillar $contentPillar)
    {
        $validated = $request->validate([
            'file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,mp4,zip,rar|max:512000',
            'description' => 'nullable|string',
        ]);

        $file = $request->file('file');
        
        // Subir archivo DIRECTAMENTE a Google Drive - SIN fallback local
        $driveFile = $this->uploadToGoogleDrive($file, $contentPillar->name);
        
        $contentPillarFile = new ContentPillarFile([
            'content_pillar_id' => $contentPillar->id,
            'file_path' => '', // String vacío - NO se guarda localmente
            'file_name' => $file->getClientOriginalName(),
            'file_type' => $file->getClientMimeType(),
            'description' => $validated['description'] ?? null,
            'google_drive_id' => $driveFile['id'],
            'google_drive_link' => $driveFile['webViewLink'],
            'file_size' => $driveFile['size'],
            'stored_in_drive' => true,
        ]);
        
        $contentPillarFile->created_by = Auth::id();
        $contentPillarFile->save();
        
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Archivo subido correctamente',
                'file' => $contentPillarFile
            ]);
        }
        
        return redirect()->route('content-pillars.show', $contentPillar)
            ->with('success', 'Archivo subido correctamente.');
    }
    
    /**
     * Delete a file from a content pillar.
     */
    public function deleteFile(ContentPillarFile $file)
    {
        $contentPillar = $file->contentPillar;
        
        // TODOS los archivos están en Google Drive - eliminar de Drive
        $this->googleDriveService->deleteFile($file->google_drive_id);
        
        // Eliminar registro de la base de datos
        $file->delete();
        
        return redirect()->route('content-pillars.show', $contentPillar)
            ->with('success', 'Archivo eliminado correctamente.');
    }
    
    /**
     * Serve a file from a content pillar.
     */
    public function serveFile(ContentPillarFile $file)
    {
        // TODOS los archivos están en Google Drive
        $content = $this->googleDriveService->downloadFile($file->google_drive_id);
        
        $headers = [
            'Content-Type' => $file->file_type,
            'Content-Disposition' => 'inline; filename="' . $file->file_name . '"',
        ];
        
        return response($content, 200, $headers);
    }
    
    /**
     * Download a file from a content pillar.
     */
    public function downloadFile(ContentPillarFile $file)
    {
        // TODOS los archivos están en Google Drive
        $content = $this->googleDriveService->downloadFile($file->google_drive_id);
        
        return response($content, 200, [
            'Content-Type' => $file->file_type,
            'Content-Disposition' => 'attachment; filename="' . $file->file_name . '"',
        ]);
    }

    /**
     * Cambia el estado activo/inactivo del pilar
     */
    public function toggleActive(ContentPillar $contentPillar)
    {
        $contentPillar -> active = !$contentPillar->active;
        $contentPillar->updated_by = Auth::id();
        $contentPillar->save();
        return redirect()->route('content-pillars.show', $contentPillar)
            ->with('success', 'Estado del pilar de contenido actualizado correctamente.');
    }

    /**
     * Helper method para subir archivos a Google Drive
     */
    private function uploadToGoogleDrive($file, $folderName)
    {
        // Crear carpeta si no existe
        $folderId = $this->getOrCreateDriveFolder($folderName);
        
        // Usar el path temporal del archivo subido
        $tempPath = $file->getRealPath();
        
        try {
            $driveFile = $this->googleDriveService->uploadFile(
                $tempPath,
                $file->getClientOriginalName(),
                $file->getClientMimeType(),
                'Archivo subido desde Laravel',
                $folderId
            );
            
            return $driveFile;
        } catch (\Exception $e) {
            Log::error('Error en uploadToGoogleDrive: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtener o crear carpeta en Google Drive con estructura jerárquica
     */
    private function getOrCreateDriveFolder($folderName)
    {
        try {
            // Usar estructura jerárquica: "Pilar de Contenido" -> nombre específico del pilar
            $folderId = $this->googleDriveService->createHierarchicalFolder('Pilar de Contenido', $folderName);
            
            Log::info('Carpeta jerárquica creada para pilar de contenido', [
                'folder_id' => $folderId,
                'main_category' => 'Pilar de Contenido',
                'subfolder' => $folderName
            ]);
            
            return $folderId;
        } catch (\Exception $e) {
            Log::error('Error creando carpeta jerárquica: ' . $e->getMessage());
            
            // Fallback: usar el método anterior
            $configFolderId = config('services.google.drive_folder_id');
            if (!empty($configFolderId)) {
                return $configFolderId;
            }
            
            // Si todo falla, usar null para subir a la raíz
            return null;
        }
    }
}
