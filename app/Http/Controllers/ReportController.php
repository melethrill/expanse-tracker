<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();

        $selectedYear = $request->input('year');
        $selectedMonth = $request->input('month');

        // Available years from user's transactions (or current year as fallback)
        $availableYears = $user->transactions()
            ->selectRaw('DISTINCT strftime("%Y", date) as year')
            ->pluck('year')
            ->filter()
            ->map(fn ($y) => (int) $y)
            ->sortDesc()
            ->values()
            ->all();

        if (empty($availableYears)) {
            $availableYears = [(int) date('Y')];
        }

        // Get total expenses grouped per category for authenticated user with year/month filter
        $categoryExpenses = Category::select('categories.id', 'categories.name')
            ->selectRaw('COALESCE(SUM(transactions.amount), 0) as total_cents')
            ->leftJoin('transactions', function ($join) use ($user, $selectedYear, $selectedMonth) {
                $join->on('categories.id', '=', 'transactions.category_id')
                    ->where('transactions.user_id', '=', $user->id)
                    ->where('transactions.type', '=', 'expense');

                if (!empty($selectedYear)) {
                    $join->whereRaw('strftime("%Y", transactions.date) = ?', [(string) $selectedYear]);
                }

                if (!empty($selectedMonth)) {
                    $join->whereRaw('strftime("%m", transactions.date) = ?', [sprintf('%02d', (int) $selectedMonth)]);
                }
            })
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('categories.name')
            ->get();

        $totalExpenseCents = $categoryExpenses->sum('total_cents');

        $months = [
            1 => 'January',
            2 => 'February',
            3 => 'March',
            4 => 'April',
            5 => 'May',
            6 => 'June',
            7 => 'July',
            8 => 'August',
            9 => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December',
        ];

        // Query expense transactions for the selected period
        $transactionsQuery = $user->transactions()
            ->with('category')
            ->where('type', 'expense')
            ->orderBy('date', 'desc');

        if (!empty($selectedYear)) {
            $transactionsQuery->whereRaw('strftime("%Y", date) = ?', [(string) $selectedYear]);
        }

        if (!empty($selectedMonth)) {
            $transactionsQuery->whereRaw('strftime("%m", date) = ?', [sprintf('%02d', (int) $selectedMonth)]);
        }

        $transactions = $transactionsQuery->get();

        return view('reports.index', compact(
            'categoryExpenses',
            'totalExpenseCents',
            'availableYears',
            'months',
            'selectedYear',
            'selectedMonth',
            'transactions'
        ));
    }
}
