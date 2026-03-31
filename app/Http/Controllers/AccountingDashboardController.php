<?php

namespace App\Http\Controllers;

use App\Models\ProgramAllocation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountingDashboardController extends Controller
{
    public function index(Request $request): View
    {
        // Obtener mes y año del request, por defecto los actuales
        $mes = $request->get('mes', date('n'));
        $year = $request->get('year', date('Y'));
        
        // Crear query base filtrada por mes (campo mes) y año (created_at)
        $query = ProgramAllocation::where('mes', $mes)
            ->whereYear('created_at', $year);
        
        // Obtener totales para el mes actual
        $totalAsignacion = $query->sum('asignacion_programa');
        
        // Calcular cobrado (máximo monto de cada asignación) para el mes
        $totalCobrado = $query->get()
            ->sum(function($allocation) {
                $montos = [
                    $allocation->monto_al_5 ?? 0,
                    $allocation->monto_al_10 ?? 0,
                    $allocation->monto_al_15 ?? 0,
                    $allocation->monto_al_20 ?? 0,
                    $allocation->monto_al_25 ?? 0,
                    $allocation->monto_al_30 ?? 0
                ];
                return max($montos);
            });

        $porcentajeTotalAlcanzado = $totalAsignacion > 0 ? ($totalCobrado / $totalAsignacion) * 100 : 0;


        // Datos para gráficos
        $chartData = [
            'months' => $this->getMonthLabels(),
            'assignmentsByMonth' => $this->getAssignmentsByMonth($year),
            'collectionsByMonth' => $this->getCollectionsByMonth($year),
            'topAccountants' => $this->getTopAccountants($mes, $year),
            'categories' => $this->getCategoriesData($mes, $year),
            'programComparison' => $this->getProgramComparison($mes, $year)
        ];

        return view('dashboard.accounting', compact(
            'totalAsignacion',
            'totalCobrado',
            'porcentajeTotalAlcanzado',
            'chartData',
            'mes',
            'year'
        ));
    }

    private function getMonthLabels(): array
    {
        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[] = \Carbon\Carbon::createFromDate(null, $i, 1)->format('M');
        }
        return $months;
    }

    private function getAssignmentsByMonth($year): array
    {
        $data = [];
        for ($month = 1; $month <= 12; $month++) {
            $total = ProgramAllocation::where('mes', $month)
                ->whereYear('created_at', $year)
                ->sum('asignacion_programa');
            $data[] = $total;
        }
        return $data;
    }

    private function getCollectionsByMonth($year): array
    {
        $data = [];
        for ($month = 1; $month <= 12; $month++) {
            $total = ProgramAllocation::where('mes', $month)
                ->whereYear('created_at', $year)
                ->get()
                ->sum(function($allocation) {
                    $montos = [
                        $allocation->monto_al_5 ?? 0,
                        $allocation->monto_al_10 ?? 0,
                        $allocation->monto_al_15 ?? 0,
                        $allocation->monto_al_20 ?? 0,
                        $allocation->monto_al_25 ?? 0,
                        $allocation->monto_al_30 ?? 0
                    ];
                    return max($montos);
                });
            $data[] = $total;
        }
        return $data;
    }

    private function getTopAccountants($mes, $year): array
    {
        $accountants = ProgramAllocation::where('mes', $mes)
            ->whereYear('created_at', $year)
            ->where('responsable_cartera', '!=', null)
            ->selectRaw('responsable_cartera, COUNT(*) as count')
            ->groupBy('responsable_cartera')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        $responsableIds = $accountants
            ->pluck('responsable_cartera')
            ->filter(fn ($value) => ctype_digit((string) $value))
            ->map(fn ($value) => (int) $value)
            ->unique()
            ->values();

        $usersById = User::whereIn('id', $responsableIds)
            ->pluck('name', 'id');

        $labels = $accountants
            ->map(function ($item) use ($usersById) {
                $rawValue = $item->responsable_cartera;
                if (ctype_digit((string) $rawValue)) {
                    $userId = (int) $rawValue;
                    return $usersById->get($userId, (string) $rawValue);
                }

                // Compatibilidad con registros antiguos que guardaban nombre directamente.
                return (string) $rawValue;
            })
            ->toArray();

        return [
            'labels' => $labels,
            'values' => $accountants->pluck('count')->toArray()
        ];
    }

    private function getCategoriesData($mes, $year): array
    {
        $allocations = ProgramAllocation::where('mes', $mes)
            ->whereYear('created_at', $year)
            ->with('program')->get();
        
        $categories = [
            'Diplomado' => 0,
            'Maestría' => 0,
            'Curso' => 0,
            'Especialidad' => 0
        ];

        foreach ($allocations as $allocation) {
            if ($allocation->program) {
                if (str_starts_with($allocation->program->name, 'Diplomado')) {
                    $categories['Diplomado']++;
                } elseif (str_starts_with($allocation->program->name, 'Maestría')) {
                    $categories['Maestría']++;
                } elseif (str_starts_with($allocation->program->name, 'Curso')) {
                    $categories['Curso']++;
                } elseif (str_starts_with($allocation->program->name, 'Especialidad')) {
                    $categories['Especialidad']++;
                }
            }
        }

        return [
            'labels' => array_keys($categories),
            'values' => array_values($categories)
        ];
    }

    private function getProgramComparison($mes, $year): array
    {
        $allocations = ProgramAllocation::where('mes', $mes)
            ->whereYear('created_at', $year)
            ->with('program')
            ->selectRaw('program_id, SUM(asignacion_programa) as total_assigned')
            ->groupBy('program_id')
            ->orderByDesc('total_assigned')
            ->limit(10)
            ->get();

        $labels = [];
        $assignments = [];
        $collections = [];

        foreach ($allocations as $allocation) {
            if ($allocation->program) {
                $labels[] = $allocation->program->name;
                $assignments[] = $allocation->total_assigned ?? 0;
                
                $totalCobrado = ProgramAllocation::where('mes', $mes)
                    ->whereYear('created_at', $year)
                    ->where('program_id', $allocation->program_id)
                    ->get()
                    ->sum(function($alloc) {
                        $montos = [
                            $alloc->monto_al_5 ?? 0,
                            $alloc->monto_al_10 ?? 0,
                            $alloc->monto_al_15 ?? 0,
                            $alloc->monto_al_20 ?? 0,
                            $alloc->monto_al_25 ?? 0,
                            $alloc->monto_al_30 ?? 0
                        ];
                        return max($montos);
                    });
                
                $collections[] = $totalCobrado;
            }
        }

        return [
            'labels' => $labels,
            'assignments' => $assignments,
            'collections' => $collections
        ];
    }
}
