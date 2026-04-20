<?php

namespace App\Http\Controllers;

use App\Models\Inscription;
use App\Models\Program;
use App\Models\User;
use App\Models\MarketingTeam;
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
        $user = $request->user();

        // Solo admin y marketing pueden ver este dashboard principal.
        // Los demás roles se redirigen a su dashboard específico.
        if (!$user->hasRole('admin') && !$user->hasRole('marketing')) {
            if ($user->hasAnyRole(['academic', 'academico'])) {
                return redirect()->route('dashboard.academic');
            }

            if ($user->hasRole('accountant')) {
                return redirect()->route('dashboard.accounting');
            }

            if ($user->hasRole('design')) {
                return redirect()->route('art_requests.dashboard');
            }

            abort(403, 'User does not have the right roles.');
        }

        // Configurar Carbon para usar español
        Carbon::setLocale('es');
        
        // Determinar el tipo de vista (mensual o anual)
        $viewType = $request->input('view_type', 'monthly');
        $year = $request->input('year', Carbon::now()->year);
        $month = $request->input('month', Carbon::now()->month);
        $programId = $request->input('program_id');

        if ((string) $month === 'all') {
            $viewType = 'yearly';
        }
        
        if ($viewType === 'monthly') {
            return $this->monthlyView($request, $year, $month, $viewType, $programId);
        } else {
            return $this->yearlyView($request, $year, $viewType, $programId);
        }
    }
    
    protected function monthlyView(Request $request, $year, $month, $viewType = 'monthly', $programId = null)
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        
                // Base mensual: inscripciones creadas en el periodo o con movimientos de pago en el periodo
                $inscriptionsQuery = Inscription::where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('inscription_date', [$startDate, $endDate])
                    ->orWhereHas('paymentHistory', function ($subQ) use ($startDate, $endDate) {
                        $subQ->whereBetween('status_date', [$startDate, $endDate]);
                    });
            })->with(['programs', 'creator.marketingTeams', 'creator.leadTeams', 'paymentHistory']);
        
        // Aplicar filtro por programa si se especifica
        if ($programId) {
            $inscriptionsQuery->whereHas('programs', function($q) use ($programId) {
                $q->where('programs.id', $programId);
            });
        }
        
        $inscriptions = $inscriptionsQuery->get();
        
        // Calcular datos para el mes anterior
        $previousMonth = $startDate->copy()->subMonth();
        $previousMonthStart = $previousMonth->copy()->startOfMonth();
        $previousMonthEnd = $previousMonth->copy()->endOfMonth();

                // Comparativo mensual: inscripciones creadas en ese mes o con movimientos de pago en ese mes
                $previousMonthInscriptionsQuery = Inscription::where(function ($q) use ($previousMonthStart, $previousMonthEnd) {
                $q->whereBetween('inscription_date', [$previousMonthStart, $previousMonthEnd])
                    ->orWhereHas('paymentHistory', function ($subQ) use ($previousMonthStart, $previousMonthEnd) {
                        $subQ->whereBetween('status_date', [$previousMonthStart, $previousMonthEnd]);
                    });
            })->with(['paymentHistory']);
        
        // Aplicar filtro por programa para datos del mes anterior también
        if ($programId) {
            $previousMonthInscriptionsQuery->whereHas('programs', function($q) use ($programId) {
                $q->where('programs.id', $programId);
            });
        }
        
        $previousMonthInscriptions = $previousMonthInscriptionsQuery->get();

        // Calcular estadísticas del mes anterior
        $previousMonthTotal = $previousMonthInscriptions->count();
        
        // Calcular estados por mes (usando payment history)
        $previousMonthCompleto = 0;
        $previousMonthCompletando = 0;
        $previousMonthAdelanto = 0;
        $previousMonthTotalPaid = 0;
        
        foreach ($previousMonthInscriptions as $inscription) {
            $resolvedStatus = $this->resolvePaymentStatusForPeriod($inscription, $previousMonthStart, $previousMonthEnd);

            if ($resolvedStatus === 'Completo') {
                $previousMonthCompleto++;
            } elseif ($resolvedStatus === 'Completando') {
                $previousMonthCompletando++;
            } elseif ($resolvedStatus === 'Adelanto') {
                $previousMonthAdelanto++;
            }

            $lastChangeThisMonth = $inscription->paymentHistory()
                ->whereBetween('status_date', [$previousMonthStart->toDateString(), $previousMonthEnd->toDateString()])
                ->orderBy('status_date', 'DESC')
                ->first();

            if ($lastChangeThisMonth) {
                $previousMonthTotalPaid += $lastChangeThisMonth->amount_paid;
            }
        }
        
        $previousMonthTotalSinAdelantos = $previousMonthCompleto + $previousMonthCompletando;

        // Estadísticas del mes actual
        $currentMonthTotal = $inscriptions->count();
        
        // Calcular estados por mes (usando payment history)
        $currentMonthCompleto = 0;
        $currentMonthCompletando = 0;
        $currentMonthAdelanto = 0;
        $currentMonthTotalPaid = 0;
        
        foreach ($inscriptions as $inscription) {
            $resolvedStatus = $this->resolvePaymentStatusForPeriod($inscription, $startDate, $endDate);

            if ($resolvedStatus === 'Completo') {
                $currentMonthCompleto++;
            } elseif ($resolvedStatus === 'Completando') {
                $currentMonthCompletando++;
            } elseif ($resolvedStatus === 'Adelanto') {
                $currentMonthAdelanto++;
            }

            $lastChangeThisMonth = $inscription->paymentHistory()
                ->whereBetween('status_date', [$startDate->toDateString(), $endDate->toDateString()])
                ->orderBy('status_date', 'DESC')
                ->first();

            if ($lastChangeThisMonth) {
                $currentMonthTotalPaid += $lastChangeThisMonth->amount_paid;
            }
        }
        
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
        // Agrupar y calcular estados usando el estado del mes
        $inscriptionsByAdvisor = $inscriptions->groupBy(function ($inscription) {
                return $inscription->creator ? $inscription->creator->name : 'Desconocido';
            })
            ->map(function ($items) use ($startDate, $endDate) {
                $completo = 0;
                $completando = 0;
                $adelanto = 0;
                
                foreach ($items as $item) {
                    $status = $this->resolvePaymentStatusForPeriod($item, $startDate, $endDate);

                    if ($status === 'Completo') {
                        $completo++;
                    } elseif ($status === 'Completando') {
                        $completando++;
                    } elseif ($status === 'Adelanto') {
                        $adelanto++;
                    }
                }
                
                return [
                    'Completo' => $completo,
                    'Completando' => $completando,
                    'Adelanto' => $adelanto
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
        
        // 2. Inscripciones por programa (completos, completando, adelanto) - usando estados del mes
        $programs = $inscriptions->flatMap(function ($inscription) {
                return $inscription->programs ?? collect();
            })
            ->unique('id')
            ->pluck('name');
        
        $inscriptionsByProgram = collect();
        foreach ($programs as $programName) {
            // Contar inscripciones de este programa por estado en el mes
            $programInscriptions = $inscriptions->filter(function ($inscription) use ($programName) {
                return $inscription->programs->pluck('name')->contains($programName);
            });
            
            $inscriptionsByProgram[$programName] = [
                'Completo' => $this->getMonthlyStatusCount($programInscriptions, 'Completo', $startDate, $endDate),
                'Completando' => $this->getMonthlyStatusCount($programInscriptions, 'Completando', $startDate, $endDate),
                'Adelanto' => $this->getMonthlyStatusCount($programInscriptions, 'Adelanto', $startDate, $endDate)
            ];
        }
        
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
        
        // 3. Inscripciones por estado (completo, completando y adelanto) - usando estados del mes
        $inscriptionsByStatus = [
            'Completo' => $this->getMonthlyStatusCount($inscriptions, 'Completo', $startDate, $endDate),
            'Completando' => $this->getMonthlyStatusCount($inscriptions, 'Completando', $startDate, $endDate),
            'Adelanto' => $this->getMonthlyStatusCount($inscriptions, 'Adelanto', $startDate, $endDate)
        ];
        
        // 4. Inscripciones por plan de pago
        $inscriptionsByPaymentPlan = $inscriptions->groupBy(function ($inscription) {
                // Si payment_plan está vacío o nulo, usar 'Pre Inscrito'
                return $inscription->payment_plan ?: 'Pre Inscrito';
            })
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
        
        // 5.1. Inscripciones por lugar de residencia con detalle por estado
        $inscriptionsByResidenceDetail = $inscriptions->groupBy('residence')
            ->map(function ($items) use ($startDate, $endDate) {
                return [
                    'completo' => $this->getMonthlyStatusCount($items, 'Completo', $startDate, $endDate),
                    'completando' => $this->getMonthlyStatusCount($items, 'Completando', $startDate, $endDate),
                    'adelanto' => $this->getMonthlyStatusCount($items, 'Adelanto', $startDate, $endDate)
                ];
            })
            ->toArray();
        
        // 6. Inscripciones por profesión
        $inscriptionsByProfession = $inscriptions->groupBy('profession')
            ->map(function ($items) {
                return $items->count();
            })
            ->toArray();

        // 7. Inscripciones por género con detalle por estado
        $inscriptionsByGender = $inscriptions->groupBy('gender')
            ->map(function ($items) {
                return $items->count();
            })
            ->toArray();

        $inscriptionsByGenderDetail = $inscriptions->groupBy('gender')
            ->map(function ($items) use ($startDate, $endDate) {
                return [
                    'completo' => $this->getMonthlyStatusCount($items, 'Completo', $startDate, $endDate),
                    'completando' => $this->getMonthlyStatusCount($items, 'Completando', $startDate, $endDate),
                    'adelanto' => $this->getMonthlyStatusCount($items, 'Adelanto', $startDate, $endDate)
                ];
            })
            ->toArray();

        // 8. Inscripciones por medio de pago
        $inscriptionsByPaymentMethod = $inscriptions->groupBy('payment_method')
            ->map(function ($items) {
                return $items->count();
            })
            ->toArray();

        // 9. Inscripciones por equipo de marketing
        $inscriptionsByMarketingTeam = $inscriptions->filter(function ($inscription) {
                return $this->getActiveMarketingTeamName($inscription) !== null;
            })
            ->groupBy(function ($inscription) {
                $teamName = $this->getActiveMarketingTeamName($inscription);
                return $teamName ?: 'Otra Sede';
            })
            ->map(function ($items) {
                return $items->count();
            })
            ->toArray();

        // Agregar inscripciones sin equipo
        $inscriptionsWithoutTeam = $inscriptions->filter(function ($inscription) {
            return $this->getActiveMarketingTeamName($inscription) === null;
            })
            ->count();

        if ($inscriptionsWithoutTeam > 0) {
            $inscriptionsByMarketingTeam['Otra Sede'] = $inscriptionsWithoutTeam;
        }

        // Serie progresiva de inscripciones (mensual: por dia) usando la misma base del dashboard
        $progressiveSeries = $this->buildMonthlyProgressiveSeries($inscriptions, $startDate, $endDate);

        // Estadísticas adicionales para el reporte mensual (relación many-to-many)
        $programStats = collect();
        foreach ($inscriptions as $inscription) {
            $programs = $inscription->programs ?? collect();

            foreach ($programs as $program) {
                if (!$programStats->has($program->id)) {
                    $programStats->put($program->id, [
                        'name' => $program->name,
                        'count' => 0,
                        'total_paid' => 0,
                    ]);
                }

                $row = $programStats->get($program->id);
                $row['count']++;
                $row['total_paid'] += $inscription->total_paid;
                $programStats->put($program->id, $row);
            }
        }
            
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
            'paymentMethodData' => json_encode($inscriptionsByPaymentMethod),
            'marketingTeamData' => json_encode($inscriptionsByMarketingTeam),
            'residenceData' => json_encode($inscriptionsByResidence),
            'residenceDetailData' => $inscriptionsByResidenceDetail,
            'professionData' => json_encode($inscriptionsByProfession),
            'genderData' => json_encode($inscriptionsByGender),
            'genderDetailData' => $inscriptionsByGenderDetail,
            'progressiveLabels' => json_encode($progressiveSeries['labels']),
            'progressiveData' => json_encode($progressiveSeries['values']),
            'progressiveGranularity' => $progressiveSeries['granularity']
        ];
        
        // Obtener las últimas inscripciones realizadas (independientemente del mes/año seleccionado)
        $latestInscriptions = Inscription::with(['programs', 'creator'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // Nombre del mes actual en español para la vista
        $nombreMes = Carbon::createFromDate($year, $month, 1)->translatedFormat('F');
        $nombreMesAnterior = Carbon::createFromDate($previousMonth->year, $previousMonth->month, 1)->translatedFormat('F');
        
        // Obtener todos los programas para el filtro
        $programs = Program::orderBy('name')->get();
        
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
            'nombreMesAnterior',
            'programs',
            'programId'
        ));
    }
    
    protected function yearlyView(Request $request, $year, $viewType = 'yearly', $programId = null)
    {
        $startDate = Carbon::createFromDate($year, 1, 1)->startOfYear();
        $endDate = $startDate->copy()->endOfYear();
        
        // Obtener todas las inscripciones del año seleccionado con la relación creator
        $inscriptionsQuery = Inscription::whereBetween('inscription_date', [$startDate, $endDate])
            ->with(['programs', 'creator.marketingTeams', 'creator.leadTeams', 'paymentHistory']);
        
        // Aplicar filtro por programa si se especifica
        if ($programId) {
            $inscriptionsQuery->whereHas('programs', function($q) use ($programId) {
                $q->where('programs.id', $programId);
            });
        }
        
        $inscriptions = $inscriptionsQuery->get();
        
        // Obtener todas las inscripciones hasta la fecha final, agrupadas por CI
        $allInscriptions = Inscription::whereDate('inscription_date', '<=', $endDate)
            ->orderBy('inscription_date')
            ->get()
            ->groupBy('ci');
        
        // Procesar inscripciones para aplicar la lógica especial de estados
        $processedInscriptions = collect();
        $excludedInscriptionIds = []; // IDs de inscripciones "Adelanto" que no deben contarse
        
        // Primero identificamos las inscripciones "Adelanto" que tienen una "Completando" posterior en el mismo año
        foreach ($inscriptions as $inscription) {
            // Buscar si esta inscripción tiene Adelanto y luego Completando en el mismo año
            $changesThisYear = $inscription->paymentHistory()
                ->whereYear('status_date', $year)
                ->orderBy('status_date', 'ASC')
                ->get();
            
            if ($changesThisYear->count() > 0) {
                $hasAdelanto = $changesThisYear->contains(function ($change) {
                    return $change->new_status === 'Adelanto';
                });
                $hasCompletando = $changesThisYear->contains(function ($change) {
                    return $change->new_status === 'Completando';
                });
                
                // Si tiene Adelanto y luego Completando, no incluimos un registro separado de Adelanto
                if ($hasAdelanto && $hasCompletando) {
                    // La inscripción será consolidada como Completo
                    // No hay que excluir nada porque es una sola inscripción
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
            $processedInscription->status = $this->getEffectivePaymentStatus($inscription);
            
            // Para determinar consolidación, buscar si hay cambios de estado en el historial de pagos
            // Si tiene tanto Adelanto como Completando en el año, marcar como Completo
            $changesThisYear = $processedInscription->paymentHistory()
                ->whereYear('status_date', $year)
                ->orderBy('status_date', 'ASC')
                ->get();
            
            if ($changesThisYear->count() > 0) {
                $hasAdelanto = $changesThisYear->contains(function ($change) {
                    return $change->new_status === 'Adelanto';
                });
                $hasCompletando = $changesThisYear->contains(function ($change) {
                    return $change->new_status === 'Completando';
                });
                
                // Si tiene Adelanto y luego Completando en el mismo año, es Completo
                if ($hasAdelanto && $hasCompletando) {
                    $processedInscription->status = 'Completo';
                }
            }
            
            $processedInscriptions->push($processedInscription);
        }
        
        // Calcular datos para el año anterior
        $previousYear = $year - 1;
        $previousYearStart = Carbon::createFromDate($previousYear, 1, 1)->startOfYear();
        $previousYearEnd = Carbon::createFromDate($previousYear, 12, 31)->endOfYear();

        $previousYearInscriptionsQuery = Inscription::whereBetween('inscription_date', [$previousYearStart, $previousYearEnd]);

        if ($programId) {
            $previousYearInscriptionsQuery->whereHas('programs', function ($q) use ($programId) {
                $q->where('programs.id', $programId);
            });
        }

        $previousYearInscriptions = $previousYearInscriptionsQuery->get();

        // Calcular estadísticas del año anterior
        $previousYearInscriptionsWithEffectiveStatus = $previousYearInscriptions->map(function ($inscription) {
            $inscription->status = $this->getEffectivePaymentStatus($inscription);
            return $inscription;
        });

        $previousYearTotal = $previousYearInscriptionsWithEffectiveStatus->count();
        $previousYearCompleto = $previousYearInscriptionsWithEffectiveStatus->where('status', 'Completo')->count();
        $previousYearCompletando = $previousYearInscriptionsWithEffectiveStatus->where('status', 'Completando')->count();
        $previousYearAdelanto = $previousYearInscriptionsWithEffectiveStatus->where('status', 'Adelanto')->count();
        
        // Calcular total pagado del año anterior - sumar todos los cambios de estado
        $previousYearTotalPaid = 0;
        foreach ($previousYearInscriptions as $inscription) {
            $changesThisYear = $inscription->paymentHistory()
                ->whereYear('status_date', $previousYear)
                ->orderBy('status_date', 'ASC')
                ->get();
            
            foreach ($changesThisYear as $change) {
                $previousYearTotalPaid += $change->amount_paid;
            }
        }
        
        $previousYearTotalSinAdelantos = $previousYearCompleto + $previousYearCompletando;

        // Estadísticas del año actual usando las inscripciones procesadas
        $currentYearTotal = $processedInscriptions->count();
        $currentYearCompleto = $processedInscriptions->where('status', 'Completo')->count();
        $currentYearCompletando = $processedInscriptions->where('status', 'Completando')->count();
        $currentYearAdelanto = $processedInscriptions->where('status', 'Adelanto')->count();
        
        // Para vista anual, sumar todos los CAMBIOS DE ESTADO (pagos) del año
        // Pedro: Adelanto 100 (feb) + Completando 500 (mar) = 600
        // Sumamos cada cambio de estado como un pago
        $currentYearTotalPaid = 0;
        foreach ($processedInscriptions as $inscription) {
            // Obtener TODOS los cambios de estado del año
            $changesThisYear = $inscription->paymentHistory()
                ->whereYear('status_date', $year)
                ->orderBy('status_date', 'ASC')
                ->get();
            
            // Sumar el monto de CADA cambio como un pago
            foreach ($changesThisYear as $change) {
                $currentYearTotalPaid += $change->amount_paid;
            }
        }
        
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
            
            // Para evolución mensual anual, contar por mes de inscripción
            // usando el estado consolidado de cada inscripción.
            $monthlyCount = [
                'Completo' => 0,
                'Completando' => 0,
                'Adelanto' => 0
            ];
            
            foreach ($processedInscriptions as $inscription) {
                if (!$inscription->inscription_date) {
                    continue;
                }

                $inscriptionDate = Carbon::parse($inscription->inscription_date);

                if ($inscriptionDate->between($monthStart, $monthEnd) && isset($monthlyCount[$inscription->status])) {
                    $monthlyCount[$inscription->status]++;
                }
            }
            
            // Usar Carbon para obtener el nombre del mes en español
            $monthName = Carbon::createFromDate($year, $month, 1)->translatedFormat('F');
            
            $monthlyStats[$month] = [
                'month' => $monthName,
                'total' => array_sum($monthlyCount),
                'completo' => $monthlyCount['Completo'],
                'completando' => $monthlyCount['Completando'],
                'adelanto' => $monthlyCount['Adelanto'],
                'total_paid' => 0
            ];
            
            // Calcular total pagado en el mes (acumulado de cambios en ese mes)
            $monthTotalPaid = 0;
            foreach ($processedInscriptions as $inscription) {
                $changesThisMonth = $inscription->paymentHistory()
                    ->whereBetween('status_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                    ->get();
                
                foreach ($changesThisMonth as $change) {
                    $monthTotalPaid += $change->amount_paid;
                }
            }
            $monthlyStats[$month]['total_paid'] = $monthTotalPaid;
            
            $monthlyDatasets['labels'][] = $monthName;
            $monthlyDatasets['total'][] = array_sum($monthlyCount);
            $monthlyDatasets['completo'][] = $monthlyCount['Completo'];
            $monthlyDatasets['completando'][] = $monthlyCount['Completando'];
            $monthlyDatasets['adelanto'][] = $monthlyCount['Adelanto'];
            $monthlyDatasets['total_paid'][] = $monthTotalPaid;
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
        
        // 2. Inscripciones por programa (completos, completando, adelanto) - ANUAL (consolidado)
        $inscriptionsByProgram = collect();
        
        // Agrupar por programa
        $programMap = collect();
        foreach ($processedInscriptions as $inscription) {
            $programs = $inscription->programs ?? collect();
            foreach ($programs as $program) {
                if (!$programMap->has($program->name)) {
                    $programMap[$program->name] = collect();
                }
                $programMap[$program->name]->push($inscription);
            }
        }
        
        // Contar estados consolidados por programa
        foreach ($programMap as $programName => $programInscriptions) {
            $inscriptionsByProgram[$programName] = [
                'Completo' => $programInscriptions->where('status', 'Completo')->count(),
                'Completando' => $programInscriptions->where('status', 'Completando')->count(),
                'Adelanto' => $programInscriptions->where('status', 'Adelanto')->count()
            ];
        }
    
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
        
        // Estadísticas por programa para el año (relación many-to-many)
        $programStats = collect();
        foreach ($processedInscriptions as $inscription) {
            $programs = $inscription->programs ?? collect();

            foreach ($programs as $program) {
                if (!$programStats->has($program->id)) {
                    $programStats->put($program->id, [
                        'name' => $program->name,
                        'count' => 0,
                        'total_paid' => 0,
                        'completo' => 0,
                        'completando' => 0,
                        'adelanto' => 0,
                    ]);
                }

                $row = $programStats->get($program->id);
                $row['count']++;
                $row['total_paid'] += $inscription->total_paid;

                if ($inscription->status === 'Completo') {
                    $row['completo']++;
                } elseif ($inscription->status === 'Completando') {
                    $row['completando']++;
                } elseif ($inscription->status === 'Adelanto') {
                    $row['adelanto']++;
                }

                $programStats->put($program->id, $row);
            }
        }
        
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
        $inscriptionsByPaymentPlan = $processedInscriptions->groupBy(function ($inscription) {
                // Si payment_plan está vacío o nulo, usar 'Pre Inscrito'
                return $inscription->payment_plan ?: 'Pre Inscrito';
            })
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
        
        // 5.1. Inscripciones por lugar de residencia con detalle por estado (anual)
        $inscriptionsByResidenceDetail = $processedInscriptions->groupBy('residence')
            ->map(function ($items) {
                return [
                    'completo' => $items->where('status', 'Completo')->count(),
                    'completando' => $items->where('status', 'Completando')->count(),
                    'adelanto' => $items->where('status', 'Adelanto')->count()
                ];
            })
            ->toArray();
        
        // 6. Inscripciones por profesión (anual)
        $inscriptionsByProfession = $processedInscriptions->groupBy('profession')
            ->map(function ($items) {
                return $items->count();
            })
            ->toArray();

        // 7. Inscripciones por género con detalle por estado (anual)
        $inscriptionsByGender = $processedInscriptions->groupBy('gender')
            ->map(function ($items) {
                return $items->count();
            })
            ->toArray();

        $inscriptionsByGenderDetail = $processedInscriptions->groupBy('gender')
            ->map(function ($items) {
                return [
                    'completo' => $items->where('status', 'Completo')->count(),
                    'completando' => $items->where('status', 'Completando')->count(),
                    'adelanto' => $items->where('status', 'Adelanto')->count()
                ];
            })
            ->toArray();

        // 8. Inscripciones por medio de pago (anual)
        $inscriptionsByPaymentMethod = $processedInscriptions->groupBy('payment_method')
            ->map(function ($items) {
                return $items->count();
            })
            ->toArray();

        // 9. Inscripciones por equipo de marketing (anual)
        $inscriptionsByMarketingTeam = $processedInscriptions->filter(function ($inscription) {
                return $this->getActiveMarketingTeamName($inscription) !== null;
            })
            ->groupBy(function ($inscription) {
                $teamName = $this->getActiveMarketingTeamName($inscription);
                return $teamName ?: 'Otra Sede';
            })
            ->map(function ($items) {
                return $items->count();
            })
            ->toArray();

        // Agregar inscripciones sin equipo (anual)
        $inscriptionsWithoutTeam = $processedInscriptions->filter(function ($inscription) {
            return $this->getActiveMarketingTeamName($inscription) === null;
            })
            ->count();

        if ($inscriptionsWithoutTeam > 0) {
            $inscriptionsByMarketingTeam['Otra Sede'] = $inscriptionsWithoutTeam;
        }

        // Serie progresiva de inscripciones (anual: por mes) usando la misma base del dashboard
        $progressiveSeries = $this->buildYearlyProgressiveSeries($processedInscriptions, $year);

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
            'paymentMethodData' => json_encode($inscriptionsByPaymentMethod),
            'marketingTeamData' => json_encode($inscriptionsByMarketingTeam),
            'residenceData' => json_encode($inscriptionsByResidence),
            'residenceDetailData' => $inscriptionsByResidenceDetail,
            'professionData' => json_encode($inscriptionsByProfession),
            'genderData' => json_encode($inscriptionsByGender),
            'genderDetailData' => $inscriptionsByGenderDetail,
            'progressiveLabels' => json_encode($progressiveSeries['labels']),
            'progressiveData' => json_encode($progressiveSeries['values']),
            'progressiveGranularity' => $progressiveSeries['granularity']
        ];
        
        // Obtener las últimas inscripciones realizadas (independientemente del año seleccionado)
        $latestInscriptions = Inscription::with(['programs', 'creator'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // Obtener todos los programas para el filtro
        $programs = Program::orderBy('name')->get();
        
        $month = 'all';

        return view('dashboard', compact(
            'stats', 
            'chartData', 
            'year', 
            'month',
            'viewType', 
            'monthlyStats', 
            'programStats', 
            'advisorStats', 
            'latestInscriptions',
            'previousYear',
            'programs',
            'programId'
        ));
    }

    /**
     * Helper para obtener el estado de una inscripción en un mes específico
     */
    private function getMonthlyStatusCount($inscriptions, $status, $startDate, $endDate)
    {
        $count = 0;
        foreach ($inscriptions as $inscription) {
            $resolvedStatus = $this->resolvePaymentStatusForPeriod($inscription, $startDate, $endDate);

            if ($resolvedStatus === $status) {
                $count++;
            }
        }
        return $count;
    }

    private function resolvePaymentStatusForPeriod($inscription, $startDate, $endDate)
    {
        $lastChangeThisPeriod = $inscription->paymentHistory()
            ->whereBetween('status_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->orderBy('status_date', 'DESC')
            ->first();

        if ($lastChangeThisPeriod) {
            return $this->normalizePaymentStatus($lastChangeThisPeriod->new_status);
        }

        if (
            $inscription->inscription_date
            && Carbon::parse($inscription->inscription_date)->between($startDate, $endDate)
        ) {
            return $this->getEffectivePaymentStatus($inscription);
        }

        return null;
    }

    private function getEffectivePaymentStatus($inscription)
    {
        return $this->normalizePaymentStatus($inscription->local_payment_status ?? $inscription->status);
    }

    private function normalizePaymentStatus($status)
    {
        $normalized = trim((string) $status);

        return match ($normalized) {
            'Completo' => 'Completo',
            'Completando' => 'Completando',
            'Adelanto' => 'Adelanto',
            'Pendiente' => 'Pendiente',
            default => null,
        };
    }

    private function getActiveMarketingTeamName($inscription)
    {
        if (!$inscription->creator) {
            return null;
        }

        // Priorizar membresia activa en equipo activo
        $activeTeam = $inscription->creator->marketingTeams()
            ->wherePivot('active', true)
            ->where('marketing_teams.active', true)
            ->orderBy('marketing_teams.id')
            ->first();

        if ($activeTeam) {
            return $activeTeam->name;
        }

        // Fallback: si es lider de un equipo activo y no tiene membresia pivote
        $leaderTeam = $inscription->creator->leadTeams()
            ->where('active', true)
            ->orderBy('id')
            ->first();

        return $leaderTeam ? $leaderTeam->name : null;
    }

    private function buildMonthlyProgressiveSeries($inscriptions, $startDate, $endDate)
    {
        $countsByDate = [];
        $today = Carbon::now()->startOfDay();
        $seriesEndDate = $endDate->copy();

        if ($today->between($startDate, $endDate)) {
            $seriesEndDate = $today;
        }

        foreach ($inscriptions as $inscription) {
            if (!$inscription->inscription_date) {
                continue;
            }

            $inscriptionDate = Carbon::parse($inscription->inscription_date);
            if (!$inscriptionDate->between($startDate, $endDate)) {
                continue;
            }

            $dayKey = $inscriptionDate->toDateString();
            $countsByDate[$dayKey] = ($countsByDate[$dayKey] ?? 0) + 1;
        }

        $labels = [];
        $values = [];
        $runningTotal = 0;

        for ($date = $startDate->copy(); $date->lte($seriesEndDate); $date->addDay()) {
            $dayKey = $date->toDateString();
            $runningTotal += (int) ($countsByDate[$dayKey] ?? 0);
            $labels[] = $date->format('d/m');
            $values[] = $runningTotal;
        }

        return [
            'labels' => $labels,
            'values' => $values,
            'granularity' => 'day'
        ];
    }

    private function buildYearlyProgressiveSeries($inscriptions, $year)
    {
        $startDate = Carbon::createFromDate($year, 1, 1)->startOfYear();
        $endDate = Carbon::createFromDate($year, 12, 31)->endOfYear();
        $today = Carbon::now()->startOfDay();
        $seriesEndDate = $today->year === (int) $year ? $today->copy()->min($endDate) : $endDate->copy();
        $seriesEndMonth = $seriesEndDate->copy()->startOfMonth();

        $countsByDate = [];
        foreach ($inscriptions as $inscription) {
            if (!$inscription->inscription_date) {
                continue;
            }

            $inscriptionDate = Carbon::parse($inscription->inscription_date);
            if (!$inscriptionDate->between($startDate, $endDate)) {
                continue;
            }

            $dayKey = $inscriptionDate->toDateString();
            $countsByDate[$dayKey] = ($countsByDate[$dayKey] ?? 0) + 1;
        }

        $labels = [];
        $values = [];
        $runningTotal = 0;

        for ($monthCursor = $startDate->copy()->startOfMonth(); $monthCursor->lte($seriesEndMonth); $monthCursor->addMonth()) {
            $periodStart = $monthCursor->copy()->startOfMonth();
            $periodEnd = $monthCursor->copy()->endOfMonth()->min($seriesEndDate);

            for ($date = $periodStart->copy(); $date->lte($periodEnd); $date->addDay()) {
                $runningTotal += (int) ($countsByDate[$date->toDateString()] ?? 0);
            }

            $labels[] = strtoupper($monthCursor->translatedFormat('M'));
            $values[] = $runningTotal;
        }

        return [
            'labels' => $labels,
            'values' => $values,
            'granularity' => 'month'
        ];
    }
}
