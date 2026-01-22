<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FinancialTransaction;
use App\Models\RecurringPayment;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Carbon\Carbon;

class FinancialDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);
        
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        // Summary cards
        $totalPayable = FinancialTransaction::payable()->pending()->byPeriod($startDate, $endDate)->sum('amount');
        $totalReceivable = FinancialTransaction::receivable()->pending()->byPeriod($startDate, $endDate)->sum('amount');
        $totalPaid = FinancialTransaction::payable()->paid()->whereBetween('payment_date', [$startDate, $endDate])->sum('amount');
        $totalReceived = FinancialTransaction::receivable()->paid()->whereBetween('payment_date', [$startDate, $endDate])->sum('amount');
        
        // Overdue
        $overduePayable = FinancialTransaction::payable()->overdue()->sum('amount');
        $overdueReceivable = FinancialTransaction::receivable()->overdue()->sum('amount');
        $overdueCount = FinancialTransaction::overdue()->count();

        // Due soon (next 7 days)
        $dueSoon = FinancialTransaction::pending()
            ->whereBetween('due_date', [now(), now()->addDays(7)])
            ->orderBy('due_date')
            ->limit(10)
            ->get();

        // Recent transactions
        $recentTransactions = FinancialTransaction::with(['category', 'supplier'])
            ->latest()
            ->limit(10)
            ->get();

        // Recurring payments due soon
        $recurringDueSoon = RecurringPayment::active()
            ->dueSoon(14)
            ->with('category')
            ->orderBy('next_due_date')
            ->limit(5)
            ->get();

        // Monthly chart data
        $chartData = $this->getMonthlyChartData($year);

        return view('admin.financial.dashboard', compact(
            'totalPayable',
            'totalReceivable',
            'totalPaid',
            'totalReceived',
            'overduePayable',
            'overdueReceivable',
            'overdueCount',
            'dueSoon',
            'recentTransactions',
            'recurringDueSoon',
            'chartData',
            'month',
            'year'
        ));
    }

    private function getMonthlyChartData(int $year): array
    {
        $months = [];
        $expenses = [];
        $income = [];

        for ($i = 1; $i <= 12; $i++) {
            $startDate = Carbon::create($year, $i, 1)->startOfMonth();
            $endDate = Carbon::create($year, $i, 1)->endOfMonth();

            $months[] = $startDate->translatedFormat('M');
            $expenses[] = (float) FinancialTransaction::payable()->paid()
                ->whereBetween('payment_date', [$startDate, $endDate])
                ->sum('amount');
            $income[] = (float) FinancialTransaction::receivable()->paid()
                ->whereBetween('payment_date', [$startDate, $endDate])
                ->sum('amount');
        }

        return [
            'labels' => $months,
            'expenses' => $expenses,
            'income' => $income,
        ];
    }
}
