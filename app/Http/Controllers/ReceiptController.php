<?php

namespace App\Http\Controllers;

use App\Models\Inscription;
use App\Models\Receipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Auth;
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

        if ($request->hasFile('receipt_file')) {
            $file = $request->file('receipt_file');
            $path = $file->store('receipts', 'public');

            $receipt = new Receipt();
            $receipt->inscription_id = $inscription->id;
            $receipt->file_path = $path;
            $receipt->created_by = Auth::user()->id;
            $receipt->save();

            return redirect()->route('inscriptions.show', $inscription)
                ->with('success', 'Recibo subido correctamente.');
        }

        return back()->with('error', 'Error al subir el archivo.');
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
        // // Verificar si el usuario tiene permiso para ver inscripciones
        // if (!Auth::user()->hasPermissionTo('inscription.view')) {
        //     abort(403, 'No tiene permiso para ver este archivo');
        // }

        // Verificar si el archivo existe
        if (!Storage::disk('public')->exists($receipt->file_path)) {
            abort(404, 'Archivo no encontrado');
        }

        // Obtener el archivo
        $file = Storage::disk('public')->get($receipt->file_path);
        
        // Obtener el tipo MIME basado en la extensión del archivo
        $extension = pathinfo($receipt->file_path, PATHINFO_EXTENSION);
        $mimeTypes = [
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
        ];
        $type = $mimeTypes[$extension] ?? 'application/octet-stream';

        // Devolver la respuesta con el archivo
        return Response::make($file, 200, [
            'Content-Type' => $type,
            'Content-Disposition' => 'inline; filename="' . basename($receipt->file_path) . '"',
        ]);
    }

    /**
     * Remove the specified receipt from storage.
     */
    public function destroy(Inscription $inscription, Receipt $receipt)
    {
        if ($receipt->inscription_id !== $inscription->id) {
            abort(404);
        }

        // Delete the file from storage
        if (Storage::disk('public')->exists($receipt->file_path)) {
            Storage::disk('public')->delete($receipt->file_path);
        }

        $receipt->delete();

        return redirect()->route('inscriptions.show', $inscription)
            ->with('success', 'Recibo eliminado correctamente.');
    }
}
