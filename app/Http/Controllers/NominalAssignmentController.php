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
        $this->middleware('permission:program_allocation.view')->only(['index']);
        $this->middleware('permission:program_allocation.create')->only(['generate']);
        $this->middleware('permission:program_allocation.edit')->only(['updateDetail', 'updateResponsable', 'toggleExcluded']);
    }

    public function index(Request $request): View
    {
        $mes     = (int) $request->get('mes', date('n'));
        $gestion = (int) $request->get('gestion', date('Y'));
        $mes     = max(1, min(12, $mes));

        $programs = Program::whereIn('status', ['INSCRIPCION', 'DESARROLLO', 'Inscripciones', 'Desarrollo'])
            ->orderBy('name')
            ->get();

        $programId  = $request->get('program_id');
        $program    = null;
        $assignment = null;
        $details    = collect();

        $accountants = User::active()->role('accountant')->orderBy('name')->get();

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
            'accountants'
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

    public function toggleExcluded(AssignmentDetail $detail): JsonResponse
    {
        $detail->excluido = !$detail->excluido;
        $detail->save();

        // Totales del programa excluyendo los registros marcados
        $activeDetails = $detail->monthlyAssignment->details()->where('excluido', false)->get();

        return response()->json([
            'success'                  => true,
            'excluido'                 => $detail->excluido,
            'program_total_asignacion' => round($activeDetails->sum('total_asignacion'), 2),
            'program_total_cobrado'    => round(
                $activeDetails->sum('cuotas_retrasadas_cobrado') +
                $activeDetails->sum('cuota_vigente_cobrado') +
                $activeDetails->sum('matricula_cobrado') +
                $activeDetails->sum('certificacion_cobrado'),
                2
            ),
        ]);
    }

    public function destroy(MonthlyAssignment $assignment): JsonResponse
    {
        $assignment->delete();
        return response()->json(['success' => true]);
    }
}
