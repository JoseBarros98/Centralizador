<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Inscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Routing\Controller;

class DocumentController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:inscription.view'])->only(['index', 'show', 'serve']);
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
                $path = $file->store('documents', 'public');
                $document = new Document();
                $document->inscription_id = $inscription->id;
                $document->file_path = $path;
                $document->file_name = $file->getClientOriginalName();
                $document->file_type = $file->getClientMimeType();
                $document->description = $descriptions[$idx] ?? null;
                $document->document_type = $types[$idx] ?? null;
                $document->created_by = Auth::id();
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
        
        // Verificar si el archivo existe
        if (!Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'El archivo no existe.');
        }
        
        // Servir el archivo
        return response()->file(Storage::disk('public')->path($document->file_path));
    }

    /**
     * Eliminar un documento.
     */
    public function destroy(Inscription $inscription, Document $document, Request $request)
    {
        // Eliminar el archivo físico
        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }
        // Eliminar el registro
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
}
