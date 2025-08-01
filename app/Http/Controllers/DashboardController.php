<?php

namespace App\Http\Controllers;

use App\Models\Inscription;
use App\Models\Program;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controller;

class DashboardController extends Controller
{

    public function __construct()
    {
        $this->middleware('permission:dashboard.view');
    }
    public function index(Request $request)
    {
        // Configurar Carbon para usar español
        Carbon::setLocale('es');
        
        // Determinar el tipo de vista (mensual o anual)
        $viewType = $request->input('view_type', 'monthly');
        $year = $request->input('year', Carbon::now()->year);
        $month = $request->input('month', Carbon::now()->month);
        
        if ($viewType === 'monthly') {
            return $this->monthlyView($request, $year, $month, $viewType);
        } else {
            return $this->yearlyView($request, $year, $viewType);
        }
    }
    
    protected function monthlyView(Request $request, $year, $month, $viewType = 'monthly')
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        
        // Obtener todas las inscripciones del mes seleccionado con la relación creator
        $inscriptions = Inscription::whereBetween('inscription_date', [$startDate, $endDate])
            ->with(['program', 'creator'])
            ->get();
        
        // Calcular datos para el mes anterior
        $previousMonth = $startDate->copy()->subMonth();
        $previousMonthStart = $previousMonth->copy()->startOfMonth();
        $previousMonthEnd = $previousMonth->copy()->endOfMonth();

        $previousMonthInscriptions = Inscription::whereBetween('inscription_date', [$previousMonthStart, $previousMonthEnd])
            ->get();

        // Calcular estadísticas del mes anterior
        $previousMonthTotal = $previousMonthInscriptions->count();
        $previousMonthCompleto = $previousMonthInscriptions->where('status', 'Completo')->count();
        $previousMonthCompletando = $previousMonthInscriptions->where('status', 'Completando')->count();
        $previousMonthAdelanto = $previousMonthInscriptions->where('status', 'Adelanto')->count();
        $previousMonthTotalPaid = $previousMonthInscriptions->sum('total_paid');
        $previousMonthTotalSinAdelantos = $previousMonthCompleto + $previousMonthCompletando;

        // Estadísticas del mes actual
        $currentMonthTotal = $inscriptions->count();
        $currentMonthCompleto = $inscriptions->where('status', 'Completo')->count();
        $currentMonthCompletando = $inscriptions->where('status', 'Completando')->count();
        $currentMonthAdelanto = $inscriptions->where('status', 'Adelanto')->count();
        $currentMonthTotalPaid = $inscriptions->sum('total_paid');
        $currentMonthTotalSinAdelantos = $currentMonthCompleto + $currentMonthCompletando;

        // Calcular los porcentajes de cambio
        $percentageChangeTotal = 0;
        $percentageChangeCompleto = 0;
        $percentageChangeCompletando = 0;
        $percentageChangeAdelanto = 0;
        $percentageChangeTotalPaid = 0;
        $percentageChangeTotalSinAdelantos = 0;

        if ($previousMonthTotal > 0) {
            $percentageChangeTotal = (($currentMonthTotal - $previousMonthTotal) / $previousMonthTotal) * 100;
        }
        if ($previousMonthCompleto > 0) {
            $percentageChangeCompleto = (($currentMonthCompleto - $previousMonthCompleto) / $previousMonthCompleto) * 100;
        }
        if ($previousMonthCompletando > 0) {
            $percentageChangeCompletando = (($currentMonthCompletando - $previousMonthCompletando) / $previousMonthCompletando) * 100;
        }
        if ($previousMonthAdelanto > 0) {
            $percentageChangeAdelanto = (($currentMonthAdelanto - $previousMonthAdelanto) / $previousMonthAdelanto) * 100;
        }
        if ($previousMonthTotalPaid > 0) {
            $percentageChangeTotalPaid = (($currentMonthTotalPaid - $previousMonthTotalPaid) / $previousMonthTotalPaid) * 100;
        }
        if ($previousMonthTotalSinAdelantos > 0) {
            $percentageChangeTotalSinAdelantos = (($currentMonthTotalSinAdelantos - $previousMonthTotalSinAdelantos) / $previousMonthTotalSinAdelantos) * 100;
        }
        
