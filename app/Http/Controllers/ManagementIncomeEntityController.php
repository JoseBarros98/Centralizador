<?php

namespace App\Http\Controllers;

use App\Models\ManagementIncomeEntity;
use App\Models\ManagementIncomeEntityAmount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ManagementIncomeEntityController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:management_income.view')->only(['monthDetail']);
        $this->middleware('permission:management_income.edit')->only(['store', 'rename', 'destroy', 'upsertAmount']);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'gestion' => 'required|integer|between:2000,2100',
            'name'    => 'required|string|max:100',
        ]);

        $maxOrder = ManagementIncomeEntity::where('gestion', $validated['gestion'])->max('display_order') ?? -1;

        $entity = ManagementIncomeEntity::create([
            'gestion'       => $validated['gestion'],
            'name'          => $validated['name'],
            'display_order' => $maxOrder + 1,
        ]);

        return response()->json([
            'success' => true,
            'entity'  => ['id' => $entity->id, 'name' => $entity->name],
        ]);
    }

    public function rename(ManagementIncomeEntity $entity, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $duplicate = ManagementIncomeEntity::where('gestion', $entity->gestion)
            ->where('name', $validated['name'])
            ->where('id', '!=', $entity->id)
            ->exists();

        if ($duplicate) {
            return response()->json(['success' => false, 'message' => 'Ya existe una entidad con ese nombre.'], 422);
        }

        $entity->update(['name' => $validated['name']]);

        return response()->json(['success' => true, 'name' => $entity->name]);
    }

    public function destroy(ManagementIncomeEntity $entity): JsonResponse
    {
        $entity->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Return all daily records for one entity / item / mes / gestion — for the calendar modal.
     */
    public function monthDetail(ManagementIncomeEntity $entity, Request $request): JsonResponse
    {
        $request->validate([
            'item'    => 'required|string|max:255',
            'mes'     => 'required|integer|between:1,12',
            'gestion' => 'required|integer|between:2000,2100',
        ]);

        $records = ManagementIncomeEntityAmount::where('entity_id', $entity->id)
            ->where('item', $request->item)
            ->where('mes', $request->mes)
            ->where('gestion', $request->gestion)
            ->orderBy('dia')
            ->get()
            ->map(fn($r) => [
                'id'     => $r->id,
                'dia'    => $r->dia,
                'amount' => (float) $r->amount,
                'obs'    => $r->observation ?? '',
            ]);

        return response()->json(['records' => $records]);
    }

    /**
     * Create or update a single daily entity record.
     * Deletes the record when amount == 0.
     */
    public function upsertAmount(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'entity_id'   => 'required|integer|exists:management_income_entities,id',
            'item'        => 'required|string|max:255',
            'mes'         => 'required|integer|between:1,12',
            'gestion'     => 'required|integer|between:2000,2100',
            'dia'         => 'required|integer|between:1,31',
            'amount'      => 'required|numeric|min:0',
            'observation' => 'nullable|string|max:1000',
        ]);

        if ($validated['amount'] == 0) {
            ManagementIncomeEntityAmount::where('entity_id', $validated['entity_id'])
                ->where('item', $validated['item'])
                ->where('mes', $validated['mes'])
                ->where('gestion', $validated['gestion'])
                ->where('dia', $validated['dia'])
                ->delete();

            $monthTotal = $this->monthTotal($validated);

            return response()->json([
                'success'     => true,
                'deleted'     => true,
                'month_total' => $monthTotal,
            ]);
        }

        $ea = ManagementIncomeEntityAmount::updateOrCreate(
            [
                'entity_id' => $validated['entity_id'],
                'item'      => $validated['item'],
                'mes'       => $validated['mes'],
                'gestion'   => $validated['gestion'],
                'dia'       => $validated['dia'],
            ],
            [
                'amount'      => $validated['amount'],
                'observation' => $validated['observation'] ?? null,
            ]
        );

        $monthTotal = $this->monthTotal($validated);

        return response()->json([
            'success'     => true,
            'amount'      => (float) $ea->amount,
            'month_total' => $monthTotal,
        ]);
    }

    private function monthTotal(array $v): float
    {
        return (float) ManagementIncomeEntityAmount::where('entity_id', $v['entity_id'])
            ->where('item', $v['item'])
            ->where('mes', $v['mes'])
            ->where('gestion', $v['gestion'])
            ->sum('amount');
    }
}
