<?php

namespace App\Services;

use App\Models\Inscription;
use App\Models\InscriptionPaymentHistory;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class InscriptionMonthlyViewService
{
    /**
     * Obtener todas las "vistas" de una inscripción en diferentes meses
     * 
     * Por ejemplo, si Pedro:
     * - Mayo: Adelanto
     * - Junio: Completando
     * 
     * Retorna 2 registros "virtuales":
     * - Mayo: Pedro - Adelanto
     * - Junio: Pedro - Completando
     */
    public static function getInscriptionsForMonth(Carbon $month): Collection
    {
        $year = $month->year;
        $monthNum = $month->month;
        
        // Obtener todas las inscripciones base
        $inscriptions = Inscription::all();
        
        $monthlyViews = collect();
        
        foreach ($inscriptions as $inscription) {
            // Obtener el estado de pago en ESTE mes específico
            $monthStatus = self::getStatusForMonth($inscription, $year, $monthNum);
            
            // Si tiene estado en este mes, crear una "vista virtual"
            if ($monthStatus) {
                $monthlyViews->push([
                    'inscription_id' => $inscription->id,
                    'original_inscription' => $inscription,
                    'display_status' => $monthStatus,
                    'year' => $year,
                    'month' => $monthNum,
                    'is_virtual' => true, // Marca que es una vista virtual
                ]);
            }
        }
        
        return $monthlyViews;
    }
    
    /**
     * Obtener el estado de pago de una inscripción para un mes específico
     * 
     * Busca en el historial el último estado registrado en ese mes
     */
    private static function getStatusForMonth(Inscription $inscription, int $year, int $month): ?string
    {
        // Obtener el historial de esta inscripción
        $history = $inscription->paymentHistory()
            ->whereYear('status_date', $year)
            ->whereMonth('status_date', $month)
            ->orderBy('status_date', 'DESC')
            ->first();
        
        if ($history) {
            return $history->new_status;
        }
        
        return null;
    }
    
    /**
     * Obtener el estado más reciente anterior a un mes específico
     * (para propagar estados de meses anteriores)
     */
    public static function getLastStatusBeforeMonth(Inscription $inscription, int $year, int $month): ?string
    {
        // Buscar el último cambio ANTES de este mes
        $history = $inscription->paymentHistory()
            ->where(function ($query) use ($year, $month) {
                $query->whereYear('status_date', '<', $year)
                    ->orWhere(function ($q) use ($year, $month) {
                        $q->whereYear('status_date', $year)
                          ->whereMonth('status_date', '<', $month);
                    });
            })
            ->orderBy('status_date', 'DESC')
            ->first();
        
        if ($history) {
            return $history->new_status;
        }
        
        return null;
    }
    
    /**
     * Obtener todos los meses en los que una inscripción aparece
     */
    public static function getActiveMonths(Inscription $inscription): Collection
    {
        return $inscription->paymentHistory()
            ->selectRaw('YEAR(status_date) as year, MONTH(status_date) as month')
            ->distinct()
            ->orderBy('year', 'DESC')
            ->orderBy('month', 'DESC')
            ->get()
            ->map(function ($item) {
                return Carbon::createFromDate($item->year, $item->month, 1);
            });
    }
    
    /**
     * Contar inscripciones completadas (estado Completo) en un mes específico
     * 
     * Para dashboards/reportes
     */
    public static function countCompletedInMonth(int $year, int $month): int
    {
        return InscriptionPaymentHistory::where('new_status', 'Completo')
            ->whereYear('status_date', $year)
            ->whereMonth('status_date', $month)
            ->distinct('inscription_id')
            ->count('inscription_id');
    }
    
    /**
     * Obtener ingresos (total_paid) de inscripciones completadas en un mes
     */
    public static function getRevenueForMonth(int $year, int $month): float
    {
        return InscriptionPaymentHistory::where('new_status', 'Completo')
            ->whereYear('status_date', $year)
            ->whereMonth('status_date', $month)
            ->sum('amount_paid') ?? 0;
    }
}
