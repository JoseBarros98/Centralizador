<?php

namespace App\Http\Controllers;

use App\Models\Inscription;
use App\Models\ParticipantDiscount;
use App\Models\ParticipantQuota;
use App\Models\PaymentPlan;
use App\Models\Program;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ParticipantQuotaController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:participant_quota.view')->only(['index']);
        $this->middleware('permission:participant_quota.create')->only(['store', 'generateFromPlan', 'previewFromPlan', 'generateBulk']);
        $this->middleware('permission:participant_quota.edit')->only(['update', 'upsertDiscount', 'togglePagada']);
        $this->middleware('permission:participant_quota.delete')->only(['destroy', 'destroyAll']);
    }

    public function index(Program $program): View
    {
        $pivotIds  = DB::table('inscription_program')
            ->where('program_id', $program->id)
            ->pluck('inscription_id');

        $directIds = Inscription::where('program_id', $program->id)->pluck('id');

        $allIds = $pivotIds->merge($directIds)->unique()->values();

        $inscriptions = Inscription::whereIn('id', $allIds)
            ->orderBy('full_name')
            ->get();

        $quotas = ParticipantQuota::where('program_id', $program->id)
            ->with('paymentPlan')
            ->orderBy('inscription_id')
            ->orderBy('numero_cuota')
            ->get()
            ->groupBy('inscription_id');

        $discounts = ParticipantDiscount::where('program_id', $program->id)
            ->where('activo', true)
            ->get()
            ->keyBy('inscription_id');

        $paymentPlans = PaymentPlan::where('activo', true)->orderBy('name')->get();

        $participantsJson = $inscriptions->map(function ($i) use ($quotas) {
            return [
                'id'        => $i->id,
                'name'      => $i->getFullName(),
                'has_quotas'=> $quotas->get($i->id, collect())->count() > 0,
            ];
        })->values();

        return view('participant-quotas.index', compact('program', 'inscriptions', 'quotas', 'discounts', 'paymentPlans', 'participantsJson'));
    }

    /**
     * Previsualiza las cuotas que se generarían desde un plan, sin persistir nada.
     */
    public function previewFromPlan(Request $request): JsonResponse
    {
        $data = $request->validate([
            'inscription_id'  => 'required|exists:inscriptions,id',
            'program_id'      => 'required|exists:programs,id',
            'payment_plan_id' => 'required|exists:payment_plans,id',
            'fecha_inicio'    => 'required|date',
        ]);

        $plan     = PaymentPlan::findOrFail($data['payment_plan_id']);
        $discount = ParticipantDiscount::where('inscription_id', $data['inscription_id'])
            ->where('program_id', $data['program_id'])
            ->where('activo', true)
            ->first();

        $startDate = Carbon::parse($data['fecha_inicio']);
        $preview   = [];

        for ($i = 1; $i <= $plan->numero_cuotas; $i++) {
            $importeBase  = (float) $plan->importe_base_cuota;
            $descuento    = $discount ? $discount->calcularDescuento($importeBase) : 0;
            $importeFinal = max(0, $importeBase - $descuento);

            $preview[] = [
                'numero_cuota'      => $i,
                'importe_base'      => round($importeBase, 2),
                'descuento_aplicado'=> round($descuento, 2),
                'importe_final'     => round($importeFinal, 2),
                'fecha_vencimiento' => $startDate->copy()->addMonths($i - 1)->format('Y-m-d'),
                'pagada'            => false,
            ];
        }

        return response()->json(['success' => true, 'quotas' => $preview]);
    }

    /**
     * Genera cuotas a partir del array confirmado por el usuario (con fechas y flags editados).
     */
    public function generateFromPlan(Request $request): JsonResponse
    {
        $data = $request->validate([
            'inscription_id'  => 'required|exists:inscriptions,id',
            'program_id'      => 'required|exists:programs,id',
            'payment_plan_id' => 'required|exists:payment_plans,id',
            'quotas'          => 'required|array|min:1',
            'quotas.*.numero_cuota'      => 'required|integer|min:1',
            'quotas.*.importe_base'      => 'required|numeric|min:0',
            'quotas.*.descuento_aplicado'=> 'required|numeric|min:0',
            'quotas.*.importe_final'     => 'required|numeric|min:0',
            'quotas.*.fecha_vencimiento' => 'required|date',
            'quotas.*.pagada'            => 'required|boolean',
        ]);

        $plan = PaymentPlan::findOrFail($data['payment_plan_id']);

        ParticipantQuota::where('inscription_id', $data['inscription_id'])
            ->where('program_id', $data['program_id'])
            ->delete();

        $created = [];
        foreach ($data['quotas'] as $q) {
            $created[] = ParticipantQuota::create([
                'inscription_id'    => $data['inscription_id'],
                'program_id'        => $data['program_id'],
                'payment_plan_id'   => $plan->id,
                'numero_cuota'      => $q['numero_cuota'],
                'importe_base'      => $q['importe_base'],
                'descuento_aplicado'=> $q['descuento_aplicado'],
                'importe_final'     => $q['importe_final'],
                'fecha_vencimiento' => $q['fecha_vencimiento'],
                'pagada'            => $q['pagada'],
            ]);
        }

        return response()->json(['success' => true, 'quotas' => $created, 'count' => count($created)]);
    }

    /**
     * Crea o actualiza una cuota individual.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'inscription_id'    => 'required|exists:inscriptions,id',
            'program_id'        => 'required|exists:programs,id',
            'payment_plan_id'   => 'nullable|exists:payment_plans,id',
            'numero_cuota'      => 'required|integer|min:1',
            'importe_base'      => 'required|numeric|min:0',
            'descuento_aplicado'=> 'nullable|numeric|min:0',
            'importe_final'     => 'required|numeric|min:0',
            'fecha_vencimiento' => 'required|date',
        ]);

        $data['descuento_aplicado'] = $data['descuento_aplicado'] ?? 0;

        $quota = ParticipantQuota::updateOrCreate(
            [
                'inscription_id' => $data['inscription_id'],
                'program_id'     => $data['program_id'],
                'numero_cuota'   => $data['numero_cuota'],
            ],
            $data
        );

        return response()->json(['success' => true, 'quota' => $quota]);
    }

    public function update(Request $request, ParticipantQuota $participantQuota): JsonResponse
    {
        $data = $request->validate([
            'importe_base'      => 'sometimes|numeric|min:0',
            'descuento_aplicado'=> 'sometimes|nullable|numeric|min:0',
            'importe_final'     => 'sometimes|numeric|min:0',
            'fecha_vencimiento' => 'sometimes|date',
            'pagada'            => 'sometimes|boolean',
        ]);

        $participantQuota->update($data);

        return response()->json(['success' => true, 'quota' => $participantQuota->fresh()]);
    }

    public function destroy(ParticipantQuota $participantQuota): JsonResponse
    {
        $participantQuota->delete();
        return response()->json(['success' => true]);
    }

    public function destroyAll(Request $request): JsonResponse
    {
        $request->validate([
            'inscription_id' => 'required|exists:inscriptions,id',
            'program_id'     => 'required|exists:programs,id',
        ]);

        ParticipantQuota::where('inscription_id', $request->inscription_id)
            ->where('program_id', $request->program_id)
            ->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Genera cuotas en masa para múltiples participantes con las mismas fechas y flags.
     */
    public function generateBulk(Request $request): JsonResponse
    {
        $data = $request->validate([
            'program_id'      => 'required|exists:programs,id',
            'payment_plan_id' => 'required|exists:payment_plans,id',
            'inscription_ids' => 'required|array|min:1',
            'inscription_ids.*' => 'integer|exists:inscriptions,id',
            'quotas'          => 'required|array|min:1',
            'quotas.*.numero_cuota'      => 'required|integer|min:1',
            'quotas.*.importe_base'      => 'required|numeric|min:0',
            'quotas.*.descuento_aplicado'=> 'required|numeric|min:0',
            'quotas.*.importe_final'     => 'required|numeric|min:0',
            'quotas.*.fecha_vencimiento' => 'required|date',
            'quotas.*.pagada'            => 'required|boolean',
        ]);

        $plan = PaymentPlan::findOrFail($data['payment_plan_id']);
        $results = ['created' => 0, 'skipped' => 0];

        foreach ($data['inscription_ids'] as $inscriptionId) {
            $discount = ParticipantDiscount::where('inscription_id', $inscriptionId)
                ->where('program_id', $data['program_id'])
                ->where('activo', true)
                ->first();

            ParticipantQuota::where('inscription_id', $inscriptionId)
                ->where('program_id', $data['program_id'])
                ->delete();

            foreach ($data['quotas'] as $q) {
                $importeBase  = (float) $q['importe_base'];
                $descuento    = $discount ? $discount->calcularDescuento($importeBase) : (float) $q['descuento_aplicado'];
                $importeFinal = max(0, $importeBase - $descuento);

                ParticipantQuota::create([
                    'inscription_id'    => $inscriptionId,
                    'program_id'        => $data['program_id'],
                    'payment_plan_id'   => $plan->id,
                    'numero_cuota'      => $q['numero_cuota'],
                    'importe_base'      => $importeBase,
                    'descuento_aplicado'=> round($descuento, 2),
                    'importe_final'     => round($importeFinal, 2),
                    'fecha_vencimiento' => $q['fecha_vencimiento'],
                    'pagada'            => $q['pagada'],
                ]);
            }

            $results['created']++;
        }

        return response()->json(['success' => true, 'results' => $results]);
    }

    public function togglePagada(ParticipantQuota $participantQuota): JsonResponse
    {
        $participantQuota->update(['pagada' => !$participantQuota->pagada]);
        return response()->json(['success' => true, 'pagada' => $participantQuota->fresh()->pagada]);
    }

    /**
     * Crea o actualiza el descuento de un participante en el programa.
     */
    public function upsertDiscount(Request $request): JsonResponse
    {
        $data = $request->validate([
            'inscription_id' => 'required|exists:inscriptions,id',
            'program_id'     => 'required|exists:programs,id',
            'tipo'           => 'required|in:porcentaje,monto_fijo',
            'valor'          => 'required|numeric|min:0',
            'descripcion'    => 'nullable|string|max:255',
        ]);

        $discount = ParticipantDiscount::updateOrCreate(
            ['inscription_id' => $data['inscription_id'], 'program_id' => $data['program_id']],
            array_merge($data, ['activo' => true, 'created_by' => auth()->id()])
        );

        return response()->json(['success' => true, 'discount' => $discount]);
    }

    public function destroyDiscount(Request $request): JsonResponse
    {
        $request->validate([
            'inscription_id' => 'required|exists:inscriptions,id',
            'program_id'     => 'required|exists:programs,id',
        ]);

        ParticipantDiscount::where('inscription_id', $request->inscription_id)
            ->where('program_id', $request->program_id)
            ->delete();

        return response()->json(['success' => true]);
    }
}
