<?php

namespace App\Http\Controllers;

use App\Models\AssignmentDetail;
use App\Models\MonthlyAssignment;
use App\Models\Program;
use App\Models\User;
use App\Services\NominalAssignmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class NominalAssignmentController extends Controller
{
    public function __construct(private NominalAssignmentService $service)
    {
        $this->middleware('permission:nominal_assignment.view')->only(['index']);
        $this->middleware('permission:nominal_assignment.create')->only(['generate', 'refresh']);
        $this->middleware('permission:nominal_assignment.edit')->only(['updateDetail', 'updateResponsable', 'toggleExcluded', 'toggleHidden']);
    }

    public function index(Request $request): View
    {
        $mes     = (int) $request->get('mes', date('n'));
        $gestion = (int) $request->get('gestion', date('Y'));
        $mes     = max(1, min(12, $mes));

        $accountants      = User::active()->role('accountant')->orderBy('name')->get();
        $responsableFilter = $request->get('responsable_filter');

        $programsQuery = Program::whereIn('status', ['INSCRIPCION', 'DESARROLLO', 'Inscripciones', 'Desarrollo']);

        // Si hay filtro por responsable, solo mostrar programas que tengan asignación con ese responsable en el período actual
        if ($responsableFilter) {
            $filteredProgramIds = MonthlyAssignment::where('mes', $mes)
                ->where('gestion', $gestion)
                ->where('responsable_id', $responsableFilter)
                ->pluck('program_id');

            $programsQuery->whereIn('id', $filteredProgramIds);
        }

        $programs = $programsQuery->orderBy('name')->get();

        $programId  = $request->get('program_id');
        $program    = null;
        $assignment = null;
        $details    = collect();

        if ($programId) {
            $program = Program::find($programId);
            if ($program) {
                $assignment = MonthlyAssignment::with(['details', 'generator', 'responsable'])
                    ->where('program_id', $program->id)
                    ->where('mes', $mes)
                    ->where('gestion', $gestion)
                    ->first();

                if ($assignment) {
                    $details = $assignment->details()->orderBy('nombre_completo')->get();
                }
            }
        }

        // Navegación entre meses
        $prevMes     = $mes === 1  ? 12 : $mes - 1;
        $prevGestion = $mes === 1  ? $gestion - 1 : $gestion;
        $nextMes     = $mes === 12 ? 1  : $mes + 1;
        $nextGestion = $mes === 12 ? $gestion + 1 : $gestion;

        $mesesNombres = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
        ];

        return view('nominal-assignments.index', compact(
            'programs', 'program', 'assignment', 'details',
            'mes', 'gestion', 'mesesNombres',
            'prevMes', 'prevGestion', 'nextMes', 'nextGestion',
            'accountants', 'responsableFilter'
        ));
    }

    public function generate(Request $request): JsonResponse
    {
        $request->validate([
            'program_id' => 'required|exists:programs,id',
            'mes'        => 'required|integer|between:1,12',
            'gestion'    => 'required|integer|between:2000,2100',
        ]);

        $program = Program::findOrFail($request->program_id);

        try {
            $assignment = $this->service->generate(
                $program,
                (int) $request->mes,
                (int) $request->gestion,
                auth()->id()
            );

            $details = $assignment->details()->orderBy('nombre_completo')->get();

            return response()->json([
                'success'    => true,
                'assignment' => $assignment,
                'details'    => $details,
                'count'      => $details->count(),
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function updateDetail(Request $request, AssignmentDetail $detail): JsonResponse
    {
        $data = $request->validate([
            'cuotas_retrasadas_cobrado' => 'nullable|numeric|min:0',
            'cuota_vigente_cobrado'     => 'nullable|numeric|min:0',
            'adelanto_numero_cuota'     => 'nullable|integer|min:1',
            'adelanto_importe'          => 'nullable|numeric|min:0',
            'matricula_cobrado'         => 'nullable|numeric|min:0',
            'certificacion_cobrado'     => 'nullable|numeric|min:0',
            'observaciones'             => 'nullable|string|max:1000',
        ]);

        // Los campos cobrado son acumulativos: se suma el nuevo pago al total anterior
        $detail->cuotas_retrasadas_cobrado += ($data['cuotas_retrasadas_cobrado'] ?? 0);
        $detail->cuota_vigente_cobrado     += ($data['cuota_vigente_cobrado'] ?? 0);
        $detail->matricula_cobrado         += ($data['matricula_cobrado'] ?? 0);
        $detail->certificacion_cobrado     += ($data['certificacion_cobrado'] ?? 0);

        // Estos campos son de reemplazo directo
        if (array_key_exists('adelanto_numero_cuota', $data)) {
            $detail->adelanto_numero_cuota = $data['adelanto_numero_cuota'];
        }
        if (array_key_exists('adelanto_importe', $data)) {
            $detail->adelanto_importe = $data['adelanto_importe'];
        }
        if (array_key_exists('observaciones', $data)) {
            $detail->observaciones = $data['observaciones'];
        }

        $detail->save();
        $detail->recalcularTotal();
        $detail->refresh();

        return response()->json([
            'success'                    => true,
            'cuotas_retrasadas_cobrado'  => (float) $detail->cuotas_retrasadas_cobrado,
            'cuotas_retrasadas_saldo'    => (float) $detail->cuotas_retrasadas_saldo,
            'cuota_vigente_cobrado'      => (float) $detail->cuota_vigente_cobrado,
            'cuota_vigente_saldo'        => (float) $detail->cuota_vigente_saldo,
            'matricula_cobrado'          => (float) $detail->matricula_cobrado,
            'matricula_saldo'            => (float) $detail->matricula_saldo,
            'certificacion_cobrado'      => (float) $detail->certificacion_cobrado,
            'certificacion_saldo'        => (float) $detail->certificacion_saldo,
            'total_asignacion'           => (float) $detail->total_asignacion,
        ]);
    }

    public function refresh(MonthlyAssignment $assignment): JsonResponse
    {
        $result = $this->service->refresh($assignment);
        $added  = $result['added'];

        $message = $added > 0
            ? "Asignación actualizada: {$added} participante(s) nuevo(s) añadido(s) y saldos recalculados."
            : 'Asignación actualizada: saldos recalculados con los pagos de meses anteriores.';

        return response()->json([
            'success' => true,
            'added'   => $added,
            'message' => $message,
        ]);
    }

    public function updateResponsable(Request $request, MonthlyAssignment $assignment): JsonResponse
    {
        $request->validate([
            'responsable_id' => 'nullable|exists:users,id',
        ]);

        $assignment->update(['responsable_id' => $request->responsable_id ?: null]);
        $assignment->load('responsable');

        return response()->json([
            'success'          => true,
            'responsable_name' => $assignment->responsable?->name ?? null,
        ]);
    }

    public function toggleHidden(AssignmentDetail $detail): JsonResponse
    {
        $detail->oculto = !$detail->oculto;
        $detail->save();

        $hiddenCount = $detail->monthlyAssignment->details()->where('oculto', true)->count();

        return response()->json([
            'success'      => true,
            'oculto'       => $detail->oculto,
            'hidden_count' => $hiddenCount,
        ]);
    }

    public function toggleExcluded(AssignmentDetail $detail): JsonResponse
    {
        $detail->excluido = !$detail->excluido;
        $detail->save();

        // Totales del programa excluyendo los registros marcados
        $active = $detail->monthlyAssignment->details()->where('excluido', false)->get();

        return response()->json([
            'success'  => true,
            'excluido' => $detail->excluido,
            'totals'   => [
                'asignacion'      => round($active->sum('total_asignacion'), 2),
                'ret_importe'     => round($active->sum('cuotas_retrasadas_importe'), 2),
                'ret_cobrado'     => round($active->sum('cuotas_retrasadas_cobrado'), 2),
                'ret_saldo'       => round($active->sum(fn($d) => max(0, $d->cuotas_retrasadas_importe - $d->cuotas_retrasadas_cobrado)), 2),
                'vig_importe'     => round($active->sum('cuota_vigente_importe'), 2),
                'vig_cobrado'     => round($active->sum('cuota_vigente_cobrado'), 2),
                'vig_saldo'       => round($active->sum(fn($d) => max(0, $d->cuota_vigente_importe - $d->cuota_vigente_cobrado)), 2),
                'adelanto'        => round($active->sum('adelanto_importe'), 2),
                'mat_saldo'       => round($active->sum(fn($d) => max(0, $d->matricula_importe - $d->matricula_cobrado)), 2),
                'cer_saldo'       => round($active->sum(fn($d) => max(0, $d->certificacion_importe - $d->certificacion_cobrado)), 2),
            ],
        ]);
    }
}
