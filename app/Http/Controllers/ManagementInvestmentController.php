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
        $this->middleware('role:admin|accountant');
    }

    public function index(Request $request): View
    {
        $gestion = (int) $request->get('gestion', date('Y'));

        $investments = ManagementInvestment::where('gestion', $gestion)->with('user')->get();

        $grid = [];
        foreach ($investments as $investment) {
            if (!array_key_exists($investment->item, $grid)) {
                $grid[$investment->item] = [];
            }
            $grid[$investment->item][$investment->mes] = [
                'id'     => $investment->id,
                'amount' => (float) $investment->investment_amount,
                'obs'    => $investment->observation ?? '',
                'user'   => $investment->user?->name ?? '',
            ];
        }
        ksort($grid);
        $items = array_keys($grid);

        return view('management-investments.index', compact('grid', 'items', 'gestion'));
    }

    public function upsertCell(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'item'              => 'required|string|max:255',
            'mes'               => 'required|integer|between:1,12',
            'gestion'           => 'required|integer|between:2000,2100',
            'investment_amount' => 'required|numeric|min:0',
            'observation'       => 'nullable|string|max:1000',
        ]);

        $investment = ManagementInvestment::updateOrCreate(
            [
                'item'    => $validated['item'],
                'mes'     => $validated['mes'],
                'gestion' => $validated['gestion'],
            ],
            [
                'investment_amount' => $validated['investment_amount'],
                'observation'       => $validated['observation'] ?? null,
                'user_id'           => $request->user()->id,
            ]
        );

        return response()->json([
            'success' => true,
            'id'      => $investment->id,
            'amount'  => (float) $investment->investment_amount,
            'user'    => $request->user()->name,
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
