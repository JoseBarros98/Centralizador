<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Inscription;
use App\Services\GoogleDriveService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controller;

class DocumentController extends Controller
{
    protected $googleDriveService;

    public function __construct(GoogleDriveService $googleDriveService)
    {
        $this->googleDriveService = $googleDriveService;
        $this->middleware(['permission:inscription.view'])->only(['index', 'show', 'serve', 'download']);
        $this->middleware(['permission:inscription.create'])->only(['create', 'store']);
        $this->middleware(['permission:inscription.edit'])->only(['edit', 'update']);
        $this->middleware(['permission:inscription.delete'])->only(['destroy']);
    }

    /**
     * Mostrar el formulario para crear un nuevo documento.
     */
    public function create(Inscription $inscription)
    {
        return view('documents.create', compact('inscription'));
    }

    /**
     * Almacenar un nuevo documento.
     */
    public function store(Request $request, Inscription $inscription)
    {
        $request->validate([
            'document_files'   => 'required|array',
            'document_files.*' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx|max:10240',
            'document_types'   => 'required|array',
            'document_types.*' => 'required|string|max:100',
            'document_descriptions'   => 'nullable|array',
            'document_descriptions.*' => 'nullable|string|max:255',
        ]);

        $files = $request->file('document_files');
        $types = $request->input('document_types');
        $descriptions = $request->input('document_descriptions', []);

        $uploaded = 0;
        foreach ($files as $idx => $file) {
            if ($file) {
                // Subir archivo DIRECTAMENTE a Google Drive - SIN fallback local
                $driveFile = $this->uploadToGoogleDrive($file, $inscription);
                
                $document = new Document([
                    'inscription_id' => $inscription->id,
                    'file_path' => '', // String vacío - NO se guarda localmente
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getClientMimeType(),
                    'file_size' => $driveFile['size'],
                    'description' => $descriptions[$idx] ?? null,
                    'document_type' => $types[$idx] ?? null,
                    'google_drive_id' => $driveFile['id'],
                    'google_drive_link' => $driveFile['webViewLink'],
                    'stored_in_drive' => true,
                    'created_by' => Auth::id(),
                ]);
                
                $document->save();
                $uploaded++;
            }
        }

        if ($uploaded > 0) {
            return redirect()->route('inscriptions.show', $inscription)
                ->with('success', 'Documento(s) subido(s) correctamente.');
        }

        return redirect()->back()
            ->with('error', 'No se pudo subir el/los documento(s).');
    }

    /**
     * Servir el archivo del documento.
     */
    public function serve(Document $document)
    {
        // TODOS los archivos están en Google Drive
        $content = $this->googleDriveService->downloadFile($document->google_drive_id);
        $headers = [
            'Content-Type' => $document->file_type,
            'Content-Disposition' => 'inline; filename="' . $document->file_name . '"',
        ];
        return response($content, 200, $headers);
    }

    /**
     * Descargar el archivo del documento.
     */
    public function download(Document $document)
    {
        // TODOS los archivos están en Google Drive
        $content = $this->googleDriveService->downloadFile($document->google_drive_id);
        $headers = [
            'Content-Type' => $document->file_type,
            'Content-Disposition' => 'attachment; filename="' . $document->file_name . '"',
        ];
        return response($content, 200, $headers);
    }

    /**
     * Eliminar un documento.
     */
    public function destroy(Inscription $inscription, Document $document, Request $request)
    {
        // TODOS los archivos están en Google Drive - eliminar de Drive
        $this->googleDriveService->deleteFile($document->google_drive_id);
        
        // Eliminar el registro de la base de datos
        $document->delete();

        // Redirección según origen
        if ($request->has('program_id')) {
            $programId = $request->input('program_id');
            return redirect()->route('programs.inscription_show', ['program' => $programId, 'inscription' => $inscription->id])
                ->with('success', 'Documento eliminado correctamente.');
        }
        return redirect()->route('inscriptions.show', $inscription)
            ->with('success', 'Documento eliminado correctamente.');
    }

    /**
     * Helper method para subir archivos a Google Drive
     */
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
            'Documento subido desde inscripción',
            $folderId
        );
        
        return $driveFile;
    }

    /**
     * Helper method para obtener o crear estructura jerárquica de carpetas
     */
    private function getOrCreateHierarchicalFolder($mainCategory, $subfolder, $tertiaryFolder = null)
    {
        return $this->googleDriveService->createHierarchicalFolder($mainCategory, $subfolder, $tertiaryFolder);
    }
}
