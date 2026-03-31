<?php

namespace App\Http\Controllers;

use App\Models\ArtRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ArtRequestDashboardController extends Controller
{
    /**
     * Mostrar el dashboard de solicitudes de arte
     */
    public function index(Request $request)
    {
        // Configurar Carbon en español
        Carbon::setLocale('es');

        $currentYear = Carbon::now()->year;
        $currentMonth = Carbon::now()->month;

        // Obtener parámetros de filtro
        $month = $request->get('month', $currentMonth);
        $year = $request->get('year', $currentYear);
        $week = $request->get('week');
        $designerId = $request->get('designer_id', null);

        // Convertir a enteros y limpiar valores vacíos
        $month = (int)$month;
        $year = (int)$year;
        $week = !empty($week) ? (int)$week : null;

        // Obtener primero y último día del mes seleccionado
        $monthStart = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();

        // Calcular semanas del mes seleccionado
        $weeks = $this->getWeeksOfMonth($monthStart, $monthEnd);

        // Pre-cargar solicitudes activas del mes para elegir una semana por defecto con datos.
        $monthRequestsQuery = ArtRequest::where('active', true)
            ->whereBetween('created_at', [$monthStart->copy()->startOfDay(), $monthEnd->copy()->endOfDay()]);

        if ($designerId) {
            $monthRequestsQuery->where('designer_id', $designerId);
        }

        $monthArtRequests = $monthRequestsQuery->get(['created_at']);

        $requestsByWeek = [];
        foreach ($weeks as $weekNum => $weekData) {
            $requestsByWeek[$weekNum] = $monthArtRequests->filter(function ($requestItem) use ($weekData) {
                return $requestItem->created_at->between(
                    $weekData['start']->copy()->startOfDay(),
                    $weekData['end']->copy()->endOfDay()
                );
            })->count();
        }
        
        // Determinar la semana por defecto (actual si el mes es el actual, sino la primera)
        $defaultWeek = 1;
        if ($month == $currentMonth && $year == $currentYear) {
            $today = Carbon::now();
            foreach ($weeks as $weekNum => $weekData) {
                if ($today->between($weekData['start'], $weekData['end'])) {
                    $defaultWeek = $weekNum;
                    break;
                }
            }
        }

        $selectedWeek = $week ? (int)$week : $defaultWeek;

        // Si no se eligió semana y la semana por defecto está vacía,
        // usar la semana más reciente del mes que sí tenga registros.
        if (!$week && (($requestsByWeek[$selectedWeek] ?? 0) === 0)) {
            $weeksWithData = array_keys(array_filter($requestsByWeek, function ($count) {
                return $count > 0;
            }));

            if (!empty($weeksWithData)) {
                $selectedWeek = max($weeksWithData);
            }
        }

        // Calcular rango de fechas según la semana seleccionada
        $weekStart = $weeks[$selectedWeek]['start'] ?? $monthStart;
        $weekEnd = $weeks[$selectedWeek]['end'] ?? $monthEnd;

        // Query base
        $query = ArtRequest::where('active', true)
            ->whereBetween('created_at', [$weekStart->copy()->startOfDay(), $weekEnd->copy()->endOfDay()]);

        // Aplicar filtro de diseñador si existe
        if ($designerId) {
            $query->where('designer_id', $designerId);
        }

        // Obtener solicitudes de arte para la semana y diseñador seleccionados
        $artRequests = $query->orderBy('created_at', 'desc')->get();

        $completedCount = $artRequests->where('status', 'COMPLETO')->count();

        $periodEndForAverage = $weekEnd->copy()->endOfDay();
        if ($weekStart->lte(now()) && $weekEnd->gte(now())) {
            $periodEndForAverage = now();
        }

        $daysForAverage = max(
            1,
            $weekStart->copy()->startOfDay()->diffInDays($periodEndForAverage->copy()->startOfDay()) + 1
        );

        $avgCompletedPerDay = round($completedCount / $daysForAverage, 2);

        // Estadísticas
        $stats = [
            'total' => $artRequests->count(),
            'pending' => $artRequests->where('status', 'NO INICIADO')->count(),
            'in_progress' => $artRequests->where('status', 'EN CURSO')->count(),
            'completed' => $completedCount,
            'avg_completed_per_day' => $avgCompletedPerDay,
            'days_for_average' => $daysForAverage,
            'overdue' => $artRequests->where('status', '!=', 'COMPLETO')
                ->filter(fn($requestItem) => $requestItem->delivery_date && $requestItem->delivery_date->copy()->endOfDay()->lt(now()))
                ->count(),
        ];

        // Datos para gráficos
        $chartData = $this->prepareChartData($artRequests);

        // Obtener diseñadores disponibles
        $designers = User::whereHas('roles', function($q) {
            $q->where('name', 'design');
        })->get();

        // Generar lista de meses disponibles para los últimos 12 meses
        $months = [];
        $monthNames = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo',
            6 => 'Junio', 7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre',
            10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];

        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months[$date->month . '-' . $date->year] = $monthNames[$date->month] . ' ' . $date->year;
        }

        return view('art_requests.dashboard', compact(
            'stats',
            'chartData',
            'weeks',
            'selectedWeek',
            'defaultWeek',
            'designers',
            'designerId',
            'currentYear',
            'currentMonth',
            'weekStart',
            'weekEnd',
            'month',
            'year',
            'months'
        ));
    }

    /**
     * Obtener las semanas del mes con sus fechas
     */
    private function getWeeksOfMonth($monthStart, $monthEnd)
    {
        $weeks = [];
        $weekNumber = 1;
        $currentDate = $monthStart->copy();

        while ($currentDate->lte($monthEnd)) {
            $weekStart = $currentDate->copy();
            $weekEnd = $currentDate->copy()->addDays(6); // 7 días totales (0-6)

            // Si el fin de semana excede el fin del mes, ajustar
            if ($weekEnd->gt($monthEnd)) {
                $weekEnd = $monthEnd->copy();
            }

            $weeks[$weekNumber] = [
                'start' => $weekStart,
                'end' => $weekEnd,
                'label' => $weekStart->format('d') . '-' . $weekEnd->format('d M')
            ];

            $currentDate = $weekEnd->copy()->addDay();
            $weekNumber++;
        }

        return $weeks;
    }

    /**
     * Preparar datos para los gráficos
     */
    private function prepareChartData($artRequests)
    {
        // Por estado
        $byStatus = $artRequests->groupBy('status')
            ->map(function($items) {
                return $items->count();
            })
            ->toArray();

        // Por tipo de arte
        $byTypeOfArt = $artRequests->groupBy(function($item) {
                return $item->typeOfArt->name ?? 'Desconocido';
            })
            ->map(function($items) {
                return $items->count();
            })
            ->toArray();

        // Por pilar de contenido
        $byContentPillar = $artRequests->groupBy(function($item) {
                return $item->contentPillar->name ?? 'Sin Pilar';
            })
            ->map(function($items) {
                return $items->count();
            })
            ->toArray();

        return [
            'statusData' => json_encode($byStatus),
            'typeOfArtData' => json_encode($byTypeOfArt),
            'contentPillarData' => json_encode($byContentPillar),
        ];
    }
}