        // Estadísticas generales
        $stats = [
            'total' => $currentMonthTotal,
            'completo' => $currentMonthCompleto,
            'completando' => $currentMonthCompletando,
            'adelanto' => $currentMonthAdelanto,
            'total_paid' => $currentMonthTotalPaid,
            'total_sin_adelantos' => $currentMonthTotalSinAdelantos,
            
            // Datos del mes anterior
            'previous_month_total' => $previousMonthTotal,
            'previous_month_completo' => $previousMonthCompleto,
            'previous_month_completando' => $previousMonthCompletando,
            'previous_month_adelanto' => $previousMonthAdelanto,
            'previous_month_total_paid' => $previousMonthTotalPaid,
            'previous_month_total_sin_adelantos' => $previousMonthTotalSinAdelantos,
            
            // Porcentajes de cambio
            'percentage_change_total' => $percentageChangeTotal,
            'percentage_change_completo' => $percentageChangeCompleto,
            'percentage_change_completando' => $percentageChangeCompletando,
            'percentage_change_adelanto' => $percentageChangeAdelanto,
            'percentage_change_total_paid' => $percentageChangeTotalPaid,
            'percentage_change_total_sin_adelantos' => $percentageChangeTotalSinAdelantos
        ];
        
        // 1. Inscripciones por creador (completos, completando y adelanto)
        $inscriptionsByAdvisor = $inscriptions->groupBy(function ($inscription) {
                return $inscription->creator ? $inscription->creator->name : 'Desconocido';
            })
            ->map(function ($items) {
                return [
                    'Completo' => $items->where('status', 'Completo')->count(),
                    'Completando' => $items->where('status', 'Completando')->count(),
                    'Adelanto' => $items->where('status', 'Adelanto')->count()
                ];
            });
        
        $advisorLabels = $inscriptionsByAdvisor->keys()->toArray();
        $advisorDatasets = [
            'Completo' => [],
            'Completando' => [],
            'Adelanto' => []
        ];
        
        foreach ($inscriptionsByAdvisor as $advisor => $statusCounts) {
            $advisorDatasets['Completo'][] = $statusCounts['Completo'];
            $advisorDatasets['Completando'][] = $statusCounts['Completando'];
            $advisorDatasets['Adelanto'][] = $statusCounts['Adelanto'];
        }
        
        // 2. Inscripciones por programa (completos, completando, adelanto)
        $inscriptionsByProgram = $inscriptions->groupBy(function ($inscription) {
                return $inscription->program->name;
            })
            ->map(function ($items) {
                return [
                    'Completo' => $items->where('status', 'Completo')->count(),
                    'Completando' => $items->where('status', 'Completando')->count(),
                    'Adelanto' => $items->where('status', 'Adelanto')->count()
                ];
            });
        
        $programLabels = $inscriptionsByProgram->keys()->toArray();
        $programDatasets = [
            'Completo' => [],
            'Completando' => [],
            'Adelanto' => []
        ];
        
        foreach ($inscriptionsByProgram as $program => $statusCounts) {
            $programDatasets['Completo'][] = $statusCounts['Completo'];
            $programDatasets['Completando'][] = $statusCounts['Completando'];
            $programDatasets['Adelanto'][] = $statusCounts['Adelanto'];
        }
        
        // 3. Inscripciones por estado (completo, completando y adelanto)
        $inscriptionsByStatus = [
            'Completo' => $inscriptions->where('status', 'Completo')->count(),
            'Completando' => $inscriptions->where('status', 'Completando')->count(),
            'Adelanto' => $inscriptions->where('status', 'Adelanto')->count()
        ];
        
        // 4. Inscripciones por plan de pago
        $inscriptionsByPaymentPlan = $inscriptions->groupBy('payment_plan')
            ->map(function ($items) {
                return $items->count();
            })
            ->toArray();
        
        // 5. Inscripciones por lugar de residencia
        $inscriptionsByResidence = $inscriptions->groupBy('residence')
            ->map(function ($items) {
                return $items->count();
            })
            ->toArray();
        
