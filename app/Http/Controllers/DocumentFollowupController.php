<?php

namespace App\Http\Controllers;

use App\Models\DocumentFollowup;
use App\Models\DocumentFollowupContact;
use App\Models\Inscription;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controller;

class DocumentFollowupController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:inscription.view')->only(['show', 'history', 'showFollowup']);
        $this->middleware('permission:inscription.edit')->only(['create', 'store', 'addContact', 'storeContact', 'deleteContact', 'close', 'createNew']);
    }

    public function create(Program $program, Inscription $inscription)
    {
        // Si es una petición AJAX para modal, retornar solo el contenido
        if (request()->ajax() || request()->wantsJson()) {
            return view('document_followups.create_modal_content', compact('program', 'inscription'));
        }

        return view('document_followups.create', compact('program', 'inscription'));
    }

    public function store(Request $request, Program $program, Inscription $inscription)
    {
        $validated = $request->validate([
            'observations' => 'required|string',
        ]);

        $followup = DocumentFollowup::create([
            'inscription_id' => $inscription->id,
            'observations' => $validated['observations'],
            'created_by' => Auth::id(),
        ]);

        // Si es una petición AJAX, retornar JSON
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Seguimiento de documentos creado correctamente.',
                'followup_id' => $followup->id
            ]);
        }

        return redirect()->route('document_followups.show', [
            'program' => $program->id,
            'inscription' => $inscription->id,
        ])->with('success', 'Seguimiento de documentos creado correctamente.');
    }

    public function show(Program $program, Inscription $inscription)
    {
        $followups = DocumentFollowup::where('inscription_id', $inscription->id)
            ->with('contacts')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Obtener el seguimiento activo (abierto) o el más reciente
        $followup = $followups->where('status', 'open')->first() ?? $followups->first();

        return view('document_followups.show', compact('program', 'inscription', 'followups', 'followup'));
    }

    public function addContact(Program $program, Inscription $inscription, string $type)
    {
        if (!in_array($type, ['call', 'message'])) {
            abort(404);
        }

        $followup = DocumentFollowup::where('inscription_id', $inscription->id)
            ->where('status', 'open')
            ->firstOrFail();

        return view('document_followups.add_contact', compact('program', 'inscription', 'followup', 'type'));
    }

    public function storeContact(Request $request, Program $program, Inscription $inscription)
    {
        $followup = DocumentFollowup::where('inscription_id', $inscription->id)
            ->where('status', 'open')
            ->firstOrFail();

        $validated = $request->validate([
            'type' => 'required|in:call,message',
            'contact_date' => 'required|date',
            'got_response' => 'nullable|boolean',
            'response_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        DocumentFollowupContact::create([
            'document_followup_id' => $followup->id,
            'contact_type' => $validated['type'],
            'contact_date' => $validated['contact_date'],
            'response_status' => $validated['got_response'] ? 'answered' : 'not_answered',
            'response_date' => $validated['got_response'] ? $validated['response_date'] : null,
            'notes' => $validated['notes'] ?? '',
        ]);

        return redirect()->route('document_followups.show', [
            'program' => $program->id,
            'inscription' => $inscription->id,
        ])->with('success', 'Contacto registrado correctamente.');
    }

    public function deleteContact(Program $program, Inscription $inscription, DocumentFollowupContact $contact)
    {
        $followup = DocumentFollowup::where('inscription_id', $inscription->id)
            ->where('status', 'open')
            ->firstOrFail();

        if ($contact->document_followup_id !== $followup->id) {
            abort(403);
        }

        $contact->delete();

        return redirect()->route('document_followups.show', [
            'program' => $program->id,
            'inscription' => $inscription->id,
        ])->with('success', 'Contacto eliminado correctamente.');
    }

    public function close(Program $program, Inscription $inscription)
    {
        $followup = DocumentFollowup::where('inscription_id', $inscription->id)
            ->where('status', 'open')
            ->firstOrFail();

        $followup->close();

        return redirect()->route('document_followups.show', [
            'program' => $program->id,
            'inscription' => $inscription->id,
        ])->with('success', 'Seguimiento cerrado correctamente.');
    }

    public function createNew(Program $program, Inscription $inscription)
    {
        // Verificar que no hay seguimientos abiertos
        $openFollowup = DocumentFollowup::where('inscription_id', $inscription->id)
            ->where('status', 'open')
            ->first();

        if ($openFollowup) {
            return redirect()->route('document_followups.show', [
                'program' => $program->id,
                'inscription' => $inscription->id,
            ])->with('error', 'Ya existe un seguimiento abierto. Debe cerrar el seguimiento actual antes de crear uno nuevo.');
        }

        return view('document_followups.create', compact('program', 'inscription'));
    }

    public function history(Program $program, Inscription $inscription)
    {
        $followups = DocumentFollowup::where('inscription_id', $inscription->id)
            ->with(['contacts', 'creator'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('document_followups.history', compact('program', 'inscription', 'followups'));
    }

    public function showFollowup(Program $program, Inscription $inscription, DocumentFollowup $followup)
    {
        // Verificar que el seguimiento pertenece a esta inscripción
        if ($followup->inscription_id !== $inscription->id) {
            abort(404);
        }

        return view('document_followups.show_single', compact('program', 'inscription', 'followup'));
    }
}
