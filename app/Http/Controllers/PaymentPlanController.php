<?php

namespace App\Http\Controllers;

use App\Models\PaymentPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class PaymentPlanController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:payment_plan.view')->only(['index']);
        $this->middleware('permission:payment_plan.create')->only(['store']);
        $this->middleware('permission:payment_plan.edit')->only(['update']);
        $this->middleware('permission:payment_plan.delete')->only(['destroy']);
    }

    public function index(): View
    {
        $plans = PaymentPlan::orderBy('name')->get();
        return view('payment-plans.index', compact('plans'));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'              => 'required|string|max:255',
            'tipo'              => 'required|in:contado,credito',
            'numero_cuotas'     => 'required|integer|min:1|max:120',
            'importe_base_cuota'=> 'required|numeric|min:0',
            'matricula'         => 'nullable|numeric|min:0',
            'certificacion'     => 'nullable|numeric|min:0',
            'descripcion'       => 'nullable|string|max:500',
            'activo'            => 'boolean',
        ]);

        $plan = PaymentPlan::create($data);

        return response()->json(['success' => true, 'plan' => $plan]);
    }

    public function update(Request $request, PaymentPlan $paymentPlan): JsonResponse
    {
        $data = $request->validate([
            'name'              => 'required|string|max:255',
            'tipo'              => 'required|in:contado,credito',
            'numero_cuotas'     => 'required|integer|min:1|max:120',
            'importe_base_cuota'=> 'required|numeric|min:0',
            'matricula'         => 'nullable|numeric|min:0',
            'certificacion'     => 'nullable|numeric|min:0',
            'descripcion'       => 'nullable|string|max:500',
            'activo'            => 'boolean',
        ]);

        $paymentPlan->update($data);

        return response()->json(['success' => true, 'plan' => $paymentPlan->fresh()]);
    }

    public function destroy(PaymentPlan $paymentPlan): JsonResponse
    {
        $paymentPlan->delete();
        return response()->json(['success' => true]);
    }
}
