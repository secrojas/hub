<?php

namespace App\Services\Finance;

use App\Models\Finance\BankAccount;
use App\Models\Finance\FixedExpense;
use App\Models\Finance\Payslip;
use App\Models\Finance\VariableExpense;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class FinanceSummaryService
{
    public function getDashboardData(): array
    {
        $accounts = BankAccount::with(['latestBalance'])
            ->where('activo', true)
            ->orderBy('orden')
            ->get();

        $totalSaldo = $accounts->sum(fn ($a) => (float) ($a->latestBalance?->monto ?? 0));

        $lastPayslip = Payslip::orderByDesc('periodo')->first();

        $fixedExpenses = FixedExpense::where('activo', true)
            ->orderBy('categoria')
            ->orderBy('nombre')
            ->get();

        $totalFijos = $fixedExpenses->sum(fn ($e) => (float) $e->monto);

        $now = Carbon::now();
        $variableExpenses = VariableExpense::whereYear('fecha', $now->year)
            ->whereMonth('fecha', $now->month)
            ->orderByDesc('fecha')
            ->get();

        $totalVariables = $variableExpenses->sum(fn ($e) => (float) $e->monto);

        return [
            'accounts'          => $accounts,
            'total_saldo'       => $totalSaldo,
            'last_payslip'      => $lastPayslip,
            'fixed_expenses'    => $fixedExpenses,
            'total_fijos'       => $totalFijos,
            'variable_expenses' => $variableExpenses,
            'total_variables'   => $totalVariables,
            'saldo_disponible'  => $totalSaldo - $totalVariables,
            'mes_actual'        => $now->format('Y-m'),
        ];
    }
}
