<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Models\ProgramAllocation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProgramAllocationController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:program_allocation.view')->only(['index']);
        $this->middleware('permission:program_allocation.create')->only(['store']);
        $this->middleware('permission:program_allocation.edit')->only(['updateField']);
        $this->middleware('permission:program_allocation.delete')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $query = ProgramAllocation::with([
            'program',
            'module',
            'paymentRequest'
        ]);

        // Aplicar filtros
        if ($request->filled('categoria')) {
            $query->whereHas('program', function ($q) use ($request) {
                $categoria = $request->categoria;
                $q->where(function($builder) use ($categoria) {
                    if ($categoria === 'Diplomado') {
                        $builder->where('name', 'like', 'Diplomado%');
                    } elseif ($categoria === 'Maestría') {
                        $builder->where('name', 'like', 'Maestría%');
                    } elseif ($categoria === 'Curso') {
                        $builder->where('name', 'like', 'Curso%');
                    } elseif ($categoria === 'Especialidad') {
                        $builder->where('name', 'like', 'Especialidad%');
                    }
                });
            });
        }

        if ($request->filled('etapa')) {
            $query->whereHas('program', function ($q) use ($request) {
                $q->where('status', $request->etapa);
            });
        }

        if ($request->filled('responsable_cartera')) {
            // Filtrar por ID de usuario (campo almacena IDs, no nombres)
            $query->where('responsable_cartera', $request->responsable_cartera);
        }

        if ($request->filled('programa_search')) {
            $search = $request->programa_search;
            $query->whereHas('program', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('id', $search);
            });
        }

        if ($request->filled('mes')) {
            $query->where('mes', $request->mes);
        }

        if ($request->filled('gestion')) {
            $year = (int) $request->gestion;
            $query->whereYear('created_at', $year);
        }

        $allocations = $query->orderBy('mes')->orderBy('created_at', 'desc')
            ->paginate(50);

        // Obtener programas que ya tienen asignaciones
        $allocatedProgramIds = ProgramAllocation::pluck('program_id')->unique()->toArray();
        
        // Obtener programas disponibles para agregar
        $availablePrograms = Program::whereNotIn('id', $allocatedProgramIds)->get();
        
        // Obtener TODOS los programas para búsqueda
        $allPrograms = Program::orderBy('name')->get();
        
        // Obtener solo usuarios activos con rol de accountant para responsable_cartera
        $users = User::active()
            ->role('accountant')
            ->orderBy('name')
            ->get();
        
        // Obtener categorías únicas
        $categorias = [];
        foreach (Program::pluck('name')->unique() as $name) {
            if (str_starts_with($name, 'Diplomado')) {
                $categorias['Diplomado'] = 'Diplomado';
            } elseif (str_starts_with($name, 'Maestría')) {
                $categorias['Maestría'] = 'Maestría';
            } elseif (str_starts_with($name, 'Curso')) {
                $categorias['Curso'] = 'Curso';
            } elseif (str_starts_with($name, 'Especialidad')) {
                $categorias['Especialidad'] = 'Especialidad';
            }
        }
        
        // Obtener etapas únicas
        $etapas = Program::distinct()->pluck('status')->filter(fn($v) => $v !== null)->values();

        // Obtener años únicos de asignaciones (desde created_at)
        $gestiones = ProgramAllocation::selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();
        
        // Obtener responsables cartera únicos
        $responsablesCartera = ProgramAllocation::where('responsable_cartera', '!=', null)
            ->distinct()
            ->pluck('responsable_cartera')
            ->values();
        
        // Calcular estadísticas basadas en las asignaciones filtradas
        // Crear una copia de la query sin la paginación para calcular totales
        $queryForStats = clone $query;
        $filteredAllocations = $queryForStats->get();
        
        $totalAsignacion = $filteredAllocations->sum('asignacion_programa');
        $totalCobrado = $filteredAllocations->sum(function($allocation) {
            // Tomar el monto más alto entre todos los montos
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

        return view('program-allocation.index', compact('allocations', 'availablePrograms', 'allPrograms', 'users', 'totalAsignacion', 'totalCobrado', 'porcentajeTotalAlcanzado', 'categorias', 'etapas', 'responsablesCartera', 'gestiones'));
    }

    /**
     * Store a new program allocation
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'program_id' => 'required|exists:programs,id',
            'mes' => 'required|integer|between:1,12'
        ]);

        // Verificar que el usuario esté autenticado
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no autenticado'
            ], 401);
        }

        // Verificar que no exista ya una asignación para ese programa y mes
        $existing = ProgramAllocation::where('program_id', $validated['program_id'])
            ->where('mes', $validated['mes'])
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Ya existe una asignación para este programa en el mes seleccionado'
            ], 422);
        }

        // Crear nueva asignación
        $allocation = ProgramAllocation::create([
            'program_id' => $validated['program_id'],
            'mes' => $validated['mes'],
            'user_id' => $user->id
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Asignación creada correctamente',
            'allocation' => $allocation->load(['program', 'module', 'paymentRequest'])
        ]);
    }

    /**
     * Update a specific field for a program allocation
     */
    public function updateField(Request $request, ProgramAllocation $allocation): JsonResponse
    {
        $validated = $request->validate([
            'field' => 'required|string|in:cobro_titulacion,asignacion_programa,responsable_cartera,fecha_pago,monto_al_5,monto_al_10,monto_al_15,monto_al_20,monto_al_25,monto_al_30',
            'value' => 'nullable',
            'type' => 'required|string|in:text,number,date,select'
        ]);

        $field = $validated['field'];
        $value = $validated['value'];

        // Procesar el valor según el tipo
        if ($validated['type'] === 'date' && $value) {
            $value = \Carbon\Carbon::createFromFormat('Y-m-d', $value)->format('Y-m-d');
        } elseif ($validated['type'] === 'number' && $value) {
            $value = (float) $value;
        }

        // Actualizar el campo
        $allocation->update([$field => $value]);

        // Calcular porcentajes si se actualiza un monto o asignación_programa
        $montoFields = ['monto_al_5', 'monto_al_10', 'monto_al_15', 'monto_al_20', 'monto_al_25', 'monto_al_30'];
        
        if (in_array($field, $montoFields) || $field === 'asignacion_programa') {
            // Refrescar la asignación desde la base de datos
            $allocation->refresh();
            
            if ($allocation->asignacion_programa && $allocation->asignacion_programa > 0) {
                foreach ($montoFields as $montoField) {
                    $monto = $allocation->{$montoField};
                    if ($monto && $monto > 0) {
                        $porcentajeField = str_replace('monto', 'porcentaje', $montoField);
                        $porcentaje = ($monto / $allocation->asignacion_programa) * 100;
                        $allocation->update([$porcentajeField => $porcentaje]);
                    }
                }
            }
        }

        // Obtener los nuevos totales del mes y año actual
        $mes = request('mes', date('n'));
        $year = request('gestion', date('Y'));
        
        $query = ProgramAllocation::where('mes', $mes)
            ->where(DB::raw('YEAR(created_at)'), $year);
        
        $totalAsignacion = $query->sum('asignacion_programa');
        
        $totalCobrado = $query->get()
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

        $porcentajeTotalAlcanzado = $totalAsignacion > 0 ? ($totalCobrado / $totalAsignacion) * 100 : 0;

        return response()->json([
            'success' => true,
            'message' => 'Campo actualizado correctamente',
            'data' => $allocation,
            'totals' => [
                'totalAsignacion' => $totalAsignacion,
                'totalCobrado' => $totalCobrado,
                'porcentajeTotalAlcanzado' => $porcentajeTotalAlcanzado
            ]
        ]);
    }

    /**
     * Delete a program allocation
     */
    public function destroy($id): JsonResponse
    {
        $allocation = ProgramAllocation::findOrFail($id);

        $allocation->delete();

        return response()->json([
            'success' => true,
            'message' => 'Asignación eliminada correctamente'
        ]);
    }

    /**
     * Import allocations from the previous month
     */
    public function importFromPreviousMonth(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mes_actual' => 'required|integer|between:1,12',
            'mes_anterior' => 'required|integer|between:1,12'
        ]);

        $mesActual = $validated['mes_actual'];
        $mesAnterior = $validated['mes_anterior'];

        // Obtener asignaciones del mes anterior
        $previousMonthAllocations = ProgramAllocation::where('mes', $mesAnterior)
            ->get();

        if ($previousMonthAllocations->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No hay asignaciones en el mes anterior'
            ], 422);
        }

        $user = Auth::user();
        $createdCount = 0;
        $skippedCount = 0;

        foreach ($previousMonthAllocations as $allocation) {
            // Verificar que no exista ya una asignación para este programa en el mes actual
            $existing = ProgramAllocation::where('program_id', $allocation->program_id)
                ->where('mes', $mesActual)
                ->first();

            if ($existing) {
                $skippedCount++;
                continue;
            }

            // Crear nueva asignación con los mismos datos de programa y responsable
            ProgramAllocation::create([
                'program_id' => $allocation->program_id,
                'mes' => $mesActual,
                'responsable_cartera' => $allocation->responsable_cartera,
                'user_id' => $user->id
            ]);

            $createdCount++;
        }

        return response()->json([
            'success' => true,
            'message' => "Se importaron $createdCount asignaciones. Se saltaron $skippedCount que ya existían.",
            'created' => $createdCount,
            'skipped' => $skippedCount
        ]);
    }
}


