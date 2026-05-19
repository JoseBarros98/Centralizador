<?php

namespace App\Http\Controllers;

use App\Models\MarketingContact;
use App\Models\MarketingContactResponse;
use App\Models\MarketingContactCall;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class MarketingContactController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = $request->user();
            if ($user && (
                $user->can('marketing_contact.view') ||
                $user->can('marketing_contact.view_team') ||
                $user->can('marketing_contact.create')
            )) {
                return $next($request);
            }
            abort(403);
        })->only(['index', 'show']);
        $this->middleware(['permission:marketing_contact.create'])->only(['create', 'store', 'storeResponse', 'storeCall']);
        $this->middleware(function ($request, $next) {
            $user = $request->user();
            if ($user && ($user->can('marketing_contact.edit') || $user->can('marketing_contact.edit_own'))) {
                return $next($request);
            }
            abort(403);
        })->only(['edit', 'update']);
        $this->middleware(function ($request, $next) {
            $user = $request->user();
            if ($user && ($user->can('marketing_contact.delete') || $user->can('marketing_contact.delete_own'))) {
                return $next($request);
            }
            abort(403);
        })->only(['destroy', 'destroyResponse', 'destroyCall']);
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user instanceof \App\Models\User) abort(403);

        $query = MarketingContact::with(['program', 'creator'])
            ->withCount(['responses', 'calls']);

        // Visibilidad según rol
        if (!$user->can('marketing_contact.view')) {
            $visibleIds = $this->getVisibleCreatorIds($user);
            $query->whereIn('created_by', $visibleIds);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('telefono', 'like', "%{$search}%")
                  ->orWhere('profesion', 'like', "%{$search}%");
            });
        }
        if ($request->filled('referencia'))  $query->where('referencia', $request->referencia);
        if ($request->filled('origen'))      $query->where('origen', $request->origen);
        if ($request->filled('estado_pago')) $query->where('estado_pago', $request->estado_pago);
        if ($request->filled('program_id'))  $query->where('program_id', $request->program_id);
        if ($request->filled('conversion'))  $query->where('conversion', (bool) $request->conversion);

        $contacts = $query->orderBy('fecha', 'desc')->paginate(25)->withQueryString();
        $programs  = Program::where('status', 'INSCRIPCION')->orderBy('name')->get(['id', 'name']);

        $isTeamLeader = $user->leadsActiveMarketingTeam();

        return view('marketing_contacts.index', compact('contacts', 'programs', 'isTeamLeader'));
    }

    public function searchPrograms(Request $request)
    {
        $q = $request->input('q', '');
        $programs = Program::where('status', 'INSCRIPCION')
            ->when(strlen($q) >= 2, fn($query) => $query->where('name', 'like', "%{$q}%"))
            ->orderBy('name')
            ->limit(15)
            ->get(['id', 'name']);

        return response()->json($programs);
    }

    public function create()
    {
        $programs = Program::where('status', 'INSCRIPCION')->orderBy('name')->get(['id', 'name']);
        return view('marketing_contacts.create', compact('programs'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateContact($request);
        $validated['created_by'] = Auth::id();
        $validated['updated_by'] = Auth::id();

        MarketingContact::create($validated);

        return redirect()->route('marketing-contacts.index')
            ->with('success', 'Contacto registrado correctamente.');
    }

    public function show(MarketingContact $marketingContact)
    {
        $this->authorizeAccess($marketingContact, 'view');
        $marketingContact->load(['program', 'creator', 'responses.creator', 'calls.creator', 'inscription']);
        return view('marketing_contacts.show', compact('marketingContact'));
    }

    public function edit(MarketingContact $marketingContact)
    {
        $this->authorizeAccess($marketingContact, 'edit');
        // Programas en inscripción + el programa actual del contacto si ya no está en ese estado
        $programs = Program::where('status', 'INSCRIPCION')
            ->when($marketingContact->program_id, fn($q) =>
                $q->orWhere('id', $marketingContact->program_id)
            )
            ->orderBy('name')
            ->get(['id', 'name']);
        return view('marketing_contacts.edit', compact('marketingContact', 'programs'));
    }

    public function update(Request $request, MarketingContact $marketingContact)
    {
        $this->authorizeAccess($marketingContact, 'edit');
        $validated = $this->validateContact($request, $marketingContact->id);
        $validated['updated_by'] = Auth::id();
        $marketingContact->update($validated);

        return redirect()->route('marketing-contacts.show', $marketingContact)
            ->with('success', 'Contacto actualizado correctamente.');
    }

    public function destroy(MarketingContact $marketingContact)
    {
        $this->authorizeAccess($marketingContact, 'delete');
        $marketingContact->delete();

        return redirect()->route('marketing-contacts.index')
            ->with('success', 'Contacto eliminado correctamente.');
    }

    public function storeResponse(Request $request, MarketingContact $marketingContact)
    {
        $this->authorizeAccess($marketingContact, 'view');

        $request->validate([
            'respuesta' => 'required|string|max:1000',
            'fecha'     => 'nullable|date',
        ]);

        $marketingContact->responses()->create([
            'respuesta'  => $request->respuesta,
            'fecha'      => $request->fecha,
            'created_by' => Auth::id(),
        ]);

        return back()->with('success', 'Respuesta registrada correctamente.');
    }

    public function destroyResponse(MarketingContact $marketingContact, MarketingContactResponse $response)
    {
        $this->authorizeAccess($marketingContact, 'edit');
        $response->delete();

        return back()->with('success', 'Respuesta eliminada.');
    }

    public function storeCall(Request $request, MarketingContact $marketingContact)
    {
        $this->authorizeAccess($marketingContact, 'view');

        $request->validate([
            'respuesta'    => 'required|string|max:1000',
            'fecha_llamada' => 'required|date',
        ]);

        $marketingContact->calls()->create([
            'respuesta'    => $request->respuesta,
            'fecha_llamada' => $request->fecha_llamada,
            'created_by'   => Auth::id(),
        ]);

        return back()->with('success', 'Llamada registrada correctamente.');
    }

    public function destroyCall(MarketingContact $marketingContact, MarketingContactCall $call)
    {
        $this->authorizeAccess($marketingContact, 'edit');
        $call->delete();

        return back()->with('success', 'Llamada eliminada.');
    }

    private function validateContact(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'nombre'       => 'required|string|max:255',
            'profesion'    => 'nullable|string|max:255',
            'program_id'   => 'nullable|exists:programs,id',
            'telefono'     => 'required|string|max:50',
            'referencia'   => 'required|in:' . implode(',', MarketingContact::REFERENCIAS),
            'origen'       => 'required|in:' . implode(',', MarketingContact::ORIGENES),
            'fecha'        => 'required|date',
            'estado_pago'  => 'nullable|in:' . implode(',', MarketingContact::ESTADOS_PAGO),
            'conversion'   => 'boolean',
            'inscription_id' => 'nullable|exists:inscriptions,id',
        ]);
    }

    private function authorizeAccess(MarketingContact $contact, string $ability): void
    {
        $user = Auth::user();
        if (!$user instanceof \App\Models\User) abort(403);
        $isOwner = (int) $contact->created_by === (int) $user->id;

        // Permiso global (admin)
        if ($ability === 'view'   && $user->can('marketing_contact.view'))   return;
        if ($ability === 'edit'   && $user->can('marketing_contact.edit'))   return;
        if ($ability === 'delete' && $user->can('marketing_contact.delete')) return;

        // Propietario
        if ($ability === 'edit'   && $user->can('marketing_contact.edit_own')   && $isOwner) return;
        if ($ability === 'delete' && $user->can('marketing_contact.delete_own') && $isOwner) return;

        // El asesor puede ver/agregar interacciones a sus propios contactos
        if ($ability === 'view' && $isOwner) return;

        // El líder de equipo puede ver los contactos de sus miembros solo si tiene el permiso view_team
        if ($ability === 'view' && $user->can('marketing_contact.view_team') && $this->userIsTeamLeaderOf($user, (int) $contact->created_by)) return;

        abort(403, 'Solo puedes acceder a los contactos de tu equipo.');
    }

    private function getVisibleCreatorIds(\App\Models\User $user): array
    {
        $ids = [$user->id];

        // El líder ve también los contactos de sus miembros solo si tiene el permiso view_team
        if ($user->can('marketing_contact.view_team') && $user->leadsActiveMarketingTeam()) {
            $memberIds = $user->leadTeams()
                ->where('active', true)
                ->with('members')
                ->get()
                ->flatMap(fn($team) => $team->members->pluck('id'))
                ->unique()
                ->toArray();

            $ids = array_unique(array_merge($ids, $memberIds));
        }

        return $ids;
    }

    private function userIsTeamLeaderOf(\App\Models\User $leader, int $memberId): bool
    {
        if (!$leader->can('marketing_contact.view_team') || !$leader->leadsActiveMarketingTeam()) return false;

        return $leader->leadTeams()
            ->where('active', true)
            ->whereHas('members', fn($q) => $q->where('users.id', $memberId))
            ->exists();
    }
}
