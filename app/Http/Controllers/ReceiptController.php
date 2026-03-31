<?php

namespace App\Http\Controllers;

use App\Models\Inscription;
use App\Models\Receipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controller;

class ReceiptController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
        $this->middleware(['permission:inscription.view'])->only(['show', 'serveFile']);
        $this->middleware(['permission:inscription.edit'])->only(['create', 'store']);
        $this->middleware(['permission:inscription.delete'])->only(['destroy']);
    }

    /**
     * Show the form for creating a new receipt.
     */
    public function create(Inscription $inscription)
    {
        return view('receipts.create', compact('inscription'));
    }

    /**
     * Store a newly created receipt in storage.
     */
    public function store(Request $request, Inscription $inscription)
    {
        $request->validate([
            'receipt_file' => 'required|file|mimes:jpeg,png,jpg,pdf|max:2048',
        ]);

        try {
            if ($request->hasFile('receipt_file')) {
                $file = $request->file('receipt_file');
                
                // Crear estructura jerárquica en Google Drive
                $googleDriveService = new \App\Services\GoogleDriveService();
                $programName = optional($inscription->program)->name ?? 'Sin Programa';
                $studentName = trim(($inscription->first_name ?? '') . ' ' . ($inscription->paternal_surname ?? ''));
                if (empty($studentName) && !empty($inscription->full_name)) {
                    $studentName = trim($inscription->full_name);
                }
                if (empty($studentName)) {
                    $studentName = 'Sin Nombre - ' . ($inscription->code ?? $inscription->id);
                }
                
                // Subir archivo DIRECTAMENTE a Google Drive - SIN fallback local
                $folderId = $googleDriveService->createHierarchicalFolder('Inscripciones', $programName, $studentName);
                
                $driveFileResult = $googleDriveService->uploadFile(
                    $file->getRealPath(),
                    $file->getClientOriginalName(),
                    $file->getClientMimeType(),
                    'Recibo de pago',
                    $folderId
                );

                $receipt = new Receipt([
                    'inscription_id' => $inscription->id,
                    'file_path' => '', // String vacío - NO se guarda localmente
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getClientMimeType(),
                    'file_size' => $driveFileResult['size'] ?? 0,
                    'google_drive_id' => $driveFileResult['id'],
                    'google_drive_link' => $driveFileResult['webViewLink'] ?? '',
                    'stored_in_drive' => true,
                    'created_by' => Auth::user()->id,
                ]);
                
                $receipt->save();

                return redirect()->route('inscriptions.show', $inscription)
                    ->with('success', 'Recibo subido correctamente.');
            }

            return back()->with('error', 'Error: No se recibió ningún archivo.');
        } catch (\Exception $e) {
            Log::error('Error al subir recibo: ' . $e->getMessage());
            return back()->with('error', 'Error al subir el archivo: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified receipt.
     */
    public function show(Inscription $inscription, Receipt $receipt)
    {
        if ($receipt->inscription_id !== $inscription->id) {
            abort(404);
        }

        return view('receipts.show', compact('inscription', 'receipt'));
    }

    /**
     * Serve the file directly without checking inscription relationship.
     * This is useful for direct access to files.
     */
    public function serveFile(Receipt $receipt)
    {
        try {
            if ($receipt->stored_in_drive && $receipt->google_drive_id) {
                $googleDriveService = new \App\Services\GoogleDriveService();
                $fileContent = $googleDriveService->downloadFile($receipt->google_drive_id);
                
                $headers = [
                    'Content-Type' => $receipt->file_type,
                    'Content-Disposition' => 'inline; filename="' . $receipt->file_name . '"',
                ];
                
                return response($fileContent, 200, $headers);
            } else {
                Log::error('Recibo no disponible en Drive', [
                    'receipt_id' => $receipt->id,
                    'stored_in_drive' => $receipt->stored_in_drive,
                    'google_drive_id' => $receipt->google_drive_id
                ]);
                abort(404, 'Archivo no encontrado en Google Drive');
            }
        } catch (\Exception $e) {
            Log::error('Error al servir recibo: ' . $e->getMessage());
            abort(500, 'Error al servir archivo: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified receipt from storage.
     */
    public function destroy(Inscription $inscription, Receipt $receipt)
    {
        if ($receipt->inscription_id !== $inscription->id) {
            abort(404);
        }

        try {
            // Eliminar archivo SOLO de Google Drive
            if ($receipt->stored_in_drive && $receipt->google_drive_id) {
                $googleDriveService = new \App\Services\GoogleDriveService();
                $googleDriveService->deleteFile($receipt->google_drive_id);
            } else {
                Log::warning('Recibo no tiene ID de Google Drive', [
                    'receipt_id' => $receipt->id,
                    'stored_in_drive' => $receipt->stored_in_drive,
                    'google_drive_id' => $receipt->google_drive_id
                ]);
            }

            $receipt->delete();

            return redirect()->route('inscriptions.show', $inscription)
                ->with('success', 'Recibo eliminado correctamente.');
                
        } catch (\Exception $e) {
            Log::error('Error al eliminar recibo: ' . $e->getMessage());
            return redirect()->back()
                ->withErrors(['error' => 'Error al eliminar el recibo: ' . $e->getMessage()]);
        }
    }
}
