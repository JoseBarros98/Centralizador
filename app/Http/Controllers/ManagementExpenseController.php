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
        $this->middleware('permission:program_allocation.view');
    }

    public function index(Request $request): View
    {
        $gestion = (int) $request->get('gestion', date('Y'));

        // Monthly totals: SUM per (item, mes)
        $rows = ManagementExpense::where('gestion', $gestion)
            ->selectRaw('item, mes, SUM(expense_amount) as total')
            ->groupBy('item', 'mes')
            ->get();

        $grid = [];
        foreach ($rows as $row) {
            $grid[$row->item][$row->mes] = ['amount' => (float) $row->total];
        }
        ksort($grid);
        $items = array_keys($grid);

        return view('management-expenses.index', compact('grid', 'items', 'gestion'));
    }

    /**
     * Return all daily records for one (item, mes, gestion) — used by the calendar modal.
     */
    public function monthDetail(Request $request): JsonResponse
    {
        $request->validate([
            'item'    => 'required|string|max:255',
            'mes'     => 'required|integer|between:1,12',
            'gestion' => 'required|integer|between:2000,2100',
        ]);

        $records = ManagementExpense::where('item', $request->item)
            ->where('mes', $request->mes)
            ->where('gestion', $request->gestion)
            ->with('user')
            ->orderBy('dia')
            ->get()
            ->map(fn($r) => [
                'id'     => $r->id,
                'dia'    => $r->dia,
                'amount' => (float) $r->expense_amount,
                'obs'    => $r->observation ?? '',
                'user'   => $r->user?->name ?? '',
            ]);

        return response()->json(['records' => $records]);
    }

    /**
     * Create or update a single daily record (item + mes + gestion + dia).
     * If amount == 0, delete the record instead (keep DB clean).
     */
    public function upsertCell(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'item'           => 'required|string|max:255',
            'mes'            => 'required|integer|between:1,12',
            'gestion'        => 'required|integer|between:2000,2100',
            'dia'            => 'required|integer|between:1,31',
            'expense_amount' => 'required|numeric|min:0',
            'observation'    => 'nullable|string|max:1000',
        ]);

        if ($validated['expense_amount'] == 0) {
            ManagementExpense::where('item', $validated['item'])
                ->where('mes', $validated['mes'])
                ->where('gestion', $validated['gestion'])
                ->where('dia', $validated['dia'])
                ->delete();

            $newTotal = ManagementExpense::where('item', $validated['item'])
                ->where('mes', $validated['mes'])
                ->where('gestion', $validated['gestion'])
                ->sum('expense_amount');

            return response()->json([
                'success'    => true,
                'deleted'    => true,
                'month_total' => (float) $newTotal,
            ]);
        }

        $expense = ManagementExpense::updateOrCreate(
            [
                'item'    => $validated['item'],
                'mes'     => $validated['mes'],
                'gestion' => $validated['gestion'],
                'dia'     => $validated['dia'],
            ],
            [
                'expense_amount' => $validated['expense_amount'],
                'observation'    => $validated['observation'] ?? null,
                'user_id'        => $request->user()->id,
            ]
        );

        $newTotal = ManagementExpense::where('item', $validated['item'])
            ->where('mes', $validated['mes'])
            ->where('gestion', $validated['gestion'])
            ->sum('expense_amount');

        return response()->json([
            'success'     => true,
            'id'          => $expense->id,
            'amount'      => (float) $expense->expense_amount,
            'month_total' => (float) $newTotal,
            'user'        => $request->user()->name,
        ]);
    }

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

    public function getItemsForYear(Request $request): JsonResponse
    {
        $gestion = (int) $request->get('gestion', date('Y'));

        $items = ManagementExpense::where('gestion', $gestion)
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

        ManagementExpense::where('item', $request->old_item)
            ->where('gestion', $request->gestion)
            ->update(['item' => $request->new_item]);

        return response()->json(['success' => true]);
    }
}
