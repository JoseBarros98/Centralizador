<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\MarketingTeam;
use App\Models\MarketingTeamMember;
use App\Models\Inscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MarketingTeamController extends Controller
{
    public function index(): View
    {
        $teams = MarketingTeam::with(['leader'])
                              ->get();
        
        return view('marketing-teams.index', compact('teams'));
    }

    public function create(): View
    {
        $users = User::all();
        return view('marketing-teams.create', compact('users'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'leader_id' => 'required|exists:users,id',
            'description' => 'nullable|string',
            'active' => 'boolean'
        ]);

        $team = MarketingTeam::create($validated);

        return redirect()->route('marketing-teams.show', $team)
                        ->with('success', 'Equipo creado exitosamente.');
    }

    public function show(Request $request, MarketingTeam $marketingTeam): View
    {
        $team = $marketingTeam->load(['leader', 'allMembers']);

        $teamMembers = $team->allMembers
            ->sortBy(function ($member) {
                return sprintf('%d-%s', $member->pivot->active ? 0 : 1, mb_strtolower($member->name));
            })
            ->values();

        $teamAdvisors = $teamMembers->sortBy('name')->values();
        $memberIds = $teamAdvisors->pluck('id')->unique()->values();

        $availableYears = Inscription::whereIn('created_by', $memberIds)
            ->whereNotNull('inscription_date')
            ->selectRaw('YEAR(inscription_date) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->filter()
            ->values();

        if ($availableYears->isEmpty()) {
            $availableYears = collect([(int) now()->year]);
        }

        $selectedYear = $request->query('year', (string) now()->year);
        $selectedMonth = $request->query('month', (string) now()->month);
        $selectedStatus = $request->query('status');
        $selectedAdvisor = $request->query('advisor_id');

        $teamInscriptionsQuery = Inscription::with(['creator', 'programs'])
            ->whereIn('created_by', $memberIds);

        if ($selectedMonth !== 'all' && $selectedYear !== 'all') {
            $monthStart = Carbon::createFromDate((int) $selectedYear, (int) $selectedMonth, 1)->startOfMonth();
            $monthEnd = $monthStart->copy()->endOfMonth();

            $teamInscriptionsQuery->where(function ($query) use ($monthStart, $monthEnd) {
                $query->whereBetween('inscription_date', [$monthStart, $monthEnd])
                    ->orWhereHas('paymentHistory', function ($subQuery) use ($monthStart, $monthEnd) {
                        $subQuery->whereBetween('status_date', [$monthStart, $monthEnd]);
                    });
            });
        } elseif ($selectedYear !== 'all') {
            $yearStart = Carbon::createFromDate((int) $selectedYear, 1, 1)->startOfYear();
            $yearEnd = $yearStart->copy()->endOfYear();

            $teamInscriptionsQuery->where(function ($query) use ($yearStart, $yearEnd) {
                $query->whereBetween('inscription_date', [$yearStart, $yearEnd])
                    ->orWhereHas('paymentHistory', function ($subQuery) use ($yearStart, $yearEnd) {
                        $subQuery->whereBetween('status_date', [$yearStart, $yearEnd]);
                    });
            });
        } elseif ($selectedMonth !== 'all') {
            $monthStart = Carbon::createFromDate((int) now()->year, (int) $selectedMonth, 1)->startOfMonth();
            $monthEnd = $monthStart->copy()->endOfMonth();

            $teamInscriptionsQuery->where(function ($query) use ($monthStart, $monthEnd) {
                $query->whereBetween('inscription_date', [$monthStart, $monthEnd])
                    ->orWhereHas('paymentHistory', function ($subQuery) use ($monthStart, $monthEnd) {
                        $subQuery->whereBetween('status_date', [$monthStart, $monthEnd]);
                    });
            });
        }

        if (in_array($selectedStatus, ['Completo', 'Completando', 'Adelanto'], true)) {
            $teamInscriptionsQuery->where(function ($query) use ($selectedStatus) {
                $query->where('local_payment_status', $selectedStatus)
                    ->orWhere(function ($fallbackQuery) use ($selectedStatus) {
                        $fallbackQuery->whereNull('local_payment_status')
                            ->where('status', $selectedStatus);
                    });
            });
        }

        if (!empty($selectedAdvisor) && $memberIds->contains((int) $selectedAdvisor)) {
            $teamInscriptionsQuery->where('created_by', (int) $selectedAdvisor);
        }

        $teamInscriptions = $teamInscriptionsQuery
            ->orderByDesc('inscription_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        // Mantener la misma lógica visual del index de inscripciones para estado/monto por mes.
        if ($selectedMonth !== 'all' && $selectedYear !== 'all') {
            foreach ($teamInscriptions as $inscription) {
                $inscription->display_month = (int) $selectedMonth;
                $inscription->display_year = (int) $selectedYear;
            }
        }

        $availableUsers = User::whereDoesntHave('marketingTeamMemberships', function ($query) use ($team) {
            $query->where('team_id', $team->id)->where('active', true);
        })->get();

        return view('marketing-teams.show', compact(
            'team',
            'teamMembers',
            'availableUsers',
            'teamInscriptions',
            'teamAdvisors',
            'availableYears',
            'selectedYear',
            'selectedMonth',
            'selectedStatus',
            'selectedAdvisor'
        ));
    }

    public function edit(MarketingTeam $marketingTeam): View
    {
        $users = User::all();
        return view('marketing-teams.edit', compact('marketingTeam', 'users'));
    }

    public function update(Request $request, MarketingTeam $marketingTeam): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'leader_id' => 'required|exists:users,id',
            'description' => 'nullable|string',
            'active' => 'boolean'
        ]);

        $marketingTeam->update($validated);

        return redirect()->route('marketing-teams.show', $marketingTeam)
                        ->with('success', 'Equipo actualizado exitosamente.');
    }

    public function destroy(MarketingTeam $marketingTeam): RedirectResponse
    {
        // Cambiar estado en lugar de eliminar completamente
        $marketingTeam->update(['active' => false]);

        return redirect()->route('marketing-teams.index')
                        ->with('success', 'Equipo desactivado exitosamente.');
    }

    public function deactivate(MarketingTeam $marketingTeam): RedirectResponse
    {
        $marketingTeam->update(['active' => false]);

        return redirect()->route('marketing-teams.index')
                        ->with('success', 'Equipo desactivado exitosamente.');
    }

    

    public function addMember(Request $request, MarketingTeam $marketingTeam): RedirectResponse
    {
        $currentUser = Auth::user();
        $isAdmin = $currentUser && $currentUser->hasRole('admin');

        if (!$isAdmin && (int) Auth::id() !== (int) $marketingTeam->leader_id) {
            return redirect()->back()->with('error', 'Solo el administrador o el líder del equipo puede gestionar miembros.');
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        // Buscar membresía previa (activa o inactiva) para evitar duplicados por índice único.
        $existingMembership = MarketingTeamMember::where('team_id', $marketingTeam->id)
                                        ->where('user_id', $validated['user_id'])
                                        ->orderByDesc('active')
                                        ->orderByDesc('id')
                                        ->first();

        if ($existingMembership && $existingMembership->active) {
            return redirect()->back()
                            ->with('error', 'El usuario ya es miembro activo del equipo.');
        }

        if ($existingMembership && !$existingMembership->active) {
            $existingMembership->update([
                'active' => true,
                'joined_at' => now(),
                'left_at' => null,
            ]);

            return redirect()->back()
                            ->with('success', 'Miembro reactivado exitosamente en el equipo.');
        }

        MarketingTeamMember::create([
            'team_id' => $marketingTeam->id,
            'user_id' => $validated['user_id'],
            'active' => true,
            'joined_at' => now()
        ]);

        return redirect()->back()
                        ->with('success', 'Miembro agregado exitosamente al equipo.');
    }

    public function deactivateMember(MarketingTeam $marketingTeam, User $user): RedirectResponse
    {
        $currentUser = Auth::user();
        $isAdmin = $currentUser && $currentUser->hasRole('admin');

        if (!$isAdmin && (int) Auth::id() !== (int) $marketingTeam->leader_id) {
            return redirect()->back()->with('error', 'Solo el administrador o el líder del equipo puede gestionar miembros.');
        }

        $member = MarketingTeamMember::where('team_id', $marketingTeam->id)
                                    ->where('user_id', $user->id)
                                    ->where('active', true)
                                    ->first();

        if (!$member) {
            return redirect()->back()
                            ->with('error', 'El usuario no es miembro activo del equipo.');
        }

        // Si ya existe un registro inactivo para este mismo usuario/equipo,
        // eliminar el duplicado legado para poder cambiar este registro a inactivo
        // sin violar el índice único (team_id, user_id, active).
        $existingInactive = MarketingTeamMember::where('team_id', $marketingTeam->id)
                                    ->where('user_id', $user->id)
                                    ->where('active', false)
                                    ->where('id', '!=', $member->id)
                                    ->first();

        if ($existingInactive) {
            $existingInactive->delete();
        }

        $member->update([
            'active' => false,
            'left_at' => now()
        ]);

        return redirect()->back()
                        ->with('success', 'Miembro dado de baja del equipo exitosamente.');
    }

    /**
     * @deprecated Mantener por compatibilidad con rutas anteriores.
     */
    public function removeMember(MarketingTeam $marketingTeam, User $user): RedirectResponse
    {
        return $this->deactivateMember($marketingTeam, $user);
    }

    /**
     * Restore (reactivate) the specified team.
     */
    public function restore(MarketingTeam $marketingTeam): RedirectResponse
    {
        $marketingTeam->update(['active' => true]);

        return redirect()->route('marketing-teams.index')
                        ->with('success', 'Equipo reactivado exitosamente.');
    }
}