        // 6. Inscripciones por profesión
        $inscriptionsByProfession = $inscriptions->groupBy('profession')
            ->map(function ($items) {
                return $items->count();
            })
            ->toArray();

        // Estadísticas adicionales para el reporte mensual
        $programStats = $inscriptions->groupBy('program_id')
            ->map(function ($items, $key) {
                $program = Program::find($key);
                return [
                    'name' => $program ? $program->name : 'Desconocido',
                    'count' => $items->count(),
                    'total_paid' => $items->sum('total_paid')
                ];
            });
            
        // Estadísticas por creador para el reporte mensual
        $advisorStats = $inscriptions->groupBy(function ($inscription) {
                return $inscription->creator ? $inscription->creator->name : 'Desconocido';
            })
            ->map(function ($items, $key) {
                return [
                    'name' => $key,
                    'count' => $items->count(),
                    'total_paid' => $items->sum('total_paid')
                ];
            });
        
        // Preparar datos para la vista
        $chartData = [
            'advisorLabels' => json_encode($advisorLabels),
            'advisorDatasets' => json_encode($advisorDatasets),
            'programLabels' => json_encode($programLabels),
            'programDatasets' => json_encode($programDatasets),
            'statusData' => json_encode($inscriptionsByStatus),
            'paymentPlanData' => json_encode($inscriptionsByPaymentPlan),
            'residenceData' => json_encode($inscriptionsByResidence),
            'professionData' => json_encode($inscriptionsByProfession)
        ];
        
