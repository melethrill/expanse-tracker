@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white"><h4 class="mb-0">Edit Transaction</h4></div>
            <div class="card-body">
                <form id="edit-transaction-form" method="POST" action="{{ route('transactions.update', $transaction) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="type" class="form-label">Expense Type</label>
                        <select name="type" id="type" class="form-select" required>
                            <option value="expense" {{ old('type', $transaction->type) == 'expense' ? 'selected' : '' }}>Expense</option>
                            <option value="income" {{ old('type', $transaction->type) == 'income' ? 'selected' : '' }}>Income</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="transaction_type" class="form-label">Payment Method / Transaction Type</label>
                        <select name="transaction_type" id="transaction_type" class="form-select" required>
                            <option value="card" {{ old('transaction_type', $transaction->transaction_type) == 'card' ? 'selected' : '' }}>Card</option>
                            <option value="cash" {{ old('transaction_type', $transaction->transaction_type) == 'cash' ? 'selected' : '' }}>Cash</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="category_id" class="form-label">Category</label>
                        <select name="category_id" id="category_id" class="form-select" required>
                            <option value="">Select a Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id', $transaction->category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="amount" class="form-label">Amount (€)</label>
                        <input type="number" step="0.01" min="0.01" name="amount" id="amount" class="form-control" value="{{ old('amount', number_format($transaction->amount / 100, 2, '.', '')) }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="date" class="form-label">Date</label>
                        <input type="date" name="date" id="date" class="form-control" value="{{ old('date', $transaction->date->format('Y-m-d')) }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description (Optional)</label>
                        <textarea name="description" id="description" class="form-control" rows="3">{{ old('description', $transaction->description) }}</textarea>
                    </div>
                </form>

                <div class="d-flex justify-content-between align-items-center mt-4">
                    <form method="POST" action="{{ route('transactions.destroy', $transaction) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this transaction?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>

                    <div class="d-flex gap-2">
                        <a href="{{ route('transactions.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" form="edit-transaction-form" class="btn btn-primary">Save</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
