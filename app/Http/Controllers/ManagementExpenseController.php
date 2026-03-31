<?php

namespace App\Http\Controllers;

use App\Models\ManagementExpense;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class ManagementExpenseController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin|accountant');
    }

    public function index(Request $request): View
    {
        $gestion = (int) $request->get('gestion', date('Y'));

        $expenses = ManagementExpense::where('gestion', $gestion)->with('user')->get();

        $grid = [];
        foreach ($expenses as $expense) {
            if (!array_key_exists($expense->item, $grid)) {
                $grid[$expense->item] = [];
            }
            $grid[$expense->item][$expense->mes] = [
                'id'     => $expense->id,
                'amount' => (float) $expense->expense_amount,
                'obs'    => $expense->observation ?? '',
                'user'   => $expense->user?->name ?? '',
            ];
        }
        ksort($grid);
        $items = array_keys($grid);

        return view('management-expenses.index', compact('grid', 'items', 'gestion'));
    }

    /**
     * Create or update a single cell (item + mes + gestion).
     */
    public function upsertCell(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'item'           => 'required|string|max:255',
            'mes'            => 'required|integer|between:1,12',
            'gestion'        => 'required|integer|between:2000,2100',
            'expense_amount' => 'required|numeric|min:0',
            'observation'    => 'nullable|string|max:1000',
        ]);

        $expense = ManagementExpense::updateOrCreate(
            [
                'item'    => $validated['item'],
                'mes'     => $validated['mes'],
                'gestion' => $validated['gestion'],
            ],
            [
                'expense_amount' => $validated['expense_amount'],
                'observation'    => $validated['observation'] ?? null,
                'user_id'        => $request->user()->id,
            ]
        );

        return response()->json([
            'success' => true,
            'id'      => $expense->id,
            'amount'  => (float) $expense->expense_amount,
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

        ManagementExpense::where('item', $request->item)
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

        $items = ManagementExpense::where('gestion', $gestion)
            ->distinct()
            ->orderBy('item')
            ->pluck('item');

        return response()->json(['items' => $items]);
    }
}
