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
        $this->middleware('permission:program_allocation.view')->only(['index']);
        $this->middleware('permission:program_allocation.create')->only(['store', 'generateFromPlan']);
        $this->middleware('permission:program_allocation.edit')->only(['update', 'upsertDiscount']);
        $this->middleware('permission:program_allocation.delete')->only(['destroy', 'destroyAll']);
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

        return view('participant-quotas.index', compact('program', 'inscriptions', 'quotas', 'discounts', 'paymentPlans'));
    }

    /**
     * Genera cuotas automáticamente a partir de un plan de pagos para un participante.
     */
    public function generateFromPlan(Request $request): JsonResponse
    {
        $data = $request->validate([
            'inscription_id'  => 'required|exists:inscriptions,id',
            'program_id'      => 'required|exists:programs,id',
            'payment_plan_id' => 'required|exists:payment_plans,id',
            'fecha_inicio'    => 'required|date',
        ]);

        $plan        = PaymentPlan::findOrFail($data['payment_plan_id']);
        $inscription = Inscription::findOrFail($data['inscription_id']);
        $discount    = ParticipantDiscount::where('inscription_id', $data['inscription_id'])
            ->where('program_id', $data['program_id'])
            ->where('activo', true)
            ->first();

        // Eliminar cuotas previas del participante en este programa
        ParticipantQuota::where('inscription_id', $data['inscription_id'])
            ->where('program_id', $data['program_id'])
            ->delete();

        $created = [];
        $startDate = Carbon::parse($data['fecha_inicio'])->startOfMonth();

        for ($i = 1; $i <= $plan->numero_cuotas; $i++) {
            $importeBase    = (float) $plan->importe_base_cuota;
            $descuento      = $discount ? $discount->calcularDescuento($importeBase) : 0;
            $importeFinal   = max(0, $importeBase - $descuento);

            $quota = ParticipantQuota::create([
                'inscription_id'    => $data['inscription_id'],
                'program_id'        => $data['program_id'],
                'payment_plan_id'   => $plan->id,
                'numero_cuota'      => $i,
                'importe_base'      => $importeBase,
                'descuento_aplicado'=> $descuento,
                'importe_final'     => $importeFinal,
                'fecha_vencimiento' => $startDate->copy()->addMonths($i - 1)->format('Y-m-d'),
            ]);

            $created[] = $quota;
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
            'importe_base'      => 'required|numeric|min:0',
            'descuento_aplicado'=> 'nullable|numeric|min:0',
            'importe_final'     => 'required|numeric|min:0',
            'fecha_vencimiento' => 'required|date',
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
