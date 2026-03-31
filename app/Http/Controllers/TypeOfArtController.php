<?php

namespace App\Http\Controllers;

use App\Models\TypeOfArt;
use App\Models\TypeOfArtFile;
use App\Services\GoogleDriveService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controller;

class TypeOfArtController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:content.view'])->only(['index', 'show']);
        $this->middleware(['permission:content.create'])->only(['create', 'store']);
        $this->middleware(['permission:content.edit'])->only(['edit', 'update']);
        $this->middleware(['permission:content.delete'])->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = TypeOfArt::with('creator');

        // Búsqueda por nombre o descripción
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $typeOfArts = $query->orderBy('name')->paginate(15);
        
        return view('type_of_arts.index', compact('typeOfArts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('type_of_arts.create');
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

        $typeOfArt = new TypeOfArt([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'active' => $request->has('active'),
        ]);
        
        $typeOfArt->created_by = Auth::id();
        $typeOfArt->save();

        // Procesar archivos si existen - SOLO GOOGLE DRIVE
        if ($request->hasFile('files')) {
            $googleDriveService = new GoogleDriveService();
            $folderId = $googleDriveService->createHierarchicalFolder('Tipo de Arte', $typeOfArt->name);
            
            foreach ($request->file('files') as $index => $file) {
                try {
                    $driveFileResult = $googleDriveService->uploadFile(
                        $file->getRealPath(),
                        $file->getClientOriginalName(),
                        $file->getClientMimeType(),
                        $request->input("file_descriptions.{$index}") ?? null,
                        $folderId
                    );
                    
                    $typeOfArtFile = new TypeOfArtFile([
                        'type_of_art_id' => $typeOfArt->id,
                        'file_path' => '',
                        'file_name' => $file->getClientOriginalName(),
                        'file_type' => $file->getClientMimeType(),
                        'description' => $request->input("file_descriptions.{$index}") ?? null,
                        'stored_in_drive' => true,
                        'google_drive_id' => $driveFileResult['id'],
                    ]);
                    
                    $typeOfArtFile->created_by = Auth::id();
                    $typeOfArtFile->save();
                } catch (\Exception $e) {
                    Log::error('Error al subir archivo en store: ' . $e->getMessage());
                    // Continuar con otros archivos si hay error en uno
                }
            }
        }

        return redirect()->route('type_of_arts.show', $typeOfArt)
            ->with('success', 'Tipo de arte creado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(TypeOfArt $typeOfArt)
    {
        $typeOfArt->load('files', 'creator', 'updater');
        
        return view('type_of_arts.show', compact('typeOfArt'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TypeOfArt $typeOfArt)
    {
        $typeOfArt->load('files');
        
        return view('type_of_arts.edit', compact('typeOfArt'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TypeOfArt $typeOfArt)
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
            $typeOfArt->name = $validated['name'];
            $typeOfArt->description = $validated['description'] ?? null;
            $typeOfArt->active = $request->has('active');
            $typeOfArt->updated_by = Auth::id();
            $typeOfArt->save();

            // Procesar nuevos archivos si existen - SOLO GOOGLE DRIVE
            if ($request->hasFile('files')) {
                $googleDriveService = new GoogleDriveService();
                $folderId = $googleDriveService->createHierarchicalFolder('Tipo de Arte', $typeOfArt->name);
                
                $files = $request->file('files');
                $descriptions = $request->input('file_descriptions', []);
                
                foreach ($files as $index => $file) {
                    // Solo procesar si el archivo no está vacío y es válido
                    if ($file && $file->isValid()) {
                        try {
                            $driveFileResult = $googleDriveService->uploadFile(
                                $file->getRealPath(),
                                $file->getClientOriginalName(),
                                $file->getClientMimeType(),
                                $descriptions[$index] ?? null,
                                $folderId
                            );
                            
                            $typeOfArtFile = new TypeOfArtFile([
                                'type_of_art_id' => $typeOfArt->id,
                                'file_path' => '',
                                'file_name' => $file->getClientOriginalName(),
                                'file_type' => $file->getClientMimeType(),
                                'description' => $descriptions[$index] ?? null,
                                'stored_in_drive' => true,
                                'google_drive_id' => $driveFileResult['id'],
                            ]);
                            
                            $typeOfArtFile->created_by = Auth::id();
                            $typeOfArtFile->save();
                        } catch (\Exception $e) {
                            Log::error('Error al subir archivo en update: ' . $e->getMessage());
                            // Continuar con otros archivos si hay error en uno
                        }
                    }
                }
            }

            return redirect()->route('type_of_arts.show', $typeOfArt)
                ->with('success', 'Tipo de arte actualizado correctamente.');
                
        } catch (\Exception $e) {
            Log::error('Error al actualizar tipo de arte: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Error al actualizar el tipo de arte: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TypeOfArt $typeOfArt)
    {
        try {
            // Eliminar archivos de Google Drive
            $googleDriveService = new GoogleDriveService();
            
            foreach ($typeOfArt->files as $file) {
                if ($file->stored_in_drive && $file->google_drive_id) {
                    try {
                        $googleDriveService->deleteFile($file->google_drive_id);
                    } catch (\Exception $e) {
                        Log::error('Error al eliminar archivo de Drive: ' . $e->getMessage());
                    }
                }
            }
            
            // Eliminar el tipo de arte (los archivos se eliminarán en cascada)
            $typeOfArt->delete();
            
            return redirect()->route('type_of_arts.index')
                ->with('success', 'Tipo de arte eliminado correctamente.');
                
        } catch (\Exception $e) {
            Log::error('Error al eliminar tipo de arte: ' . $e->getMessage());
            return redirect()->back()
                ->withErrors(['error' => 'Error al eliminar el tipo de arte: ' . $e->getMessage()]);
        }
    }

    /**
     * Upload a file to a content pillar.
     */
    public function uploadFile(Request $request, TypeOfArt $typeOfArt)
    {
        $validated = $request->validate([
            'file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,mp4,zip,rar|max:512000',
            'description' => 'nullable|string',
        ]);

        try {
            $file = $request->file('file');
            
            // Subir a Google Drive
            $googleDriveService = new GoogleDriveService();
            
            // Crear estructura jerárquica: "Tipo de Arte" -> nombre específico del tipo
            $googleDriveService = new GoogleDriveService();
            $folderId = $googleDriveService->createHierarchicalFolder('Tipo de Arte', $typeOfArt->name);
            
            $fileContent = file_get_contents($file->getRealPath());
            $driveFileResult = $googleDriveService->uploadFile(
                $file->getRealPath(),
                $file->getClientOriginalName(),
                $file->getClientMimeType(),
                $validated['description'] ?? null,
                $folderId
            );
            
            $typeOfArtFile = new TypeOfArtFile([
                'type_of_art_id' => $typeOfArt->id,
                'file_path' => '', // Ya no necesitamos path local
                'file_name' => $file->getClientOriginalName(),
                'stored_in_drive' => true,
                'google_drive_id' => $driveFileResult['id'],
                'file_type' => $file->getClientMimeType(),
                'description' => $validated['description'] ?? null,
            ]);
            
            $typeOfArtFile->created_by = Auth::id();
            $typeOfArtFile->save();
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Archivo subido correctamente',
                    'file' => $typeOfArtFile
                ]);
            }
            
            return redirect()->route('type_of_arts.show', $typeOfArt)
                ->with('success', 'Archivo subido correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al subir archivo: ' . $e->getMessage());
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al subir archivo: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->withInput()
                ->withErrors(['file' => 'Error al subir archivo: ' . $e->getMessage()]);
        }
    }

    /**
     * Delete a file from a content pillar.
     */
    public function deleteFile(TypeOfArtFile $file)
    {
        try {
            $typeOfArt = $file->typeOfArt;
            
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
            
            return redirect()->route('type_of_arts.show', $typeOfArt)
                ->with('success', 'Archivo eliminado correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al eliminar archivo: ' . $e->getMessage());
            
            return redirect()->back()
                ->withErrors(['file' => 'Error al eliminar archivo: ' . $e->getMessage()]);
        }
    }

    /**
     * Serve a file from a content pillar.
     */
    public function serveFile(TypeOfArtFile $file)
    {
        try {
            if ($file->stored_in_drive && $file->google_drive_id) {
                $googleDriveService = new GoogleDriveService();
                $fileContent = $googleDriveService->downloadFile($file->google_drive_id);
                
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
            Log::error('Error al servir archivo: ' . $e->getMessage());
            abort(500, 'Error al servir archivo');
        }
    }

    /**
     * Download a file from a content pillar.
     */
    public function downloadFile(TypeOfArtFile $file)
    {
        try {
            if ($file->stored_in_drive && $file->google_drive_id) {
                $googleDriveService = new GoogleDriveService();
                $fileContent = $googleDriveService->downloadFile($file->google_drive_id);
                
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
            Log::error('Error al descargar archivo: ' . $e->getMessage());
            abort(500, 'Error al descargar archivo');
        }
    }

    /**
     * Cambia el estado activo/inactivo del pilar
     */
    public function toggleActive(TypeOfArt $typeOfArt)
    {
        $typeOfArt -> active = !$typeOfArt->active;
        $typeOfArt->updated_by = Auth::id();
        $typeOfArt->save();
        return redirect()->route('type_of_arts.show', $typeOfArt)
            ->with('success', 'Estado del tipo de arte actualizado correctamente.');
    }
}
