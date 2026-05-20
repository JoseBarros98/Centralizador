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
        $today = Carbon::now();
        $designerId = $request->get('designer_id');

        $dateFrom = $request->filled('date_from')
            ? Carbon::createFromFormat('Y-m-d', $request->date_from)->startOfDay()
            : $today->copy()->startOfMonth();

        $dateTo = $request->filled('date_to')
            ? Carbon::createFromFormat('Y-m-d', $request->date_to)->endOfDay()
            : $today->copy()->endOfDay();

        if ($dateFrom->gt($dateTo)) {
            [$dateFrom, $dateTo] = [$dateTo->copy()->startOfDay(), $dateFrom->copy()->endOfDay()];
        }

        $query = ArtRequest::where('active', true)
            ->whereBetween('created_at', [$dateFrom, $dateTo]);

        if ($designerId) {
            $query->where('designer_id', $designerId);
        }

        $artRequests = $query->orderBy('created_at', 'desc')->get();

        $completedCount = $artRequests->where('status', 'COMPLETO')->count();

        // Promedio: si el periodo aún no terminó, el denominador llega hasta hoy
        $periodEnd    = $dateTo->gt($today) ? $today : $dateTo;
        $daysForAverage = max(1, $dateFrom->copy()->startOfDay()->diffInDays($periodEnd->copy()->startOfDay()) + 1);

        $stats = [
            'total'               => $artRequests->count(),
            'pending'             => $artRequests->where('status', 'NO INICIADO')->count(),
            'in_progress'         => $artRequests->where('status', 'EN CURSO')->count(),
            'completed'           => $completedCount,
            'avg_completed_per_day' => round($completedCount / $daysForAverage, 2),
            'days_for_average'    => $daysForAverage,
            'overdue'             => $artRequests->where('status', '!=', 'COMPLETO')
                ->filter(fn($r) => $r->delivery_date && $r->delivery_date->copy()->endOfDay()->lt($today))
                ->count(),
        ];

        $chartData = $this->prepareChartData($artRequests);

        $designers = User::whereHas('roles', fn($q) => $q->where('name', 'design'))->get();

        return view('art_requests.dashboard', compact(
            'stats', 'chartData', 'designers', 'designerId', 'dateFrom', 'dateTo'
        ));
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
