<?php

namespace App\Http\Controllers;

use App\Models\ManagementIncome;
use App\Models\ManagementIncomeEntity;
use App\Models\ManagementIncomeEntityAmount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class ManagementIncomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:management_income.view')->only(['index', 'getItemsForYear', 'monthDetail']);
        $this->middleware('permission:management_income.edit')->only(['upsertCell', 'destroyItem', 'renameItem']);
    }

    public function index(Request $request): View
    {
        $gestion = (int) $request->get('gestion', date('Y'));

        $entities = ManagementIncomeEntity::where('gestion', $gestion)
            ->orderBy('display_order')
            ->get(['id', 'name']);

        // Build grids from entity daily amounts (source of truth for totals)
        $entityRows = ManagementIncomeEntityAmount::where('gestion', $gestion)->get();

        // Per-entity monthly sums: {entityId: {item: {mes: total}}}
        $entityAmountsGrid = [];
        // Cross-entity monthly sums for the main grid: {item: {mes: total}}
        $entityTotals = [];

        foreach ($entityRows as $ea) {
            $entityAmountsGrid[$ea->entity_id][$ea->item][$ea->mes] =
                ($entityAmountsGrid[$ea->entity_id][$ea->item][$ea->mes] ?? 0) + (float) $ea->amount;

            $entityTotals[$ea->item][$ea->mes] =
                ($entityTotals[$ea->item][$ea->mes] ?? 0) + (float) $ea->amount;
        }

        // Item list: union of management_incomes items AND entity-amount items
        $itemsFromIncomes   = ManagementIncome::where('gestion', $gestion)
            ->distinct()->orderBy('item')->pluck('item')->toArray();
        $itemsFromEntities  = ManagementIncomeEntityAmount::where('gestion', $gestion)
            ->distinct()->orderBy('item')->pluck('item')->toArray();
        $items = array_values(array_unique(array_merge($itemsFromIncomes, $itemsFromEntities)));
        sort($items);

        // Main grid: entity totals, with all items present
        $grid = [];
        foreach ($items as $item) {
            $grid[$item] = [];
            for ($m = 1; $m <= 12; $m++) {
                $total = $entityTotals[$item][$m] ?? 0;
                if ($total > 0) {
                    $grid[$item][$m] = ['amount' => $total];
                }
            }
        }

        return view('management-incomes.index', compact('grid', 'items', 'gestion', 'entities', 'entityAmountsGrid'));
    }

    /**
     * Return distinct item names for a given gestion (used for import).
     */
    public function getItemsForYear(Request $request): JsonResponse
    {
        $gestion = (int) $request->get('gestion', date('Y'));

        $items = ManagementIncome::where('gestion', $gestion)
            ->distinct()
            ->orderBy('item')
            ->pluck('item');

        return response()->json(['items' => $items]);
    }

    /**
     * Delete all records for a given item in a gestion.
     */
    public function destroyItem(Request $request): JsonResponse
    {
        $request->validate([
            'item'    => 'required|string|max:255',
            'gestion' => 'required|integer|between:2000,2100',
        ]);

        ManagementIncome::where('item', $request->item)
            ->where('gestion', $request->gestion)
            ->delete();

        ManagementIncomeEntityAmount::where('item', $request->item)
            ->where('gestion', $request->gestion)
            ->delete();

        return response()->json(['success' => true]);
    }

    public function renameItem(Request $request): JsonResponse
    {
        $request->validate([
            'old_item' => 'required|string|max:255',
            'new_item' => 'required|string|max:255|different:old_item',
            'gestion'  => 'required|integer|between:2000,2100',
        ]);

        ManagementIncome::where('item', $request->old_item)
            ->where('gestion', $request->gestion)
            ->update(['item' => $request->new_item]);

        ManagementIncomeEntityAmount::where('item', $request->old_item)
            ->where('gestion', $request->gestion)
            ->update(['item' => $request->new_item]);

        return response()->json(['success' => true]);
    }
}
