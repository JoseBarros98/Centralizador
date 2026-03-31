<?php

namespace App\Http\Controllers;

use App\Models\ManagementExpense;
use App\Models\ManagementIncome;
use App\Models\ManagementInvestment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IncomeExpenseDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $year = (int) $request->get('year', date('Y'));

        $incomeByMonth = ManagementIncome::where('gestion', $year)
            ->selectRaw('mes, SUM(income_amount) as total')
            ->groupBy('mes')
            ->pluck('total', 'mes');

        $expenseByMonth = ManagementExpense::where('gestion', $year)
            ->selectRaw('mes, SUM(expense_amount) as total')
            ->groupBy('mes')
            ->pluck('total', 'mes');

        $investmentByMonth = ManagementInvestment::where('gestion', $year)
            ->selectRaw('mes, SUM(investment_amount) as total')
            ->groupBy('mes')
            ->pluck('total', 'mes');

        $months = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        $incomeSeries = [];
        $expenseSeries = [];
        $expenseOnlySeries = [];
        $investmentSeries = [];
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
            $gastosMes = (float) ($expenseByMonth[$mes] ?? 0);
            $inversionesMes = (float) ($investmentByMonth[$mes] ?? 0);
            $egresosMes = $gastosMes + $inversionesMes;

            $incomeSeries[] = $ingresosMes;
            $expenseSeries[] = $egresosMes;
            $expenseOnlySeries[] = $gastosMes;
            $investmentSeries[] = $inversionesMes;

            $balanceSeries[] = $ingresosMes - $egresosMes;

            $runningIncome += $ingresosMes;
            $runningExpense += $egresosMes;
            $incomeAccumulated[] = $runningIncome;
            $expenseAccumulated[] = $runningExpense;

            if ($ingresosMes > 0 || $egresosMes > 0) {
                $lastMonthWithData = $mes;
            }
        }

        for ($mes = 1; $mes <= 12; $mes++) {
            if ($mes <= $lastMonthWithData && $mes > 0) {
                $incomeProjectionSeries[] = round(($incomeAccumulated[$mes - 1] / $mes) * 12, 2);
                $expenseProjectionSeries[] = round(($expenseAccumulated[$mes - 1] / $mes) * 12, 2);
            } else {
                $incomeProjectionSeries[] = null;
                $expenseProjectionSeries[] = null;
            }
        }

        $totalIncome = array_sum($incomeSeries);
        $totalExpense = array_sum($expenseSeries);
        $totalOperationalExpense = array_sum($expenseOnlySeries);
        $totalInvestment = array_sum($investmentSeries);
        $balance = $totalIncome - $totalExpense;

        return view('dashboard.income-expense', [
            'year' => $year,
            'availableYears' => $this->getAvailableYears($year),
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
            'balance' => $balance,
            'chartData' => [
                'months' => $months,
                'incomeSeries' => $incomeSeries,
                'expenseSeries' => $expenseSeries,
                'balanceSeries' => $balanceSeries,
                'incomeAccumulated' => $incomeAccumulated,
                'expenseAccumulated' => $expenseAccumulated,
                'incomeProjectionSeries' => $incomeProjectionSeries,
                'expenseProjectionSeries' => $expenseProjectionSeries,
                'lastMonthWithData' => $lastMonthWithData,
                'expenseComposition' => [
                    'labels' => ['Gastos', 'Inversiones'],
                    'values' => [$totalOperationalExpense, $totalInvestment],
                ],
            ],
        ]);
    }

    private function getAvailableYears(int $selectedYear): array
    {
        $incomeYears = ManagementIncome::select('gestion')->distinct()->pluck('gestion')->toArray();
        $expenseYears = ManagementExpense::select('gestion')->distinct()->pluck('gestion')->toArray();
        $investmentYears = ManagementInvestment::select('gestion')->distinct()->pluck('gestion')->toArray();

        $years = array_unique(array_merge($incomeYears, $expenseYears, $investmentYears, [$selectedYear]));
        $years = array_filter($years, fn ($year) => !is_null($year));

        rsort($years);

        return array_values($years);
    }
}
