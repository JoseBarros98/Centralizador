<?php

namespace App\Http\Controllers;

use App\Models\GraduationCite;
use App\Models\Inscription;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class GraduationCiteController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:graduation_cite.view')->only(['index', 'show']);
        $this->middleware('permission:graduation_cite.create')->only(['create', 'store']);
        $this->middleware('permission:graduation_cite.edit')->only(['edit', 'update']);
        $this->middleware('permission:graduation_cite.delete')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $query = GraduationCite::with(['participants', 'creator'])
            ->withCount('participants');

        if ($request->filled('cite_number')) {
            $query->where('cite_number', 'like', '%' . $request->cite_number . '%');
        }

        if ($request->filled('payment_type')) {
            $query->where('payment_type', $request->payment_type);
        }

        if ($request->filled('participant')) {
            $search = $request->participant;

            $query->whereHas('participants', function ($participantQuery) use ($search) {
                $participantQuery->where('ci', 'like', '%' . $search . '%')
                    ->orWhere('full_name', 'like', '%' . $search . '%');
            });
        }

        $graduationCites = $query->orderByDesc('cite_date')
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        return view('graduation-cites.index', compact('graduationCites'));
    }

    public function create()
    {
        return view('graduation-cites.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateRequest($request);
        $participantIds = array_unique($validated['participant_ids']);
        $participantCount = count($participantIds);
        $totalAmount = $participantCount * (float) $validated['amount_per_participant'];

        $graduationCite = GraduationCite::create([
            'cite_number' => $validated['cite_number'],
            'cite_date' => $validated['cite_date'],
            'payment_type' => $validated['payment_type'],
            'amount_per_participant' => $validated['amount_per_participant'],
            'total_amount' => $totalAmount,
            'observations' => $validated['observations'] ?? null,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        $this->syncParticipants($graduationCite, $participantIds);

        return redirect()
            ->route('graduation-cites.show', $graduationCite)
            ->with('success', 'CITE de titulación creado correctamente.');
    }

    public function show(GraduationCite $graduationCite)
    {
        $graduationCite->load(['participants.programs', 'creator', 'updater']);

        return view('graduation-cites.show', compact('graduationCite'));
    }

    public function edit(GraduationCite $graduationCite)
    {
        $graduationCite->load('participants.programs');

        return view('graduation-cites.edit', compact('graduationCite'));
    }

    public function update(Request $request, GraduationCite $graduationCite)
    {
        $validated = $this->validateRequest($request, $graduationCite);
        $participantIds = array_unique($validated['participant_ids']);
        $participantCount = count($participantIds);
        $totalAmount = $participantCount * (float) $validated['amount_per_participant'];

        $graduationCite->update([
            'cite_number' => $validated['cite_number'],
            'cite_date' => $validated['cite_date'],
            'payment_type' => $validated['payment_type'],
            'amount_per_participant' => $validated['amount_per_participant'],
            'total_amount' => $totalAmount,
            'observations' => $validated['observations'] ?? null,
            'updated_by' => Auth::id(),
        ]);

        $this->syncParticipants($graduationCite, $participantIds);

        return redirect()
            ->route('graduation-cites.show', $graduationCite)
            ->with('success', 'CITE de titulación actualizado correctamente.');
    }

    public function destroy(GraduationCite $graduationCite)
    {
        $graduationCite->delete();

        return redirect()
            ->route('graduation-cites.index')
            ->with('success', 'CITE de titulación eliminado correctamente.');
    }

    public function searchParticipants(Request $request)
    {
        abort_unless(
            Auth::user()?->can('graduation_cite.view')
                || Auth::user()?->can('graduation_cite.create')
                || Auth::user()?->can('graduation_cite.edit'),
            403
        );

        $validated = $request->validate([
            'q' => 'required|string|min:2|max:255',
        ]);

        $search = trim($validated['q']);

        $participants = Inscription::with('programs:id,name')
            ->where(function ($query) use ($search) {
                $query->where('ci', 'like', '%' . $search . '%')
                    ->orWhere('full_name', 'like', '%' . $search . '%');
            })
            ->where(function ($query) {
                $query->whereNull('external_inscription_status')
                    ->orWhere('external_inscription_status', '!=', 'Preinscrito');
            })
            ->orderBy('full_name')
            ->limit(20)
            ->get()
            ->map(function (Inscription $inscription) {
                $programNames = $inscription->programs
                    ->pluck('name')
                    ->filter()
                    ->values()
                    ->implode(', ');

                return [
                    'id' => $inscription->id,
                    'full_name' => $inscription->getFullName(),
                    'ci' => $inscription->ci,
                    'program' => $programNames !== '' ? $programNames : 'Sin programa',
                ];
            })
            ->values();

        return response()->json($participants);
    }

    protected function validateRequest(Request $request, ?GraduationCite $graduationCite = null): array
    {
        return $request->validate([
            'cite_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('graduation_cites', 'cite_number')->ignore($graduationCite),
            ],
            'cite_date' => 'required|date',
            'payment_type' => 'required|in:inscripcion,matricula,colegiatura,certificacion',
            'amount_per_participant' => 'required|numeric|min:0',
            'observations' => 'nullable|string|max:2000',
            'participant_ids' => 'required|array|min:1',
            'participant_ids.*' => 'integer|exists:inscriptions,id',
        ], [
            'participant_ids.required' => 'Debes agregar al menos un participante al CITE.',
            'participant_ids.min' => 'Debes agregar al menos un participante al CITE.',
        ]);
    }

    protected function syncParticipants(GraduationCite $graduationCite, array $participantIds): void
    {
        $participants = Inscription::with('programs:id,name')
            ->whereIn('id', $participantIds)
            ->get()
            ->keyBy('id');

        $syncData = [];

        foreach (array_unique($participantIds) as $participantId) {
            $participant = $participants->get($participantId);

            if (!$participant) {
                continue;
            }

            $programNames = $participant->programs
                ->pluck('name')
                ->filter()
                ->values()
                ->implode(', ');

            $syncData[$participant->id] = [
                'participant_full_name' => $participant->getFullName(),
                'participant_ci' => $participant->ci,
                'participant_program' => $programNames !== '' ? $programNames : 'Sin programa',
            ];
        }

        $graduationCite->participants()->sync($syncData);
    }
}