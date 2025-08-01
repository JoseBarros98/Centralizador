<?php

namespace App\Http\Controllers;

use App\Models\ContentPillar;
use App\Models\ContentPillarFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controller;

class ContentPillarController extends Controller
{
    public function __construct()
    {
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
    public function index()
    {
        $contentPillars = ContentPillar::with('creator')->orderBy('name')->paginate(15);
        
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
            'files.*' => 'file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,mp4,zip,rar|max:20480',
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
                $path = $file->store('content_pillars', 'public');
                
                $contentPillarFile = new ContentPillarFile([
                    'content_pillar_id' => $contentPillar->id,
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getClientMimeType(),
                    'description' => $request->input("file_descriptions.{$index}") ?? null,
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
                'files.*' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,mp4,zip,rar|max:20480',
                'file_descriptions' => 'nullable|array',
                'file_descriptions.*' => 'nullable|string',
            ]);

            // Actualizar los datos básicos del pilar
            $contentPillar->name = $validated['name'];
            $contentPillar->description = $validated['description'] ?? null;
            $contentPillar->active = $request->has('active');
            $contentPillar->updated_by = Auth::id();
            $contentPillar->save();

            // Procesar nuevos archivos si existen
            if ($request->hasFile('files')) {
                $files = $request->file('files');
                $descriptions = $request->input('file_descriptions', []);
                
                foreach ($files as $index => $file) {
                    // Solo procesar si el archivo no está vacío y es válido
                    if ($file && $file->isValid()) {
                        $path = $file->store('content_pillars', 'public');
                        
                        $contentPillarFile = new ContentPillarFile([
                            'content_pillar_id' => $contentPillar->id,
                            'file_path' => $path,
                            'file_name' => $file->getClientOriginalName(),
                            'file_type' => $file->getClientMimeType(),
                            'description' => $descriptions[$index] ?? null,
                        ]);
                        
                        $contentPillarFile->created_by = Auth::id();
                        $contentPillarFile->save();
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
        // Eliminar archivos físicos
        foreach ($contentPillar->files as $file) {
            Storage::disk('public')->delete($file->file_path);
        }
        
        // Eliminar el pilar (los archivos se eliminarán en cascada)
        $contentPillar->delete();
        
        return redirect()->route('content-pillars.index')
            ->with('success', 'Pilar de contenido eliminado correctamente.');
    }
    
    /**
     * Upload a file to a content pillar.
     */
    public function uploadFile(Request $request, ContentPillar $contentPillar)
    {
        $validated = $request->validate([
            'file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,mp4,zip,rar|max:20480',
            'description' => 'nullable|string',
        ]);

        try {
            $file = $request->file('file');
            $path = $file->store('content_pillars', 'public');
            
            $contentPillarFile = new ContentPillarFile([
                'content_pillar_id' => $contentPillar->id,
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'file_type' => $file->getClientMimeType(),
                'description' => $validated['description'] ?? null,
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
    public function deleteFile(ContentPillarFile $file)
    {
        try {
            $contentPillar = $file->contentPillar;
            
            // Eliminar archivo físico
            Storage::disk('public')->delete($file->file_path);
            
            // Eliminar registro
            $file->delete();
            
            return redirect()->route('content-pillars.show', $contentPillar)
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
    public function serveFile(ContentPillarFile $file)
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
     * Download a file from a content pillar.
     */
    public function downloadFile(ContentPillarFile $file)
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
}