        // Obtener las últimas inscripciones realizadas (independientemente del mes/año seleccionado)
        $latestInscriptions = Inscription::with(['program', 'creator'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // Nombre del mes actual en español para la vista
        $nombreMes = Carbon::createFromDate($year, $month, 1)->translatedFormat('F');
        $nombreMesAnterior = Carbon::createFromDate($previousMonth->year, $previousMonth->month, 1)->translatedFormat('F');
        
        return view('dashboard', compact(
            'stats', 
            'chartData', 
            'year', 
            'month', 
            'viewType', 
            'programStats', 
            'advisorStats', 
            'latestInscriptions',
            'nombreMes',
            'nombreMesAnterior'
        ));
    }
    
    protected function yearlyView(Request $request, $year, $viewType = 'yearly')
    {
        $startDate = Carbon::createFromDate($year, 1, 1)->startOfYear();
        $endDate = $startDate->copy()->endOfYear();
        
        // Obtener todas las inscripciones del año seleccionado con la relación creator
        $inscriptions = Inscription::whereBetween('inscription_date', [$startDate, $endDate])
            ->with(['program', 'creator'])
            ->get();
        
        // Obtener todas las inscripciones hasta la fecha final, agrupadas por CI
        $allInscriptions = Inscription::whereDate('inscription_date', '<=', $endDate)
            ->orderBy('inscription_date')
            ->get()
            ->groupBy('ci');
        
        // Procesar inscripciones para aplicar la lógica especial de estados
        $processedInscriptions = collect();
        $excludedInscriptionIds = []; // IDs de inscripciones "Adelanto" que no deben contarse
        
        // Primero identificamos las inscripciones "Adelanto" que tienen una "Completando" posterior
        foreach ($inscriptions as $inscription) {
            if ($inscription->status == 'Completando' && isset($allInscriptions[$inscription->ci])) {
                $personInscriptions = $allInscriptions[$inscription->ci];
                
                $previousAdelanto = $personInscriptions
                    ->where('id', '!=', $inscription->id)
                    ->where('status', 'Adelanto')
                    ->where('inscription_date', '<', $inscription->inscription_date)
                    ->where('inscription_date', '>=', $startDate) // Solo del año actual
                    ->first();
                
                if ($previousAdelanto) {
                    // Marcar esta inscripción "Adelanto" para excluirla
                    $excludedInscriptionIds[] = $previousAdelanto->id;
                }
            }
        }
        
        // Ahora procesamos todas las inscripciones
        foreach ($inscriptions as $inscription) {
            // Si es una inscripción "Adelanto" que debe excluirse, la saltamos
            if (in_array($inscription->id, $excludedInscriptionIds)) {
                continue;
            }
            
            $processedInscription = clone $inscription;
            
            // Si es "Completando" y tiene un "Adelanto" previo, la marcamos como "Completo"
            if ($inscription->status == 'Completando' && isset($allInscriptions[$inscription->ci])) {
                $personInscriptions = $allInscriptions[$inscription->ci];
                
                $previousAdelanto = $personInscriptions
                    ->where('id', '!=', $inscription->id)
                    ->where('status', 'Adelanto')
                    ->where('inscription_date', '<', $inscription->inscription_date)
                    ->first();
                
                if ($previousAdelanto) {
                    $processedInscription->status = 'Completo';
                }
            }
            
            $processedInscriptions->push($processedInscription);
        }
        
        // Calcular datos para el año anterior
        $previousYear = $year - 1;
        $previousYearStart = Carbon::createFromDate($previousYear, 1, 1)->startOfYear();
        $previousYearEnd = Carbon::createFromDate($previousYear, 12, 31)->endOfYear();

        $previousYearInscriptions = Inscription::whereBetween('inscription_date', [$previousYearStart, $previousYearEnd])
            ->get();

        // Calcular estadísticas del año anterior
        $previousYearTotal = $previousYearInscriptions->count();
        $previousYearCompleto = $previousYearInscriptions->where('status', 'Completo')->count();
        $previousYearCompletando = $previousYearInscriptions->where('status', 'Completando')->count();
        $previousYearAdelanto = $previousYearInscriptions->where('status', 'Adelanto')->count();
        $previousYearTotalPaid = $previousYearInscriptions->sum('total_paid');
        $previousYearTotalSinAdelantos = $previousYearCompleto + $previousYearCompletando;

        // Estadísticas del año actual usando las inscripciones procesadas
        $currentYearTotal = $processedInscriptions->count();
        $currentYearCompleto = $processedInscriptions->where('status', 'Completo')->count();
        $currentYearCompletando = $processedInscriptions->where('status', 'Completando')->count();
        $currentYearAdelanto = $processedInscriptions->where('status', 'Adelanto')->count();
        $currentYearTotalPaid = $processedInscriptions->sum('total_paid');
        $currentYearTotalSinAdelantos = $currentYearCompleto + $currentYearCompletando;

        // Calcular los porcentajes de cambio
        $percentageChangeTotal = 0;
        $percentageChangeCompleto = 0;
        $percentageChangeCompletando = 0;
        $percentageChangeAdelanto = 0;
        $percentageChangeTotalPaid = 0;
        $percentageChangeTotalSinAdelantos = 0;

        if ($previousYearTotal > 0) {
            $percentageChangeTotal = (($currentYearTotal - $previousYearTotal) / $previousYearTotal) * 100;
        }
        if ($previousYearCompleto > 0) {
            $percentageChangeCompleto = (($currentYearCompleto - $previousYearCompleto) / $previousYearCompleto) * 100;
        }
        if ($previousYearCompletando > 0) {
            $percentageChangeCompletando = (($currentYearCompletando - $previousYearCompletando) / $previousYearCompletando) * 100;
        }
        if ($previousYearAdelanto > 0) {
            $percentageChangeAdelanto = (($currentYearAdelanto - $previousYearAdelanto) / $previousYearAdelanto) * 100;
        }
        if ($previousYearTotalPaid > 0) {
            $percentageChangeTotalPaid = (($currentYearTotalPaid - $previousYearTotalPaid) / $previousYearTotalPaid) * 100;
        }
        if ($previousYearTotalSinAdelantos > 0) {
            $percentageChangeTotalSinAdelantos = (($currentYearTotalSinAdelantos - $previousYearTotalSinAdelantos) / $previousYearTotalSinAdelantos) * 100;
        }

        // Estadísticas generales anuales
        $stats = [
            'total' => $currentYearTotal,
            'completo' => $currentYearCompleto,
            'completando' => $currentYearCompletando,
            'adelanto' => $currentYearAdelanto,
            'total_paid' => $currentYearTotalPaid,
            'total_sin_adelantos' => $currentYearTotalSinAdelantos,
            
            // Datos del año anterior
            'previous_month_total' => $previousYearTotal,
            'previous_month_completo' => $previousYearCompleto,
            'previous_month_completando' => $previousYearCompletando,
            'previous_month_adelanto' => $previousYearAdelanto,
            'previous_month_total_paid' => $previousYearTotalPaid,
            'previous_month_total_sin_adelantos' => $previousYearTotalSinAdelantos,
            
            // Porcentajes de cambio
            'percentage_change_total' => $percentageChangeTotal,
            'percentage_change_completo' => $percentageChangeCompleto,
            'percentage_change_completando' => $percentageChangeCompletando,
            'percentage_change_adelanto' => $percentageChangeAdelanto,
            'percentage_change_total_paid' => $percentageChangeTotalPaid,
            'percentage_change_total_sin_adelantos' => $percentageChangeTotalSinAdelantos
        ];
        
        // Estadísticas mensuales para el año
        $monthlyStats = [];
        $monthlyDatasets = [
            'labels' => [],
            'total' => [],
            'completo' => [],
            'completando' => [],
            'adelanto' => [],
            'total_paid' => []
        ];
        
        for ($month = 1; $month <= 12; $month++) {
            $monthStart = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $monthEnd = $monthStart->copy()->endOfMonth();
            
            $monthInscriptions = $processedInscriptions->filter(function ($inscription) use ($monthStart, $monthEnd) {
                return $inscription->inscription_date->between($monthStart, $monthEnd);
            });
            
            // Usar Carbon para obtener el nombre del mes en español
            $monthName = Carbon::createFromDate($year, $month, 1)->translatedFormat('F');
            
            $monthlyStats[$month] = [
                'month' => $monthName,
                'total' => $monthInscriptions->count(),
                'completo' => $monthInscriptions->where('status', 'Completo')->count(),
                'completando' => $monthInscriptions->where('status', 'Completando')->count(),
                'adelanto' => $monthInscriptions->where('status', 'Adelanto')->count(),
                'total_paid' => $monthInscriptions->sum('total_paid')
            ];
            
            $monthlyDatasets['labels'][] = $monthName;
            $monthlyDatasets['total'][] = $monthInscriptions->count();
            $monthlyDatasets['completo'][] = $monthInscriptions->where('status', 'Completo')->count();
            $monthlyDatasets['completando'][] = $monthInscriptions->where('status', 'Completando')->count();
            $monthlyDatasets['adelanto'][] = $monthInscriptions->where('status', 'Adelanto')->count();
            $monthlyDatasets['total_paid'][] = $monthInscriptions->sum('total_paid');
        }
        
        // 1. Inscripciones por creador (completos, completando y adelanto) - ANUAL
        $inscriptionsByAdvisor = $processedInscriptions->groupBy(function ($inscription) {
            return $inscription->creator ? $inscription->creator->name : 'Desconocido';
        })
        ->map(function ($items) {
            return [
                'Completo' => $items->where('status', 'Completo')->count(),
                'Completando' => $items->where('status', 'Completando')->count(),
                'Adelanto' => $items->where('status', 'Adelanto')->count()
            ];
        });
    
        $advisorLabels = $inscriptionsByAdvisor->keys()->toArray();
        $advisorDatasets = [
            'Completo' => [],
            'Completando' => [],
            'Adelanto' => []
        ];
        
        foreach ($inscriptionsByAdvisor as $advisor => $statusCounts) {
            $advisorDatasets['Completo'][] = $statusCounts['Completo'];
            $advisorDatasets['Completando'][] = $statusCounts['Completando'];
            $advisorDatasets['Adelanto'][] = $statusCounts['Adelanto'];
        }
        
        // 2. Inscripciones por programa (completos, completando, adelanto) - ANUAL
        $inscriptionsByProgram = $processedInscriptions->groupBy(function ($inscription) {
            return $inscription->program->name;
        })
        ->map(function ($items) {
            return [
                'Completo' => $items->where('status', 'Completo')->count(),
                'Completando' => $items->where('status', 'Completando')->count(),
                'Adelanto' => $items->where('status', 'Adelanto')->count()
            ];
        });
    
        $programLabels = $inscriptionsByProgram->keys()->toArray();
        $programDatasets = [
            'Completo' => [],
            'Completando' => [],
            'Adelanto' => []
        ];
        
        foreach ($inscriptionsByProgram as $program => $statusCounts) {
            $programDatasets['Completo'][] = $statusCounts['Completo'];
            $programDatasets['Completando'][] = $statusCounts['Completando'];
            $programDatasets['Adelanto'][] = $statusCounts['Adelanto'];
        }
        
        // Estadísticas por programa para el año
        $programStats = $processedInscriptions->groupBy('program_id')
            ->map(function ($items, $key) {
                $program = Program::find($key);
                return [
                    'name' => $program ? $program->name : 'Desconocido',
                    'count' => $items->count(),
                    'total_paid' => $items->sum('total_paid'),
                    'completo' => $items->where('status', 'Completo')->count(),
                    'completando' => $items->where('status', 'Completando')->count(),
                    'adelanto' => $items->where('status', 'Adelanto')->count()
                ];
            });
        
        // Estadísticas por creador para el año
        $advisorStats = $processedInscriptions->groupBy(function ($inscription) {
            return $inscription->creator ? $inscription->creator->name : 'Desconocido';
        })
        ->map(function ($items, $key) {
            return [
                'name' => $key,
                'count' => $items->count(),
                'total_paid' => $items->sum('total_paid'),
                'completo' => $items->where('status', 'Completo')->count(),
                'completando' => $items->where('status', 'Completando')->count(),
                'adelanto' => $items->where('status', 'Adelanto')->count()
            ];
        });
    
        // Inscripciones por estado (anual)
        $inscriptionsByStatus = [
            'Completo' => $processedInscriptions->where('status', 'Completo')->count(),
            'Completando' => $processedInscriptions->where('status', 'Completando')->count(),
            'Adelanto' => $processedInscriptions->where('status', 'Adelanto')->count()
        ];
        
        // Inscripciones por plan de pago (anual)
        $inscriptionsByPaymentPlan = $processedInscriptions->groupBy('payment_plan')
            ->map(function ($items) {
                return $items->count();
            })
            ->toArray();
        
        // 5. Inscripciones por lugar de residencia (anual)
        $inscriptionsByResidence = $processedInscriptions->groupBy('residence')
            ->map(function ($items) {
                return $items->count();
            })
            ->toArray();
        
        // 6. Inscripciones por profesión (anual)
        $inscriptionsByProfession = $processedInscriptions->groupBy('profession')
            ->map(function ($items) {
                return $items->count();
            })
            ->toArray();

        // Preparar datos para la vista
        $chartData = [
            'monthlyLabels' => json_encode($monthlyDatasets['labels']),
            'monthlyDatasets' => json_encode([
                'total' => $monthlyDatasets['total'],
                'completo' => $monthlyDatasets['completo'],
                'completando' => $monthlyDatasets['completando'],
                'adelanto' => $monthlyDatasets['adelanto'],
                'total_paid' => $monthlyDatasets['total_paid']
            ]),
            'advisorLabels' => json_encode($advisorLabels),
            'advisorDatasets' => json_encode($advisorDatasets),
            'programLabels' => json_encode($programLabels),
            'programDatasets' => json_encode($programDatasets),
            'statusData' => json_encode($inscriptionsByStatus),
            'paymentPlanData' => json_encode($inscriptionsByPaymentPlan),
            'residenceData' => json_encode($inscriptionsByResidence),
            'professionData' => json_encode($inscriptionsByProfession)
        ];
        
        // Obtener las últimas inscripciones realizadas (independientemente del año seleccionado)
        $latestInscriptions = Inscription::with(['program', 'creator'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        return view('dashboard', compact(
            'stats', 
            'chartData', 
            'year', 
            'viewType', 
            'monthlyStats', 
            'programStats', 
            'advisorStats', 
            'latestInscriptions',
            'previousYear'
        ));
    }
}
