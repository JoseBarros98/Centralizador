<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Models\Inscription;
use App\Models\Teacher;
use App\Models\Module;
use App\Models\Grade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class AcademicDashboardController extends Controller
{
    /**
     * Mostrar el dashboard académico con métricas específicas
     */
    public function index(Request $request)
    {
        // Configurar Carbon en español
        Carbon::setLocale('es');

        $currentYear = Carbon::now()->year;

        // Obtener parámetros de filtro
        $programFilter = $request->get('program');
        $areaFilter = $request->get('area');
        $yearFilter = $request->get('year', $currentYear); // Por defecto el año actual
        $statusFilter = $request->get('status');

        // Base query para programas con filtros
        $programQuery = Program::query();

        if ($programFilter) {
            $programQuery->where('id', $programFilter);
        }

        if ($areaFilter) {
            // Filtrar por área desde ExternalPostgraduate
            $postgraduateIds = \App\Models\External\ExternalPostgraduate::where('area_posgrado', $areaFilter)
                ->pluck('id_posgrado');
            $programQuery->whereIn('postgraduate_id', $postgraduateIds);
        }

        if ($yearFilter) {
            $programQuery->where('year', $yearFilter);
        }

        if ($statusFilter) {
            $programQuery->where('status', $statusFilter);
        }

        // Obtener programas filtrados
        $filteredPrograms = $programQuery->get();
        $filteredProgramCodes = $filteredPrograms->pluck('code');

        // Query base para inscripciones con filtros aplicados
        // Buscar inscritos que tengan los programas filtrados en la tabla pivot inscription_program
        $inscriptionsQuery = Inscription::query();
        if ($filteredProgramCodes->isNotEmpty()) {
            $inscriptionsQuery->whereHas('programs', function($q) use ($filteredProgramCodes) {
                $q->whereIn('code', $filteredProgramCodes);
            });
        }

        // 1. Programas por área - usando datos filtrados
        // Obtener áreas desde ExternalPostgraduate usando postgraduate_id
        $programsByAreaData = [];
        foreach ($filteredPrograms as $program) {
            try {
                if ($program->postgraduate_id) {
                    $postgrad = \App\Models\External\ExternalPostgraduate::where('id_posgrado', $program->postgraduate_id)->first();
                    if ($postgrad && $postgrad->area_posgrado) {
                        $area = $postgrad->area_posgrado;
                        if (!isset($programsByAreaData[$area])) {
                            $programsByAreaData[$area] = 0;
                        }
                        $programsByAreaData[$area]++;
                    }
                }
            } catch (\Exception $e) {
                // Ignorar programas con errores al obtener el área
            }
        }
        $programsByArea = collect($programsByAreaData);

        // 2. Programas por estado - con filtros
        $programsByState = $filteredPrograms->groupBy('status')
            ->map(function($programs) {
                return $programs->count();
            });

        // Compatibilidad con versiones antiguas de la vista que referencian este dataset
        $inscriptionsByYear = collect();

        // 4. Listas para filtros (sin filtrar para mostrar todas las opciones)
        $programs = Program::select('id', 'name')->orderBy('name')->get();
        
        // Obtener áreas desde ExternalPostgraduate
        $areas = \App\Models\External\ExternalPostgraduate::select('area_posgrado')
            ->whereNotNull('area_posgrado')
            ->where('area_posgrado', '!=', '')
            ->distinct()
            ->orderBy('area_posgrado')
            ->pluck('area_posgrado');

        // Obtener gestiones (años) disponibles
        $years = Program::whereNotNull('year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');
        
        // Obtener estados/fases disponibles
        $statuses = Program::whereNotNull('status')
            ->where('status', '!=', '')
            ->distinct()
            ->orderBy('status')
            ->pluck('status');
        

        // Columna de estado academico externo (compatibilidad entre esquemas)
        $academicStatusColumn = Schema::hasColumn('inscriptions', 'estado_academico')
            ? 'estado_academico'
            : 'external_academic_status';
        $academicStatusExpression = "UPPER(COALESCE(inscriptions.{$academicStatusColumn}, ''))";

        $applyAcademicStatusValues = function ($query, array $values) use ($academicStatusExpression) {
            $query->where(function ($statusQuery) use ($values, $academicStatusExpression) {
                foreach ($values as $index => $value) {
                    $method = $index === 0 ? 'whereRaw' : 'orWhereRaw';
                    $statusQuery->{$method}("{$academicStatusExpression} = ?", [mb_strtoupper($value, 'UTF-8')]);
                }
            });
        };

        // Calcular totales para cards de estados académicos exactos (fuente externa)
        $totalRegistros = (clone $inscriptionsQuery)->count();

        $countByAcademicStatus = function (array $values) use ($inscriptionsQuery, $applyAcademicStatusValues) {
            $query = clone $inscriptionsQuery;
            $applyAcademicStatusValues($query, $values);
            return $query->count();
        };

        $academicStatusCards = [
            [
                'key' => 'en_desarrollo',
                'label' => 'En Desarrollo',
                'count' => $countByAcademicStatus(['EN DESARROLLO']),
                'color' => 'text-blue-600',
            ],
            [
                'key' => 'reprobado',
                'label' => 'Reprobado',
                'count' => $countByAcademicStatus(['REPROBADO']),
                'color' => 'text-rose-600',
            ],
            [
                'key' => 'abandono_academico',
                'label' => 'Abandono Académico',
                'count' => $countByAcademicStatus(['ABANDONO ACADÉMICO', 'ABANDONO ACADEMICO']),
                'color' => 'text-red-600',
            ],
            [
                'key' => 'en_desarrollo_nivelacion',
                'label' => 'En Desarrollo - Nivelación',
                'count' => $countByAcademicStatus(['EN DESARROLLO - NIVELACIÓN', 'EN DESARROLLO - NIVELACION']),
                'color' => 'text-indigo-600',
            ],
            [
                'key' => 'concluido_aprobado',
                'label' => 'Concluido Aprobado',
                'count' => $countByAcademicStatus(['CONCLUIDO APROBADO']),
                'color' => 'text-emerald-600',
            ],
            [
                'key' => 'congelado',
                'label' => 'Congelado',
                'count' => $countByAcademicStatus(['CONGELADO']),
                'color' => 'text-cyan-600',
            ],
            [
                'key' => 'grupo',
                'label' => 'Grupo',
                'count' => $countByAcademicStatus(['GRUPO']),
                'color' => 'text-violet-600',
            ],
        ];

        // Debug log
        Log::info('Dashboard Metrics', [
            'totalRegistros' => $totalRegistros,
            'academicStatusCards' => collect($academicStatusCards)->pluck('count', 'label')->toArray(),
            'academicStatusColumn' => $academicStatusColumn,
            'filteredProgramCodes' => $filteredProgramCodes->toArray()
        ]);

        // Programas más populares (con más inscripciones) - con filtros aplicados
        // Obtener los programas filtrados
        if ($filteredProgramCodes->isNotEmpty()) {
            $popularPrograms = Program::whereIn('code', $filteredProgramCodes)
                ->withCount('inscriptions')
                ->orderBy('inscriptions_count', 'desc')
                ->take(5)
                ->get();
        } else {
            // Si no hay filtros, mostrar todos los programas
            $popularPrograms = Program::withCount('inscriptions')
                ->orderBy('inscriptions_count', 'desc')
                ->take(5)
                ->get();
        }

        // Top docentes mejor valorados.
        // 1) Intenta con filtros actuales
        // 2) Si no hay datos, hace fallback global para no dejar el podio vacío
        $buildTopTeachers = function ($programIds = null) {
            $ratedModulesQuery = Module::query()
                ->where('status', 'CONCLUIDO')
                ->whereNotNull('teacher_rating')
                ->where(function ($query) {
                    $query->whereNotNull('teacher_id')
                        ->orWhere(function ($subQuery) {
                            $subQuery->whereNotNull('teacher_name')
                                ->where('teacher_name', '!=', '');
                        });
                });

            if (!empty($programIds)) {
                $ratedModulesQuery->whereIn('program_id', $programIds);
            }

            $ratedModules = $ratedModulesQuery
                ->with('teacher:id,name,academic_degree')
                ->get(['id', 'teacher_id', 'teacher_name', 'teacher_rating']);

            return $ratedModules
                ->groupBy(function ($module) {
                    if (!empty($module->teacher_id)) {
                        return 'id:' . $module->teacher_id;
                    }

                    return 'name:' . mb_strtolower(trim((string) $module->teacher_name));
                })
                ->map(function ($modules) {
                    $first = $modules->first();
                    $avgRating = round((float) $modules->avg('teacher_rating'), 2);
                    $ratedCount = $modules->count();

                    $teacher = $first->teacher;
                    $displayName = $teacher?->name ?: trim((string) $first->teacher_name);

                    return (object) [
                        'name' => $displayName,
                        'paternal_surname' => '',
                        'academic_degree' => $teacher?->academic_degree,
                        'modules_avg_teacher_rating' => $avgRating,
                        'modules_count' => $ratedCount,
                    ];
                })
                ->sortByDesc(function ($teacher) {
                    return $teacher->modules_avg_teacher_rating + ($teacher->modules_count / 1000);
                })
                ->take(3)
                ->values();
        };

        $programIds = $filteredPrograms->pluck('id')->filter()->values()->toArray();
        $topTeachers = $buildTopTeachers($programIds);

        if ($topTeachers->isEmpty()) {
            $topTeachers = $buildTopTeachers();
        }

        // Aprobaciones por tipo - ya no se usa en la vista

        // Programas por tipo - con filtros
        $programsByTypeData = [];
        foreach ($filteredPrograms as $program) {
            $type = 'Otros';
            $name = strtolower($program->name);
            
            if (strpos($name, 'diplomado') === 0) {
                $type = 'Diplomado';
            } elseif (strpos($name, 'maestría') === 0 || strpos($name, 'maestria') === 0) {
                $type = 'Maestría';
            } elseif (strpos($name, 'curso') === 0) {
                $type = 'Curso';
            } elseif (strpos($name, 'especialización') === 0 || strpos($name, 'especializacion') === 0) {
                $type = 'Especialización';
            } elseif (strpos($name, 'certificado') === 0) {
                $type = 'Certificado';
            } elseif (strpos($name, 'doctorado') === 0) {
                $type = 'Doctorado';
            } elseif (strpos($name, 'programa') === 0) {
                $type = 'Programa';
            } elseif (strpos($name, 'taller') === 0) {
                $type = 'Taller';
            } elseif (strpos($name, 'seminario') === 0) {
                $type = 'Seminario';
            }
            
            if (!isset($programsByTypeData[$type])) {
                $programsByTypeData[$type] = 0;
            }
            $programsByTypeData[$type]++;
        }
        
        $programsByType = collect($programsByTypeData)->map(function($count, $type) {
            return (object) ['program_type' => $type, 'total' => $count];
        })->values();

        // Preparar datos de programas populares para el gráfico
        $popularProgramsData = $popularPrograms->map(function($program) {
            return [
                'name' =>  $program->name,
                'short_name' => $program->name,
                'inscriptions_count' => $program->inscriptions_count ?? 0
            ];
        });

        $filteredProgramIds = $filteredPrograms->pluck('id')->filter()->values();

        // Estado por programa (Top 10 por volumen de inscritos)
        $statusByProgramData = DB::table('inscription_program as ip')
            ->join('programs as p', 'p.id', '=', 'ip.program_id')
            ->join('inscriptions as i', 'i.id', '=', 'ip.inscription_id')
            ->when($filteredProgramIds->isNotEmpty(), function ($query) use ($filteredProgramIds) {
                $query->whereIn('p.id', $filteredProgramIds);
            })
            ->select(
                'p.id',
                'p.name',
                DB::raw("SUM(CASE WHEN i.participant_status = 'VIGENTE' THEN 1 ELSE 0 END) as vigente"),
                DB::raw("SUM(CASE WHEN i.participant_status = 'DEVOLUCIÓN' THEN 1 ELSE 0 END) as devolucion"),
                DB::raw("SUM(CASE WHEN i.participant_status = 'ABANDON' THEN 1 ELSE 0 END) as abandono"),
                DB::raw("SUM(CASE WHEN i.participant_status = 'INSCRIPCIÓN INCOMPLETA' THEN 1 ELSE 0 END) as incompleta"),
                DB::raw('COUNT(i.id) as total')
            )
            ->groupBy('p.id', 'p.name')
            ->havingRaw('COUNT(i.id) > 0')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(function ($row) {
                return [
                    'program' => $row->name,
                    'vigente' => (int) $row->vigente,
                    'devolucion' => (int) $row->devolucion,
                    'abandono' => (int) $row->abandono,
                    'incompleta' => (int) $row->incompleta,
                    'total' => (int) $row->total,
                ];
            })
            ->values();

        // Tasa de aprobacion por programa (Top 10 por evaluaciones)
        $approvalRateByProgramData = DB::table('programs as p')
            ->leftJoin('modules as m', 'm.program_id', '=', 'p.id')
            ->leftJoin('grades as g', 'g.module_id', '=', 'm.id')
            ->when($filteredProgramIds->isNotEmpty(), function ($query) use ($filteredProgramIds) {
                $query->whereIn('p.id', $filteredProgramIds);
            })
            ->select(
                'p.id',
                'p.name',
                DB::raw('COUNT(g.id) as total_evaluated'),
                DB::raw('SUM(CASE WHEN g.grade >= 51 THEN 1 ELSE 0 END) as approved_count')
            )
            ->groupBy('p.id', 'p.name')
            ->havingRaw('COUNT(g.id) > 0')
            ->orderByDesc('total_evaluated')
            ->limit(10)
            ->get()
            ->map(function ($row) {
                $totalEvaluated = (int) $row->total_evaluated;
                $approvedCount = (int) $row->approved_count;
                $approvalRate = $totalEvaluated > 0
                    ? round(($approvedCount / $totalEvaluated) * 100, 1)
                    : 0;

                return [
                    'program' => $row->name,
                    'approved_count' => $approvedCount,
                    'total_evaluated' => $totalEvaluated,
                    'approval_rate' => $approvalRate,
                ];
            })
            ->values();

        // Obtener cumpleaños del mes actual de docentes
        $currentMonth = Carbon::now()->month;
        $birthdayTeachers = Teacher::whereNotNull('birth_date')
            ->whereRaw('MONTH(birth_date) = ?', [$currentMonth])
            ->select('id', 'name', 'birth_date', 'email', 'phone')
            ->orderByRaw('DAY(birth_date)')
            ->get()
            ->map(function($teacher) {
                $nextBirthday = Carbon::createFromDate(
                    Carbon::now()->year,
                    $teacher->birth_date->month,
                    $teacher->birth_date->day
                );
                if ($nextBirthday->isPast()) {
                    $nextBirthday->addYear();
                }
                return [
                    'id' => $teacher->id,
                    'name' => $teacher->name,
                    'birth_date' => $teacher->birth_date,
                    'email' => $teacher->email,
                    'phone' => $teacher->phone,
                    'day' => $teacher->birth_date->day,
                    'days_until' => $nextBirthday->diffInDays(Carbon::now()),
                    'type' => 'teacher'
                ];
            });

        // Obtener cumpleaños del mes actual de inscritos
        $birthdayInscriptions = Inscription::whereNotNull('birth_date')
            ->whereRaw('MONTH(birth_date) = ?', [$currentMonth])
            ->with('programs')
            ->select('id', 'full_name', 'birth_date', 'email', 'phone', 'program_id')
            ->orderByRaw('DAY(birth_date)')
            ->get()
            ->map(function($inscription) {
                $nextBirthday = Carbon::createFromDate(
                    Carbon::now()->year,
                    $inscription->birth_date->month,
                    $inscription->birth_date->day
                );
                if ($nextBirthday->isPast()) {
                    $nextBirthday->addYear();
                }
                
                // Obtener el primer programa asociado
                $programName = 'Sin programa';
                if ($inscription->programs && $inscription->programs->count() > 0) {
                    $programName = $inscription->programs->first()->name;
                }
                
                return [
                    'id' => $inscription->id,
                    'full_name' => $inscription->full_name,
                    'name' => explode(' ', $inscription->full_name)[0] ?? $inscription->full_name,
                    'program_name' => $programName,
                    'birth_date' => $inscription->birth_date,
                    'email' => $inscription->email,
                    'phone' => $inscription->phone,
                    'day' => $inscription->birth_date->day,
                    'days_until' => $nextBirthday->diffInDays(Carbon::now()),
                    'type' => 'inscription'
                ];
            });

        // Obtener solo docentes ordenados por día
        $birthdayTeachersOnly = $birthdayTeachers->sortBy('day');
        
        // Obtener solo inscritos ordenados por día
        $birthdayInscriptionsOnly = $birthdayInscriptions->sortBy('day');
        
        // Separar inscritos que cumplen hoy de los demás
        $currentDay = Carbon::now()->day;
        $birthdayInscriptionsToday = $birthdayInscriptionsOnly->filter(function($inscription) use ($currentDay) {
            return $inscription['day'] == $currentDay;
        })->values();
        
        $birthdayInscriptionsOther = $birthdayInscriptionsOnly->filter(function($inscription) use ($currentDay) {
            return $inscription['day'] != $currentDay;
        })->values();

        // Combinar y ordenar por día del mes
        $allBirthdays = collect(array_merge(
            $birthdayTeachers->toArray(),
            $birthdayInscriptions->toArray()
        ))->sortBy('day');

        return view('dashboard.academic', compact(
            'totalRegistros',
            'academicStatusCards',
            'popularPrograms',
            'popularProgramsData',
            'topTeachers',
            'programs',
            'areas',
            'years',
            'statuses',
            'programsByArea',
            'inscriptionsByYear',
            'programsByType',
            'currentYear',
            'allBirthdays',
            'birthdayTeachersOnly',
            'birthdayInscriptionsOnly',
            'birthdayInscriptionsToday',
            'birthdayInscriptionsOther',
            'programsByState'
        ));
    }

    /**
     * Obtener datos específicos por área
     */
    public function getAreaData($area)
    {
        $programs = Program::where('area', $area)
            ->orWhere(DB::raw('CASE 
                WHEN LOWER(name) LIKE "%música%" OR LOWER(name) LIKE "%music%" THEN "Música"
                WHEN LOWER(name) LIKE "%arte%" OR LOWER(name) LIKE "%paint%" OR LOWER(name) LIKE "%dibujo%" THEN "Artes Visuales"
                WHEN LOWER(name) LIKE "%danza%" OR LOWER(name) LIKE "%baile%" OR LOWER(name) LIKE "%dance%" THEN "Danza"
                WHEN LOWER(name) LIKE "%teatro%" OR LOWER(name) LIKE "%actuación%" OR LOWER(name) LIKE "%drama%" THEN "Teatro"
                WHEN LOWER(name) LIKE "%literatura%" OR LOWER(name) LIKE "%escritura%" OR LOWER(name) LIKE "%poesía%" THEN "Literatura"
                ELSE "Otros"
                END'), $area)
            ->with(['inscriptions'])
            ->get();

        return response()->json([
            'programs' => $programs,
            'total_programs' => $programs->count(),
            'total_inscriptions' => $programs->sum(function($program) {
                return $program->inscriptions->count();
            })
        ]);
    }

    /**
     * Obtener estadísticas detalladas por período
     */
    public function getDetailedStats(Request $request)
    {
        $year = $request->input('year', Carbon::now()->year);
        $month = $request->input('month');

        $query = Inscription::query();

        if ($month) {
            $query->whereYear('inscription_date', $year)
                  ->whereMonth('inscription_date', $month);
        } else {
            $query->whereYear('inscription_date', $year);
        }

        $inscriptions = $query->with(['programs'])->get();

        $stats = [
            'total_inscriptions' => $inscriptions->count(),
            'by_program' => $inscriptions->flatMap(function($inscription) {
                return $inscription->programs->pluck('name');
            })->groupBy(function($name) { return $name; })->map->count(),
            'by_month' => $inscriptions->groupBy(function($inscription) {
                return $inscription->inscription_date->format('m');
            })->map->count(),
            'by_gender' => $inscriptions->groupBy('gender')->map->count(),
        ];

        return response()->json($stats);
    }
}