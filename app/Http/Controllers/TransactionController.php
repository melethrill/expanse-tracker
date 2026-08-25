<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function index(): View
    {
        $transactions = Auth::user()
            ->transactions()
            ->with('category')
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('transactions.index', compact('transactions'));
    }

    public function create(): View
    {
        $categories = Category::orderBy('name')->get();

        return view('transactions.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'type' => ['required', 'in:income,expense'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'description' => ['nullable', 'string', 'max:1000'],
            'date' => ['required', 'date'],
        ]);

        $amountInCents = (int) round(((float) $validated['amount']) * 100);

        Auth::user()->transactions()->create([
            'category_id' => $validated['category_id'],
            'type' => $validated['type'],
            'amount' => $amountInCents,
            'description' => $validated['description'] ?? null,
            'date' => $validated['date'],
        ]);

        return redirect()->route('transactions.index')->with('success', 'Transaction created successfully.');
    }

    public function edit(Transaction $transaction): View
    {
        $this->authorizeTransaction($transaction);
        $categories = Category::orderBy('name')->get();

        return view('transactions.edit', compact('transaction', 'categories'));
    }

    public function update(Request $request, Transaction $transaction): RedirectResponse
    {
        $this->authorizeTransaction($transaction);

        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'type' => ['required', 'in:income,expense'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'description' => ['nullable', 'string', 'max:1000'],
            'date' => ['required', 'date'],
        ]);

        $amountInCents = (int) round(((float) $validated['amount']) * 100);

        $transaction->update([
            'category_id' => $validated['category_id'],
            'type' => $validated['type'],
            'amount' => $amountInCents,
            'description' => $validated['description'] ?? null,
            'date' => $validated['date'],
        ]);

        return redirect()->route('transactions.index')->with('success', 'Transaction updated successfully.');
    }

    public function destroy(Transaction $transaction): RedirectResponse
    {
        $this->authorizeTransaction($transaction);

        $transaction->delete();

        return redirect()->route('transactions.index')->with('success', 'Transaction deleted successfully.');
    }

    private function authorizeTransaction(Transaction $transaction): void
    {
        if ($transaction->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
    }
}
