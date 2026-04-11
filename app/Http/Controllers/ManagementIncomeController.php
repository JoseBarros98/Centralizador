<?php

namespace App\Http\Controllers;

use App\Models\ManagementIncome;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class ManagementIncomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin|accountant');
    }

    public function index(Request $request): View
    {
        $gestion = (int) $request->get('gestion', date('Y'));

        $incomes = ManagementIncome::where('gestion', $gestion)->with('user')->get();

        $grid = [];
        foreach ($incomes as $income) {
            if (!array_key_exists($income->item, $grid)) {
                $grid[$income->item] = [];
            }
            $grid[$income->item][$income->mes] = [
                'id'     => $income->id,
                'amount' => (float) $income->income_amount,
                'obs'    => $income->observation ?? '',
                'user'   => $income->user?->name ?? '',
            ];
        }
        ksort($grid);
        $items = array_keys($grid);

        return view('management-incomes.index', compact('grid', 'items', 'gestion'));
    }

    /**
     * Create or update a single cell (item + mes + gestion).
     */
    public function upsertCell(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'item'          => 'required|string|max:255',
            'mes'           => 'required|integer|between:1,12',
            'gestion'       => 'required|integer|between:2000,2100',
            'income_amount' => 'required|numeric|min:0',
            'observation'   => 'nullable|string|max:1000',
        ]);

        $income = ManagementIncome::updateOrCreate(
            [
                'item'    => $validated['item'],
                'mes'     => $validated['mes'],
                'gestion' => $validated['gestion'],
            ],
            [
                'income_amount' => $validated['income_amount'],
                'observation'   => $validated['observation'] ?? null,
                'user_id'       => $request->user()->id,
            ]
        );

        return response()->json([
            'success' => true,
            'id'      => $income->id,
            'amount'  => (float) $income->income_amount,
            'user'    => $request->user()->name,
        ]);
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

        return response()->json(['success' => true]);
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

        return response()->json(['success' => true]);
    }
}
