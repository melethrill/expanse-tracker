<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        // Get total expenses grouped per category for authenticated user
        $categoryExpenses = Category::select('categories.id', 'categories.name')
            ->selectRaw('COALESCE(SUM(transactions.amount), 0) as total_cents')
            ->leftJoin('transactions', function ($join) use ($user) {
                $join->on('categories.id', '=', 'transactions.category_id')
                    ->where('transactions.user_id', '=', $user->id)
                    ->where('transactions.type', '=', 'expense');
            })
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('categories.name')
            ->get();

        $totalExpenseCents = $categoryExpenses->sum('total_cents');

        return view('reports.index', compact('categoryExpenses', 'totalExpenseCents'));
    }
}
