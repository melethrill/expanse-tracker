@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Daily Income & Expenses</h2>
    <a href="{{ route('transactions.create') }}" class="btn btn-primary">Add Transaction</a>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        @if($transactions->isEmpty())
            <div class="p-4 text-center text-muted">No transactions logged yet.</div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Payment Method</th>
                            <th>Category</th>
                            <th>Description</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactions as $transaction)
                            <tr onclick="window.location='{{ route('transactions.edit', $transaction) }}'" style="cursor: pointer;">
                                <td>{{ $transaction->date->format('d/m/Y') }}</td>
                                <td>
                                    <span class="badge {{ $transaction->type === 'income' ? 'bg-success' : 'bg-warning text-dark' }}">
                                        {{ ucfirst($transaction->type) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $transaction->transaction_type === 'card' ? 'bg-info' : 'bg-secondary' }}">
                                        {{ ucfirst($transaction->transaction_type) }}
                                    </span>
                                </td>
                                <td>{{ $transaction->category->name }}</td>
                                <td>{{ $transaction->description ?? '-' }}</td>
                                <td class="fw-bold">
                                    €{{ number_format($transaction->amount / 100, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($transactions->hasPages())
                <div class="p-3">
                    {{ $transactions->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
