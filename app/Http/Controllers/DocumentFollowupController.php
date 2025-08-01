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
        $this->middleware('permission:inscription.view')->only(['show']);
        $this->middleware('permission:inscription.edit')->only(['create', 'store', 'addContact', 'storeContact', 'deleteContact']);
    }

    public function create(Program $program, Inscription $inscription)
    {
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

        return redirect()->route('document_followups.show', [
            'program' => $program->id,
            'inscription' => $inscription->id,
        ])->with('success', 'Seguimiento de documentos creado correctamente.');
    }

    public function show(Program $program, Inscription $inscription)
    {
        $followup = DocumentFollowup::where('inscription_id', $inscription->id)->firstOrFail();
        $calls = $followup->calls;
        $messages = $followup->messages;

        return view('document_followups.show', compact('program', 'inscription', 'followup', 'calls', 'messages'));
    }

    public function addContact(Program $program, Inscription $inscription, string $type)
    {
        if (!in_array($type, ['call', 'message'])) {
            abort(404);
        }

        $followup = DocumentFollowup::where('inscription_id', $inscription->id)->firstOrFail();

        return view('document_followups.add_contact', compact('program', 'inscription', 'followup', 'type'));
    }

    public function storeContact(Request $request, Program $program, Inscription $inscription)
    {
        $followup = DocumentFollowup::where('inscription_id', $inscription->id)->firstOrFail();

        $validated = $request->validate([
            'type' => 'required|in:call,message',
            'contact_date' => 'required|date',
            'response_status' => 'required|in:answered,not_answered',
            'response_date' => 'nullable|date|required_if:response_status,answered',
            'notes' => 'required|string',
        ]);

        DocumentFollowupContact::create([
            'document_followup_id' => $followup->id,
            'type' => $validated['type'],
            'contact_date' => $validated['contact_date'],
            'response_status' => $validated['response_status'],
            'response_date' => $validated['response_status'] === 'answered' ? $validated['response_date'] : null,
            'notes' => $validated['notes'],
        ]);

        return redirect()->route('document_followups.show', [
            'program' => $program->id,
            'inscription' => $inscription->id,
        ])->with('success', 'Contacto registrado correctamente.');
    }

    public function deleteContact(Program $program, Inscription $inscription, DocumentFollowupContact $contact)
    {
        $followup = DocumentFollowup::where('inscription_id', $inscription->id)->firstOrFail();

        if ($contact->document_followup_id !== $followup->id) {
            abort(403);
        }

        $contact->delete();

        return redirect()->route('document_followups.show', [
            'program' => $program->id,
            'inscription' => $inscription->id,
        ])->with('success', 'Contacto eliminado correctamente.');
    }
}
