<?php

namespace App\Http\Controllers;

use App\Models\ManagementExpense;
use App\Models\ManagementExpenseEntity;
use App\Models\ManagementExpenseEntityAmount;
use App\Models\ManagementIncome;
use App\Models\ManagementIncomeEntity;
use App\Models\ManagementIncomeEntityAmount;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IncomeExpenseDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $year          = (int) $request->get('year', date('Y'));
        $incomeEntity  = $request->get('income_entity') ?: null;
        $expenseEntity = $request->get('expense_entity') ?: null;

        // Available entities for the selected year
        $incomeEntities  = ManagementIncomeEntity::where('gestion', $year)->orderBy('display_order')->get(['id', 'name']);
        $expenseEntities = ManagementExpenseEntity::where('gestion', $year)->orderBy('display_order')->get(['id', 'name']);

        // Income: sum from entity amounts (source of truth)
        $incomeByMonth = ManagementIncomeEntityAmount::where('gestion', $year)
            ->when($incomeEntity, fn($q) => $q->where('entity_id', $incomeEntity))
            ->selectRaw('mes, SUM(amount) as total')
            ->groupBy('mes')
            ->pluck('total', 'mes');

        // Fallback to management_incomes if no entity amounts exist yet
        if ($incomeByMonth->isEmpty() && !$incomeEntity) {
            $incomeByMonth = ManagementIncome::where('gestion', $year)
                ->selectRaw('mes, SUM(income_amount) as total')
                ->groupBy('mes')
                ->pluck('total', 'mes');
        }

        // Expense: sum from entity amounts (source of truth)
        $expenseByMonth = ManagementExpenseEntityAmount::where('gestion', $year)
            ->when($expenseEntity, fn($q) => $q->where('entity_id', $expenseEntity))
            ->selectRaw('mes, SUM(amount) as total')
            ->groupBy('mes')
            ->pluck('total', 'mes');

        // Fallback to management_expenses if no entity amounts exist yet
        if ($expenseByMonth->isEmpty() && !$expenseEntity) {
            $expenseByMonth = ManagementExpense::where('gestion', $year)
                ->selectRaw('mes, SUM(expense_amount) as total')
                ->groupBy('mes')
                ->pluck('total', 'mes');
        }

        $months = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        $incomeSeries = [];
        $expenseSeries = [];
        $balanceSeries = [];
        $incomeAccumulated = [];
        $expenseAccumulated = [];
        $incomeProjectionSeries = [];
        $expenseProjectionSeries = [];

        $runningIncome = 0.0;
        $runningExpense = 0.0;
        $lastMonthWithData = 0;

        for ($mes = 1; $mes <= 12; $mes++) {
            $ingresosMes = (float) ($incomeByMonth[$mes] ?? 0);
            $egresosMes  = (float) ($expenseByMonth[$mes] ?? 0);

            $incomeSeries[]  = $ingresosMes;
            $expenseSeries[] = $egresosMes;
            $balanceSeries[] = $ingresosMes - $egresosMes;

            $runningIncome  += $ingresosMes;
            $runningExpense += $egresosMes;
            $incomeAccumulated[]  = $runningIncome;
            $expenseAccumulated[] = $runningExpense;

            if ($ingresosMes > 0 || $egresosMes > 0) {
                $lastMonthWithData = $mes;
            }
        }

        for ($mes = 1; $mes <= 12; $mes++) {
            if ($mes <= $lastMonthWithData && $mes > 0) {
                $incomeProjectionSeries[]  = round(($incomeAccumulated[$mes - 1] / $mes) * 12, 2);
                $expenseProjectionSeries[] = round(($expenseAccumulated[$mes - 1] / $mes) * 12, 2);
            } else {
                $incomeProjectionSeries[]  = null;
                $expenseProjectionSeries[] = null;
            }
        }

        $totalIncome  = array_sum($incomeSeries);
        $totalExpense = array_sum($expenseSeries);
        $balance      = $totalIncome - $totalExpense;

        // Top items for the year
        $topIncomeItems = ManagementIncomeEntityAmount::where('gestion', $year)
            ->when($incomeEntity, fn($q) => $q->where('entity_id', $incomeEntity))
            ->selectRaw('item, SUM(amount) as total')
            ->groupBy('item')
            ->orderByDesc('total')
            ->limit(10)
            ->pluck('total', 'item');

        if ($topIncomeItems->isEmpty() && !$incomeEntity) {
            $topIncomeItems = ManagementIncome::where('gestion', $year)
                ->selectRaw('item, SUM(income_amount) as total')
                ->groupBy('item')
                ->orderByDesc('total')
                ->limit(10)
                ->pluck('total', 'item');
        }

        $topExpenseItems = ManagementExpenseEntityAmount::where('gestion', $year)
            ->when($expenseEntity, fn($q) => $q->where('entity_id', $expenseEntity))
            ->selectRaw('item, SUM(amount) as total')
            ->groupBy('item')
            ->orderByDesc('total')
            ->limit(10)
            ->pluck('total', 'item');

        if ($topExpenseItems->isEmpty() && !$expenseEntity) {
            $topExpenseItems = ManagementExpense::where('gestion', $year)
                ->selectRaw('item, SUM(expense_amount) as total')
                ->groupBy('item')
                ->orderByDesc('total')
                ->limit(10)
                ->pluck('total', 'item');
        }

        // Expense by category (totals)
        $expenseByCategory = ManagementExpenseEntityAmount::where('gestion', $year)
            ->when($expenseEntity, fn($q) => $q->where('entity_id', $expenseEntity))
            ->selectRaw("COALESCE(NULLIF(category,''), 'operativo') as cat, SUM(amount) as total")
            ->groupBy('cat')
            ->pluck('total', 'cat');

        // Expense by category per month (for stacked bar)
        $expenseCategoryMonthly = ManagementExpenseEntityAmount::where('gestion', $year)
            ->when($expenseEntity, fn($q) => $q->where('entity_id', $expenseEntity))
            ->selectRaw("COALESCE(NULLIF(category,''), 'operativo') as cat, mes, SUM(amount) as total")
            ->groupBy('cat', 'mes')
            ->get();

        $categoryMonthlyData = ['operativo' => array_fill(1, 12, 0), 'otro' => array_fill(1, 12, 0)];
        foreach ($expenseCategoryMonthly as $row) {
            $cat = $row->cat === 'operativo' ? 'operativo' : 'otro';
            $categoryMonthlyData[$cat][$row->mes] = (float) $row->total;
        }

        return view('dashboard.income-expense', [
            'year'            => $year,
            'availableYears'  => $this->getAvailableYears($year),
            'incomeEntities'  => $incomeEntities,
            'expenseEntities' => $expenseEntities,
            'incomeEntity'    => $incomeEntity,
            'expenseEntity'   => $expenseEntity,
            'expenseByCategory'     => $expenseByCategory,
            'categoryMonthlyData'   => $categoryMonthlyData,
            'totalIncome'    => $totalIncome,
            'totalExpense'   => $totalExpense,
            'balance'        => $balance,
            'chartData'      => [
                'months'                  => $months,
                'incomeSeries'            => $incomeSeries,
                'expenseSeries'           => $expenseSeries,
                'balanceSeries'           => $balanceSeries,
                'incomeAccumulated'       => $incomeAccumulated,
                'expenseAccumulated'      => $expenseAccumulated,
                'incomeProjectionSeries'  => $incomeProjectionSeries,
                'expenseProjectionSeries' => $expenseProjectionSeries,
                'lastMonthWithData'       => $lastMonthWithData,
            ],
            'topIncomeItems'  => $topIncomeItems,
            'topExpenseItems' => $topExpenseItems,
        ]);
    }

    private function getAvailableYears(int $selectedYear): array
    {
        $incomeYears  = ManagementIncome::select('gestion')->distinct()->pluck('gestion')->toArray();
        $expenseYears = ManagementExpense::select('gestion')->distinct()->pluck('gestion')->toArray();

        $years = array_unique(array_merge($incomeYears, $expenseYears, [$selectedYear]));
        $years = array_filter($years, fn($year) => !is_null($year));

        rsort($years);

        return array_values($years);
    }
}
