<?php

namespace App\Http\Controllers;

use App\Models\ManagementInvestment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class ManagementInvestmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:management_investment.view')->only(['index', 'monthDetail', 'getItemsForYear']);
        $this->middleware('permission:management_investment.edit')->only(['upsertCell', 'destroyItem', 'renameItem']);
    }

    public function index(Request $request): View
    {
        $gestion = (int) $request->get('gestion', date('Y'));

        $rows = ManagementInvestment::where('gestion', $gestion)
            ->selectRaw('item, mes, SUM(investment_amount) as total')
            ->groupBy('item', 'mes')
            ->get();

        $grid = [];
        foreach ($rows as $row) {
            $grid[$row->item][$row->mes] = ['amount' => (float) $row->total];
        }
        ksort($grid);
        $items = array_keys($grid);

        return view('management-investments.index', compact('grid', 'items', 'gestion'));
    }

    public function monthDetail(Request $request): JsonResponse
    {
        $request->validate([
            'item'    => 'required|string|max:255',
            'mes'     => 'required|integer|between:1,12',
            'gestion' => 'required|integer|between:2000,2100',
        ]);

        $records = ManagementInvestment::where('item', $request->item)
            ->where('mes', $request->mes)
            ->where('gestion', $request->gestion)
            ->with('user')
            ->orderBy('dia')
            ->get()
            ->map(fn($r) => [
                'id'     => $r->id,
                'dia'    => $r->dia,
                'amount' => (float) $r->investment_amount,
                'obs'    => $r->observation ?? '',
                'user'   => $r->user?->name ?? '',
            ]);

        return response()->json(['records' => $records]);
    }

    public function upsertCell(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'item'              => 'required|string|max:255',
            'mes'               => 'required|integer|between:1,12',
            'gestion'           => 'required|integer|between:2000,2100',
            'dia'               => 'required|integer|between:1,31',
            'investment_amount' => 'required|numeric|min:0',
            'observation'       => 'nullable|string|max:1000',
        ]);

        if ($validated['investment_amount'] == 0) {
            ManagementInvestment::where('item', $validated['item'])
                ->where('mes', $validated['mes'])
                ->where('gestion', $validated['gestion'])
                ->where('dia', $validated['dia'])
                ->delete();

            $newTotal = ManagementInvestment::where('item', $validated['item'])
                ->where('mes', $validated['mes'])
                ->where('gestion', $validated['gestion'])
                ->sum('investment_amount');

            return response()->json([
                'success'     => true,
                'deleted'     => true,
                'month_total' => (float) $newTotal,
            ]);
        }

        $investment = ManagementInvestment::updateOrCreate(
            [
                'item'    => $validated['item'],
                'mes'     => $validated['mes'],
                'gestion' => $validated['gestion'],
                'dia'     => $validated['dia'],
            ],
            [
                'investment_amount' => $validated['investment_amount'],
                'observation'       => $validated['observation'] ?? null,
                'user_id'           => $request->user()->id,
            ]
        );

        $newTotal = ManagementInvestment::where('item', $validated['item'])
            ->where('mes', $validated['mes'])
            ->where('gestion', $validated['gestion'])
            ->sum('investment_amount');

        return response()->json([
            'success'     => true,
            'id'          => $investment->id,
            'amount'      => (float) $investment->investment_amount,
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

        ManagementInvestment::where('item', $request->item)
            ->where('gestion', $request->gestion)
            ->delete();

        return response()->json(['success' => true]);
    }

    public function getItemsForYear(Request $request): JsonResponse
    {
        $gestion = (int) $request->get('gestion', date('Y'));

        $items = ManagementInvestment::where('gestion', $gestion)
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

        ManagementInvestment::where('item', $request->old_item)
            ->where('gestion', $request->gestion)
            ->update(['item' => $request->new_item]);

        return response()->json(['success' => true]);
    }
}
